<?php
// GeoIP templates

function template_main()
{
	global $context, $scripturl, $modSettings, $txt, $settings;
	
	if (isset($_GET['saved']))
	echo '
				<span class="upperframe"><span></span></span>
				<div class="roundframe">
					<div id="savestatus">',
						$txt['geoIP_saved_settings'], '
					</div>
				</div>
				<span class="lowerframe"><span></span></span>';
	echo '
				<div class="cat_bar">
					<h4 class="catbg">
						<span class="align_left">', $txt['geoIP'], '</span>
					</h4>
				</div>
				
				<div class="windowbg2">
					<span class="topslice"><span></span></span>
					<div class="content">';
	echo '			
				<br class="clear" />
				<form action="', $scripturl, '?action=admin;area=geoIP;sa=main" method="post" name="geoip" id="geoip" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">';
	echo '
				<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px;">
					<legend>', $txt['geoIP_install_settings'], '</legend>
					<span class="upperframe"><span></span></span>
					<div class="roundframe">
					<p>', $txt['geoIP_database_license'], '</p>
					<dl class="settings">
						<dt>
							<label>', $txt['geoIP_database_select'], '</label>
							<select name="id_type" id="id_type" size="3">';

	// Loop and show the drop down.
	foreach ($context['geoIP_db_option'] as $key => $option)
		echo  '
								<option title="', $option['name'], '" value="', $option['id'], '" ', isset($_REQUEST['db_id']) &&  $_REQUEST['db_id'] == $option['id'] ? 'selected="selected"' : '', '>', $option['name'], '</option>';
	echo '
							</select>
						</dt>
						<dd>
						<span id="option_desc" ></span><br /><br />';

	// and the descriptions for them, hidden and used by javascript to fill in the above span
	foreach($context['geoIP_db_option'] as $desc)
		echo '
							<span id="option_desc_', $desc['id'], '" style="display:none">', $desc['desc'], '</span>';
		
	echo '
						</dd>
						<dt>
							<label>', $txt['geoIP_database'], '</label>:<br />
							<span class="smalltext">', $txt['geoIP_database_desc'], '</span>
						</dt>
						<dd>';
	// database status
	if (!empty($context['geoIP_count']))
		echo 
							$txt['geoIP_isinstalled'], ' ', number_format($context['geoIP_count']), ' ', $txt['geoIP_entries'], '<br />',
							$txt['geoIP_date'], (!empty($modSettings['geoIP_date']) ? $modSettings['geoIP_date'] : '?');
	else
		echo 
							$txt['geoIP_notinstalled'];
	// ajax divs for status info
	echo '
							<div id="geoIP_progress_container">
								<div id="geoIP_progress" style="width: 0%"></div>
							</div>
							<div id="geoIP_status">' .
								$txt['geoIP_update_status'] . ': ' . (!empty($modSettings['geoIP_status_details']) ? $modSettings['geoIP_status_details'] : '') . '
							</div>';
	echo '
						</dd>
					</dl>						
					<hr class="hrcolor" />
					<div class="righttext">';
	if (!empty($context['geoIP_prereq_sql']))
		echo '
						<input class="button_submit" name="load_database" value="', $txt['geoIP_update'], '" tabindex="', $context['tabindex']++, '" onclick="geoip_init()" />';
	else
		echo '			
						<img align="top" border="0" src="' . $settings['images_url'] . '/warning_mute.gif" alt="" />&nbsp;' . $txt['geoIP_prereq_sql'];
	if (empty($context['geoIP_prereq_zip']))
		echo '			
						<span id="warn"><br /><img align="top" border="0" src="' . $settings['images_url'] . '/warn.gif" alt="" />&nbsp;' . $txt['geoIP_prereq_zip'] . '</span>';
	echo '
					</div>
					</div>
					<span class="lowerframe"><span></span></span>
				</fieldset>';					
					
	// The checkbox settings
	echo '		<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px;">
					<legend>', $txt['geoIP_basic_settings'], '</legend>
					<span class="upperframe"><span></span></span>
					<div class="roundframe">
					<dl class="settings">
						<dt>
							<label for="geoIP_enablemap">', $txt['geoIP_enablemap'], '</label>:<br />
							<span class="smalltext">', $txt['geoIP_enablemap_desc'], '</span>
						</dt>
						<dd>
							<input type="checkbox" name="geoIP_enablemap" id="geoIP_enablemap" ', empty($modSettings['geoIP_enablemap']) ? '' : 'checked="checked"', ' />
						</dd>
						
						<dt>
							<label for="geoIP_enablepinid">', $txt['geoIP_enablepinid'], '</label>:<br />
							<span class="smalltext">', $txt['geoIP_enablepinid_desc'], '</span>
						</dt>
						<dd>
							<input type="checkbox" name="geoIP_enablepinid" id="geoIP_enablepinid" ', empty($modSettings['geoIP_enablepinid']) ? '' : 'checked="checked"', ' />
						</dd>
						
						<dt>
							<label for="geoIP_enablereg">', $txt['geoIP_enablereg'], '</label>:<br />
							<span class="smalltext">', $txt['geoIP_enablereg_desc'], '</span>
						</dt>
						<dd>
							<input type="checkbox" name="geoIP_enablereg" id="geoIP_enablereg" ', empty($modSettings['geoIP_enablereg']) ? '' : 'checked="checked"', ' />
						</dd>
						
						<dt>
							<label for="geoIP_enableflags">', $txt['geoIP_enableflags'], '</label>:<br />
							<span class="smalltext">', $txt['geoIP_enableflags_desc'], '</span>
						</dt>
						<dd>
							<input type="checkbox" name="geoIP_enableflags" id="geoIP_enableflags" ', empty($modSettings['geoIP_enableflags']) ? '' : 'checked="checked"', ' />
						</dd>
					</dl>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<hr class="hrcolor" />
					<div class="righttext">
						<input type="submit" class="button_submit" name="save" value="', $txt['save'], '" tabindex="', $context['tabindex']++, '" />
					</div>
					</div>
					<span class="lowerframe"><span></span></span>
				</fieldset>
				</form>';
	
	// Done
	echo '
					</div>
					<span class="botslice"><span></span></span>
				</div>
				<br class="clear" />';
			
	// Some javascript to make the form interactive
	echo '
				<script type="text/javascript"><!-- // --><![CDATA[
				var db_type = document.getElementById(\'id_type\');
				mod_addEvent(db_type, \'change\', toggledbTrigger);
				toggledbTrigger();
				// ]]></script>';
}

function template_geoIP()
{
	global $context, $modSettings, $scripturl, $txt, $settings;
	
	if (!empty($modSettings['geoIP_enablemap']))
	{
		echo '
					<div class="cat_bar">
						<h4 class="catbg">
							<span class="align_left">', $txt['geoIP'], '</span>
						</h4>
					</div>
					
					<div class="windowbg2">
						<span class="topslice"><span></span></span>
						<div class="content">';

		echo '
							<table width="100%">
								<tr>
									<td class="windowbg2" valign="middle" align="center">
										<div id="map"  style="width: 675px; height: 500px; color: #000000;"></div>
									</td>';

		// Show a right sidebar?
		if ((!empty($modSettings['geoIPSidebar'])) && $modSettings['geoIPSidebar'] == 'right')
		{
			echo '
									<td style="white-space: nowrap;">
										<div class="centertext"><em><strong>', $txt['online_users'], '</strong></em></div>
										<hr style="width: 94%;" />
										<div id="gooSidebar" class="geoIPSidebar" align="left" style="padding-left: 15px;"></div>
									</td>';
		}

		// No sidebar then put the data below the map
		if ((!empty($modSettings['geoIPSidebar'])) && $modSettings['geoIPSidebar'] == 'none')
			echo '
								</tr>
								<tr>
									<td align="center">
										<div id="gooSidebar" class="geoIPLegend" align="left"></div>
									</td>';

		// close this table 
		echo '
								</tr>
							</table>';

		// Load the scripts so google starts to render this page
		echo '
							<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false" ></script>
							<script type="text/javascript" src="', $scripturl, '?action=geoIP;sa=.js"></script>';

		// Close it up jim
		echo '
						</div>
						<span class="botslice"><span></span></span>
					</div>';
	}
}

function template_geoIPreg()
{
	global $context, $txt, $scripturl, $settings, $modSettings;
	
	echo '
		<form action="', $context['post_url'], '" method="post" name="geoIP" id="geoIP" accept-charset="', $context['character_set'], '"enctype="multipart/form-data">';
	echo '
		<div class="cat_bar">
			<h4 class="catbg">
				<span class="align_left">', $txt['geoIPRegistration'], '</span>
			</h4>
		</div>';
	echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">';
	echo '		<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px;">
					<legend>', $txt['geoIP_basic_settings'], '</legend>
					<span class="upperframe"><span></span></span>
					<div class="roundframe">
					<dl class="settings">
		
						<dt>
							<label for="geoIP_cc_block">', $txt['geoIP_cc_block'], '</label>:<br />
							<span class="smalltext">', $txt['geoIP_cc_block_desc'], '</span>
						</dt>
						<dd>
							<input type="checkbox" name="geoIP_cc_block" id="geoIP_cc_block" ', empty($modSettings['geoIP_cc_block']) ? '' : 'checked="checked"', ' />
						</dd>
					</dl>
					</div>
					<span class="lowerframe"><span></span></span>
				</fieldset>';
	// all the countries and the flags .... 
	echo '
				<fieldset id="countrycode" style="padding: 5px;">
					<legend>', $txt['geoIPCCToUse_select'], '</legend>
						<ul class="reset">';

	// for each column
	foreach ($context['geoCC_columns'] as $geoCC_Column)
	{
		// and for each country in this col
		foreach ($geoCC_Column as $cc)
			echo '
							<li class="floatleft" style="width:33%;">
								<input type="checkbox" name="geoIPCC[]" id="geoIPCC_', $cc['cc'], '" value="', $cc['cc'], '"',  'class="input_check"', ($cc['checked'] ? 'checked="checked"' : '') ,' /> 
								<label for="geoIPCC_', $cc['cc'], '"><img src="' , $settings['default_images_url'] , '/ISO_3166_Flags/' , $cc['cc'] . '.gif"  height="12" width="18" border="0" alt="[ * ]" title="' . $cc['cn'] . '"/>&nbsp;', $cc['cn'], '</label>
							</li>';
	}
	echo '				</ul>
				</fieldset>';
	echo '
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<div class="righttext">
				<input type="submit" class="button_submit" name="save" value="', $txt['save'], '" tabindex="', $context['tabindex']++, '" />
			</div>';

	// Done
	echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>
		</form>
		<br class="clear" />';
}
?>