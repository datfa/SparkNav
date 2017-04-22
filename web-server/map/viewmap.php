<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script src="lib/jquery.js" type="text/javascript"></script>
	<script src="lib/raphael.min.js" type="text/javascript"></script>
	<style type="text/css">

	.map-container {
		width:900px;
		height: 1350px;
		position: relative;
		margin: 20px auto;
		border: 1px solid #EEE2E2;
		z-index: 5px;
	}
	.map-container img {
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		top: 0;
	}
	.mapersvg {
		z-index: 1;
	}

	</style>
</head>
<body>
	<div id="map-container" class="map-container">
		<img class="map-image" src="img/EC2.png" alt="" height=1350 width=900 />
	</div>

	<script type="text/javascript">
		var map = [];
		var mapPath = 0;
		var mapPathColor = ["#8BC34A","#FFC107","#9C27B0","#E91E63","#F44336","#F44336","#4CAF50","#009688","#00BCD4","#03A9F4","#2196F3"];
		var paper = null;
		var points = [];
		var currentIndex = 0;
		var usedIndex = 0;
		var path = "";
		var used_path = "";
		var lastCircle = null;
		var newCircle = null;
		var line = null;
		var used_line = null;
		var lineColor = mapPathColor[ mapPath % mapPathColor.length ];
		var autoSelect = false;
		//var REST_URL = "http://localhost:8080/";
		var REST_URL = "http://" + window.location.hostname + ":8080";

		var tx = 0;
		var ty = 0;

		var beacons = [];

		function reset(){
			paper.clear();
			map = [];
			mapPath = 0;
			points = [];
			currentIndex = 0;
			usedIndex = 0;
			path = "";
			line = null;
			used_line = null;
			lineColor = mapPathColor[ mapPath % 4 ];
			autoSelect = false;
			lastCircle = null;
		}


		function loadMapBeaconExit(beaconId) {
			console.log( "loadMapBeaconExit(", beaconId, ",", dst, ")" );

		    var dst;
			var url = REST_URL + "/getexits?callback=?";

			$.getJSON(url, function(result) {
			    var p = $.parseJSON(result);
			    console.log(" EXITS: ==> ", p);

			    for( var i in p) {
			    	dst = p[i].loc_id; //TODO: now taking the first exit, later make it the closest one
			    	break;
			    }
			    loadMapBeacon (beaconId, dst, true)
			});


		}


		function loadMapBeacon (beaconId, dst, isEmergency) {
			console.log( "loadMapBeacon(", beaconId, ",", dst, ")" );

			var url = REST_URL + "/getbeaconlocations?callback=?";
			$.getJSON(url, function(result) {
			   //alert(JSON.parse(result));
			   //alert(result);
			   //console.log(result);
			   //var resp = JSON.parse(result);
			    var p = $.parseJSON(result);
			    //alert(p);
			    //console.log(p);
			    beacons = p;

			    for(var i in beacons) {
//					console.log("=====> ", beacons[i].uid);
//					console.log(beaconId);
					if( beacons[i].uid.toUpperCase() == beaconId.toUpperCase() ) {
						loadMap( beacons[i].loc_id, dst, isEmergency );
						break;
					}
				}
			});
		}

		function loadMap(src, dst, isEmergency) {

			console.log("loadMap(", src, ",", dst, ")");

			//var url = "http://localhost:8080/shortest?src=62&dst=65&callback=?";
			var url = REST_URL + "/shortest?src=" + src + "&dst=" + dst + "&callback=?";
			$.getJSON(url, function(result) {
			   //alert(JSON.parse(result));
			   //console.log(result);
			   //var resp = JSON.parse(result);
			    var p = $.parseJSON(result);
				//console.log(p);
				reset();
				if( true == isEmergency ) {
					//drawEmergency(330, 1030);
					drawEmergency(67, 710);
					drawEmergency(220, 275);
				}
				var l = 0;
				var len = p.length;

				action = function() {
					if (l < len) {
						var obj = p[l];
						//console.log(obj.x, obj.y);
						if( obj.x == 0 && obj.y == 0 ) {
							//skip (0,0)
						} else {
							var pathStr = "draw_path: " + obj.x + ", " + obj.y + " -> " + obj.ID;
							//console.log(pathStr);

							draw_path(obj.x, obj.y, obj.ID);
							tx = obj.x;
							ty = obj.y;
						}
						l++;
						setTimeout(action, 100);
					} else {
						if( true == isEmergency ) {
							drawExit(tx, ty, "EXIT");
						} else {
							drawExit(tx, ty, "2.311");
							//drawCurrentLocation('0001020304050607081C04514000B000');
						}
					}
				};
				setTimeout(action, 100);
			});
/*
			$.get( "getmap.php", function( data ) {
				//alert( "Data Loaded: " + data );
				//console.log(data);
				var p = $.parseJSON(data);
				console.log(p);

				reset();

				drawEmergency(330, 1030);

				var l = 0;
				var len = p.length;

				action = function() {
					if (l < len) {
						var obj = p[l];
						//console.log(obj.x, obj.y);
						if( obj.x == 0 && obj.y == 0 ) {
							//skip (0,0)
						} else {
							var pathStr = "draw_path: " + obj.x + ", " + obj.y + " -> " + obj.name;
							//console.log(pathStr);

							draw_path(obj.x, obj.y, obj.name);
							tx = obj.x;
							ty = obj.y;
						}
						l++;
						setTimeout(action, 100);
					} else {
						drawExit(tx, ty);
					}
				};
				setTimeout(action, 100);
			});
*/
		}

		function makeCircle (x,y) {
			console.log("Draw a circle");
			newCircle = paper.circle(x,y, 12);
			newCircle.customAttr = { r:10, fill:lineColor };
			newCircle.attr("fill", "#f00");
			newCircle.attr("stroke", "#000");
		}

		function drawEmergency (x,y) {
			console.log("Draw a fire circle");
 			paper.image("img/fire.png", x, y, 90, 130);
		}

		function drawExit(x, y, name) {
			console.log("Draw a exit circle");

 			ec = paper.ellipse(x, y, 45, 30);
 			ec.attr("stroke", "#000");
 			ec.attr("fill", "#fF0");
			pt = paper.text(x, y, name);
			pt.attr( "fill", "#ff0000" );
			pt.attr({ "font-size": 24, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });
		}


		function drawUsingLocation (loc_id) {
			var location = "draw: " + loc_id;
			console.log(location);

			var p = map[0];
			for(var i in p) {
				var obj = p[i];
				if( obj.ID == loc_id ) {
					var tmpCoordinate = "COORDINATE: " + obj.x + "," + obj.y + " - " + obj.ID + " - " + loc_id;
					//console.log(tmpCoordinate);
					// console.log('COOL FOUND SENSOR ON MAP: ', sensorId);
					deleteCircle();
					newCircle = paper.circle(obj.x,obj.y, 20);
					newCircle.customAttr = { r:10, fill:lineColor };
					newCircle.attr("fill", "#f00");
					newCircle.attr("stroke", "#000");

					draw_used_path(obj.x, obj.y, obj.ID);

					if( obj.x == tx && obj.y == ty ) {
						var x = 900/2;
						var y = 1350/2;
						var width_height = 430;
						var width_height_half = width_height / 2;
						paper.image("img/spark_nav.png", x-width_height_half, y-width_height_half, width_height, width_height);
						var rect_height = 55;
						ec2 = paper.rect(x - width_height_half, y + width_height_half, width_height, rect_height);
			 			ec2.attr("stroke", "#000");
			 			ec2.attr("fill", "#F00");
						pt2 = paper.text(x, y + width_height_half + (rect_height / 2), "You have arrived!");
						pt2.attr( "fill", "#FFFF00" );
						pt2.attr({ "font-size": 40, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });
					}
				}
			}
		}


		function drawCurrentLocation (beaconId) {
			var location = "drawCurrentLocation ====> " + beaconId;
			//console.log(location);
			var loc_id;

			if( 1 > beacons.length ) {
				console.log("MAP NOT LOADED");
			}

			for(var i in beacons) {
				//console.log(beaconId, " ==> ", beacons[i].uid);
				if( beacons[i].uid.toUpperCase() == beaconId.toUpperCase() ) {
					//console.log(beaconId, " ==> ", beacons[i].uid);
					loc_id = beacons[i].loc_id;
					break;
				}
			}

			drawUsingLocation (loc_id);

/*
			var p = map[0];
			for(var i in p) {
				var obj = p[i];
				if( obj.ID == loc_id ) {
					var tmpCoordinate = "COORDINATE: " + obj.x + "," + obj.y + " - " + obj.ID + " - " + loc_id;
					console.log(tmpCoordinate);
					// console.log('COOL FOUND SENSOR ON MAP: ', sensorId);
					deleteCircle();
					newCircle = paper.circle(obj.x,obj.y, 20);
					newCircle.customAttr = { r:10, fill:lineColor };
					newCircle.attr("fill", "#f00");
					newCircle.attr("stroke", "#000");

					draw_used_path(obj.x, obj.y, obj.ID);

					if( obj.x == tx && obj.y == ty ) {
						ec2 = paper.ellipse(x, y, 65, 45);
			 			ec2.attr("stroke", "#000");
			 			ec2.attr("fill", "#0F0");
						pt2 = paper.text(x, y, "You have arrived!!");
						pt2.attr( "fill", "#ffFF00" );
						pt2.attr({ "font-size": 20, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });
					}
				}
			}
*/
		}

		function deleteCircle () {
			//console.log("Remove a circle");
			if( newCircle != null )
				newCircle.remove();
		}

		function newPath(){
			console.log('newPath()');
			mapPath++;
			points = [];
			currentIndex = 0;
			path = "";
			line = null;
			used_line = null;
			lineColor = mapPathColor[ mapPath % 4 ];
		}

		function draw_path(x, y, name) {
			x = Math.round(x);
			y = Math.round(y);
			points[currentIndex] = {x:x, y:y, ID:name};

			if(path=="") {
				path = "M " + x + "," + y;
			}else{
				path += " L " + x + "," + y;
			}
			if(line) {
				if( currentIndex > 0 ) {
					line.animate( {path:path}, 200, function() {
						//r.animate({path:"M190 60 L 210 90"}, 2000);
					});
				} else {
					line.attr("path", path);
				}
			} else {
				var path2 = path + " L " + x + "," + y;
				line = paper.path( path2 );
				line.attr( { 'stroke-width':'20', "stroke":lineColor } );
			//#437DCC
			}

			autoSelect = true;

			currentIndex++;
			map[mapPath] = points;
			//console.log(JSON.stringify( points ) );
		}


		function draw_used_path(x, y, name) {
			x = Math.round(x);
			y = Math.round(y);

			if(used_path=="") {
				used_path = "M " + x + "," + y;
			}else{
				used_path += " L " + x + "," + y;
			}
			if(used_line) {
				if( usedIndex > 0 ) {
					used_line.animate( {path:used_path}, 200, function() {
						//r.animate({path:"M190 60 L 210 90"}, 2000);
					});
				} else {
					used_line.attr("path", used_path);
				}
			} else {
				var path2 = used_path + " L " + x + "," + y;
				used_line = paper.path( path2 );
				used_line.attr( { 'stroke-width':'20', "stroke":"#854ed8" } );
			}
			usedIndex++;
		}


		$(document).ready(function() {
			window.oncontextmenu = function () {
				return false;     // cancel default menu
			};

			paper=Raphael(document.getElementById('map-container'), 900, 1350);
			paper.canvas.className.baseVal="mapersvg";

	        //loadMap();
	        //loadMapBeacon('0001020304050607081C04514000b000', 1);
	        //drawCurrentLocation('0001020304050607081C04514000B000');
	        //loadMapBeaconExit('0001020304050607081004514000b000');
	        //loadMap(62, 68)
		});

	</script>

 </body>
 </html>