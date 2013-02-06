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
 * geoIPEntry()
 *
 * Traffic cop, checks permissions
 * calls the appropriate sub-function
 *
 * @return
 */
function geoIPEntry()
{
	// The entrance point for all 'geoIP' actions.
	global $context, $txt, $scripturl, $sourcedir;

	// admins only
	isAllowedTo('admin_forum');

	// subaction array ... function to call
	$subActions = array(
		'main' => array('geoIPMain'),
		'settings' => array('geoIPRegSettings'),
		'map' => array('geoIPMapSettings'),
		'xml' => array('geoIPxml'),
	);

	// Default to sub action main if nothing else was provided
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	// Setup the admin tabs and templates, but not for an xml response, thats just a passthru
	if ($_REQUEST['sa'] != 'xml')
	{
		// Language and template stuff, the usual.
		loadLanguage('geoIP');
		loadTemplate('geoIP');
		geoIPhtml();

		$context['tabindex'] = 1;
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['geoIP'],
			'help' => $txt['geoIP_help'],
			'description' => $txt['geoIP_description_' . $_REQUEST['sa']],
		);
	}

	// Call the right function
	$subActions[$_REQUEST['sa']][0]();
}

/**
 * geoIPMain()
 *
 * Sets up the context vars for the main geoIP admin page
 * If updating the database call the full, lite or manual functions
 * saves the admin age settings for how the mod functions
 *
 * @return
 */
function geoIPMain()
{
	global $context, $txt, $smcFunc, $sourcedir, $modSettings;

	// is a database installed?
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}geoip_ip',
		array(
		)
	);
	list ($context['geoIP_count']) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

	// Made of the right stuff?
	$context['geoIP_prereq_sql'] = $smcFunc['db_title'] == 'MySQL';
	$context['geoIP_prereq_zip'] = get_extension_funcs('zip');

	// db Install choices
	$context['geoIP_db_option'] = array(
		array('id' => 1, 'name' => $txt['db_option1'], 'desc' => $txt['db_option1_desc'] . ' ' . ($context['geoIP_prereq_zip'] ? $txt['db_zip'] : $txt['db_zlib'])),
		array('id' => 2, 'name' => $txt['db_option2'], 'desc' => $txt['db_option2_desc'] ),
		array('id' => 3, 'name' => $txt['db_option3'], 'desc' => $txt['db_option3_desc'] ),
	);

	// Have we been asked to update the database, if so we have a lot to do!
	if (isset($_POST['load_database']) && empty($_POST['save']))
	{
		// no funny biz yall
		checkSession('post');

		// Init
		updateSettings(array('geoIP_status' => 5, 'geoIP_status_details' => $txt['geoIP_status0']));

		// What database are we updating, full, lite, manual?
		$id_type = (int) $_POST['id_type'];
		switch ($id_type)
		{
			case 1:
				geoIPFull();
				break;
			case 2:
				geoIPLite();
				break;
			case 3:
				geoIPManual();
				break;
		}

		// Redirect back to the mod so we can show an update status
		$_REQUEST['db_id'] = $id_type;
		redirectexit('action=admin;area=geoIP');
	}

	// Just updating the mod settings?
	if (isset($_POST['save']))
	{
		// You can save, maybe
		checkSession('post');

		// clean em
		$_POST['geoIP_enablemap'] = empty($_POST['geoIP_enablemap']) ? 0 : 1;
		$_POST['geoIP_enablepinid'] = empty($_POST['geoIP_enablepinid']) ? 0 : 1;
		$_POST['geoIP_enablereg'] = empty($_POST['geoIP_enablereg']) ? 0 : 1;
		$_POST['geoIP_enableflags'] = empty($_POST['geoIP_enableflags']) ? 0 : 1;

		// save em
		updateSettings(array(
			'geoIP_enablemap' => $_POST['geoIP_enablemap'],
			'geoIP_enablepinid' => $_POST['geoIP_enablepinid'],
			'geoIP_enablereg' => $_POST['geoIP_enablereg'],
			'geoIP_enableflags' => $_POST['geoIP_enableflags'],
		));

		// show em off
		redirectexit('action=admin;area=geoIP;saved');
	}

	// Setup the title and template.
	
	$_REQUEST['db_id'] = isset($modSettings['geoIP_db']) ? $modSettings['geoIP_db'] : 1;
	$context['page_title'] = $txt['geoIP'];
	$context['sub_template'] = 'main';
}

