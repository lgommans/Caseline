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

$("saveView").onclick = function() {
	var name = escape(prompt("Name?", ""));
	if (name == null) {
		return;
	}
	var advfilter = 0;
	if ($("advfilter").checked) {
		advfilter = 1;
	}
	var regexfilter = 0;
	if ($("regexfilter").checked) {
		regexfilter = 1;
	}
	var mode = advfilter.toString() + regexfilter.toString();
	var filter = escape($("filter").value);
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	GET("api.php?saveView=" + name
		+ "&datefrom=" + from
		+ "&dateuntil=" + until
		+ "&filter=" + mode + filter
	);
	updateViewDropdown();
};

var elem = "selectView";
$(elem).onkeyup = $(elem).onchange = $(elem).onmouseup = function() {
	var index = $("selectView").selectedIndex;
	if (index == 0) {
		return;
	}
	var view = views[index - 1];
	var advfilter = view.filter[0];
	var regexfilter = view.filter[1];
	view.filter = view.filter.substr(2);
	if (advfilter) {
		$("advfilter").checked = true;
	}
	else {
		$("advfilter").checked = false;
	}
	if (regexfilter) {
		$("regexfilter").checked = true;
	}
	else {
		$("regexfilter").checked = false;
	}
	$("from").value = unix2datestr(view.datefrom);
	$("until").value = unix2datestr(view.dateuntil);
	$("filter").value = view.filter;
	filterEvents();
	updateEvents();
};

$("deleteView").onclick = function() {
	if (confirm("Are you sure?")) {
		var result = GET("api.php?deleteView=" + evtDiv.target.pleasedontkillme.rowid);
		if (result != 'true') {
			alert('Error deleting this event');
		}
		else {
			updateViewDropdown();
		}
	}
};

$("parseLog").onclick = function() {
	open("webmanipulator.php");
};

onkeydown = function(ev) {
	var ae = document.activeElement;
	if (ae == $("filter") || ae == $("from") || ae == $("until")) {
		return;
	}
	if (ev.keyCode == 37) {
		scrollBack(0.12);
	}
	if (ev.keyCode == 38) {
		zoom(0, 0.5);
	}
	if (ev.keyCode == 39) {
		scrollForward(0.12);
	}
	if (ev.keyCode == 40) {
		zoom(-1, 0.5);
	}
};

document.addEventListener('wheel', function(ev) {
	var ratio = ev.clientX / window.innerWidth;
	zoom(ev.deltaY > 0, ratio);
});

