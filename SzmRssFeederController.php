<?php

/**
 * The szm_rss_feeder plugin add Wolf CMS with RSS provider.
 *
 * @package wolf
 * @subpackage plugin.szm_rss_feeder
 *
 * @author Maurizio Serrazanetti <info@sezamo.net>
 * @version 0.6.x beta
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Maurizio Serrazanetti, 2011
 */

/**
 * Plugin controller class to handle both backend and
 * frontend requests.
 *
 * @author Maurizio Serrazanetti <info@sezamo.net>
 */
class SzmRssFeederController extends PluginController {

	/* Plugin details */
	const	PLUGIN_ID		= 'szm_rss_feeder';
	const	PLUGIN_TITLE	= 'SeZaMo RSS Feeder';
	const	PLUGIN_VERSION	= '0.6.2 beta';
	const	PLUGIN_URL		= 'wolf/plugins/szm_rss_feeder';

	const	LOG_DATA		= false;

	/** Local static settings, loaded via Init() static method */
	private static $savedSettings;

	/**
	 * Init static data, registers the plugin and controller to the system as
	 * well as the observers.
	 */
	public static function Init() {
		
		// Register plugin
		Plugin::setInfos(array(
		    'id'					=> self::PLUGIN_ID,
		    'title'					=> self::PLUGIN_TITLE,
		    'description'			=> 'Enrich Wolf CMS with basic RSS service.',
		    'version'				=> self::PLUGIN_VERSION,
		   	'license'				=> 'GPL',
			'author'				=> 'Maurizio Serrazanetti',
		    'website'				=> 'http://www.sezamo.net/writings/the-rss-feeder-plugin-for-wolfcms.html',
		    'update_url'			=> URL_PUBLIC .'public/WolfPlugins.sezamo.xml',
		    'require_wolf_version'	=> '0.7.0',
			'type'					=> 'both'
		));
		
		// Add the plugin's controller
		Plugin::addController( self::PLUGIN_ID, 'SeZaMo RSS', false, false );
		
		// Add route for frontend, the contact url is defined on settings
		self::$savedSettings = Plugin::getAllSettings( self::PLUGIN_ID );
		if( self::validSettings() )
			Dispatcher::addRoute( array( self::getRssUrl(true) => '/plugin/'. self::PLUGIN_ID .'/outputRss' ) );
		
		// Add observer when header builds up
		Observer::observe('page_found_building_head', 'SzmRssFeederController::addHeaderMetas');
	}

	private static function validSettings() {
		return( !empty(self::$savedSettings['relativeUrl']) &&
				!empty(self::$savedSettings['parents']) &&
				!empty(self::$savedSettings['title']) );
	}

	private static function getTitle() {
		return self::$savedSettings['title'];
	}

	private static function getDescription() {
		return self::$savedSettings['description'];
	}

	private static function getWebmaster() {
		return self::$savedSettings['webmaster'];
	}

	private static function getLanguage() {
		return self::$savedSettings['language'];
	}

	private static function getCategories() {
		return self::$savedSettings['categories'];
	}

	private static function getRssUrl( $relative = false ) {
		if( $relative )
			return self::$savedSettings['relativeUrl'];
		return trim(URL_PUBLIC,'/') . self::$savedSettings['relativeUrl'];
	}

	/**
	 * Add header metas info to reach RSS url
	 * 
	 * @param Page $page	the caller Page instance
	 * @return none
	 */
	public static function addHeaderMetas($page) {
		echo "\t<meta name='Wolf-Plugin' content='SeZaMo RSS Feeder v". self::PLUGIN_VERSION ."' />\n";
		if( self::validSettings() )
			echo "\t<link rel='alternate' type='application/rss+xml' title='". self::getTitle() ."' href='". self::getRssUrl() ."' />\n";
	}

	/**
	 * A private static (and stateless) logger method
	 * 
	 * @param mixed $msg	message string or data to be logged
	 */
	private static function _logData( $msg ) {
		if (self::LOG_DATA) {
			$fp = fopen( "C:\\Temp\\metaweblog.debug\\rssfeeder_debug.log", "a+");
			$date = gmdate("Y-m-d H:i:s ");
			fwrite( $fp, $date ."> ". $msg ."\n" );
			fclose( $fp );
		}
	}