/**
 * geoIPFull()
 *
 * finds the latest city lite csv file from maxmind
 * downloads the file
 * calls unzip
 * calls the geoIP_db_full csv function
 * updates the status so our ajax request knows whats going on
 *
 * @return
 */
function geoIPFull()
{
	global $context, $txt, $smcFunc, $sourcedir;

	// Init
	$address = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/';
	$regex1 = '~<a href="(.*?)".*?([0-9][0-9])M\s+.*?<hr>~U';
	$regex2 = '~<a href="(.*?)".*?([0-9][0-9])M.*\s+<tr><th~';

	// First find the latest csv file, there is no direct MOST RECENT link that I can find
	require_once ($sourcedir . '/Subs-Package.php');
	$pagedata = fetch_web_data($address);

	// If we found the page with the downloads
	if ($pagedata)
	{
		// We want the last file listed in the csv page and its size ... the last is the latest ;)
		$result = (preg_match($regex1, $pagedata, $data)) ? true : preg_match($regex2, $pagedata, $data);
		if ($result)
		{
			// we found the latest file name to download
			updateSettings(array('geoIP_status' => 10, 'geoIP_status_details' => sprintf($txt['geoIP_status1'], $data[1])));
			unset($pagedata);

			// what to get and where to put it
			$address = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/' . $data[1];
			$filename = $sourcedir . '/geoIP/' . $data[1];

			// This is a 32M file, can take a while to download if the connection or maxmind site is slow, try to get more time
			@set_time_limit(900);
			$zipfile = geoIP_fetchdata($address, $data[2]);
			list($dummy,$filedate) = explode('_',str_replace('.zip', '', $data[1]));

			// if we got the file, save it
			if ($zipfile)
			{
				// open the file for writing, and well write it out!
				$fp = @fopen($filename, 'wb');
				if ($fp)
				{
					fwrite($fp, $zipfile);
					fclose($fp);

					// relase the memory, update where we are
					unset($zipfile, $data);
					updateSettings(array('geoIP_status_details' => $txt['geoIP_status2']));

					// Time to unzip ... two choices, lets pick the best one based on availablity
					if (get_extension_funcs('zip') !== false)
						$extracted = geoIP_unzip_ziplib($filename,$sourcedir . '/geoIP/');
					else
						$extracted = geoIP_unzip_zlib($filename,$sourcedir . '/geoIP/', 288);

					// unzip result says ....
					if ($extracted !== false)
					{
						// the sweet taste of success ?
						if (!empty($extracted) || (file_exists($sourcedir . '/geoIP/GeoLiteCity-Blocks.csv') && file_exists($sourcedir . '/geoIP/GeoLiteCity-Location.csv')))
						{
							@unlink($filename);
							updateSettings(array('geoIP_status' => 75, 'geoIP_status_details' => $txt['geoIP_status4']));

							// place the data in to the database so its available.
							if (geoIP_db_full())
								updateSettings(array('geoIP_date' => substr($filedate,4,2) . '-' . substr($filedate,6,2) . '-' . substr($filedate,0,4)));
						}
						// cant find the unziped files?
						else
							updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status12']));
					}
					// Error doing the unzip
					else
						updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status3']));
				}
				// could not open the file for writting?
				else
					updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status11']));
			}
			// error getting the zip file, maxmind can be slow so probably a timeout
			else
				updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status9']));
		}
		// error parsing the page
		else
			updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status10']));
	}
	// Maxmind did not respond, prob a temp block from trying to much, leecher ;)
	else
		updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status7']));
}

/**
 * geoIPLite()
 *
 * finds the latest country lite csv file from maxmind
 * downloads the file
 * calls unzip
 * calls the geoIP_db_lite function
 * updates the status so our ajax request knows whats going on
 *
 * @return
 */
