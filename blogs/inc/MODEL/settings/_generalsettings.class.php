<?php
/**
 * This file implements the GeneralSettings class, which handles Name/Value pairs.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * {@internal
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Includes
 */
require_once dirname(__FILE__).'/_abstractsettings.class.php';

/**
 * Class to handle the global settings.
 *
 * @package evocore
 */
class GeneralSettings extends AbstractSettings
{
	/**
	 * The default settings to use, when a setting is not given
	 * in the database.
	 *
	 * @todo Allow overriding from /conf/_config_TEST.php?
	 * @access protected
	 * @var array
	 */
	var $_defaults = array(
		'admin_skin' => 'legacy',

		'antispam_last_update' => '2000-01-01 00:00:00',

		'archive_mode' => 'monthly',
		'AutoBR' => '0',
		'auto_prune_stats' => '30',         // days (T_hitlog and T_sessions)

		'fm_enabled' => '1',
		'fm_enable_create_dir' => '1',
		'fm_enable_create_file' => '0',
		'fm_enable_roots_blog' => '1',
		// 'fm_enable_roots_group' => '0',  // TO DO
		'fm_enable_roots_user' => '0',

		'fm_showtypes' => '0',
		'fm_showfsperms' => '0',

		'fm_default_chmod_file' => '664',   // Needs UI
		'fm_default_chmod_dir' => '775',    // Needs UI

		'hit_doublecheck_referer' => '0',
		'links_extrapath' => '0',
		'newusers_canregister' => '0',
		'newusers_level' => '1',
		'permalink_type' => 'urltitle',
		'posts_per_page' => '5',
		'regexp_filename' => '^[a-zA-Z0-9\-_.]+$', // TODO: accept spaces and special chars / do full testing on this
		'regexp_dirname' => '^[a-zA-Z0-9\-_]+$', // TODO: accept spaces and special chars / do full testing on this
		'reloadpage_timeout' => '300',
		'time_difference' => '0',
		'timeout_sessions' => '604800',     // seconds (604800 == 7 days)
		'upload_enabled' => '1',
		'upload_allowedext' => 'jpg jpeg gif png txt csv xls doc ppt css htm html',
		'upload_maxkb' => '100',
		'user_minpwdlen' => '5',
		'what_to_show' => 'posts',
	);


	/**
	 * Constructor.
	 *
	 * This loads the general settings and checks db_version.
	 *
	 * It will also turn off error-reporting/halting of the {@link $DB DB object}
	 * temporarily to present a more decent error message if tables do not exist yet.
	 *
	 * Because the {@link $DB DB object} itself creates a connection when it gets
	 * created "Error selecting database" occurs before we can check for it here.
	 */
	function GeneralSettings()
	{
		global $new_db_version, $DB;

		$save_DB_show_errors = $DB->show_errors;
		$save_DB_halt_on_error = $DB->halt_on_error;
		$DB->halt_on_error = $DB->show_errors = false;

		// Init through the abstract constructor. This should be the first DB connection.
		parent::AbstractSettings( 'T_settings', array( 'set_name' ), 'set_value' );


		// check DB version:
		if( $this->get( 'db_version' ) != $new_db_version )
		{ // Database is not up to date:
			if( $DB->last_error )
			{
				$error_message = '<p>MySQL error:</p>'.$DB->last_error;
			}
			else
			{
				$error_message = '<p>Database schema is not up to date!</p>'
					.'<p>You have schema version &laquo;'.(integer)$this->get( 'db_version' ).'&raquo;, '
					.'but we would need &laquo;'.(integer)$new_db_version.'&raquo;.</p>';
			}
			global $inc_path;
			require $inc_path.'_conf_error.inc.php'; // error & exit
		}

		$DB->halt_on_error = $save_DB_halt_on_error;
		$DB->show_errors = $save_DB_show_errors;
	}

}