	/* ----------------------------------------------------------------------------------
	 *	FRONTEND member functions
	 */
	private $maxPubDate	= 0;		// Earlier published date
	private $maxUpdDate	= 0;		// Earlier updated date
	private $categories	= array();	// An array of category strings
	private $items		= array();	// An array of _SzmRssItem to output
	
	/**
	 * Default instance constructor
	 */
	function __construct() {

		if (defined('CMS_BACKEND')) {
			AuthUser::load();
			if ( ! AuthUser::isLoggedIn())
				redirect(get_url('login'));
			$this->setLayout('backend');
			$this->assignToLayout('sidebar', new View('../../plugins/'. self::PLUGIN_ID .'/views/sidebar'));
		}
	}

	/**
	 * The compare function: expects Page with member
	 * published_on not null
	 * 
	 * @param $a	Page instance one
	 * @param $b	Page instance to compare to
	 * @return standard compare return type
	 */
	public static function comparePages( Page $a, Page $b ) {
		$da = strtotime($a->published_on);
		$db = strtotime($b->published_on);
		if( $da == $db )
			return 0;
		return( $db > $da ? +1 : -1 );
	}

	/**
	 * Internally builds the array of _SzmRssItem ($this->items) objects
	 * that will be printed out as RSS item; also, collect categories from
	 * each item and calculate maximum published and updated dates for listed
	 * items.
	 * 
	 * @return none
	 */
	private function processItems() {
		$limit	= self::$savedSettings['maxitems'];
		$ppppp	= explode(",",self::$savedSettings['parents']);	//split(",",self::$savedSettings['parents']);
		$parens	= array();
		foreach ($ppppp as $p) {
			$parens[] = "/". trim( $p, "/\\ " ) ."/";
		}

		// If adv-find plugin is enabled, things go shorter
		if( Plugin::isEnabled('adv-find')) {
//			$articles = adv_find( $parens, array('where' => 'status_id = 100 AND published_on < NOW()', 'limit' => $limit, 'order' => 'published_on DESC') );
// 2012/02/14: Using UTC for datetime based queries!
			$articles = adv_find( $parens, array('where' => 'status_id = 100 AND published_on < UTC_TIMESTAMP()', 'limit' => $limit, 'order' => 'published_on DESC') );
		} else {
			// Get articles for each parent 
			$articles = array();
			foreach ($parens as $parent) {
//				$articles = array_merge( $articles, Page::find($parent)->children(array('where' => 'page.status_id = 100 AND page.published_on < NOW()', 'limit' => $limit, 'order' => 'page.published_on DESC')) );
// 2012/02/14: Using UTC for datetime based queries!
				$articles = array_merge( $articles, Page::find($parent)->children(array('where' => 'page.status_id = 100 AND page.published_on < UTC_TIMESTAMP()', 'limit' => $limit, 'order' => 'page.published_on DESC')) );
			}
			// Sort articles by published date
			usort( $articles, array("SzmRssFeederController", "comparePages") );
			// And cut to limit resulting array
			$articles = array_slice( $articles, 0, $limit );
		}

		// Loop through found pages
		foreach ($articles as &$article) {
		
			$atitle			= $article->title();
			$aurl			= $article->url();
			$adescription	= "";
			
			// Get highest published date
			$d = strtotime($article->published_on);
			if( $this->maxPubDate < $d )	$this->maxPubDate = $d;
			// Get highest updated date
			$d = strtotime($article->updated_on);
			if( $this->maxUpdDate < $d )	$this->maxUpdDate = $d;
			
			// Check for known plugin behaviour IDs
//			self::_logData( print_r( $papa, true ));
			$papa = $article->parent();
			$isSzmGallery	= false;
			$isSzmPhotoblog	= false;
			if( !empty($papa) && !empty($papa->behavior_id) ) {
				if( $papa->behavior_id == 'SeZaMo_Gallery' ) {
					$isSzmGallery = true;
				}
				if( $papa->behavior_id == 'photoblog' ) {
					$isSzmPhotoblog = true;
				}
			}
//	// Check tests
//	if( $isSzmPhotoblog == true )
//		self::_logData( "A photoblog strpos: ". strpos($article->url(),"/photoblog/") .", with url ". $article->url() );

			// Current page is a SeZaMo Gallery
//			if (strpos($article->url(),"/galleries/")) {
			if ($isSzmGallery) {
				$atitle			= __("Aggiornamento album") ." ". $article->title();
				
				$article->description = "";
				$children = $article->children();
				$next = current( $articles );
				
				// Skip if found gallery is not leaf (?)
				if( ! is_array($children) )
					continue;
			
				foreach ($children as $child) {
					// FIXME: this will cut visibility of updates in this album made before
					// next element in $articles list.
					if( $next->published_on < $child->published_on ) {
						$gallery = $child->_sezamo->getCurrentAlbumOrGallery();
						$adescription .= "<p>La galleria ". $child->link() ." è stata aggiornata: aggiunte ". $gallery->countPictures( "insert_date >= '". $next->published_on ."'" ) ." immagini.</p>";
						if( $images = $gallery->getPictures( "insert_date >= '". $next->published_on ."'", "insert_date DESC", "0, 5" ) ) {
							$adescription .= "<p><a href='". $child->url()."' title='Ultime 5 immagini inserite'>\n";
							foreach ($images as $image) {
								$adescription .= "<img src='". $image->getThumbUrl($gallery->getFullUrl()) ."' title='". $image->headline ."' width='100px'/>&nbsp;";
							}
							$adescription .= "</a></p>\n";
						}
					}
				}
			// else If Page Metadata plugin is enabled...
			} else if( Plugin::isEnabled('page_metadata') ) {
				$page_metas = PageMetadata::FindAllByPageAsArray($article);
			
				if( $page_metas['galleryXml'] ) {
					$page_alt = szm_strImageFolderInfos( $page_metas["imagesFolder"] ."/*.jpg", ", " );
				} else {
					$page_alt = strpos($article->url(),"/photoblog/") ? __("Open image") : __("Read more...");
				}
				// Cover image is set??
				if( $page_metas['coverImage'] ) {
					$adescription .= $article->link( "<img src='". URL_PUBLIC . $page_metas['coverImage'] ."' />", "title='".$page_alt."'" );
				}
				// Is this a SeZaMo Photoblog page
//				if( strpos($article->url(),"/photoblog/") ) {
				if( $isSzmPhotoblog && Plugin::isEnabled('photoblog') ) {
					$uri = str_replace( array(URL_PUBLIC,URL_SUFFIX), '', $article->url());
					if( ($pg = Page::find_page_by_uri($uri)) ) {
						$adescription .= $article->link( "<img src='". $pg->photoblog->thumbUrl() ."' />", "title='".$page_alt."'" );
					}
				}
				$adescription	.= "<p>". $article->description() ."</p>\n";
//				$adescription	.= "<p>". $article->link( "&gt; ". $page_alt ) ."</p>\n";
			// else if current page has a 'abstract' page part use it for description
			} else if( $article->hasContent('abstract') ) { 
				$adescription	.= $article->content('abstract'); 
			// else if current page is a SeZaMo Photoblog page
//			} else if (strpos($article->url(),"/photoblog/")) { 
			} else if( $isSzmPhotoblog && Plugin::isEnabled('photoblog')) { 
				$uri = str_replace( array(URL_PUBLIC,URL_SUFFIX), '', $article->url());
				if( ($pg = Page::find_page_by_uri($uri)) ) {
					$adescription	.= $article->link( "<img src='". $pg->photoblog->thumbUrl() ."' />", "title='".$page_alt."'" );
				}
				$adescription	.= "<p>". $article->description() ."</p>\n";
//				$adescription	.= "<p>". $article->link( "&gt; ". __("Open image") ) ."</p>\n";
			// finally, if none match use content for description... potentially dangerous for memory consuption
			} else { 
				$adescription	= strip_tags($article->content());
			}
			
			// Now create Item object
			$this->items[] = new _SzmRssItem( $atitle, $adescription, $article, self::getRssUrl(), self::getTitle() );
			// Merge categories
			$this->categories	= array_merge( $this->categories, $article->tags() );
		}
	}
	