function geoIPLite()
{
	global $context, $txt, $smcFunc, $sourcedir;

	// what to get and where to put it
	$address = 'http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip';
	$filename = $sourcedir . '/geoIP/GeoIPCountryCSV.zip';

	// download the file, approx size is 2.1 for the status line
	$zipfile = geoIP_fetchdata($address,2.1);

	// if we got the file, save it
	if ($zipfile)
	{
		// open the file for writing, and well write it out!
		$fp = @fopen($filename, 'wb');
		if ($fp)
		{
			fwrite($fp, $zipfile);
			fclose($fp);

			// free up some memory, let the admin know we are alive
			unset($zipfile);
			updateSettings(array('geoIP_status_details' => $txt['geoIP_status2']));

			// Time to unzip ... two choices, lets pick the best one based on availablity
			if (get_extension_funcs('zip') !== false)
				$extracted = geoIP_unzip_ziplib($filename,$sourcedir . '/geoIP/');
			else
				$extracted = geoIP_unzip_zlib($filename,$sourcedir . '/geoIP/',128);

			// unzip result says ....
			if ($extracted !== false)
			{
				// victory is ours?
				if (!empty($extracted) || (file_exists($sourcedir . '/geoIP/GeoIPCountryWhois.csv')))
				{
					@unlink($filename);
					updateSettings(array('geoIP_status' => 80, 'geoIP_status_details' => $txt['geoIP_status4']));
					$filetime = filemtime($sourcedir . '/geoIP/GeoIPCountryWhois.csv');

					// try to put the data in to the database so we can use it
					if (geoIP_db_lite())
						updateSettings(array('geoIP_date' => date("m-d-Y", $filetime)));
				}
				// cant find the unziped files?
				else
					updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status12']));
			}
			// Error doing the unzip
			else
				updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status3']));
		}
		// could not open the file for writting?
		else
			updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status11']));
	}
	// error getting the zip file, maxmind can be slow so probably a timeout
	else
		updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status9']));

}

/**
 * geoIPManual()
 *
 * finds either the city csv file set or the country csv file in /geoip
 * calls either db_full or db_lite based on what it finds
 *
 * @return
 */
function geoIPManual()
{
	global $txt, $sourcedir;

	// City Data?
	if ((file_exists($sourcedir . '/geoIP/GeoLiteCity-Blocks.csv') && file_exists($sourcedir . '/geoIP/GeoLiteCity-Location.csv')))
	{
		updateSettings(array('geoIP_status' => 60, 'geoIP_status_details' => $txt['geoIP_status4']));

		// load in the city lite csv files
		$filetime = filemtime($sourcedir . '/geoIP/GeoLiteCity-Location.csv');
		if (geoIP_db_full(false))
			updateSettings(array('geoIP_status' => 100, 'geoIP_date' => date("m-d-Y", $filetime)));
	}
	// Country Only
	elseif (file_exists($sourcedir . '/geoIP/GeoIPCountryWhois.csv'))
	{
		updateSettings(array('geoIP_status' => 60, 'geoIP_status_details' => $txt['geoIP_status4']));

		// load in the country database
		$filetime = filemtime($sourcedir . '/geoIP/GeoIPCountryWhois.csv');
		if (geoIP_db_lite(false))
			updateSettings(array('geoIP_status' => 100, 'geoIP_date' => date("m-d-Y", $filetime)));
	}
	// cant find the anything
	else
		updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status12']));
}

/**
 * geoIP_db_full()
 *
 * Calls geoIP_importCsv to load the database
 * Loads the full set of csv files in to the database
 * optionally removes the csv files
 *
 * @param mixed $remove
 * @return
 */
function geoIP_db_full($remove = true)
{
	global $txt, $sourcedir;

	// loading in the ip blocks csv file
	$filename = $sourcedir . '/geoIP/GeoLiteCity-Blocks.csv';
	$result_ip = geoIP_importCsv($filename, 'geoip_ip', 'start', $remove);
	updateSettings(array('geoIP_status' => 85));

	// loading in the location table csv file
	$filename = $sourcedir . '/geoIP/GeoLiteCity-Location.csv';
	$result_block = geoIP_importCsv($filename, 'geoip_blocks', 'locid', $remove);

	// Make sure things went as planned
	if ($result_ip && $result_block)
		updateSettings(array('geoIP_status' => 100, 'geoIP_status_details' => $txt['geoIP_status5'], 'geoIP_db' => 1));
	else
		updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status6']));

	return ($result_ip && $result_block);
}

/**
 * geoIP_db_lite()
 *
 * Calls geoIP_importCsv to load a temp table
 * builds an optimized table and links it to the country codes table
 * optionally removes the csv file
 *
 * @param mixed $remove
 * @return
 */
