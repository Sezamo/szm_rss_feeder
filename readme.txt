/**
 * SeZaMo RSS feeder plugin for Wolf CMS
 * Adds an RSS feeder to your CMS.
 * 
 * Download site <http://www.sezamo.net/writings/the-rss-feeder-plugin-for-wolfcms.html>
 * Copyright (C) 2011 Maurizio Serrazanetti <info@sezamo.net>
 * 
 * Licensed under GPLv3 (http://www.gnu.org/licenses/gpl.html) license.
 */

== WHAT IT IS ==

The SeZaMo RSS Feeder plugin adds RSS feeder functionalities to Wolf CMS.

Dependencies:
    - Wolf 0.7.0+

== Installation ==

1) Unzip szm_rss_feeder.vXXX.zip in the Wolf /wolf/plugins directory.
2) Activate 'SeZaMo RSS Feeder' plugin through the administration screen.
3) Configure the plugin through settings page, the minimum mandatory parameters to set are: Title, Relative URL and Root pages to grab children from.
   See online documentation for details.

== HOW TO USE IT ==

When properly configured the plugin will output a RSS document at http://<your site url>/<the configured relative url>
The items of the RSS XML will be the children of the configured Root pages of your site.

If Advanced Find plugin by Tyler Beckett (http://www.tbeckett.net/articles/plugins/adv-find.xhtml) is installed
and enabled it will be used to list and order the pages, otherwise the standard Wolf function find() is used and 
children will be ordered in a loop step.

The data for RSS item is:
1) a title, the page title;
2) a url, the page url;
3) a description, could be the full page content (the default page part) but can be customized, see later;
4) an author, taken from the page author;
5) a comma separated list of categories, taken from Tagger plugin by Andrew Smith and Tyler Beckett (http://thehub.silentworks.co.uk/plugins/frog-cms/tagger.html)
if enabled, or no categories otherwise;
6) a permalink, again the page url (this could change in future);
7) publish date, the page published date.

** Customize item description **

If you want to add an image (I call it a cover) to an item, you need to install and enable the Page Metadata 
by THE M (http://github.com/them/frog_page_metadata/) plugin; follow plugin documentation to add a metadata
to the page named 'coverImage' and with value the relative url (to URL_PUBLIC) of an image.

You can add a page part called 'abstract' to be used as the item description instead of the actual page content.

== NOTES ==

* When you disable the plugin, all related plugin settings will be deleted from database.
