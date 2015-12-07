function scrollBack(timechangeratio) {
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	var timespan = until - from;
	var timechange = timespan * timechangeratio;
	if (timechange < 60) {
		if (parseInt(new Date().getTime().toString().substr(-3)) % 75 < 17) {
			timechange = 60;
		}
		else {
			timechange = 0;
		}
	}
	var newfrom = from - timechange;
	var newuntil = until - timechange;
	if (newfrom < mindatetime) {
		newfrom = mindatetime;
		newuntil = newfrom + timespan;
	}
	if (newuntil > maxdatetime) {
		newuntil = maxdatetime;
		newfrom = newuntil - timespan;
	}
	$("from").value = unix2datestr(newfrom);
	$("until").value = unix2datestr(newuntil);
	updateEvents();
}

function scrollForward(timechangeratio) {
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	var timespan = (until - from);
	var timechange = Math.round(timespan * timechangeratio);
	if (timechange < 60) {
		if (parseInt(new Date().getTime().toString().substr(-3)) % 75 < 17) {
			timechange = 60;
		}
		else {
			timechange = 0;
		}
	}
	var newfrom = from + timechange;
	var newuntil = until + timechange;
	if (newfrom < mindatetime) {
		newfrom = mindatetime;
		newuntil = newfrom + timespan;
	}
	if (newuntil > maxdatetime) {
		newuntil = maxdatetime;
		newfrom = newuntil - timespan;
	}
	$("from").value = unix2datestr(newfrom);
	$("until").value = unix2datestr(newuntil);
	updateEvents();
}

function updateViewDropdown() {
	views = JSON.parse(GET('api.php?getViews'));
	var sv = $("selectView");
	sv.options.length = 0;
	sv.options.add(new Option("Select a view", -1));
	for (var viewi in views) {
		var view = views[viewi];
		sv.options.add(new Option(view.name + " (" + view.filter + ")", view.rowid));
	}
}

function filterEvents() {
	var starttime = new Date().getTime();
	filteredEvents = [];
	var filter = $("filter").value;
	if (filter.length == 0) {
		filteredEvents = events;
		return;
	}
	for (var eventi in events) {
		var event = events[eventi];
		if ($("advfilter").checked) {
			var filters = filter.split("|");
			for (var filters2 in filters) {
				filters3 = filters[filters2];
				var ok = true;
				var filters3 = filters3.split("&");
				for (var filters4 in filters3) {
					filters4 = filters3[filters4].split(":");
					var field = filters4[0];
					var filter = filters4[1];
					switch (field) {
						case 'dtls':
							field = event.details;
							break;
						case 'sum':
							field = event.summary;
							break;
						case 'dev':
							field = event.device;
							break;
						case 'src':
							field = event.source;
							break;
						default:
							return [];
					}
					if (!new RegExp(filter).test(field)) {
						console.log(filter + " did not match " + field);
						ok = false;
						break;
					}
				}
				if (!ok) {
					continue;
				}
				else {
					filteredEvents.push(event);
					break;
				}
			}
		}
		else {
			// No advanced filters
			if ($("regexfilter").checked) {
				re = new RegExp(filter);
				if (re.test(event.summary)
						|| re.test(event.details)
						|| re.test(event.source)
						|| re.test(event.device)) {
					filteredEvents.push(event);
				}
			}
			else {
				if (event.summary.indexOf(filter) != -1
						|| event.details.indexOf(filter) != -1
						|| event.source.indexOf(filter) != -1
						|| event.device.indexOf(filter) != -1) {
					filteredEvents.push(event);
				}
			}
		}
		if (starttime + maxtime < new Date().getTime()) {
			console.log("Error 1895: I won't be working overtime.");
			return;
		}
	}
	if (displayRenderingTime) {
		console.log("Filtering time: " + (new Date().getTime() - starttime) + "ms");
	}
}

