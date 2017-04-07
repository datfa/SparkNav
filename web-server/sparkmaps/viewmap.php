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
		<img class="map-image" src="img/map.png" alt="" height=1350 width=900 />
	</div>

	<script type="text/javascript">
		var map = [];
		var mapPath = 0;
		var mapPathColor = ["#8BC34A","#FFC107","#9C27B0","#E91E63","#F44336","#F44336","#4CAF50","#009688","#00BCD4","#03A9F4","#2196F3"];
		var paper = null;
		var points = [];
		var currentIndex = 0;
		var path = "";
		var lastCircle = null;
		var newCircle = null;
		var line = null;
		var lineColor = mapPathColor[ mapPath % mapPathColor.length ];
		var autoSelect = false;

		var tx = 0;
		var ty = 0;

		function reset(){
			paper.clear();
			map = [];
			mapPath = 0;
			points = [];
			currentIndex = 0;
			path = "";
			line = null;
			lineColor = mapPathColor[ mapPath % 4 ];
			autoSelect = false;
			lastCircle = null;
		}


		function loadMap() {

			console.log("=====> COOL: loadMap() called");

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

		function drawExit(x,y) {
			console.log("Draw a exit circle");

 			ec = paper.ellipse(x, y, 45, 30);
 			ec.attr("stroke", "#000");
 			ec.attr("fill", "#fF0");
			pt = paper.text(x, y, "EXIT");
			pt.attr( "fill", "#ff0000" );
			pt.attr({ "font-size": 20, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });
		}

		function drawCurrentLocation (sensorId) {
			var location = "drawCurrentLocation: " + sensorId;
			//console.log(location);

			var p = map[0];
			for(var i in p) {
				var obj = p[i];
				var tmpCoordinate = "COORDINATE: " + obj.x + "," + obj.y + " - " + obj.name;
				//console.log(tmpCoordinate);
				if( obj.name == sensorId ) {
					// console.log('COOL FOUND SENSOR ON MAP: ', sensorId);
					deleteCircle();
					newCircle = paper.circle(obj.x,obj.y, 20);
					newCircle.customAttr = { r:10, fill:lineColor };
					newCircle.attr("fill", "#f00");
					newCircle.attr("stroke", "#000");

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
			lineColor = mapPathColor[ mapPath % 4 ];
		}

		function draw_path(x, y, name) {
			x = Math.round(x);
			y = Math.round(y);
			points[currentIndex] = {x:x, y:y, name:name};

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

		$(document).ready(function() {
			window.oncontextmenu = function () {
				return false;     // cancel default menu
			};

			paper=Raphael(document.getElementById('map-container'), 900, 1350);
			paper.canvas.className.baseVal="mapersvg";

	        loadMap();
		});

	</script>

 </body>
 </html>