	/**
	 * The entry point for frontend dispatched requests
	 * 
	 * @return none
	 */
	public function outputRss() {
		
		if( ! self::validSettings() ) {
			// Something is wrong with configuration: return page not found
			page_not_found();
		}
		
		// Build list of item
		$this->processItems();
		
		// Output header
		header('Content-Type: text/xml; charset=UTF-8', true);
		// Output XML
		echo "<?xml version='1.0' encoding='UTF-8'?".">\n";
		echo "<rss version=\"2.0\"\n\txmlns:atom=\"http://www.w3.org/2005/Atom\"\n\txmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\">\n";
		echo "<channel>\n";
		echo "\t<title>", self::getTitle(), "</title>\n";
		echo "\t<link>", URL_PUBLIC, "</link>\n";
		echo "\t<atom:link href=\"", self::getRssUrl(), "\" rel=\"self\" type=\"application/rss+xml\" />\n";
		echo "\t<copyright>Copyright ", date('Y'), " ", BASE_URL, "</copyright>\n";
		// Optional tags
		$x = self::getDescription();	if( !empty($x) )	echo "\t<description>", $x, "</description>\n";
		$x = self::getLanguage();		if( !empty($x) )	echo "\t<language>", $x, "</language>\n";
		$x = self::getWebmaster();		if( !empty($x) )	echo "\t<webMaster>", $x, "</webMaster>\n";
		
		$this->printHeaderDates();
		$this->printCategories();
		echo "\t<generator>", self::PLUGIN_TITLE, " v", self::PLUGIN_VERSION, "</generator>\n";
		echo "\t<docs>http://www.rssboard.org/rss-2-0-1</docs>\n";
		echo "\t<sy:updatePeriod>daily</sy:updatePeriod>\n";
		echo "\t<sy:updateFrequency>1</sy:updateFrequency>\n";
		
		// Finally, print all items
		$this->printItems();

		echo "</channel>\n";
		echo "</rss>\n";

		// And flush output
		ob_flush();
		flush();
		exit();
	}
	
