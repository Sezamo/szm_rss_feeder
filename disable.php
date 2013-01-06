<?php
/**
 * The szm_rss_feeder plugin add Wolf CMS with RSS provider.
 *
 * @package wolf
 * @subpackage plugin.szm_rss_feeder
 *
 * @author Maurizio Serrazanetti <info@sezamo.net>
 * @version 0.5.x beta
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Maurizio Serrazanetti, 2011
 */

// Security checks
if( !defined('CMS_BACKEND') || !AuthUser::isLoggedIn() ) {
	die("All your base are belong to us!");
}

// Really want to delete settings??
Plugin::deleteAllSettings('szm_rss_feeder');

exit();