function updateEvents() {
	var starttime = new Date().getTime();
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	if (from == false || until == false) {
		console.log("Invalid date.");
		return;
	}
	var timespan = until - from;

	$("timeline").innerHTML = '';

	var previousEventPosPerc = 0;
	var previousEventTop = 0;
	for (var eventi in filteredEvents) {
		var event = filteredEvents[eventi];
		if (event.datetime < from || event.datetime > until) {
			continue;
		}
		var posPerc = (event.datetime - from) / timespan * 100;
		var left = Math.round(posPerc) + "%";
		var top = 0;
		if (previousEventPosPerc / posPerc > 0.9) {
			if (previousEventTop > 600) {
				previousEventTop = 0;
			}
			top = previousEventTop + 95;
		}

		var col = (eventi % 2) * 8 + 247;
		var evtDiv = newDiv($("timeline"));
		var cur = (event.details.length > 0 && event.details != "\n" ? "pointer" : "auto");
		evtDiv.style = obj2style(
			{ width: "150px"
			, position: "absolute"
			, left: left
			, "background-color": "rgb(" + col + "," + col + "," + col + ")"
			, border: "1px solid grey"
			, "border-left": "2px solid black"
			, "border-bottom": "2px solid black"
			, "word-wrap": "break-word"
			, top: top + "px"
			, cursor: cur
		});
		evtDiv.onmouseover = function(ev) {
			ev.target.style.zIndex = 9999;
		};
		evtDiv.onmouseout  = function(ev) {
			ev.target.style.zIndex = 0;
		};
		var summary = (event.summary.length > 0 ? event.summary + "<br>" : "");
		evtDiv.innerHTML = "<i>" + event.printableDatetime + "</i><br>"
			+ "@" + event.device + ":<br>"
			+ summary
			+ "<i>Source: " + event.source + "</i>";
		evtDiv.pleasedontkillme = {details: event.details};
		evtDiv.onclick = function(ev) {
			alert(ev.target.pleasedontkillme.details);
		};

		if (top == 0) {
			previousEventPosPerc = posPerc;
		}
		previousEventTop = top;

		if (starttime + maxtime < new Date().getTime()) {
			console.log("Error 6571: I won't be working overtime.");
			return;
		}
	}

	if (displayRenderingTime) {
		console.log("Event render time: " + (new Date().getTime() - starttime) + "ms");
	}
	starttime = new Date().getTime();

	if (window.lastTimespan != timespan) {
		// Render timeline block background
		var altcol = "235, 250, 235";
		col = altcol;
		var html = '';
		var daywidth = (3600 * 24) / (until - from) * 100;
		var hourwidth = 3600 / (until - from) * 100;
		for (var i = from; i < until - 3600 * 24; i += 3600 * 24) {
			html += "<div style='display:inline-block; width:" + daywidth + "%; "
				+ "height:20px; background-color: rgb(" + col + ");'></div>";
			if (col == altcol) {
				col = "255, 255, 255";
			}
			else {
				col = altcol;
			}
		}
		html += "<br>";
		if (hourwidth > 0.5) {
			for (var i = from; i < until - 3600; i += 3600) {
				html += "<div style='display:inline-block; width:" + hourwidth + "%; "
					+ " height:20px; background-color: rgb(" + col + ");'></div>";
				if (col == altcol) {
					col = "255, 255, 255";
				}
				else {
					col = altcol;
				}
			}
		}
		$("timelinebg").innerHTML = html;
	}

	window.lastTimespan = timespan;

	if (displayRenderingTime) {
		console.log("Block bg rendering: " + (new Date().getTime() - starttime) + "ms");
	}
}

function zoom(direction, ratio) {
	var from = datestr2unix($("from").value);
	var until = datestr2unix($("until").value);
	var timespan = until - from;
	var fromdiff = timespan * 0.3 * ratio;
	var untildiff = timespan * 0.3 * (1 - ratio);
	if (direction) {
		if (timespan > 3600 * 24 * 200) {
			timespan = 3600 * 24 * 200;
			return;
		}
		fromdiff = -fromdiff;
		untildiff = -untildiff;
	}
	else {
		if (timespan < 90) {
			return;
		}
	}
	var newfrom = from + fromdiff;
	var newuntil = until - untildiff;
	if (newfrom < mindatetime) {
		newfrom = mindatetime;
		newuntil = newfrom + timespan;
	}
	if (newuntil > maxdatetime) {
		newuntil = maxdatetime;
		newfrom = newuntil - timespan;
	}
	$("from").value = unix2datestr(newfrom);
	$("until").value = unix2datestr(newuntil);
	updateEvents();
}