function geoIP_db_lite($remove = true)
{
	global $txt, $sourcedir, $smcFunc;

	// loading in the country csv file
	$filename = $sourcedir . '/geoIP/GeoIPCountryWhois.csv';
	$result_ip = geoIP_importCsv($filename, 'geoip_ip_temp', '', $remove);
	updateSettings(array('geoIP_status' => 75));

	// clear the receiving table of its data
	$smcFunc['db_query']('truncate_table', '
		TRUNCATE {db_prefix}geoip_ip',
		array()
	);

	// copy just the items that we need from the geoip_ip_temp to the geoip_ip table to save some space, link in the country table as well
	$request = $smcFunc['db_query']('', '
		INSERT INTO {db_prefix}geoip_ip
			(start, end, locid)
		SELECT t.start, t.end, c.ci
		FROM {db_prefix}geoip_ip_temp AS t
			LEFT JOIN {db_prefix}geoip_countries AS c ON t.cc = c.cc',
		array(
		)
	);
	updateSettings(array('geoIP_status' => 85));

	// clear the temp table of its data, its just taking up space now
	$smcFunc['db_query']('truncate_table', '
		TRUNCATE {db_prefix}geoip_ip_temp',
		array()
	);

	// Make sure things went as planned
	if ($result_ip && $request)
		updateSettings(array('geoIP_status' => 100, 'geoIP_status_details' => $txt['geoIP_status5'], 'geoIP_db' => 2));
	else
		updateSettings(array('geoIP_status' => 101, 'geoIP_status_details' => $txt['geoIP_status6']));

	return ($result_ip && $request);
}

/**
 * geoIPRegSettings()
 *
 * Updates the registration settings
 * Allows the admin to select countries to block or allo
 *
 * @return
 */
function geoIPRegSettings()
{
	global $txt, $scripturl, $context;

	// Saving?
	if (isset($_POST['save']))
	{
		checkSession();

		// Clean up the response
		if (!isset($_POST['geoIPCC']))
			$_POST['geoIPCC'] = array();
		elseif (!is_array($_POST['geoIPCC']))
			$_POST['geoIPCC'] = array($_POST['geoIPCC']);

		// all the country codes selected as just a single string please
		$_POST['geoIPCC'] = implode(',', $_POST['geoIPCC']);
		$_POST['geoIP_cc_block'] = empty($_POST['geoIP_cc_block']) ? 0 : 1;

		// save the updates
		updateSettings(array(
			'geoIPCC' => $_POST['geoIPCC'],
			'geoIP_cc_block' => $_POST['geoIP_cc_block'],
		));
	}

	// Load the country data in to context for selection
	geoIP_country();

	// Setup the title and template, etc
	$context['page_title'] = $txt['geoIP'];
	$context['sub_template'] = 'geoIPreg';
	$context['post_url'] = $scripturl . '?action=admin;area=geoIP;sa=settings';
	$context['settings_title'] = $txt['geoIPFO'];
}

/**
 * geoIPMapSettings()
 *
 * Updates the maps settings
 *
 * @return
 */
