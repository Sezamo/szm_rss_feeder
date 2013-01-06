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
// Store initial settings ---------------------------------------------------

$settings = Plugin::getAllSettings('szm_rss_feeder');
if( empty( $settings ) ) {
	$settings = array(	'title'			=> 'RSS Feeder',
						'description'	=> 'Write here your description',
						'relativeUrl'	=> '/rss.xml',
						'maxitems'		=> '15',
						'language'		=> Setting::get('language'),
						'parents'		=> ''
						);
					
	Plugin::setAllSettings($settings, 'szm_rss_feeder');
}

exit();
