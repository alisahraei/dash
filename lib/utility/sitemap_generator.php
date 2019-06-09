<?php
namespace dash\utility;

/** generating Sitemap files **/
class sitemap_generator
{
	/**
	 * Sitemap
	 *
	 * This class used for generating Google Sitemap files
	 *
	 * @package    Sitemap
	 * @author     Osman Üngür <osmanungur@gmail.com>
	 * @author     Javad Evazzadeh <J.Evazzadeh@gmail.com>
	 * @copyright  2009-2015 Osman Üngür
	 * @license    http://opensource.org/licenses/MIT MIT License
	 * @link       http://github.com/o/sitemap-php
	 */


	/**
	 *
	 * @var XMLWriter
	 */
	private $writer;
	private $domain;
	private $root;
	private $path;
	private $filename = 'sitemap';
	private $current_item = 0;
	private $current_sitemap = 0;

	const EXT = '.xml';
	const SCHEMA = 'http://www.sitemaps.org/schemas/sitemap/0.9';
	const DEFAULT_PRIORITY = 0.5;
	const ITEM_PER_SITEMAP = 50000;
	const SEPERATOR = '-';
	const INDEX_SUFFIX = 'index';

	/**
	 *
	 * @param string $domain
	 */
	public function __construct($_domain, $_root = null, $_path = null)
	{
		$this->setDomain($_domain);
		$this->root = $_root;
		$this->setPath($this->root.$_path.'/');

		$folder = $this->getPath();
		if(!is_dir($folder))
			mkdir($folder, 0775, true);
	}

	/**
	 * Sets root path of the website, starting with http:// or https://
	 *
	 * @param string $domain
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Returns root path of the website
	 *
	 * @return string
	 */
	private function getDomain() {
		return $this->domain;
	}

	/**
	 * Returns XMLWriter object instance
	 *
	 * @return XMLWriter
	 */
	private function getWriter() {
		return $this->writer;
	}

	/**
	 * Assigns XMLWriter object instance
	 *
	 * @param XMLWriter $writer
	 */
	private function setWriter(\XMLWriter $writer) {
		$this->writer = $writer;
	}

	/**
	 * Returns path of sitemaps
	 *
	 * @return string
	 */
	private function getPath($_all = true)
	{
		if($_all)
			return $this->path;
		else
			return $this->root;
	}

	/**
	 * Sets paths of sitemaps
	 *
	 * @param string $path
	 * @return Sitemap
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * Returns filename of sitemap file
	 *
	 * @return string
	 */
	private function getFilename() {
		return $this->filename;
	}

	/**
	 * Sets filename of sitemap file
	 *
	 * @param string $filename
	 * @return Sitemap
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
		return $this;
	}

	/**
	 * Returns current item count
	 *
	 * @return int
	 */
	private function getCurrentItem() {
		return $this->current_item;
	}

	/**
	 * Increases item counter
	 *
	 */
	private function incCurrentItem() {
		$this->current_item = $this->current_item + 1;
	}

	/**
	 * Returns current sitemap file count
	 *
	 * @return int
	 */
	private function getCurrentSitemap() {
		return $this->current_sitemap;
	}

	/**
	 * Increases sitemap file count
	 *
	 */
	private function incCurrentSitemap() {
		$this->current_sitemap = $this->current_sitemap + 1;
	}

	/**
	 * Prepares sitemap XML document
	 *
	 */
	private function startSitemap() {
		$this->setWriter(new \XMLWriter());
		if ($this->getCurrentSitemap()) {
			$this->getWriter()->openURI($this->getPath() . $this->getFilename() . self::SEPERATOR . $this->getCurrentSitemap() . self::EXT);
		} else {
			$this->getWriter()->openURI($this->getPath() . $this->getFilename() . self::EXT);
		}
		$this->getWriter()->startDocument('1.0', 'UTF-8');
		$this->getWriter()->setIndent(true);
		$this->getWriter()->startElement('urlset');
		$this->getWriter()->writeAttribute('xmlns', self::SCHEMA);
	}

