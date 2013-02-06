"use strict";

//
// @package "geopIP" Mod for Simple Machines Forum (SMF) V2.0
// @author Spuds
// @copyright (c) 2011 Spuds
// @license license.txt (included with package) BSD
//
// @version 1.1
//
//

// global variables
var iFreq = 2000,
	progress,
	percent,
	status,
	details,
	intervalID,
	iNum = 0;

// start our self calling routine and submit the base form
function geoip_init() {
	// submit the form so the database process starts
	var dummy = document.getElementById('geoip').submit();
	
	// show the progress bar
	var progress_div = document.getElementById('geoIP_progress_container');
	progress_div.style.display = "block";

	// Make sure the status area is set to zero
	document.getElementById("geoIP_progress").style.width = "0%";
	document.getElementById("geoIP_status").innerHTML = '';
	
	// Make the initial xml call and then set up an interval to continue to poll
	// geoIPSend();
	intervalID = setInterval(geoIPSend, iFreq);
}

// While we are not done .... make the request
function geoIPSend() {
	var url = smf_prepareScriptUrl(smf_scripturl) + "action=admin;area=geoIP;sa=xml";
	progress = document.getElementById("geoIP_progress");
	status = document.getElementById("geoIP_status");

	// Send in the XMLhttp request and let's hope for the best.
	sendXMLDocument(url, "", geoIPReply);

	return false;
}

// The reply to our request 
function geoIPReply(oXMLDoc) {
	// read the XMLhttp response
	if (oXMLDoc.getElementsByTagName("geoIP")) {
		percent = oXMLDoc.getElementsByTagName("geoIP")[0].getAttribute("percent");
		details = oXMLDoc.getElementsByTagName("geoIP")[1].getAttribute("details");
	} else {
		geoIP_stop();
	}

	// show them we are still alive!
	progress.style.width = percent + "%";
	status.innerHTML = details;
	
	// 101 reasons things went wrong ... 
	if (percent > 100) {
		progress.style.width = "100%";
		progress.style.background = "red";
		geoIP_stop();
	}
	
	// done yet?
	if (percent == 100) {
		geoIP_stop();
	}
}

// Get me out of here!
function geoIP_stop() {
    clearInterval(intervalID);
    intervalID = false;
}

// page event control
function mod_addEvent(control, ev, fn) {
	if (control.addEventListener)
	{
		control.addEventListener(ev, fn, false); 
	} 
	else if (control.attachEvent)
	{
		control.attachEvent('on'+ev, fn);
	}
}

// show the descriptions that match the selection
function toggledbTrigger() {
	var desc = document.getElementById('option_desc_' + db_type.value).firstChild.data;
	document.getElementById('option_desc').innerHTML = desc;
	
	if (db_type.value == 0)
		document.getElementById('warn').style.visibility = 'visible';
	else
		document.getElementById('warn').style.visibility = 'hidden';
}