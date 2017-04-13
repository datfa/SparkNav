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

/*function get_neighbors (src, dst, route) {
	var all_paths = [];
	var bidirectional_path = [];
	var neighbors = [];

	console.log("SRC: ", src);

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

		console.log("BI-PATH: ", JSON.stringify(bidirectional_path));
		
		for(var j in bidirectional_path) {
			if( bidirectional_path[j].loc_start == src ) {
				neighbors.push( bidirectional_path[j].loc_end );	
			}			
		}

		for(var k in neighbors) {
			if( neighbors[k] == dst ) {
				path_found(route);
			} else {
				route.push( neighbors[k] );
				get_neighbors(src, dst, route);
			}
		}

		//console.log("NEIGHBOR: ", JSON.stringify(neighbors));
		
	});
	
}*/
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

