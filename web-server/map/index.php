<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script src="lib/jquery.js" type="text/javascript"></script>
	<script src="lib/raphael.min.js" type="text/javascript"></script>
	<style type="text/css">

	.map-container {
		width:1000px;
		height: 800px;
		position: relative;
		margin: 25px auto;
		border: 1px solid #EEE2E2;
		z-index: 5px;
	}
	.control {
		width:600px;
		height: 50px;
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
		left: 0;
		overflow: auto;
		position: absolute;
		top: 24px;
		width: 447px;
	}
	</style>
</head>
<body>
	<div id="map-container" class="map-container">
		<img class="map-image" src="img/map.png" alt="" height=800 width=1000 />
	</div>
	<div class="control">
		<button type="button" id="newpath">New Path</button>
		<button type="button" id="resetmap">Reset map</button>
		<button type="button" id="playmap">Play map</button>
	</div>
	<div class="console">

	</div>
	<script type="text/javascript">
		var map = [];
		var mapPath = 0;
		var mapPathColor = ["#FFC107","#8BC34A","#9C27B0","#E91E63","#F44336","#F44336","#4CAF50","#009688","#00BCD4","#03A9F4","#2196F3"];
		var paper = null;
		var points = [];
		var currentIndex = 0;
		var path = "";
		var lastCircle = null;
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

		function playMap() {
			var newmap = map;
			reset();
			var p = newmap[0];
			var l = 0;
			var len = p.length;
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

		function newPath(){
			mapPath++;
			points = [];
			currentIndex = 0;
			path = "";
			line = null;
			lineColor = mapPathColor[ mapPath % 4 ];
		}

		function push_path(x, y, isNoCricle) {
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
					e = e || window.event;

					if ("which" in e) { // Gecko (Firefox), WebKit (Safari/Chrome) & Opera
						isRightMB = e.which == 3;
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
						push_path(this.attrs.cx,this.attrs.cy,true);
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

			paper=Raphael(document.getElementById('map-container'), 1000, 800);
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
		});

	</script>

 </body>
 </html>