function geoIPMapSettings()
{
	global $txt, $scripturl, $context, $settings, $sourcedir;

	$config_vars = array(
			// Geoip - sidebar?
			array('select', 'geoIPSidebar', array('none' => $txt['nosidebar'], 'right' => $txt['rightsidebar'])),
		'',
			// Map Type
			array('select', 'geoIPType', array(
				'ROADMAP' => $txt['roadmap'],
				'SATELLITE' => $txt['satellite'],
				'HYBRID' => $txt['hybrid']
				)
			),
			array('select', 'geoIPNavType', array(
				'SMALL' => $txt['gsmallzoomcontrol'],
				'LARGE' => $txt['glargezoomcontrol'],
				'DEFAULT' => $txt['gdefaultzoomcontrol']
				)
			),
		'',
			// Default Location/Zoom
			array('float', 'geoIPDefaultLat', '25'),
			array('float', 'geoIPDefaultLong', '25'),
			array('int', 'geoIPDefaultZoom'),
		'',
			// Member Pin Style
			array('text', 'geoIPPinBackground', '6'),
			array('text', 'geoIPPinForeground', '6'),
			array('select', 'geoIPPinStyle',
				array(
					'plainpin' => $txt['plainpin'],
					'textpin' => $txt['textpin'],
					'iconpin' => $txt['iconpin']
				)
			),
			array('check', 'geoIPPinShadow'),
			array('int', 'geoIPPinSize', '2'),
			array('text', 'geoIPPinText'),
			array('select', 'geoIPPinIcon',
				array(
					'academy' => $txt['academy'],
					'activities' => $txt['activities'],
					'airport' => $txt['airport'],
					'amusement' => $txt['amusement'],
					'aquarium' => $txt['aquarium'],
					'art-gallery' => $txt['art-gallery'],
					'atm' => $txt['atm'],
					'baby' => $txt['baby'],
					'bank-dollar' => $txt['bank-dollar'],
					'bank-euro' => $txt['bank-euro'],
					'bank-intl' => $txt['bank-intl'],
					'bank-pound' => $txt['bank-pound'],
					'bank-yen' => $txt['bank-yen'],
					'bar' => $txt['bar'],
					'barber' => $txt['barber'],
					'beach' => $txt['beach'],
					'beer' => $txt['beer'],
					'bicycle' => $txt['bicycle'],
					'books' => $txt['books'],
					'bowling' => $txt['bowling'],
					'bus' => $txt['bus'],
					'cafe' => $txt['cafe'],
					'camping' => $txt['camping'],
					'car-dealer' => $txt['car-dealer'],
					'car-rental' => $txt['car-rental'],
					'car-repair' => $txt['car-repair'],
					'casino' => $txt['casino'],
					'caution' => $txt['caution'],
					'cemetery-grave' => $txt['cemetery-grave'],
					'cemetery-tomb' => $txt['cemetery-tomb'],
					'cinema' => $txt['cinema'],
					'civic-building' => $txt['civic-building'],
					'computer' => $txt['computer'],
					'corporate' => $txt['corporate'],
					'fire' => $txt['fire'],
					'flag' => $txt['flag'],
					'floral' => $txt['floral'],
					'helicopter' => $txt['helicopter'],
					'home' => $txt['home1'],
					'info' => $txt['info'],
					'landslide' => $txt['landslide'],
					'legal' => $txt['legal'],
					'location' => $txt['location1'],
					'locomotive' => $txt['locomotive'],
					'medical' => $txt['medical'],
					'mobile' => $txt['mobile'],
					'motorcycle' => $txt['motorcycle'],
					'music' => $txt['music'],
					'parking' => $txt['parking'],
					'pet' => $txt['pet'],
					'petrol' => $txt['petrol'],
					'phone' => $txt['phone'],
					'picnic' => $txt['picnic'],
					'postal' => $txt['postal'],
					'repair' => $txt['repair'],
					'restaurant' => $txt['restaurant'],
					'sail' => $txt['sail'],
					'school' => $txt['school'],
					'scissors' => $txt['scissors'],
					'ship' => $txt['ship'],
					'shoppingbag' => $txt['shoppingbag'],
					'shoppingcart' => $txt['shoppingcart'],
					'ski' => $txt['ski'],
					'snack' => $txt['snack'],
					'snow' => $txt['snow'],
					'sport' => $txt['sport'],
					'star' => $txt['star'],
					'swim' => $txt['swim'],
					'taxi' => $txt['taxi'],
					'train' => $txt['train'],
					'truck' => $txt['truck'],
					'wc-female' => $txt['wc-female'],
					'wc-male' => $txt['wc-male'],
					'wc' => $txt['wc'],
					'wheelchair' => $txt['wheelchair'],
				)
			),
	);

	// Our helper functions
	require_once($sourcedir . '/ManagePermissions.php');
	require_once($sourcedir . '/ManageServer.php');

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=geoIP;sa=map');
	}

	// Setup the title and template.
	$context['page_title'] = $txt['geoIP'];
	$context['sub_template'] = 'show_settings';
	$context['post_url'] = $scripturl . '?action=admin;area=geoIP;save;sa=map';
	$context['settings_title'] = $txt['geoIPFO'];

	prepareDBSettingContext($config_vars);
}

/**
 * geoIP_country()
 *
 * loads all of the counties from the database for display
 * loads the currently selected counties from the settings table
 * builds the context array with the information for dispaly via geoIPRegSettings
 *
 * @return
 */
