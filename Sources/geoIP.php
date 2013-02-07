<?php

/**
 *
 * @package "geopIP" Mod for Simple Machines Forum (SMF) V2.0
 * @author Spuds
 * @copyright (c) 2011 Spuds
 * @license license.txt (included with package) BSD
 *
 * @version 1.1.1
 *
 */

// Are we calling this directly, umm lets say bu-bye
if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * geoIP()
 *
 * Traffic cop, checks permissions
 * and calls the template which in turn calls this to request the xml file or js file to template inclusion
 *
 * @return
 */
function geoIP()
{
	global $db_prefix, $context, $txt, $smcFunc, $modSettings;

	// whos online enabled and geoip enabled are required
	if (!empty($modSettings['who_enabled']) && !empty($modSettings['geoIP_enablemap']));
	{
		// First are they allowed to view whos online and the online map?
		isallowedTo(array('geoIP_view'));
		isallowedTo(array('who_view'));

		// language files
		loadLanguage('geoIP');

		// create the pins for use, do it now so its available everywhere
		geo_buildpins();

		// requesting the XML details or the JS file?
		if (isset($_GET['sa']) && $_GET['sa'] == '.xml')
			return geoMapsXML();

		if (isset($_GET['sa']) && $_GET['sa'] == '.js')
			return geoMapsJS();

		// load up our template and style sheet
		loadTemplate('geoIP', 'geoIP');
		$context['sub_template'] = 'geoIP';
		$context['page_title'] = $txt['geoIP'];
	}
}

/**
 * geoMapsJS()
 *
 * creates the javascript file based on the admin settings
 * called from the map template file via map sa = js
 *
 * @return
 */
function geoMapsJS()
{
	global $db_prefix, $context, $scripturl, $txt, $modSettings;

	// what type of pin are we using?
	$npin = $modSettings['npin'];
	$mshd = (!empty($modSettings['geoIPPinShadow'])) ? $mshd = '_withshadow' : $mshd = '';

	// Validate the icon size to keep from breaking
	$m_iconsize = (isset($modSettings['geoIPPinSize']) && $modSettings['geoIPPinSize'] > 19) ? $modSettings['geoIPPinSize'] : 20;

	// set our member and pin sizes the image sizes are 21 X 34 for standard 40 X 37 with a shadow
	// we need to tweak the sizes based on these W/H ratios to maintain aspect ratio and overall size so that a mixed shadown./no appear the same size
	$m_icon_w = ($mshd != '') ? $m_iconsize * 1.08 : $m_iconsize * .62;
	$m_icon_h = $m_iconsize;

	// Now set all those anchor points based on the icon size, icon at pin mid bottom, info mid top(ish)....
	$m_iconanchor_w = ($mshd != '') ? $m_icon_w / 3 : $m_icon_w / 2;
	$m_iconanchor_h = $m_icon_h;
	$m_infoanchor_h = $m_icon_h / 4;

	// Lets dump everything in the buffer so we can return nice clean javascript to the template
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();
	ob_start('ob_sessrewrite');

	echo '// Read the xml data
var xhr = false;
function makeRequest(url) {
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	else {
		if (window.ActiveXObject) {
			try { xhr = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (e) { }
		}
	}
	if (xhr) {
		xhr.onreadystatechange = showContents;
		xhr.open("GET", url, true);
		xhr.send(null);
	}
	else {
		document.write("' . $txt['geoIP_xmlerror'] . '");
	}
}

function showContents() {
	var xmldoc = \'\';
	if (xhr.readyState == 4)
	{
		// Run on server (200) or local machine (0)
		if (xhr.status == 200 || xhr.status == 0)  {
			xmldoc = xhr.responseXML;
			makeMarkers(xmldoc);
		}
		else {
			document.write("' . $txt['geoIP_error'] . ' - " + xhr.status);
		}
	}
}

var map;
var infowindow;

// Start everything up
function initialize() {
	// create the map
	var latlng = new google.maps.LatLng(' . (!empty($modSettings['geoIPDefaultLat']) ? $modSettings['geoIPDefaultLat'] : 0)  . ', ' . (!empty($modSettings['geoIPDefaultLong']) ? $modSettings['geoIPDefaultLong'] : 0) . ');
	var options = {
		zoom: ' . $modSettings['geoIPDefaultZoom'] . ',
		center: latlng,
		scrollwheel: false,
		mapTypeId: google.maps.MapTypeId.' . $modSettings['geoIPType'] . ',
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
		},
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.' . $modSettings['geoIPNavType'] . '
		},
	};
	map = new google.maps.Map(document.getElementById("map"), options);
	makeRequest("' . $scripturl . '?action=geoIP;sa=.xml");
}

