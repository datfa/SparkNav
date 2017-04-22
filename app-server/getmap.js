var express = require('express');
var mysql	= require('mysql');
var bodyParser = require('body-parser')

var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'root123',
  database : 'sparknav'
});

connection.connect();

//var app = express.createServer(); //for ver 2.x
var app = express();

app.use( bodyParser.json() );       // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({     // to support URL-encoded bodies
  extended: true
}));

////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/map', function(req, res){	
	var full_path = [];
	var result_path;

	var query = "SELECT ID, x, y FROM location";
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		result_path = results;
	});

	connection.query('SELECT loc_start, loc_end FROM path', function (error, results, fields) {
		if (error) throw error;
		for(var a in results) {
			var path = {
				start : { },
				end   : { }
			};
			for(var b in result_path) {
				if( result_path[b].ID == results[a].loc_start ) {
					path.start = result_path[b];					
				}
				if( result_path[b].ID == results[a].loc_end ) {
					path.end = result_path[b];					
				}				
			}
			full_path.push(path);
		}
		console.log(JSON.stringify(full_path));
		res.setHeader('Content-Type', 'application/json');
		//res.send(JSON.stringify(full_path));
		res.jsonp(JSON.stringify(full_path));
		//res.end(JSON.stringify(full_path));
	});
	

});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
function check_loop (node, final_path) {
	for(var i in final_path) {
		if( node == final_path[i] ) {
			console.log("LOOP DETECTED: ", node);
			return true;
		}
	} 
	return false;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
function get_neighbors (src, bi_paths, final_path) {
	
	var neighbors = [];

	//console.log("BI-PATH: ", JSON.stringify(bi_paths));
	//console.log("LOCATIONS: ", JSON.stringify(all_locations));	

	for(var j in bi_paths) {
		if( bi_paths[j].loc_start == src ) {
			if( false == check_loop( bi_paths[j].loc_end, final_path ) ) {
				neighbors.push( bi_paths[j].loc_end );				
			}
		}			
	}

	//console.log("NEIGHBOR: ", JSON.stringify(neighbors));	
	return neighbors;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
function do_queries_find_shortest_path (src, dst, res) {
	var all_paths = [];
	var all_locations = [];
	var bidirectional_path = [];	

	src = parseInt(src);
	dst = parseInt(dst);

	var query = "SELECT * FROM path";
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		all_paths = results;

		//console.log("PATH: ", all_paths);

		for(var i in all_paths) {
			var path = all_paths[i];
			//console.log("PATH: ", JSON.stringify(path);
			bidirectional_path.push({loc_start: path.loc_start, loc_end: path.loc_end});
			bidirectional_path.push({loc_start: path.loc_end, loc_end: path.loc_start});
		}
		//console.log("BI-PATH: ", JSON.stringify(bidirectional_path));		
	});

	var query = "SELECT * FROM location";
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		all_locations = results;

		//console.log("RES: ", res);

		console.log("LOCATIONS: ", JSON.stringify(all_locations));
		var final_path = [];
		final_path.push( src );
		shortest_path(src, dst, bidirectional_path, all_locations, final_path, res);
		
	});

}
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
function res_coordinate_path(all_locations, final_path, res) {
	var final_path_with_xy = [];

	for(var i in final_path) {
		for(var j in all_locations) {
			if( all_locations[j].ID == final_path[i] ) {
				final_path_with_xy.push(all_locations[j]);
			}
		}
	}
	res.setHeader('Content-Type', 'application/json');
	res.jsonp(JSON.stringify(final_path_with_xy));
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
function shortest_path (src, dst, bi_paths, all_locations, final_path, res) {
	var neighbors = get_neighbors( src, bi_paths, final_path );	
	console.log("NEIGHBORS: ", JSON.stringify(neighbors), " OF: ", src);

	console.log("CURRENT PATH: ", JSON.stringify(final_path));

	if( neighbors.length <= 0) {
		console.log("NO MORE NEIGHBOR");
		final_path.pop();
		return src;
	}

	for(var i in neighbors) {
		if( neighbors[i] == dst ) {
			console.log("FOUND DESTINASTION: ", neighbors[i]);
			final_path.push( neighbors[i] );
			console.log("COMPLETE PATH: ", JSON.stringify(final_path));
			res_coordinate_path(all_locations, final_path, res);
			return dst;
		} else {
			final_path.push( neighbors[i] );
			var neighbor = shortest_path(neighbors[i], dst, bi_paths, all_locations, final_path, res);
			console.log("NEIGHBOR: ", JSON.stringify(neighbor));
			if( neighbor == dst ) {
				return neighbor;
			}
		}
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.post('/shortest', function(req, res){	
	var src = req.body.src;
	var dst = req.body.dst;

	do_queries_find_shortest_path( src, dst, res );
	//shortest_path( src, dst );

	// res.setHeader('Content-Type', 'application/json');
	// res.send(JSON.stringify(req.body));
		//res.end(JSON.stringify(full_path));

});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/shortest', function(req, res){	
	var src = req.query.src;
	var dst = req.query.dst;
	do_queries_find_shortest_path( src, dst, res );
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/getexits', function(req, res){	

	var query = "SELECT * FROM exits" ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		console.log("getexits ==========> ", JSON.stringify(results));
		res.setHeader('Content-Type', 'application/json');
		res.jsonp(JSON.stringify(results));
	});	
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/getrooms', function(req, res){	
	var loc_id = req.query.loc;
	var room = req.query.room;

	var rooms_persons_events = [];

	var query = "SELECT * FROM room" ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		//console.log(JSON.stringify(results));
		rooms_persons_events = results;
	});	

	var query = "SELECT r.ID, rp.name , r.loc_id FROM room r, room_person rp where r.ID = rp.room_id" ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		rooms_persons_events = rooms_persons_events.concat( results );		
		console.log(JSON.stringify(rooms_persons_events));
		res.setHeader('Content-Type', 'application/json');
		res.jsonp(rooms_persons_events);
	});	
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/saveroom', function(req, res){	
	var loc_id = req.query.loc;
	var data = req.query.data;

	var query = "SELECT * FROM room WHERE loc_id=" + loc_id ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		if( results.length > 0 && results[0].loc_id == loc_id ) {
			var query = "UPDATE room SET name ='" + data + "' WHERE loc_id = " + loc_id;
			connection.query(query);
		} else {
			var query = "INSERT INTO room SET name ='" + data + "', loc_id = " + loc_id;
			connection.query(query);
		}
	});
	
    res.jsonp("ROOM SAVED");
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/saveexit', function(req, res){	
	var loc_id = req.query.loc;
	var data = req.query.data;

	var query = "SELECT * FROM exits WHERE loc_id=" + loc_id ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		if( results.length > 0 && results[0].loc_id == loc_id ) {
			var query = "UPDATE exits SET name ='" + data + "' WHERE loc_id = " + loc_id;
			connection.query(query);
		} else {
			var query = "INSERT INTO exits SET name ='" + data + "', loc_id = " + loc_id;
			connection.query(query);
		}
	});
	
    res.jsonp("EXIT SAVED");
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/savebeacon', function(req, res){	
	var loc_id = req.query.loc;
	var data = req.query.data;

	var query = "SELECT * FROM beacon_location WHERE beacon_id=" + data ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		console.log("THIS BEACON ALLREADY ADDED SO REMOVING FROM PREVIOUS LOCATION: ", data);
		var query = "UPDATE beacon_location SET beacon_id = 0 WHERE beacon_id = " + data;
		connection.query(query);
	});

	var query = "SELECT * FROM beacon_location WHERE loc_id=" + loc_id ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		if( results.length > 0 && results[0].loc_id == loc_id ) {
			var query = "UPDATE beacon_location SET beacon_id =" + data + " WHERE loc_id = " + loc_id;
			connection.query(query);
			console.log("UPDATING BEACON: ", loc_id);
		} else {
			var query = "INSERT INTO beacon_location SET beacon_id =" + data + ", loc_id = " + loc_id;
			connection.query(query);
			console.log("ADDING BEACON: ", loc_id);
		}
	});
	
    res.jsonp("BEACON SAVED");
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/getbeacons', function(req, res){	

	var query = "SELECT ID, name FROM beacon" ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		console.log("getbeacons ===> ", JSON.stringify(results));
		res.setHeader('Content-Type', 'application/json');
		res.jsonp(JSON.stringify(results));
	});	
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/getbeaconlocations', function(req, res){	

	var query = "SELECT b.uid uid, bl.loc_id loc_id FROM beacon_location bl, beacon b WHERE b.ID = bl.beacon_id" ;
	connection.query(query, function (error, results, fields) {
		if (error) throw error;
		console.log("getbeaconlocations ==> ", JSON.stringify(results));
		res.setHeader('Content-Type', 'application/json');
		res.jsonp(JSON.stringify(results));
	});	
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.post('/map', function(req, res) {
    var my_query = "INSERT INTO path SET start_x = " + req.body.x + ", start_y = " + req.body.y;

		connection.query(my_query, function (error, results, fields) {
		if (error) throw error;

		});
    res.end("DATA SAVED");
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.get('/*', function(req, res) {
 res.send('NOT FOUND');
});
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
app.listen(8080);