function geoIP_country()
{
	global $context, $smcFunc, $modSettings;

	// don't really need these ....
	$remove_codes = array(
		'A1' =>	'Anonymous Proxy',
		'A2' =>	'Satellite Provider',
		'O1' =>	'Other Country'
	);

	// load all the country codes in to an array for use
	$temp = array();
	$request = $smcFunc['db_query']('', '
		SELECT cc, cn
		FROM {db_prefix}geoip_countries
		ORDER BY cn',
		array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$temp[$row['cc']] = trim($row['cn']);
	$smcFunc['db_free_result']($request);

	// One key only and dump the ones we don't want
	$geoIPCCs = array_diff($temp,$remove_codes);
	$geoIPCCs = array_unique($geoIPCCs);

	// load up what has been selected to date
	$geoIP_cc_checked = array();
	if (!empty($modSettings['geoIPCC']))
	{
		$temp = explode(',', $modSettings['geoIPCC']);

		// set the cc as the key, just easier
		foreach ($temp as $cc)
			$geoIP_cc_checked[$cc] = 1;
	}

	// The number of columns we want to show, would need to change the col width in the template if you change this
	$numColumns = 3;
	$totalCCs = count($geoIPCCs);

	// Start working out the context stuff.
	$context['geoCC_columns'] = array();
	$geoCCsPerColumn = ceil($totalCCs / $numColumns);
	$col = 0;
	$i = 0;
	foreach ($geoIPCCs as $geoCC => $geoCN)
	{
		if ($i % $geoCCsPerColumn == 0 && $i != 0)
			$col++;
		$context['geoCC_columns'][$col][] = array('cn' => $geoCN, 'cc' => $geoCC, 'checked' => isset($geoIP_cc_checked[$geoCC]));
		$i++;
	}
}

/**
 * geoIP_importCsv()
 *
 * starts by clearing any data from the existing table
 * for a supplied filename, loads it to the tablename
 * optionally removes extra rows from the table
 *
 * @param mixed $filename
 * @param mixed $tablename
 * @param string $extra
 * @param mixed $remove
 * @return
 */
function geoIP_importCsv($filename, $tablename, $extra = '', $remove = true)
{
	global $connection, $db_server, $db_user, $db_passwd, $smcFunc;

	// clear the old table of its data
	$smcFunc['db_query']('truncate_table', '
		TRUNCATE {db_prefix}' . $tablename,
		array()
	);

	// make the everything safe for our query, ** yup load data is mysql ONLY, sorry to non conformers
	$query = 'LOAD DATA LOCAL INFILE {string:filename} INTO TABLE {db_prefix}' . $tablename . ' FIELDS TERMINATED BY {string:comma} OPTIONALLY ENCLOSED BY {string:quote} LINES TERMINATED BY {string:break}';
	$query = $smcFunc['db_quote']($query, array('filename' => $filename, 'comma' => ',', 'quote' => '"', 'break' => "\n"));

	// Load all the new data in one big gulp
	$connection = ($connection == null ? @mysql_connect($db_server, $db_user, $db_passwd) : $connection);
	$result = mysql_query($query, $connection);
	if ($result)
	{
		if ($remove)
			unlink($filename);

		// remove header lines as well?
		if (!empty($extra))
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}' . $tablename . '
				WHERE ' . $extra . ' = {int:start}',
				array(
					'start' => 0,
				)
			);
	}
	return $result;
}

/**
 * geoIP_unzip_ziplib()
 *
 * SMF's standard zip functions did not work with maxmind files (invalid ??)
 * additionally the standard functions unpack the full file in memory which is bad for this file (133M)
 * unzips source file to destination directory (ignores directors in the source file)
 * writes out the file in chunks to preserve memory
 * needs PHP with zip extension or it will not work
 *
 * @param mixed $source
 * @param mixed $destination
 * @return
 */
function geoIP_unzip_ziplib($source, $destination)
{
	// This is the prefered way to work with zip files as it is not a memory pig and you don't have to do a bunch
	// of work to extract the deflated data streams for gzinflate to work on.  The downside is that the zip library
	// is not always available.

	if (substr($destination, -1) != '/')
		$destination .= '/';
	$extracted = array();

	// Perform a test to see if the zip module is installed.
	if (get_extension_funcs('zip'))
	{
		// Make sure we have the memory to do this
		if (@ini_get('memory_limit') < 128)
			@ini_set('memory_limit', '128M');

		// Open the zip file
		$zip = zip_open($source);
		if ($zip)
		{
			while ($zip_entry = zip_read($zip))
			{
				// Get this zip file details, note we only want the name, we are not extracting directories ATM so strip those
				$name = basename(zip_entry_name($zip_entry));
				$filesize = zip_entry_filesize($zip_entry);

				// save it to the supplied directory
				if (zip_entry_open($zip, $zip_entry, 'r'))
				{
					$fp = @fopen($destination . $name, 'w');
					chmod($destination . $name, 0755);
					if ($fp)
					{
						// save this in chunks to save memory
						while($filesize > 0)
						{
							$chunksize = ($filesize > 10240) ? 10240 : $filesize;
							$filesize -= $chunksize;
							$buf = zip_entry_read($zip_entry, $chunksize);
							if ($buf !== false)
								fwrite($fp, $buf);
						}
						fclose($fp);
					}
					$extracted[]['name'] = $name;
					$extracted[]['filesize'] = zip_entry_filesize($zip_entry);
					zip_entry_close($zip_entry);
				}
				// could not open the file to save this
				else
					$extracted = false;
			}
			// Yipee we are done!
			zip_close($zip);
		}
		// Was not a valid zip file
		else
			$extracted = false;

		return $extracted;
	}
	// no zip lib functions here :(
	else
		return false;
}

