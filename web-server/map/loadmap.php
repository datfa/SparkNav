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

		function draw_line(path, name) {
			//console.log( "START: ", path.start.x, ",", path.start.y);
			//console.log( "END: ", path.end.x, ",", path.end.y);
			line_path = "M " + path.start.x + "," + path.start.y + " L " + path.end.x + "," + path.end.y;
			line = paper.path( line_path );
			line.attr( { 'stroke-width':'20', "stroke":lineColor } );

			lastCircle = paper.circle(path.start.x, path.start.y, 15);
			lastCircle.customAttr = { r:10, fill:lineColor };
			lastCircle.attr("fill", "#f00");
			lastCircle.attr("stroke", "#000");
			lastCircle.toFront();

			pt = paper.text(path.start.x, path.start.y, "L-" + path.start.ID);
			pt.attr( "fill", "#0000FF" );
			pt.attr({ "font-size": 12, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });

			lastCircle = paper.circle(path.end.x, path.end.y, 15);
			lastCircle.customAttr = { r:10, fill:lineColor };
			lastCircle.attr("fill", "#f00");
			lastCircle.attr("stroke", "#000");
			lastCircle.toFront();

			pt = paper.text(path.end.x, path.end.y, "L-" + path.end.ID);
			pt.attr( "fill", "#0000FF" );
			pt.attr({ "font-size": 12, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });


		}

		function loadMap() {

			console.log("loadMap() => called");

			reset();

			//next line "?callback=?" is used for jsonp
			$.getJSON("http://localhost:8080/map?callback=?", function(result) {
			   //alert(JSON.parse(result));
			   var resp = JSON.parse(result);
			   for (var i = 0; i < resp.length; i++) {
			   		draw_line( resp[i] );
			   }
			});

			/*$.ajax({
				url: "http://localhost:8080/map",
				type: "GET",
				dataType: "jsonp",
				success: function (response) {
					var resp = JSON.parse(response);
					//alert(resp.status);
					//alert(resp);
				},
				error: function (xhr, status) {
					alert("error " + status);
  					//alert(xhr.responseText);
				}
			});*/

			/*$.get( "http://localhost:8080/map", function( data ) {
				//alert( "Data Loaded: " + data );
				//console.log(data);
				var p = $.parseJSON(data);
				console.log("RESPONSE: ", p);
			});*/

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