// arrays to hold copies of the markers and html used by the sidebar
var gmarkers = [];
var htmls = [];
var sidebar_html = "";

// our pin to show on the map ....
var pic = new google.maps.MarkerImage(
	"http://chart.apis.google.com/chart' . $npin . '",
	// This markers size
	new google.maps.Size(' . $m_icon_w . ',' . $m_icon_h . '),
	// The origin for this image is 0,0.
	new google.maps.Point(0,0),
	// The anchor for this image
	new google.maps.Point(' . $m_iconanchor_w . ',' . $m_iconanchor_h . '),
	new google.maps.Size(' . $m_icon_w . ',' . $m_icon_h . ')
);

// function to read the output of the marker xml
function makeMarkers(xmldoc) {
	var markers=xmldoc.documentElement.getElementsByTagName("marker");
	for (var i = 0; i < markers.length; i++) {
			var point = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")),parseFloat(markers[i].getAttribute("lng")));
			var html = markers[i].childNodes[0].nodeValue;
			var label = markers[i].getAttribute("label");
			var marker = createMarker(point, pic, label, html, i);
	}

	// put the assembled sidebar_html contents into the sidebar div
	document.getElementById("gooSidebar").innerHTML = sidebar_html;
}

// A function to create the marker and set up the event window
function createMarker(point, pic, name, html, i) {
    var marker = new google.maps.Marker({position: point, map: map, icon: pic, clickable: true, title: name});
    google.maps.event.addListener(marker,"click", function() {
    	if (infowindow)
			infowindow.close();
		infowindow = new google.maps.InfoWindow({content: html});
		infowindow.open(map, marker);
    });

	// save the info we need to use later for the sidebar
	gmarkers[i] = marker;
	htmls[i] = html;

	// add a line to the sidebar html';
	if ($modSettings['geoIPSidebar'] == 'right')
		echo '
		sidebar_html += \'<a href="javascript:myclick(\' + i + \')">\' + name + \'</a><br /> \';';

	if ($modSettings['geoIPSidebar'] == 'none')
		echo '
		sidebar_html += \'<a href="javascript:myclick(\' + i + \')">\' + name + \'</a>, \';';

	echo '
	// Now that we cached it lets return the marker....
	return marker;
}

// This function picks up the click and opens the corresponding info window
function myclick(i) {
	if (infowindow)
		infowindow.close();
	infowindow = new google.maps.InfoWindow({content: htmls[i]});
	infowindow.open(map, gmarkers[i]);
}

google.maps.event.addDomListener(window, "load", initialize);';

	obExit(false);
}

/**
 * geoMapsXML()
 *
 * creates the xml data for use on the map
 * pin info window content
 * map sidebar layout
 *
 * @return
 */
