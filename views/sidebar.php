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
?>
<div class="box">
<h3><b>SeZaMo RSS Feeder</b></h3>
<p>A basic RSS feeder implementation</p>
<p>What is RSS? <a href="http://en.wikipedia.org/wiki/RSS" target="_blank">read here</a>.</p>
<p>Visit my site at <a href="http://www.sezamo.net/" target="_blank">Sezamo home</a></p>
<p style="font-size: 70%">
This code is licensed under GPLv3.<br/>
Read the license at <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">http://www.gnu.org/licenses/gpl.html</a>
</p>
</div>
