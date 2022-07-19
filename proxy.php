<?php
/**
 * ezForum http://www.ezforum.com
 * Copyright 2011-2017 ezForum
 * License: BSD
 *
 * Based on:
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2016 Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0.19
 */


define('EZF', 'proxy');

/**
 * Class ProxyServer
 */
class ProxyServer
{
	/** @var bool $enabled Whether or not this is enabled */
	protected $enabled;

	/** @var int $maxSize The maximum size for files to cache */
	protected $maxSize;

	/** @var string $secret A secret code used for hashing */
	protected $secret;

	/** @var string The cache directory */
	protected $cache;


	/**
	 * Constructor, loads up the Settings for the proxy
	 *
	 * @access public
	 */
	public function __construct()
	{
		global $image_proxy_enabled, $image_proxy_maxsize, $image_proxy_secret, $cachedir, $sourcedir;

		require_once(dirname(__FILE__) . '/Settings.php');
		require_once($sourcedir . '/Class-CurlFetchWeb.php');

		// Turn off all error reporting; any extra junk makes for an invalid image.
		error_reporting(0);

		$this->enabled = (bool) $image_proxy_enabled;
		$this->maxSize = (int) $image_proxy_maxsize;
		$this->secret = (string) $image_proxy_secret;
		$this->cache = $cachedir . '/images';
	}

	/**
	 * Checks whether the request is valid or not
	 *
	 * @access public
	 * @return bool Whether the request is valid
	 */
	public function checkRequest()
	{
		if (!$this->enabled)
			return false;

		// Try to create the image cache directory if it doesn't exist
		if (!file_exists($this->cache))
		{
			if (!mkdir($this->cache) || !copy(dirname($this->cache) . '/index.php', $this->cache . '/index.php'))
				return false;
		}

		// Basic sanity check
		$_GET['request'] = filter_var($_GET['request'], FILTER_VALIDATE_URL);

		// We aren't going anywhere without these
		if (empty($_GET['hash']) || empty($_GET['request']))
			return false;

		$hash = $_GET['hash'];
		$request = $_GET['request'];

		if (hash_hmac('sha1', $request, $this->secret) != $hash)
			return false;

		// Attempt to cache the request if it doesn't exist
		if (!$this->isCached($request))
			return $this->cacheImage($request);

		return true;
	}


	/**
	 * Serves the request
	 *
	 * @access public
	 * @return void
	 */
	public function serve()
	{
		$request = $_GET['request'];
		$cached_file = $this->getCachedPath($request);
		$cached = json_decode(file_get_contents($cached_file), true);

		// Did we get an error when trying to fetch the image
		$response = $this->checkRequest();
		if (!$response)
		{
			// Throw a 404
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		// Is the cache expired? Try to refresh it.
		if (!$cached || time() - $cached['time'] > (5 * 86400))
		{
			@unlink($cached_file);
			if ($this->checkRequest())
				$this->serve();
			exit;
		}

		// Make sure we're serving an image
		$contentParts = explode('/', !empty($cached['content_type']) ? $cached['content_type'] : '');
		if ($contentParts[0] != 'image')
			exit;

		// Check whether the ETag was sent back, and cache based on that...
		$eTag = '"' . substr(sha1($request) . $cached['time'], 0, 64) . '"';
		if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false)
		{
			header('HTTP/1.1 304 Not Modified');
			exit;
		}

		header('Content-type: ' . $cached['content_type']);
		header('Content-length: ' . $cached['size']);

		// Add some caching
		header('Cache-control: public');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($cached_file)) . ' GMT');
		header('ETag: ' . $eTag);

		echo base64_decode($cached['body']);
	}

	/**
	 * Returns the request's hashed filepath
	 *
	 * @access protected
	 * @param string $request The request to get the path for
	 * @return string The hashed filepath for the specified request
	 */
	protected function getCachedPath($request)
	{
		return $this->cache . '/' . sha1($request . $this->secret);
	}

	/**
	 * Check whether the image exists in local cache or not
	 *
	 * @access protected
	 * @param string $request The image to check for in the cache
	 * @return bool Whether or not the requested image is cached
	 */
	protected function isCached($request)
	{
		return file_exists($this->getCachedPath($request));
	}

	/**
	 * Attempts to cache the image while validating it
	 *
	 * @access protected
	 * @param string $request The image to cache/validate
	 * @return bool|null Whether the specified image was cached; null if not found or not an image.
	 */
	protected function cacheImage($request)
	{
		$request_url = $request;

		$dest = $this->getCachedPath($request);
		$curl = new curl_fetch_web_data(array(CURLOPT_BINARYTRANSFER => 1));
		$request = $curl->get_url_data($request);
		$responseCode = $request->result('code');
		$response = $request->result();

		if (empty($response) || $responseCode != 200)
			$this->redirectexit($request_url);

		$headers = $response['headers'];

		// What kind of file did they give us?
		if (function_exists('finfo_open'))
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$headers['content-type'] = finfo_buffer($finfo, $response['body']);
			finfo_close($finfo);
		}

		// SVG needs a little extra care
		if (in_array($headers['content-type'], array('text/plain', 'text/xml')) && strtolower(pathinfo(parse_url($request_url, PHP_URL_PATH), PATHINFO_EXTENSION)) == 'svg' && strpos($response['body'], '<svg') !== false && strpos($response['body'], '</svg>') !== false)
			$headers['content-type'] = 'image/svg+xml';

		// Make sure the url is returning an image
		$contentParts = explode('/', !empty($headers['content-type']) ? $headers['content-type'] : '');
		if ($contentParts[0] != 'image')
			$this->redirectexit($request_url);

		// Validate the filesize
		if ($response['size'] > ($this->maxSize * 1024))
			$this->redirectexit($request_url);

		return file_put_contents($dest, json_encode(array(
			'content_type' => $headers['content-type'],
			'size' => $response['size'],
			'time' => time(),
			'body' => base64_encode($response['body']),
		))) !== false;
	}

	/**
	 * A helper function to redirect a request
	 *
	 * @access private
	 * @param string $request
	 */
	private function redirectexit($request)
	{
		header('Location: ' . un_htmlspecialchars($request), false, 301);
		exit;
	}
}

$proxy = new ProxyServer();
$proxy->serve();

?>