	/**
	 * Adds an item to sitemap
	 *
	 * @param string $loc URL of the page. This value must be less than 2,048 characters.
	 * @param string $priority The priority of this URL relative to other URLs on your site. Valid values range from 0.0 to 1.0.
	 * @param string $changefreq How frequently the page is likely to change. Valid values are always, hourly, daily, weekly, monthly, yearly and never.
	 * @param string|int $lastmod The date of last modification of url. Unix timestamp or any English textual datetime description.
	 * @return Sitemap
	 */
	public function addItem($loc, $priority = self::DEFAULT_PRIORITY, $changefreq = NULL, $lastmod = NULL) {
		// plus count of link added
		\dash\utility\sitemap::plus_count_all();

		if (($this->getCurrentItem() % self::ITEM_PER_SITEMAP) == 0) {
			if ($this->getWriter() instanceof \XMLWriter) {
				$this->endSitemap();
			}
			$this->startSitemap();
			$this->incCurrentSitemap();
		}
		$this->incCurrentItem();
		$this->getWriter()->startElement('url');
		$this->getWriter()->writeElement('loc', $this->getDomain() . $loc);
		$this->getWriter()->writeElement('priority', $priority);
		if ($changefreq)
			$this->getWriter()->writeElement('changefreq', $changefreq);
		if ($lastmod)
			$this->getWriter()->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
		$this->getWriter()->endElement();
		return $this;
	}

	/**
	 * Prepares given date for sitemap
	 *
	 * @param string $date Unix timestamp or any English textual datetime description
	 * @return string Year-Month-Day formatted date.
	 */
	private function getLastModifiedDate($date) {
		if (ctype_digit($date)) {
			return date('Y-m-d', $date);
		} else {
			$date = strtotime($date);
			return date('Y-m-d', $date);
		}
	}

	/**
	 * Finalizes tags of sitemap XML document.
	 *
	 */
	public function endSitemap() {
		if (!$this->getWriter()) {
			$this->startSitemap();
		}
		$this->getWriter()->endElement();
		$this->getWriter()->endDocument();
	}

	/**
	 * Writes Google sitemap index for generated sitemap files
	 *
	 * @param string $loc Accessible URL path of sitemaps
	 * @param string|int $lastmod The date of last modification of sitemap. Unix timestamp or any English textual datetime description.
	 */
	public function createSitemapIndex($loc = null, $lastmod = 'Today')
	{
		if(!$loc)
			$loc = $this->getDomain().'sitemap/';

		$this->endSitemap();
		$indexwriter = new \XMLWriter();
		$indexwriter->openURI($this->getPath(false) . $this->getFilename() . self::EXT);
		$indexwriter->startDocument('1.0', 'UTF-8');
		$indexwriter->setIndent(true);
		$indexwriter->startElement('sitemapindex');
		$indexwriter->writeAttribute('xmlns', self::SCHEMA);

		$current_sitemap = $this->getCurrentSitemap();

		for ($index = 0; $index < $current_sitemap; $index++)
		{
			$indexwriter->startElement('sitemap');
			$indexwriter->writeElement('loc', $loc . $this->getFilename() . ($index ? self::SEPERATOR . $index : '') . self::EXT);
			$indexwriter->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
			$indexwriter->endElement();
		}
		$indexwriter->endElement();
		$indexwriter->endDocument();
	}


	// make some sitemapindex in one file
	public function makeSitemapIndex($urls, $loc = null, $lastmod = 'Today')
	{
		if(!$loc)
		{
			$loc = $this->getDomain().'sitemap/';
		}

		$indexwriter = new \XMLWriter();
		$indexwriter->openURI($this->getPath(false) . $this->getFilename() . self::EXT);
		$indexwriter->startDocument('1.0', 'UTF-8');
		$indexwriter->setIndent(true);
		$indexwriter->startElement('sitemapindex');
		$indexwriter->writeAttribute('xmlns', self::SCHEMA);

		$current_sitemap = $this->getCurrentSitemap();

		foreach ($urls as $myUrl)
		{
			$indexwriter->startElement('sitemap');
			$indexwriter->writeElement('loc', $loc. $myUrl . self::EXT);
			$indexwriter->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
			$indexwriter->endElement();
		}

		$indexwriter->endElement();
		$indexwriter->endDocument();
	}
}
?>