function geoMapsXML()
{
	global $smcFunc, $context, $settings, $options, $scripturl, $txt, $modSettings, $user_info, $memberContext;

	// Lets dump everything in the buffer and start clean for this xml result
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();
	ob_start('ob_sessrewrite');

	// XML Header
	header('Content-Type: application/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	// Lets find the online members and thier ip's
	$guests = array();
	$temp = array();
	$ips = array();
	$spider = false;

	// can they see spiders ... ewww stomp em :P
	if (!empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] == 2 || allowedTo('admin_forum')) && !empty($modSettings['spider_name_cache']))
		$spider = '(lo.id_member = 0 AND lo.id_spider > 0)';

	// Look for people online
	$request = $smcFunc['db_query']('', '
		SELECT
			lo.session, lo.id_member, lo.latitude, lo.longitude, lo.country, lo.city, lo.id_spider, lo.cc, INET_NTOA(lo.ip) AS ip, IFNULL(mem.show_online, 1) AS show_online
		FROM {db_prefix}log_online AS lo
			LEFT JOIN {db_prefix}members AS mem ON (lo.id_member = mem.id_member)
		WHERE (lo.id_member >= 0 AND lo.id_spider = 0)' . (!empty($spider) ? ' OR {raw:spider}' : '') . '
		ORDER BY lo.log_time DESC',
		array(
			'spider' => $spider,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// don't load blank locations or hidden members to non moderators.
		if ((!empty($row['show_online']) || allowedTo('moderate_forum')) && (!empty($row['latitude'])) && (!empty($row['longitude'])))
		{
			// load the information for map use.
			$ips[$row['id_member']] = array(
				'ip' => $row['ip'],
				'session' => $row['session'],
				'is_hidden' => $row['show_online'] == 0,
				'id_spider' => $row['id_spider'],
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude'],
				'country' => $row['country'],
				'city' => $row['city'],
				'cc' => $row['cc'],
			);

			// keep track of the members vs guests/spiders
			if (!empty($row['id_member']))
				$temp[] = $row['id_member'];
			else
				$guests[] = $ips[$row['id_member']];
		}
	}
	$smcFunc['db_free_result']($request);

	// Get the geoIP information for these members
	$memberIPData = geo_search($ips);

	// Load all of the data for these online members
	loadMemberData($temp);
	foreach ($temp as $v)
		loadMemberContext($v);

	// Let's actually start making the XML
	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?' . '>
<markers>';

	if (isset($memberContext))
	{
		// Assuming we have data to work with ... build the info bubble
		foreach ($memberContext as $marker)
		{
			// no location ... no pin ;)
			if (empty($memberIPData[$marker['id']]['latitude']) && empty($memberIPData[$marker['id']]['longitude']))
				continue;

			// if they are allowed to see the user info to pin, build the blurb.
			if (!empty($modSettings['geoIP_enablepinid']) || allowedTo('moderate_forum'))
			{
				$datablurb = '
		<table class="geoIP" border="0">
			<tr>
				<td style="white-space: nowrap;" align="left">
					<a href="' . $marker['href'] . '">' . $marker['name'] . '</a>
				</td>';

				if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($marker['avatar']['image']))
					$datablurb .= '<td style="height:100px;width:100px" rowspan="2">' . $marker['avatar']['image'] . '</td>';

				// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
				if ((empty($settings['hide_post_group']) || $marker['group'] == '') && $marker['post_group'] != '')
					$datablurb .= '
			</tr>
			<tr>
				<td style="white-space: nowrap;" align="left">' . $marker['post_group'] . '<br />' . $marker['group_stars'] . '</td>';

				$datablurb .= '
			</tr>
			<tr>
				<td colspan="2" style="white-space: nowrap;" align="left">';

				// Show some geo id info
				if (!empty($memberIPData[$marker['id']]['city']))
					$datablurb .= $memberIPData[$marker['id']]['city'];
				if (!empty($memberIPData[$marker['id']]['region']))
					$datablurb .= ', ' . $memberIPData[$marker['id']]['region'];
				$datablurb .=  '<br />';
				if (!empty($memberIPData[$marker['id']]['country']))
					$datablurb .= '<img src="' . $settings['default_images_url'] . '/flags/' . $memberIPData[$marker['id']]['cc'] . '.png"  height="16" width="11" border="0" alt="[ * ]" title="' . $memberIPData[$marker['id']]['country'] . '"/><br />';
				$datablurb .= '
				</td>
			</tr>
		</table>';
			}
			else
				$datablurb = $txt['who_member'];

			// Let's bring it all together...
			$markers = '<marker lat="' . round($memberIPData[$marker['id']]['latitude'], 6) . '" lng="' . round($memberIPData[$marker['id']]['longitude'], 6) . '" ';
			$markers .= 'label="' . $marker['name'] . '"><![CDATA[' . $datablurb . ']]></marker>';
			echo $markers;
		}
	}

	// and now those lovely little guests and spiders as well
	if (!empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] < 3 || allowedTo('admin_forum')) && !empty($modSettings['spider_name_cache']))
		$spidernames = unserialize($modSettings['spider_name_cache']);
	foreach ($guests as $marker)
	{
		if (!empty($marker['id_spider']) && empty($modSettings['show_spider_online']))
			continue;
		$marker['name'] = empty($marker['id_spider']) ? $txt['guest'] : (isset($spidernames[$marker['id_spider']]) ? $spidernames[$marker['id_spider']] : $txt['spider']);
		$markers = '<marker lat="' . round($marker['latitude'], 6) . '" lng="' . round($marker['longitude'], 6) . '" ';
		$markers .= 'label="' . $marker['name'] . '"><![CDATA[' . $marker['name'] . ']]></marker>';
		echo $markers;
	}

	echo '
