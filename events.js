$("scrollBack").onclick = function() {
	if (window.cancelClick > 0) {
		window.cancelClick -=1;
		return;
	}
	scrollBack(0.1);
};

$("scrollForward").onclick = function() {
	if (window.cancelClick > 0) {
		window.cancelClick -=1;
		return;
	}
	scrollForward(0.1);
};

$("scrollBack").onmousedown = function() {
	window.forwardInterval = setInterval(function() {
		scrollBack(0.01);
	}, 30);
};

$("scrollBack").onmouseup = function() {
	clearInterval(window.forwardInterval);
	window.cancelClick = 1;
};

$("scrollBack").onmouseout = function() {
	clearInterval(window.forwardInterval);
};

$("scrollForward").onmousedown = function() {
	window.forwardInterval = setInterval(function() {
		scrollForward(0.01);
	}, 30);
};

$("scrollForward").onmouseup = function() {
	clearInterval(window.forwardInterval);
	window.cancelClick = 1;
};

$("scrollForward").onmouseout = function() {
	clearInterval(window.forwardInterval);
};

$("filterhelp").onclick = function() {
	alert("Syntax: field:value|field:value&field:value\n"
		+ "Where field is dev, msg or src and value is a regex.\n"
		+ "Example: msg:installed");
};

$("showalltime").onclick = function() {
	$("from").value = unix2datestr(filteredEvents[0].datetime - 1);
	$("until").value = unix2datestr(filteredEvents[filteredEvents.length - 1].datetime + 1);
	updateEvents();
};

$("saveView").onclick = function() {
	var name = escape(prompt("Name?", ""));
	if (name == null) {
		return;
	}
	var filter = escape($("filter").value);
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	GET("api.php?saveView=" + name
		+ "&datefrom=" + from
		+ "&dateuntil=" + until
		+ "&filter=" + filter
	);
	updateViewDropdown();
};

var elem = "until";
$(elem).onkeyup = $(elem).onchange = $(elem).onmouseup = function() {
	updateEvents();
};

var elem = "from";
$(elem).onkeyup = $(elem).onchange = $(elem).onmouseup = function() {
	updateEvents();
};

var elem = "filter";
$(elem).onkeyup = $(elem).onchange = $(elem).onmouseup = function() {
	filterEvents();
	updateEvents();
};

var elem = "selectView";
$(elem).onkeyup = $(elem).onchange = $(elem).onmouseup = function() {
	var v = $("selectView").selectedIndex;
	if (v == 0) {
		return;
	}
	var view = views[v - 1];
	$("from").value = unix2datestr(view.datefrom);
	$("until").value = unix2datestr(view.dateuntil);
	$("filter").value = view.filter;
	filterEvents();
	updateEvents();
};

document.addEventListener('wheel', function(ev) {
	var ratio = ev.clientX / window.innerWidth;
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	var timespan = until - from;
	var fromdiff = timespan * 0.3 * ratio;
	var untildiff = timespan * 0.3 * (1 - ratio);
	if (ev.deltaY > 0) {
		if (timespan > 3600 * 24 * 200) {
			timespan = 3600 * 24 * 200;
			return;
		}
		fromdiff = -fromdiff;
		untildiff = -untildiff;
	}
	var newfrom = from + fromdiff;
	var newuntil = until - untildiff;
	if (newfrom < new Date(2015, 4, 1).getTime() / 1000) {
		newfrom = new Date(2015, 4, 1).getTime() / 1000;
		newuntil = newfrom + timespan;
	}
	if (newuntil > new Date(2015, 11, 1).getTime() / 1000) {
		newuntil = new Date(2015, 11, 1).getTime() / 1000;
		newfrom = newuntil - timespan;
	}
	$("from").value = unix2datestr(newfrom);
	$("until").value = unix2datestr(newuntil);
	updateEvents();
});

$("deleteView").onclick = function() {
	if (confirm("Are you sure?")) {
		alert("Then please ask Luc to implement this now. Tell him the time has come.");
	}
};