/**
 * geoIP_unzip_zlib()
 *
 * This entire function is here because SMF's built in zip handler does not work with the maxmind zip files.
 *
 * Now *why* is the question ? ? ?
 *
 * SMF's function results in an invalid data response from gzinflate.  This appears to be because it does not calculate
 * the correct data offset for the start of the compressed data stream.
 *
 * Anyway this function is not a general purpose routine (could be though), Right now it will only handle deflated files (type 8)
 * and writes all files out to the destination ignoring diretory structure in the zip
 *
 * http://en.wikipedia.org/wiki/ZIP_(file_format) was a big help in the zip file struture, YMMV
 *
 * @param mixed $source
 * @param mixed $destination
 * @param integer $needed_mem
 * @return
 */
function geoIP_unzip_zlib($source, $destination, $needed_mem = 128)
{
	// If you know how to make gzinflate go in chunks, or break a deflated string on boundaries to make this more efficent, please let me know!
	if (@ini_get('memory_limit') < $needed_mem)
	{
		if (@ini_set('memory_limit', $needed_mem .'M') === false)
			return false;
		else
			$needed_mem = (int) @ini_get('memory_limit');
	}

	if (substr($destination, -1) != '/')
		$destination .= '/';
	$extracted = array();

	// Read the zip file
	$fh = fopen($source, 'rb');
	$filedata = fread($fh,filesize($source));
	fclose($fh);

	// Cut the file at the Central directory file header signature -- 0x02014b50 (read as a little-endian number)
	$file_section = explode("\x50\x4b\x01\x02", $filedata);

	// And cut this chunk on each of the ZIP local file headers -- 0x04034b50 (read as a little-endian number)
	$file_sections = explode("\x50\x4b\x03\x04", $file_section[0]);

	// drop the first element (its header signatures etc) and then flip it so we work on the small file first.  This so we can
	// remove it from the array and have the best shot at not creating an OOM on the 117M one ....
	array_shift($file_sections);
	$file_sections = array_reverse($file_sections);

	// clear memory
	unset($filedata,$file_section);

	// Each file section contains the detailed information for the block of data
	$files = count($file_sections);
	for ($i = 0; $i < $files; $i++)
	{
		// Read all of the local headers based on the bit lengths specified by the standards
		$unzipped = array();
		$unzipped = unpack("v1version/v1general_purpose/v1compress_method/v1file_time/v1file_date/V1crc/V1size_compressed/V1size_uncompressed/v1filename_length/v1extrafield_length", $file_sections[$i]);

		// Check for value block after compressed data
		if ($unzipped['general_purpose'] & 0x0008)
		{
			// If bit 3 (0x08) of the general-purpose flag is set, then the CRC-32 and file sizes are not known when the header is written.
			// The fields in the local header are filled with zeros, and the CRC-32 and size are appended in a 12-byte structure immediately
			// after the compressed data:
			$unzipped2 = unpack("V1crc/V1size_compressed/V1size_uncompressed", substr($file_sections[$i], -12));
			$unzipped['crc'] = $unzipped2['crc'];
			$unzipped['size_compressed'] = $unzipped2['size_uncompressed'];
			$unzipped['size_uncompressed'] = $unzipped2['size_uncompressed'];
			unset($unzipped2);
		}

		// get the file name but skip over plain directory entries ...
		$unzipped['name'] = substr($file_sections[$i], 26, $unzipped['filename_length']);
		if (substr($unzipped['name'], -1) == "/")
			continue;
		$unzipped['name'] = basename($unzipped['name']);

		// get the compressed data, the last standard marker is at bit 26, then its based off two variable length fields after
		// which we have the datastream we want to deflate
		$file_sections[$i] = substr($file_sections[$i], 26 + $unzipped['filename_length'] + $unzipped['extrafield_length']);

		// Lets unzip, errr inflate this bad boy if we can, this *is* the memory pig.
		$unzipped['data'] = false;
		if (strlen($file_sections[$i]) == $unzipped['size_compressed'] && ($unzipped['compress_method'] == 8))
		{
			// We have all the data and the compression method is 8 (deflate)
			$fp = fopen($destination . $unzipped['name'], 'w');
			if ($fp)
			{
				chmod($destination . $unzipped['name'], 0755);

				// try to avoid the white screen .... because asking with ini_get and receiving are not the same ;)
				if (((($unzipped['size_uncompressed']/1024/1024) * 2 + memory_get_usage()/1024/1024) - $needed_mem) > 0)
					return false;

				// inflate it and use a lot of memory while you do it :P, its ~2x the inflated file size at peak
				$file_sections[$i] = gzinflate($file_sections[$i]);
				fwrite($fp, $file_sections[$i]);
				fclose($fp);
				$unzipped['data'] = true;
			}
			else
				return false;
		}

		// put the details in to our extraction array
		$extracted[] = $unzipped;

		// Free some memory in hopes we dont OOM
		unset($file_sections[$i],$unzipped);
	}
	return $extracted;
}

