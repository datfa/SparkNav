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
		margin: 25px auto;
		border: 1px solid #EEE2E2;
		z-index: 5px;
	}
	.control {
		width:600px;
		height: 100px;
		position: relative;
		margin: 25px auto;
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
	.console {
		background: #ccc none repeat scroll 0 0;
		height: 425px;
		left: 400px;
		overflow: auto;
		position: absolute;
		top: 1500px;
		width: 447px;
	}
	</style>
</head>
<body>
	<div id="map-container" class="map-container">
		<img class="map-image" src="img/EC2.png" alt="" height=1350 width=900 />
	</div>
	<div class="control">
		<button type="button" id="newpath">New Path</button>
		<button type="button" id="resetmap">Reset map</button>
		<button type="button" id="playmap">Play map</button>
		<button type="button" id="savemap">Save map</button>
		<button type="button" id="loadmap">Load map</button>
	</div>
	<div class="console">

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
			displayPath();
		}

		function displayPath() {
			$(".console").html("");
			for(var i in map) {
				//console.log( map[i] );
				var pathnumber = 1 * i + 1;
				$(".console").append( "Path " + pathnumber + "<br/>" );
				$(".console").append( JSON.stringify( map[i] ) + "<br/>" );
			}
		}

		function drawEmergency (x,y) {
			console.log("Draw a fire circle");
 			paper.image("img/fire.png", x, y, 50, 50);
		}

		function playMap() {
			var newmap = map;
			reset();
			var p = newmap[0];
			var l = 0;
			var len = p.length;
			console.log(p);
			action = function() {
				if (l < len) {
					var obj = p[l];
					console.log(obj.x, obj.y);
					push_path(obj.x, obj.y);
					l++;
					setTimeout(action, 400);
				}
			};
			setTimeout(action, 400);
		}

		function saveMap() {
			var newmap = map;
			var p = newmap[0];

			for(var i in newmap) {
				var obj = newmap[i];
				//console.log("map",i,JSON.stringify(obj));
				$.ajax({
				type: "POST",
				url: "savemap.php",
				data: {"data":JSON.stringify(obj)},
				dataType: "json",
				success: function(content) {
					alert(content);
				}
			});
			 }

			return;

			/*$.ajax({
				type: "POST",
				url: "savemap.php",
				data: {"data":JSON.stringify(p)},
				dataType: "json",
				success: function(content) {
					alert(content);
				}
			});*/
		}

		function saveSensorData(x,y,id) {

			console.log("Submit => saveSensorData: ", x, ",", y, " -> ", id );

			var p = {x:x, y:y, id:id};

			$.ajax({
				type: "POST",
				url: "savesensor.php",
				data: {"sensor":JSON.stringify(p)},
				dataType: "json",
				success: function(content) {
					alert(content);
				}
			});
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
					if (l <= len) {
						var obj = p[l];
						//console.log(obj.x, obj.y);
						if( obj.x == 0 && obj.y == 0 ) {
							//skip (0,0)
						} else {
							//push_path(obj.x, obj.y);
							var pathStr = "draw_path: " + obj.x + ", " + obj.y + " -> " + obj.name;
							//console.log(pathStr);

							draw_path(obj.x, obj.y, obj.name, true);
						}
						l++;
						setTimeout(action, 400);
					}
				};
				setTimeout(action, 400);
			});
		}

		function makeCircle (x,y) {
			console.log("====> Draw a circle");
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
					newCircle = paper.circle(obj.x,obj.y, 12);
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

		function draw_path(x, y, name, isNoCricle) {
			if( typeof(isNoCricle) == "undefined" ) {
				isNoCricle = false;
			}
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
				line.attr( { 'stroke-width':'10', "stroke":lineColor } );
			//#437DCC
			}

			//circle generation
			if(!isNoCricle) {
				if( currentIndex == 0 ) {
					lastCircle = paper.circle( x, y, 12);
					lastCircle.attr("fill", "#0AA332");
					lastCircle.attr("stroke", "#000");
					lastCircle.customAttr = {r:10,fill:lineColor};
				} else {
					if( currentIndex > 1 || autoSelect ) {
						//lastCircle.attr("fill", "#77F899");
						lastCircle.toFront();
						lastCircle.animate({
							r : lastCircle.customAttr.r,
							fill : lastCircle.customAttr.fill,
						}, 200);
					}

					lastCircle = paper.circle(x,y, 12);
					lastCircle.customAttr = { r:10, fill:lineColor };
					lastCircle.attr("fill", "#f00");
					lastCircle.attr("stroke", "#000");
				}
			} else {
				autoSelect = true;
			}

			currentIndex++;
			map[mapPath] = points;
			//console.log(JSON.stringify( points ) );
		}


		function push_path(x, y, isNoCricle) {
			console.log("push_path: " , x, ",", y );
			if( typeof(isNoCricle) == "undefined" ) {
				isNoCricle = false;
			}
			x = Math.round(x);
			y = Math.round(y);
			points[currentIndex] = {x:x, y:y};

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
				line.attr( { 'stroke-width':'6', "stroke":lineColor } );
			//#437DCC
			}

			//circle generation
			if(!isNoCricle) {
				if( currentIndex == 0 ) {
					lastCircle = paper.circle( x, y, 12);
					lastCircle.attr("fill", "#0AA332");
					lastCircle.attr("stroke", "#000");
					lastCircle.customAttr = {r:10,fill:lineColor};
				} else {
					if( currentIndex > 1 || autoSelect ) {
						//lastCircle.attr("fill", "#77F899");
						lastCircle.toFront();
						lastCircle.animate({
							r : lastCircle.customAttr.r,
							fill : lastCircle.customAttr.fill,
						}, 200);
					}

					lastCircle = paper.circle(x,y, 12);
					lastCircle.customAttr = { r:10, fill:lineColor };
					lastCircle.attr("fill", "#f00");
					lastCircle.attr("stroke", "#000");
				}

				lastCircle.mousedown(function(e) {
					var isRightMB;
					var isLeftMB;
					e = e || window.event;

					if ("which" in e) { // Gecko (Firefox), WebKit (Safari/Chrome) & Opera
						isRightMB = e.which == 3;
						isLeftMB = e.which == 1;
					}else if ("button" in e) { // IE, Opera
						isRightMB = e.button == 2;
					}

					if(isRightMB && (this.attrs.cx!=lastCircle.attrs.cx || this.attrs.cy!=lastCircle.attrs.cy)) {
						e.preventDefault();
						e.stopPropagation();
						lastCircle.animate({
							r : lastCircle.customAttr.r,
							fill : lastCircle.customAttr.fill,
						},200);
						newPath();
						lastCircle=this;
						lastCircle.animate({
							r : 12,
							fill : "#f00",
						},200);

						//var sensor = prompt("Please enter sensor ID", "Sensor ID");
						//saveSensorData(this.attrs.cx, this.attrs.cy, sensor);

						push_path(this.attrs.cx,this.attrs.cy,true);
					}

					if(isLeftMB && (this.attrs.cx!=lastCircle.attrs.cx || this.attrs.cy!=lastCircle.attrs.cy)) {
						e.preventDefault();
						e.stopPropagation();
						lastCircle.animate({
							r : lastCircle.customAttr.r,
							fill : lastCircle.customAttr.fill,
						},200);
						lastCircle=this;
						lastCircle.animate({
							r : 12,
							fill : "#f00",
						},200);

						//var sensor = prompt("Please enter sensor ID", "Sensor ID");
						//saveSensorData(this.attrs.cx, this.attrs.cy, sensor);

						//push_path(this.attrs.cx,this.attrs.cy,true);
						push_path(lastCircle.attrs.cx,lastCircle.attrs.cy,true);
						console.log(lastCircle.attrs.cx, ", " ,lastCircle.attrs.cy);
						displayPath();
					}
				});

			} else {
				autoSelect = true;
			}

			currentIndex++;
			map[mapPath] = points;
		}


		$(document).ready(function() {
			window.oncontextmenu = function () {
				return false;     // cancel default menu
			};
			$("#newpath").click(function(event) {
				event.preventDefault();
				newPath();
			});
			$("#resetmap").click(function(event) {
				event.preventDefault();
				reset();
			});
			$("#playmap").click(function(event) {
				event.preventDefault();
				playMap();
			});
			$("#savemap").click(function(event) {
				event.preventDefault();
				saveMap();
			});

			$("#loadmap").click(function(event) {
				event.preventDefault();
				loadMap();
			});

			paper=Raphael(document.getElementById('map-container'), 900, 1350);
			paper.canvas.className.baseVal="mapersvg";

			$("#map-container").on("mousedown",function(e) {
				e.preventDefault();
				var offset = $(this).offset();
				tempStartPosition = { x:e.pageX - offset.left, y:e.pageY - offset.top };
				push_path( tempStartPosition.x, tempStartPosition.y );
				displayPath();
			});


	        /* $("#map-container").on("mouseup",function(e){
	        e.preventDefault();
	        console.log("Mouse up");

	        var offset = $(this).offset();
	        tempEndPosition={x:e.pageX - offset.left,y:e.pageY - offset.top};

	        var circle = paper.circle(tempEndPosition.x, tempEndPosition.y, 10);
	        circle.attr("fill", "#0AA332");
	        // Sets the stroke attribute of the circle to white
	        circle.attr("stroke", "#000");

	        });*/

	        loadMap();
		});

	</script>

 </body>
 </html>