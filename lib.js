// Dutch months. Sorry.
monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"];

function GET(uri) {
	var req = new XMLHttpRequest();
	req.open("GET", uri, false);
	req.send(null);
	return req.responseText;
}

function $(id) {
	return document.getElementById(id);
}

function newDiv(appendObj) {
	var mydiv = document.createElement("div");
	appendObj.appendChild(mydiv);
	return mydiv;
}

function obj2style(obj) {
	stylestr = '';
	for (prop in obj) {
		stylestr += prop + ":" + obj[prop] + ";";
	}
	return stylestr;
}

function datestr2unix(datestr) {
	datestr = datestr.split(' ');
	if (datestr.length != 3 || datestr[2].split(":").length != 3) {
		return false;
	}
	var date = parseInt(datestr[0]);
	var month = monthNames.indexOf(datestr[1]);
	var hour = datestr[2].split(":")[0];
	var minute = datestr[2].split(":")[1];
	var second = datestr[2].split(":")[2];
	return (+new Date(2015, month, date, hour, minute, second)) / 1000;
}

function unix2datestr(unix) {
	var d = new Date(unix * 1000);
	return d.getDate() + " "
		+ monthNames[d.getMonth()] + " "
		+ leadingZero(d.getHours()) + ":"
		+ leadingZero(d.getMinutes()) + ":"
		+ leadingZero(d.getSeconds());
}

function leadingZero(n) {
	if (n < 10) {
		return "0" + n;
	}
	return "" + n;
}

