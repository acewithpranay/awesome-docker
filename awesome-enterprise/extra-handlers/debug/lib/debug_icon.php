<?php 

echo '	
<a href="#" id="awesome-debug-button" style="position:fixed;z-index: 10000;width:60px;height:60px;bottom:40px;left:40px;background-color:#0C9;color:#FFF;border-radius:50px;text-align:center;box-shadow: 2px 2px 3px #999;">
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
<g>
	<g>
		<path d="M498.667,290.667H404V240c0-1.016,0.313-2.036,0.291-3.055C452.4,231.927,480,200.272,480,148.333v-12
			c0-7.364-5.971-13.333-13.333-13.333s-13.333,5.969-13.333,13.333v12c0,38.399-17.005,58.821-51.885,62.167
			c-6.069-27.025-20.875-50.381-45.537-55.7c-3.745-28.54-21.413-52.689-46.115-65.227c10.321-10.501,16.871-24.887,16.871-40.74V37
			c0-7.364-5.971-13.333-13.333-13.333S300,29.636,300,37v11.833C300,66.203,285.536,80,268.167,80h-23
			c-17.369,0-31.833-13.797-31.833-31.167V37c0-7.364-5.971-13.333-13.333-13.333S186.667,29.636,186.667,37v11.833
			c0,15.853,6.549,30.239,16.871,40.741c-24.701,12.537-42.453,36.687-46.199,65.227c-24.695,5.324-39.465,28.736-45.519,55.808
			c-35.759-2.96-53.153-23.403-53.153-62.276v-12c0-7.364-5.971-13.333-13.333-13.333S32,128.969,32,136.333v12
			c0,52.415,27.439,84.168,76.375,88.739C108.353,238.048,108,239.025,108,240v50.667H13.333C5.971,290.667,0,296.636,0,304
			s5.971,13.333,13.333,13.333H108v23c0,10.628,1.469,20.993,3.608,30.992C60.659,374.777,32,406.773,32,460.333v12
			c0,7.364,5.971,13.333,13.333,13.333s13.333-5.969,13.333-13.333v-12c0-41.795,20.151-62.291,61.565-62.649
			c22.451,53.208,75.151,90.649,136.435,90.649c61.276,0,113.971-37.432,136.425-90.629c40.519,0.784,60.241,21.283,60.241,62.629
			v12c0,7.364,5.971,13.333,13.333,13.333S480,479.697,480,472.333v-12c0-53.1-28.823-85.013-78.96-88.921
			c2.151-10.025,2.96-20.421,2.96-31.079v-23h94.667c7.363,0,13.333-5.969,13.333-13.333S506.029,290.667,498.667,290.667z
			 M242.667,460.855c-60.333-6.964-108-58.353-108-120.521V240c0-20.793,6.948-50.531,24.483-58.035
			c6.627,18.368,24.56,31.368,45.184,31.368h38.333V460.855z M204.333,186.667c-11.58,0-21-9.42-21-21c0-32.532,26.468-59,59-59
			h2.833h23H271c32.532,0,59,26.468,59,59c0,11.58-9.42,21-21,21H204.333z M377.333,340.333c0,62.627-47.027,114.32-108,120.673
			V213.333H309c20.624,0,37.891-13,44.517-31.368c17.535,7.504,23.816,37.241,23.816,58.035V340.333z"/>
	</g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
</svg>

</a>
<script>
document.addEventListener("click", function(event) {
	html="<title>Awesome Debug Window</title>";
    let el = event.target.closest("a#awesome-debug-button");
    if(el){
		event.preventDefault();
		var debug_data = document.querySelectorAll("template.awesome_live_debug_data");
		debug_data.forEach(function(element, index) {
			// current DOM element
			html += element.innerHTML;
		});
		
	
		var w = window.open("", "awesome-debug-window");
		w.document.body.innerHTML = html;
		
		var s = document.createElement("script");
			s.type = "text/javascript";
		
		var code = "var colToggle = function(toggID) {var img = document.getElementById(toggID);if (document.getElementById(toggID + \"-collapsable\").style.display == \"none\") {document.getElementById(toggID + \"-collapsable\").style.display = \"inline\";setImg(toggID, 0);var previousSibling = document.getElementById(toggID + \"-collapsable\").previousSibling;while (previousSibling != null && (previousSibling.nodeType != 1 || previousSibling.tagName.toLowerCase() != \"br\")) {previousSibling = previousSibling.previousSibling;}} else {document.getElementById(toggID + \"-collapsable\").style.display = \"none\";setImg(toggID, 1);var previousSibling = document.getElementById(toggID + \"-collapsable\").previousSibling; while (previousSibling != null && (previousSibling.nodeType != 1 || previousSibling.tagName.toLowerCase() != \"br\")) {previousSibling = previousSibling.previousSibling;}}};";
		
		code +="var setImg = function(objID,imgID,addStyle) {var imgStore = [\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAMAAADXT/YiAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3MjlFRjQ2NkM5QzJFMTExOTA0MzkwRkI0M0ZCODY4RCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpFNzFDNDQyNEMyQzkxMUUxOTU4MEM4M0UxRDA0MUVGNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpFNzFDNDQyM0MyQzkxMUUxOTU4MEM4M0UxRDA0MUVGNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo3NDlFRjQ2NkM5QzJFMTExOTA0MzkwRkI0M0ZCODY4RCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3MjlFRjQ2NkM5QzJFMTExOTA0MzkwRkI0M0ZCODY4RCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuF4AWkAAAA2UExURU9t2DBStczM/1h16DNmzHiW7iNFrypMvrnD52yJ4ezs7Onp6ejo6P///+Tk5GSG7D9h5SRGq0Q2K74AAAA/SURBVHjaLMhZDsAgDANRY3ZISnP/y1ZWeV+jAeuRSky6cKL4ryDdSggP8UC7r6GvR1YHxjazPQDmVzI/AQYAnFQDdVSJ80EAAAAASUVORK5CYII=\", \"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAMAAADXT/YiAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3MTlFRjQ2NkM5QzJFMTExOTA0MzkwRkI0M0ZCODY4RCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpFQzZERTJDNEMyQzkxMUUxODRCQzgyRUNDMzZEQkZFQiIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpFQzZERTJDM0MyQzkxMUUxODRCQzgyRUNDMzZEQkZFQiIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo3MzlFRjQ2NkM5QzJFMTExOTA0MzkwRkI0M0ZCODY4RCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3MTlFRjQ2NkM5QzJFMTExOTA0MzkwRkI0M0ZCODY4RCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PkmDvWIAAABIUExURU9t2MzM/3iW7ubm59/f5urq85mZzOvr6////9ra38zMzObm5rfB8FZz5myJ4SNFrypMvjBStTNmzOvr+mSG7OXl8T9h5SRGq/OfqCEAAABKSURBVHjaFMlbEoAwCEPRULXF2jdW9r9T4czcyUdA4XWB0IgdNSybxU9amMzHzDlPKKu7Fd1e6+wY195jW0ARYZECxPq5Gn8BBgCr0gQmxpjKAwAAAABJRU5ErkJggg==\"];if (objID) {document.getElementById(objID).setAttribute(\"src\", imgStore[imgID]);if (addStyle){document.getElementById(objID).setAttribute(\"style\", \"position:relative;left:-5px;top:-1px;cursor:pointer;\");}}};"
		
		s.appendChild(document.createTextNode(code));
		w.document.body.appendChild(s);
		
    }
   
});
</script>
		';