</markers>';

	// Ok we should be done with output, dump it to user...
	obExit(false);
}

/**
 * geo_buildpins()
 *
 * Does the majority of work in determining how the map pin should look based on admin settings
 *
 * @return
 */
function geo_buildpins()
{
	global $modSettings;

	// lets work out all those options
	$modSettings['geoIPPinBackground'] = geo_validate_color('geoIPPinBackground', '66FF66');
	$modSettings['geoIPPinForeground'] = geo_validate_color('geoIPPinForeground', '202020');

	// what kind of pins have been chosen
	$mpin = geo_validate_pin('geoIPPinStyle', 'd_map_pin_icon');

	// shall we add in shadows
	$mshd = (isset($modSettings['geoIPPinShadow']) && $modSettings['geoIPPinShadow']) ? $mshd = '_withshadow' : $mshd = '';

	// set the member and cluster pin styles, icon or text
	if ($mpin == 'd_map_pin_icon')
		$mchld = ((isset($modSettings['geoIPPinIcon']) && trim($modSettings['geoIPPinIcon']) != '') ? $modSettings['geoIPPinIcon'] : 'info');
	elseif ($mpin == 'd_map_pin_letter')
		$mchld = (isset($modSettings['geoIPPinText']) && trim($modSettings['geoIPPinText']) != '') ? $modSettings['geoIPPinText'] : '';
	else {
		$mpin = 'd_map_pin_letter';
		$mchld = '';
	}

	// and now for the colors
	$mchld .= '|' . $modSettings['geoIPPinBackground'] . '|' . $modSettings['geoIPPinForeground'];

	// Build those pins
	$modSettings['npin'] = '?chst=' . $mpin . $mshd . '&chld=' . $mchld;
	if ($mpin == 'd_map_pin_icon')
		$modSettings['mpin'] = '?chst=d_map_pin_icon' . $mshd . '&chld=WCmale|0066FF';
	else
		$modSettings['mpin'] = '?chst=d_map_pin_letter' . $mshd . '&chld=|0066FF|' . $modSettings['geoIPPinForeground'];

	return;
}

/**
 * geo_validate_color()
 *
 * Makes sure we have a 6digit hex for the color definitions or sets a default value
 *
 * @param mixed $color
 * @param mixed $default
 * @return
 */
function geo_validate_color($color,$default)
{
	global $modSettings;

	// no leading #'s please
	if (substr($modSettings[$color], 0, 1) == '#')
		$modSettings[$color] = substr($modSettings[$color],1);

	// is it a hex
	if (!preg_match('/^[a-f0-9]{6}$/i', $modSettings[$color]))
		$modSettings[$color] = $default;

	return strtoupper($modSettings[$color]);
}

/**
 * geo_validate_pin()
 *
 * outputs the correct goggle chart pin type based on selection
 *
 * @param mixed $area
 * @param mixed $default
 * @return
 */
function geo_validate_pin($area, $default)
{
	global $modSettings;

	if (isset($modSettings[$area]))
	{
		switch ($modSettings[$area])
		{
			case 'plainpin':
				$pin = 'd_map_pin';
				break;
			case 'textpin':
				$pin = 'd_map_pin_letter';
				break;
			case 'iconpin':
				$pin = 'd_map_pin_icon';
				break;
			default:
				$pin = 'd_map_pin_icon';
		}
	}
	else
		$pin = $default;

	return $pin;
}

/**
 * geo_dot2long()
 *
 * takes a 123.456.789.012 ip address are returns it as a long int
 * take a long int and converts it back to a dot ip address
 *
 * @param mixed $ip_addr
 * @return
 */