/**
 * geoIP_fetchdata()
 *
 * This is a cut down version of the standard fetch_web_data
 * only for http and :80, no redirects
 * updates the percent done based on the input size to the function, used for ajax status update
 *
 * @param mixed $url
 * @param integer $size
 * @return
 */
function geoIP_fetchdata($url, $size = 0)
{
	// Just a cut down fetch_web_data so we can show some progress during downloads
	global $context, $txt;

	preg_match('~^(http)(s)?://([^/:]+)(:(\d+))?(.+)$~', $url, $match);
	if (empty($match[1]))
		return false;
	if (isset($match[1]))
	{
		// Open the socket
		$fp = @fsockopen($match[3], 80, $err, $err, 5);
		if (!$fp)
			return false;

		// let the site know we want this file
		fwrite($fp, 'GET ' . $match[6] . ' HTTP/1.0' . "\r\n");
		fwrite($fp, 'Host: ' . $match[3] . "\r\n");
		fwrite($fp, 'User-Agent: PHP/SMF' . "\r\n");
		fwrite($fp, 'Connection: close' . "\r\n\r\n");

		// make sure we got a 200 or 201 back
		$response = fgets($fp, 768);
		if (preg_match('~^HTTP/\S+\s+20[01]~i', $response) === 0)
			return false;

		// Skip the headers...
		while (!feof($fp) && trim($header = fgets($fp, 4096)) != '')
			continue;

		// Get the data!
		$data = '';
		$reset = function_exists('apache_reset_timeout') ? .2 : 1000;
		while (!feof($fp))
		{
			$data .= fread($fp, 4096);
			$progress = number_format(strlen($data) / 1048576, 2);
			$percent = number_format($progress / $size, 2);

			// 100 max, we don't have the exact file size
			if ($percent > 1)
				$percent = 1;

			updateSettings(array(
				'geoIP_status_details' => sprintf($txt['geoIP_status8'], $progress, $percent * 100),
				'geoIP_status' => 10 + (int) (60 * $percent),
				'settings_updated' => time(),
				)
			);

			// try to keep the connection alive
			if ($percent > $reset)
			{
				@apache_reset_timeout();
				$reset = $reset * 2;
			}
		}
		fclose($fp);
	}

	return $data;
}

/**
 * geoIPxml()
 *
 * Provides the xml data for the ajax status during the database update and csv retrieval
 *
 * @return
 */
function geoIPxml()
{
	global $context, $modSettings;

	// Not set yet, well set it blank
	if (empty($modSettings['geoIP_status']))
		$modSettings['geoIP_status'] = 0;
	if (empty($modSettings['geoIP_status_details']))
		$modSettings['geoIP_status_details'] = '';

	// return our xml response indicating our status
	header('Content-Type: text/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<status>
	<geoIP percent="', cleanXml($modSettings['geoIP_status']), '"></geoIP>
	<geoIP details="', cleanXml($modSettings['geoIP_status_details']), '"></geoIP>
</status>';

	obExit(false);
}

/**
 * geoIPhtml()
 *
 * css and javascript headers for the templates
 *
 * @return
 */
function geoIPhtml()
{
	global $context, $settings;

	$context['html_headers'] .= '
<script type="text/javascript" src="'. $settings['default_theme_url'] . '/scripts/geoIP_database.js?fin"></script>
<style type="text/css">
div#geoIP_progress_container {
	border: 6px double #ccc;
	width: 200px;
	margin: 0px;
	padding: 0px;
	text-align: left;
	display: none;
}
div#geoIP_progress {
	color: white;
	background-color: #FF8D40;
	height: 12px;
	padding-bottom: 2px;
	font-size: 12px;
	text-align: center;
	overflow: hidden;
}
</style>';
}

?>