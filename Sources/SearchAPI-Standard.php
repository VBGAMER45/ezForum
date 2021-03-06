<?php

/**
 * ezForum http://www.ezforum.com
 * Copyright 2011 ezForum
 * License: BSD
 *
 * Based on:
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class standard_search
{
	// This is the last version of ezForum that this was tested on, to protect against API changes.
	public $version_compatible = 'ezForum 3.0';

	// This won't work with versions of ezForum less than this.
	public $min_smf_version = 'ezForum 3.0 Beta 2';

	// Standard search is supported by default.
	public $is_supported = true;

	// Method to check whether the method can be performed by the API.
	public function supportsMethod($methodName, $query_params = null)
	{
		// Always fall back to the standard search method.
		return false;
	}
}

?>