function geo_dot2long($ip_addr)
{
	// sure we could use built in functions but why when math is fun!
    if (empty($ip_addr))
		return 0;
    elseif (strpos($ip_addr, '.') === false)
		return (int) ($ip_addr / (256 * 256 * 256) % 256) . '.' . (int) ($ip_addr / (256 * 256) % 256) . '.' . (int) (($ip_addr / 256) % 256) . '.' . (int) (($ip_addr) % 256);
    elseif (preg_match('~\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}~', $ip_addr, $dummy))
	{
        $ips = explode('.', $ip_addr);
        return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
    }
	else
		return 0;
}

/**
 * geo_search()
 *
 * Takes an array of ip address and determines the geo location
 * uses the database to find the location, if it returns a generic hit and search is set then uses api.hostip.info as backup
 * returns the information in an array
 *
 * @param mixed $ips
 * @param mixed $search
 * @return
 */
function geo_search($ip_input, $search = true)
{
	global $smcFunc, $sourcedir;

	require_once ($sourcedir . '/Subs-Package.php');
	$memberIPData = array();
	$ips = array();

	// It must be an array, even if we are only looking up one IP
	if (!is_array($ip_input))
		$ips = array($ip_input);
	else
	{
		// passed an array from the log_online, this should contain all geoip info established at logon
		foreach ($ip_input as $member => $data)
		{
			// already have the data?
			if (!empty($data['latitude']) && !empty($data['longitude']) && !empty($data['country']) && !empty($data['cc']))
			{
				// data is available, use it and save a database lookup
				$memberIPData[$member]['country'] = $data['country'];
				$memberIPData[$member]['city'] = isset($data['city']) ? $data['city'] : '';
				$memberIPData[$member]['latitude'] = $data['latitude'];
				$memberIPData[$member]['longitude'] = $data['longitude'];
				$memberIPData[$member]['cc'] = $data['cc'];
				$memberIPData[$member]['session'] = $data['session'];
			}
			// doh! look it up instead :\
			else
				$ips[$member] = $data['ip'];
		}
	}

	// Query the Geoip database for each IP we need to find ... There is NO batch/group method for this and the
	// table is 3M lines long so you need to be VERY carful with what you do here!
	foreach ($ips as $member => $ip)
	{
		// make sure its an int an not in a 1.2.3.4 format
		if (strpos($ip,'.'))
			$ip = geo_dot2long($ip);

		$request = $smcFunc['db_query']('', '
			SELECT ip.start, ip.end,
				bl.city, bl.latitude, bl.longitude,
				gc.cc, gc.cn as country,
				gr.rn as region
			FROM {db_prefix}geoip_ip as ip
				LEFT JOIN {db_prefix}geoip_blocks AS bl ON (ip.locid = bl.locid)
				LEFT JOIN {db_prefix}geoip_countries AS gc ON (gc.cc = bl.country)
				LEFT JOIN {db_prefix}geoip_regions AS gr ON (gr.rc = bl.region && gr.cc = bl.country)
			WHERE ip.end >= {int:ip}
			ORDER BY ip.end ASC
			LIMIT 1',
			array(
				'ip' => (int) $ip
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$memberIPData[$member] = false;

			// if the search IP is not part of any range, it will still return the next highest range.
			// we need to check whether ip_start is <= your IP >= ip_end and not local host 127.0.0.1
			if ($row['start'] <= $ip && $row['end'] >= $ip && $ip != 2130706433)
			{
				$memberIPData[$member] = $row;
				if (empty($row['city']) && $search)
				{
					// did not find a full match, likely the center of a country, so try an external service
					$data = fetch_web_data('http://api.hostip.info/get_html.php?ip=' . geo_dot2long($ip) . '&position=true');
					if (preg_match('~Country: (.*(?:\((.*)\)))\n?City: (.*)\n?Latitude: (.*)\nLongitude: (.*)\n~isU', $data, $match))
					{
						// We trust the country & data from geomind just a bit more
						if (!empty($match[2]) && $match[2] == $row['cc'])
						{
							// put this result into our result for this user
							$memberIPData[$member]['country'] = $match[1];
							$memberIPData[$member]['city'] = $match[3];
							$memberIPData[$member]['latitude'] = $match[4];
							$memberIPData[$member]['longitude'] = $match[5];

							// now update the online log so we don't do this again if it was for an online user ofcourse
							if (!empty($memberIPData[$member]['session']))
								geo_save_data($memberIPData[$member]);
						}
					}
				}
			}
		}
		$smcFunc['db_free_result']($request);
	}
	return $memberIPData;
}

/**
 * geo_search_lite()
 *
 * Takes an array of ip address and determines the geo location
 * uses the lite database to find the country only
 * if search is set then uses api.hostip.info to get extra info
 * returns the information in an array
 *
 * @param mixed $ips
 * @param mixed $search
 * @return
 */
function geo_search_lite($ip_input, $search = false)
{
	global $smcFunc, $sourcedir;

	require_once ($sourcedir . '/Subs-Package.php');
	$ips = array();

	// It must be an array
	if (!is_array($ip_input))
		$ips = array($ip_input);
	else
	{
		// passed an array from the log_online, this should already contain all geoip info established at logon
		foreach ($ip_input as $member => $data)
		{
			// already have the data
			if (!empty($data['country']) && !empty($data['cc']))
			{
				// data is available, use it and save another lookup
				$memberIPData[$member]['country'] = $data['country'];
				$memberIPData[$member]['cc'] = $data['cc'];
			}
			// doh! look it up instead :\
			else
				$ips[$member] = $data['ip'];
		}
	}

	// Query the Geoip database for each IP
	foreach ($ips as $member => $ip)
	{
		// make sure its an int an not in a 1.2.3.4 format
		if (strpos($ip,'.'))
			$ip = geo_dot2long($ip);

		$request = $smcFunc['db_query']('', '
			SELECT ip.start, ip.end,
				gc.cc, gc.cn as country
			FROM {db_prefix}geoip_ip as ip
				LEFT JOIN {db_prefix}geoip_countries AS gc ON (ip.locid = gc.ci)
			WHERE ip.end >= {int:ip}
			ORDER BY ip.end ASC
			LIMIT 1',
			array(
				'ip' => (int) $ip
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$memberIPData[$member] = false;
			// if the search IP is not part of any range, it will return the next highest range.
			// we need to check whether ip_start is <= your IP and not local host 127.0.0.1
			if ($row['start'] <= $ip && $row['end'] >= $ip && $ip != 2130706433)
			{
				// this data is not available from lite, just the country code is ....
				$memberIPData[$member] = $row;
				$memberIPData[$member]['city'] = '';
				$memberIPData[$member]['region'] = '';
				$memberIPData[$member]['latitude'] = 0.0;
				$memberIPData[$member]['longitude'] = 0.0;
				if ($search)
				{
					// We want more so use an external service
					$data = fetch_web_data('http://api.hostip.info/get_html.php?ip=' . geo_dot2long($ip) . '&position=true');
					if (preg_match('~Country: (.*(?:\((.*)\)))\n?City: (.*)\n?Latitude: (.*)\nLongitude: (.*)\n~isU', $data, $match))
					{
						// We trust the country & data from geomind more, so make sure the CC's match before adding
						if (!empty($match[2]) && $match[2] == $row['cc'])
						{
							// put this result into our result for this user
							$memberIPData[$member]['country'] = $match[1];
							$memberIPData[$member]['city'] = $match[3];
							$memberIPData[$member]['latitude'] = $match[4];
							$memberIPData[$member]['longitude'] = $match[5];
						}
					}
				}
			}
		}
		$smcFunc['db_free_result']($request);
	}
	return $memberIPData;
}

function geo_save_data($data)
{
	global $smcFunc;
	$data=array();
	// simply update this session with the newly found data
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_online
		SET latitude = {float:latitude}, longitude = {float:longitude}, country = {string:country}, city = {string:city}, cc = {string:cc}
		WHERE session = {string:session}',
		array(
			'latitude' => $data['latitude'],
			'longitude' => $data['longitude'],
			'country' =>  $data['country'],
			'city' => $data['city'],
			'cc' => $data['cc'],
			'session' => $data['session'],
		)
	);
}
?>