	private function printHeaderDates() {
//		$cl = setLocale( LC_ALL, "0" );
//		echo "<!-- Current locale: ". $cl ." -->\n";
//		$cl = setLocale( LC_ALL, "en" );
		// Set English locale to format timestamp and timezone
		$cl = setLocale( LC_ALL, "en_US" );
//		echo "<!-- Current locale: ". $cl ." -->\n";
		// Sul mio test locale il timezone non funziona, usiamo GMT
//		echo "\t<pubDate>",			gmstrftime('%a, %d %b %Y %H:%M:%S %Z',$this->maxPubDate), "</pubDate>\n";
		echo "\t<pubDate>",			gmstrftime('%a, %d %b %Y %H:%M:%S +0000',$this->maxPubDate), "</pubDate>\n";
		echo "\t<lastBuildDate>",	gmstrftime('%a, %d %b %Y %H:%M:%S +0000',$this->maxUpdDate), "</lastBuildDate>\n";
	}
	
	private function printCategories() {
//		$s = self::getCategories();
//		if( ! empty( $this->categories) )
//			$s .= ",". join(',',$this->categories);
//		if( !empty($s) )
//			echo "\t<category>$s</category>\n";
//		foreach (explode(",", $s) as $category ) {
//			echo "\t<category><![CDATA[",			$category, "]]></category>\n";
//		}
		foreach ($this->categories as $category) {
			echo "\t<category><![CDATA[",			$category, "]]></category>\n";
		}
	}
	
	private function printItems() {
		foreach ($this->items as $item) {
			$item->outputRssItem();
		}
	}

	/* ----------------------------------------------------------------------------------
	 * BACKEND member functions
	 */
	public function index() {
		$this->documentation();
	}

	public function documentation() {
		$this->display(self::PLUGIN_ID.'/views/documentation');
	}

	function settings() {
		$this->display(self::PLUGIN_ID.'/views/settings', self::$savedSettings);
	}

