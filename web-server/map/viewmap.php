<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script src="lib/jquery.js" type="text/javascript"></script>
	<script src="lib/raphael.min.js" type="text/javascript"></script>
	<style type="text/css">

	.map-container {
		width:650px;
		height: 1100px;
		position: relative;
		margin: 25px auto;
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
		<img class="map-image" src="img/map.png" alt="" height=1100 width=650 />
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
						}
						l++;
						setTimeout(action, 300);
					}
				};
				setTimeout(action, 300);
			});
		}

		function makeCircle (x,y) {
			console.log("Draw a circle");
			newCircle = paper.circle(x,y, 12);
			newCircle.customAttr = { r:10, fill:lineColor };
			newCircle.attr("fill", "#f00");
			newCircle.attr("stroke", "#000");
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
				line.attr( { 'stroke-width':'15', "stroke":lineColor } );
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

			paper=Raphael(document.getElementById('map-container'), 650, 1100);
			paper.canvas.className.baseVal="mapersvg";

	        loadMap();
		});

	</script>

 </body>
 </html>