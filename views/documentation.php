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
<h1><?php echo __('SeZaMo RSS Feeder documentation'); ?></h1>
<p>This plugin provide <a href="http://www.wolfcms.org" target="_blank">WolfCMS</a> with RSS v2.0.1 feeder capabilities.</p>
<h2>Configure</h2>
<p>When plugin is enabled configure parameters by changing <a href="<?php echo get_url('plugin/'.SzmRssFeederController::PLUGIN_ID.'/settings'); ?>">settings</a>.
Mandatory parameters are:</p>
<dl>
<dt>Title</dt>
<dd>title to be publish as RSS title.</dd>
<dt>Relative URL</dt>
<dd>the URL relative to site (ie. to URL_PUBLIC) where RSS will be published.<br/>
<span style="font-size: 85%">For example, if you what to publish RSS at <code>http://&lt;your site url&gt;/rss.xml</code>, than write <code>/rss.xml</code></span></dd>
<dt>Root pages</dt>
<dd>comma separated list of parent page slugs where to get children from, and publish them as RSS items.<br/></dd>
</dl>
<!-- div style="clear: both; margin-bottom: 1em;"></div -->
<p style="clear: both;">To publish the availability of RSS on your site you need to put a &lt;meta&gt; attribute in your page header;
your can manually add following line in your HTML head section (usually in a layout or snippet):</p>
<p class="code"><code>&lt;link rel='alternate' type='application/rss+xml' title='</code><span class="red">Put your title here</span><code>' href='</code><span class="red">Put RSS complete URL here</span><code>' /&gt;</code></p>
<p>A better and cleaner way is to notify a well known signal to the plugin when the HTML head is building, and plugin will
output the right meta tag with up-to-date values for you; simply add this PHP code in the snippet or layout where you want meta to be generated:</p>
<p class="code"><code>&lt;?php Observer::notify('page_found_building_head', $this ); ?&gt;</code></p>
<p>The plugin will output the meta tag with configured title and url. The code above is <a href="http://www.wolfcms.org/wiki/the_observer_system" target="_blank" title="the_observer_system [Wolf CMS Wiki]">WolfCMS standard</a> and you don't need to remove it once RSS plugin is disabled: it will simply have no effects.<br/>
The code above will also output an info tag with plugin details, a meta similar to the following:</p>
<p class="code"><code>&lt;meta name='Wolf-Plugin' content='SeZaMo RSS Feeder v0.4.4 beta' /&gt;</code></p>
<h2>How to use it</h2>
<p>When properly configured the plugin will output a RSS document at <code>http://&lt;your site url&gt;/&lt;the configured relative url&gt;</code>
The items of the RSS XML will be the children of the configured Root pages of your site.</p>
<p>If <a href="http://www.tbeckett.net/articles/plugins/adv-find.xhtml" target="_blank">Advanced Find plugin by Tyler Beckett</a> is installed
and enabled it will be used to list and order the pages, otherwise the standard Wolf function find() is used and 
children will be ordered in a loop step.</p>
<p>The data for RSS item is:</p>
<dl>
<dt>a title</dt><dd>the page title;</dd>
<dt>a url</dt><dd>the page url;</dd>
<dt>a description</dt><dd>could be the full page content (the default page part) but can be customized, see later;</dd>
<dt>an author</dt><dd>taken from the page author;</dd>
<dt>some categories</dt><dd>taken from <a href="http://thehub.silentworks.co.uk/plugins/frog-cms/tagger.html" target="_blank">Tagger plugin by Andrew Smith and Tyler Beckett</a>
if enabled, or no categories otherwise;</dd>
<dt>a permalink</dt><dd>again the page url (this could change in future);</dd>
<dt>a publish date</dt><dd>the page published date.</dd>
</dl>
<div style="clear: both; margin-bottom: 1em;"></div>
<h3>Customize item description</h3>
<ul>
<li>If you want to add an image (I call it a cover) to an item, you need to install and enable the <a href="http://github.com/them/frog_page_metadata/" target="_blank">Page Metadata 
by THE M</a> plugin; follow plugin documentation to add a metadata
named 'coverImage' to the page and the relative url (to URL_PUBLIC) of an image as value.</li>
<li>You can add a page part called 'abstract' to be used as the item description instead of the actual page content.</li>
</ul>
<h2>About a beta</h2>
<p>This plugin serves me as a training plugin for wolfcms frontend dispatching and is a beta version: the code is (heavily?) bound to my site structure, and I can't guarantee it will work at your site.<br/>
I'll try to keep in touch with <a href="http://www.wolfcms.org/discuss.html" target="_blank">WolfCMS forum</a> for any question.</p>

<h3>Have fun!</h3>
