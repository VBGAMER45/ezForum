<?php

/**
 *
 * @package "geopIP" Mod for Simple Machines Forum (SMF) V2.0
 * @author Spuds
 * @copyright (c) 2011 Spuds
 * @license license.txt (included with package) BSD
 *
 * @version 1.1
 *
 */

/**
 * ilp_geoIP()
 *
 * @param mixed $permissionGroups
 * @param mixed $permissionList
 * @param mixed $leftPermissionGroups
 * @param mixed $hiddenPermissions
 * @param mixed $relabelPermissions
 * @return
 */
function ilp_geoIP(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	// Permissions hook, integrate_load_permissions, called from ManagePermissions.php
	// used to add new permisssions
	$permissionList['membergroup']['geoIP_view'] = array(false, 'general', 'view_basic_info');
	$permissionList['membergroup']['geoIP_viewdetail'] = array(false, 'general', 'view_basic_info');
}

/**
 * ia_geoIP()
 *
 * @param mixed $actionArray
 * @return
 */
function ia_geoIP(&$actionArray)
{
	// Actions hook, integrate_actions, called from index.php
	// used to add new actions to the system for index.php?xyz
	$actionArray = array_merge($actionArray,array(
		'geoIP' => array('geoIP.php', 'geoIP'))
	);
}

/**
 * iaa_geoIP()
 *
 * @param mixed $admin_areas
 * @return
 */
function iaa_geoIP(&$admin_areas)
{
	// Admin Hook, integrate_admin_areas, called from Admin.php
	// used to add/modify admin menu areas
	global $txt;

	// our geoip tab, under
	$admin_areas['config']['areas']['geoIP'] = array(
		'label' => $txt['geoIP'],
		'file' => 'geoIPAdmin.php',
		'function' => 'geoIPEntry',
		'icon' => 'geoip.gif',
		'permission' => array('admin_forum'),
		'subsections' => array(
			'main' => array($txt['geoIPMain']),
			'settings' => array($txt['geoIPSettings']),
			'map' => array($txt['geoIPMap']),
		)
	);
}

/**
 * ipa_geoIP()
 *
 * @param mixed $profile_areas
 * @return
 */
function ipa_geoIP(&$profile_areas)
{
	global $context, $sourcedir, $modSettings;

	// Lets be sure to have geoIP information available when in the profile area.
	$ip = (isset($_GET['searchip'])) ? $_GET['searchip'] : $context['member']['ip'];
	include_once($sourcedir . '/geoIP.php');
	if (isset($modSettings['geoIP_db']))
		$context['geoIP'] =	($modSettings['geoIP_db'] == 1) ? geo_search($ip) : geo_search_lite($ip, true);
}

/**
 * ilt_geoIP()
 *
 * @param mixed $profile_areas
 * @return
 */
function ilt_geoIP()
{
	global $context, $modSettings;

	// Some people can't see the online map button, enabled, full database and perms are needed
	$context['can_see_onlinemap'] = !empty($modSettings['geoIP_enablemap']) && allowedTo('geoIP_view') && (isset($modSettings['geoIP_db']) && $modSettings['geoIP_db'] == 1);
}
?>