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

		/* The Modal (background) */
		.modal {
			display: none; /* Hidden by default */
			position: fixed; /* Stay in place */
			z-index: 1; /* Sit on top */
			left: 0;
			top: 0;
			width: 100%; /* Full width */
			height: 100%; /* Full height */
			overflow: auto; /* Enable scroll if needed */
			background-color: rgb(0,0,0); /* Fallback color */
			background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
			-webkit-animation-name: fadeIn; /* Fade in the background */
			-webkit-animation-duration: 0.4s;
			animation-name: fadeIn;
			animation-duration: 0.4s

		}

		/* Modal Content */
		.modal-content {
			position: fixed;
			bottom: 0;
			background-color: #fefefe;
			/*width: 50%;*/
			left: 50%;

			width:30em;
    		margin-left: -15em; /*set to a negative number 1/2 of your width*/

			-webkit-animation-name: slideIn;
			-webkit-animation-duration: 0.4s;
			animation-name: slideIn;
			animation-duration: 0.4s
		}

		/* The Close Button */
		.close {
		    color: white;
		    float: right;
		    font-size: 28px;
		    font-weight: bold;
		}

		.close:hover,
		.close:focus {
		    color: #000;
		    text-decoration: none;
		    cursor: pointer;
		}

		.modal-header {
		    padding: 2px 16px;
		    background-color: #5cb85c;
		    color: white;
		}

		.modal-body {
			padding: 5px 25px;
		}

		.modal-footer {
		    padding: 2px 16px;
		    background-color: #5cb85c;
		    color: white;
		}

		/* Add Animation */
		@-webkit-keyframes slideIn {
		    from {bottom: -300px; opacity: 0}
		    to {bottom: 0; opacity: 1}
		}

		@keyframes slideIn {
		    from {bottom: -300px; opacity: 0}
		    to {bottom: 0; opacity: 1}
		}

		@-webkit-keyframes fadeIn {
		    from {opacity: 0}
		    to {opacity: 1}
		}

		@keyframes fadeIn {
		    from {opacity: 0}
		    to {opacity: 1}
		}


		ul {
		  list-style-type: none;
		  margin: 10px 15px;
		  display:table;
		}

		li {
		  margin: 15px 15px;
		}

	</style>
</head>
<body>
	<div id="map-container" class="map-container">
		<img class="map-image" src="img/map.png" alt="" height=1350 width=900 />
	</div>

	<!-- The Modal -->
	<div id="myModal" class="modal">

	  <!-- Modal content -->
	  <div class="modal-content">
	    <div class="modal-header">
	      <span class="close">&times;</span>
	      <h2>Add/Edit Room</h2>
	    </div>
	    <div id="locations" class="modal-body ">
		    <ul>
			    <li>
			    	<label>Locations:</label>
			    	<select id="location">
					</select>
			    </li>
			    <li>
			    	<label>Exit Name:</label>
			    	<input type="data" name="data"  id="data" />
			    </li>
		    	<li>
		    		<button id="savedata">Save</button>
		    	</li>
		    </ul>
	    </div>
	    <div class="modal-footer">
	      <h3>SparkNav</h3>
	    </div>
	  </div>

	</div>


	<script type="text/javascript">
		var map = [];
		var mapPath = 0;
		var mapPathColor = ["#8BC34A","#FFC107","#9C27B0","#E91E63","#F44336","#F44336","#4CAF50","#009688","#00BCD4","#03A9F4","#2196F3"];
		var paper = null;
		var circles = [];
		var currentIndex = 0;
		var path = "";
		var lastCircle = null;
		var newCircle = null;
		var line = null;
		var lineColor = mapPathColor[ mapPath % mapPathColor.length ];
		var autoSelect = false;

		var tx = 0;
		var ty = 0;

		var modal = null;
		var span = null;

		var all_locations = [];

		function reset(){
			paper.clear();
			map = [];
			mapPath = 0;
			circles = [];
			currentIndex = 0;
			path = "";
			line = null;
			lineColor = mapPathColor[ mapPath % 4 ];
			autoSelect = false;
			lastCircle = null;
		}


		function check_point_already_added (point) {
			for( var i in circles) {
				if( circles[i].circle.attrs.cx == point.x && circles[i].circle.attrs.cy == point.y ) {
					circles[i].circle.toFront();
					//console.log("CIRCLE ALLREADY ADDED: ", point.x, ",", point.y);
					circles[i].point.toFront();
					return true;
				}
			}
			return false;
		}

		function get_point_id (point) {
			for( var i in circles) {
				if( circles[i].circle.attrs.cx == point.attrs.cx && circles[i].circle.attrs.cy == point.attrs.cy ) {
					return circles[i].circle.loc_id;
				}
			}
		}


		function draw_circle(point, name) {

			if( false == check_point_already_added(point) ) {
				lastCircle = paper.circle(point.x, point.y, 15);
				lastCircle.customAttr = { r:10, fill:lineColor };
				lastCircle.attr("fill", "#f00");
				lastCircle.attr("stroke", "#000");
				lastCircle.toFront();
				lastCircle.loc_id = point.ID;

				pt = paper.text(point.x, point.y, point.ID);
				pt.attr( "fill", "#0000FF" );
				pt.attr({ "font-size": 10, "font-family": "Arial, Helvetica, sans-serif", "font-weight": "bold" });

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
						modal.style.display = "block";

						var list = document.getElementById("location");
						for (var i = 0; i < all_locations.length; i++) {
							var opt = document.createElement('option');
							opt.innerHTML = all_locations[i];
							opt.value = all_locations[i];
							list.appendChild(opt);
						}

						$("#location").val(get_point_id(this)).change();

						var btn = document.getElementById("savedata");
						btn.onclick = function() {
						     var url = "http://localhost:8080/saveexit?loc=" + $('#location :selected').text() + "&data=" + $('#data').val() + "&callback=?";
							$.getJSON(url, function(result) {
								alert(result);
								//var p = $.parseJSON(result);
							});
						}
					}
				});

				circles.push({ circle: lastCircle, point: pt });
			} // check_point_already_added
		}


		function draw_line(path, name) {
			line_path = "M " + path.start.x + "," + path.start.y + " L " + path.end.x + "," + path.end.y;
			line = paper.path( line_path );
			line.attr( { 'stroke-width':'20', "stroke":lineColor } );

			if( -1 == $.inArray( path.start.ID, all_locations ) ) {
				all_locations.push(path.start.ID);
			}
			if( -1 == $.inArray( path.end.ID, all_locations ) ) {
				all_locations.push(path.end.ID);
			}

			draw_circle(path.start, name);
			draw_circle(path.end, name);
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


		$(document).ready(function() {
			window.oncontextmenu = function () {
				return false;     // cancel default menu
			};

			paper=Raphael(document.getElementById('map-container'), 900, 1350);
			paper.canvas.className.baseVal="mapersvg";

		    // Get the modal
			modal = document.getElementById('myModal');

			// Get the <span> element that closes the modal
			span = document.getElementsByClassName("close")[0];

			// When the user clicks on <span> (x), close the modal
			span.onclick = function() {
			    modal.style.display = "none";
			}

			// When the user clicks anywhere outside of the modal, close it
			window.onclick = function(event) {
			    if (event.target == modal) {
			        modal.style.display = "none";
			    }
			}

			loadMap();

		});


	</script>

 </body>
 </html>