	function save() {
		// Retrieve settings from form
		self::$savedSettings['title']		= mysql_escape_string($_POST['title']);
		self::$savedSettings['description']	= mysql_escape_string($_POST['description']);
		self::$savedSettings['relativeUrl']	= mysql_escape_string($_POST['relativeUrl']);
		self::$savedSettings['webmaster']	= mysql_escape_string($_POST['webmaster']);
		self::$savedSettings['maxitems']	= mysql_escape_string($_POST['maxitems']);
		self::$savedSettings['language']	= mysql_escape_string($_POST['language']);
		self::$savedSettings['parents']		= mysql_escape_string($_POST['parents']);
		self::$savedSettings['categories']	= mysql_escape_string(trim($_POST['categories'],",; "));
		//		$settings = array('snippetname' => mysql_escape_string($_POST['snippetname']),
		//						  'priLanguage' => mysql_escape_string($_POST['priLanguage']),
		//						  'altLanguage' => mysql_escape_string($_POST['altLanguage']),
		//						  'page_status' => mysql_escape_string($_POST['page_status']),
		//						  'thumbwidth' => mysql_escape_string($_POST['thumbwidth']),
		//						  'thumbheight' => mysql_escape_string($_POST['thumbheight'])
		//						 );

		// Adjust url format
		if( ! startsWith( self::$savedSettings['relativeUrl'], "/") )
			self::$savedSettings['relativeUrl'] = "/" . self::$savedSettings['relativeUrl'];
			
		// Save settings
		if( Plugin::setAllSettings(self::$savedSettings, self::PLUGIN_ID) )
			Flash::set('success', __('The settings have been updated.'));
		else
			Flash::set('error', 'An error has occured.');

		redirect( get_url('plugin/'. self::PLUGIN_ID .'/settings') );
	}
}

/**
 * Helper class to collect item data and
 * output them as RSS <item>s.
 * 
 * @author Maurizio Serrazanetti <info@sezamo.net>
 */
class _SzmRssItem {
	
	private	$title;				// Item title
	private	$description;		// Item description
	private $url;				// The URL
	private $permalink;			// The GUID as permalink
	private $publishedDate;		// A date object
//	private $updatedDate;		// A date object
	private $author;			// Author string
	private $categories;		// An array of strings
	private $article;			// A Page instance ..?!? XXX Better to remove this
	private	$sourceUrl;			// The source url
	private	$sourceTitle;		// The source url
	
	
	/**
	 * Build from a Page instance
	 * 
	 * @param $page	Page instance
	 * @return none
	 */
	function __construct( $title, $description, Page $page, $sourceUrl, $sourceTitle ) {
		$this->title			= $title;
		$this->description		= $description;
		$this->url				= $page->url();
		$this->permalink		= $page->url();
		$this->publishedDate	= strtotime($page->published_on);
//		$this->updatedDate		= strtotime($page->updated_on);
		$this->categories		= $page->tags();
		$this->article			= $page;
		$this->sourceUrl		= $sourceUrl;
		$this->sourceTitle		= $sourceTitle;
		$u = User::findById( $page->authorId() );
		if( !empty($u) )
		$this->author			= $u->email .' ('. $u->name .')';
	}
	
	/**
	 * Output this instance as a RSS <item>
	 * 
	 * @return none
	 */
	public function outputRssItem() {
		echo "\t<item>\n";
		echo "\t\t<title>", 					$this->title, "</title>\n";
		echo "\t\t<link>",						$this->url, "</link>\n";
		echo "\t\t<description><![CDATA[",		$this->description, "]]></description>\n";
		echo "\t\t<author>",					$this->author, "</author>\n";
//		echo "\t\t<category>",					join(', ', $this->categories), "</category>\n";
		foreach ($this->categories as $category) {
			echo "\t\t<category><![CDATA[",			$category, "]]></category>\n";
		}
		if( !empty($this->permalink) )
		echo "\t\t<guid isPermaLink=\"true\">",	$this->permalink, "</guid>\n";
		echo "\t\t<pubDate>",					gmstrftime('%a, %d %b %Y %H:%M:%S +0000',$this->publishedDate), "</pubDate>\n";
//< ?php //		<!-- comments></comments -->	? >
//		<pubDate>< ? php echo $article->date( '%a, %d %b %Y %H:%M:%S %z', 'published' ); ? ></pubDate>

		echo "\t\t<source url=\"",				$this->sourceUrl, "\">", $this->sourceTitle, "</source>\n";
		echo "\t</item>\n";
	}
}