/*
 * $Log$
 * Revision 1.2  2006/03/04 19:51:58  blueyed
 * Fixed path to _conf_error.inc.php
 *
 * Revision 1.1  2006/02/23 21:11:58  fplanque
 * File reorganization to MVC (Model View Controller) architecture.
 * See index.hml files in folders.
 * (Sorry for all the remaining bugs induced by the reorg... :/)
 *
 * Revision 1.34  2005/12/14 19:36:16  fplanque
 * Enhanced file management
 *
 * Revision 1.33  2005/12/12 19:21:22  fplanque
 * big merge; lots of small mods; hope I didn't make to many mistakes :]
 *
 * Revision 1.32  2005/12/10 02:54:33  blueyed
 * Default chmod moved to $Settings again
 *
 * Revision 1.31  2005/11/25 14:33:36  fplanque
 * no message
 *
 * Revision 1.30  2005/11/21 18:33:19  fplanque
 * Too many undiscussed changes all around: Massive rollback! :((
 * As said before, I am only taking CLEARLY labelled bugfixes.
 *
 * Revision 1.29  2005/11/21 04:05:40  blueyed
 * File manager: fm_sources_root to remember the root of fm_sources!, chmod centralized ($Settings), Default for dirs fixed, Normalisation; this is ready for the alpha (except bug fixes of course)
 *
 * Revision 1.28  2005/11/16 21:53:49  fplanque
 * minor
 *
 * Revision 1.27  2005/11/07 02:08:52  blueyed
 * Added settings antispam_block_ip and antispam_block_ip_dnsbl to defaults and sorted the defaults
 *
 * Revision 1.26  2005/11/05 00:05:43  blueyed
 * Moved pruning of T_sessions into Hitlist::dbprune()
 *
 * Revision 1.25  2005/11/02 20:11:19  fplanque
 * "containing entropy"
 *
 * Revision 1.24  2005/11/01 23:43:35  blueyed
 * $Settings default admin_skin is 'legacy'
 *
 * Revision 1.23  2005/10/31 09:33:21  blueyed
 * Set session timeout (auto_prune_sessions) to 30 days (old behaviour with cookies); added checks to not allow setting it below 1 hour and give a warning when below 1 day
 *
 * Revision 1.22  2005/10/31 06:13:03  blueyed
 * Finally merged my work on $Session in.
 *
 * Revision 1.21  2005/10/31 01:33:38  blueyed
 * Added default settings that were written explicitely into DB before
 *
 * Revision 1.20  2005/10/30 04:44:32  blueyed
 * Moved $stats_autoprune to auto_prune_stats in $Settings.
 * Automagic pruning of old hits, when a hit gets logged, but just once a day (remembered in $Settings->get(auto_prune_stats_done))
 *
 * Revision 1.19  2005/10/11 19:28:57  blueyed
 * Added decent error message if tables do not exist yet (not installed).
 *
 * Revision 1.18  2005/10/06 17:03:02  fplanque
 * allow to set a specific charset for the MySQL connection.
 * This allows b2evo to work internally in a charset different from the database charset.
 *
 * Revision 1.17  2005/09/25 03:50:45  blueyed
 * Hit class: Fixes, normalized; moved $doubleCheckReferers to $Settings ("feature" admin tab)
 *
 * Revision 1.16  2005/09/06 17:13:55  fplanque
 * stop processing early if referer spam has been detected
 *
 * Revision 1.15  2005/05/09 16:09:42  fplanque
 * implemented file manager permissions through Groups
 *
 * Revision 1.14  2005/05/06 20:04:48  fplanque
 * added contribs
 * fixed filemanager settings
 *
 * Revision 1.13  2005/05/04 19:40:41  fplanque
 * cleaned up file settings a little bit
 *
 * Revision 1.12  2005/04/13 17:48:23  fplanque
 * File manager refactoring
 * storing of file meta data through upload
 * displaying or metadate in previews
 *
 * Revision 1.11  2005/03/15 19:19:47  fplanque
 * minor, moved/centralized some includes
 *
 * Revision 1.9  2005/02/28 09:06:33  blueyed
 * removed constants for DB config (allows to override it from _config_TEST.php), introduced EVO_CONFIG_LOADED
 *
 * Revision 1.8  2005/01/15 20:13:38  blueyed
 * regexp_fileman moved to $Settings
 *
 * Revision 1.7  2005/01/14 17:38:13  blueyed
 * defaults added
 *
 * Revision 1.6  2005/01/10 02:14:02  blueyed
 * new settings
 *
 * Revision 1.5  2005/01/06 05:20:14  blueyed
 * refactored (constructor), getDefaults()
 *
 * Revision 1.4  2004/12/30 22:54:38  blueyed
 * errormessage beautified
 *
 * Revision 1.3  2004/11/08 02:23:44  blueyed
 * allow caching by column keys (e.g. user ID)
 *
 * Revision 1.2  2004/10/16 01:31:22  blueyed
 * documentation changes
 *
 * Revision 1.1  2004/10/13 22:46:32  fplanque
 * renamed [b2]evocore/*
 *
 * Revision 1.11  2004/10/12 10:27:18  fplanque
 * Edited code documentation.
 *
 */
?>
