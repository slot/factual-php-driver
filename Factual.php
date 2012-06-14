<?php


/**
 * Requires PHP5, php5-curl, SPL (for autoloading)
 */

//Oauth libs (from http://code.google.com/p/oauth-php/)
require_once ('oauth-php/library/OAuthStore.php');
require_once ('oauth-php/library/OAuthRequester.php');

/**
 * Represents the public Factual API. Supports running queries against Factual
 * and inspecting the response. Supports the same levels of authentication
 * supported by Factual's API.
 * This is a refactoring of the Factual Driver by Aaron: https://github.com/Factual/factual-java-driver
 * @author Tyler
 * @package Factual
 * @license Apache 2.0
 */
class Factual {

	protected $factHome; //string assigned from config
	protected $signer; //OAuthStore object
	protected $config; //array from config.ini file on construct
	protected $geocoder; //geocoder object (unsupported, experimental)
	protected $configPath = "config.ini"; //where the config file is found: path + file
	protected $lastTable = null; //last table queried
	protected $fetchQueue = array (); //array of queries teed up for multi

	/**
	 * Constructor. Creates authenticated access to Factual.
	 * @param string key your oauth key.
	 * @param string secret your oauth secret.
	 */
	public function __construct($key, $secret) {
		//load configuration
		$this->loadConfig();
		$this->factHome = $this->config['factual']['endpoint']; //assign endpoint
		//create authentication object
		$options = array (
			'consumer_key' => $key,
			'consumer_secret' => $secret
		);
		$this->signer = OAuthStore :: instance("2Leg", $options);
		//register autoloader
		spl_autoload_register(array (
			get_class(),
			'factualAutoload'
		));
	}

	/**
	 * Sets location of config file at runtime
	 * @param string path path+filename
	 * @return void
	 */
	protected function setConfigPath($path) {
		$this->configPath = $path;
	}

	/**
	 * Loads config file from ini
	 * @return void
	 */
	protected function loadConfig() {
		if (!$this->config) {
			try {
				$this->config = parse_ini_file($this->configPath, true);
			} catch (Exception $e) {
				throw new Exception("Failed parsing config file");
			}
		}
	}

	/**
	 * Change the base URL at which to contact Factual's API. This
	 * may be useful if you want to talk to a test or staging
	 * server withou changing config
	 * Example value: <tt>http://staging.api.v3.factual.com/t/</tt>
	 * @param urlBase the base URL at which to contact Factual's API.
	 * @return void
	 */
	public function setFactHome($urlBase) {
		$this->factHome = $urlBase;
	}

	/**
	 * Convenience method to return Crosswalks for the specific query.
	 * @param string table Table name
	 * @param object query Query Object
	 */
	public function crosswalks($table, $query) {
		return $this->fetch($table, $query)->getCrosswalks();
	}

	/**
	 * Factual Fetch Abstraction
	 * @param string tableName The name of the table you wish to query (e.g., "places")
	 * @param obj query The query to run against <tt>table</tt>.
	 * @return object ReadResponse object with result of running <tt>query</tt> against Factual.
	 */
	public function fetch($tableName, $query) {
		switch (get_class($query)) {
			case "FactualQuery" :
				$res = new ReadResponse($this->request($this->urlForFetch($tableName, $query)));
				break;
			case "CrosswalkQuery" :
				$res = new CrosswalkResponse($this->request($this->urlForCrosswalk($tableName, $query)));
				break;
			case "ResolveQuery" :
				$res = new ResolveResponse($this->request($this->urlForResolve($tableName, $query)));
				break;
			case "FacetQuery" :
				$res = new ReadResponse($this->request($this->urlForFacets($tableName, $query)));
				break;
			default :
				throw new Exception(__METHOD__ . " class type '" . get_class($query) . "' not recognized");
				$res = false;
		}
		$this->lastTable = $tableName; //assign table name to object for logging
		return $res;
	}

	/**
	* Build query string without running fetch
	* @param string tableName The name of the table you wish to query (e.g., "places")
	* @param obj query The query to run against <tt>table</tt>.
	* @return string
	*/
	public function buildQuery($tableName, $query) {
		switch (get_class($query)) {
			case "FactualQuery" :
				$res = $this->urlForFetch($tableName, $query);
				break;
			case "CrosswalkQuery" :
				$res = $this->urlForCrosswalk($tableName, $query);
				break;
			case "ResolveQuery" :
				$res = $this->urlForResolve($tableName, $query);
				break;
			case "FacetQuery" :
				$res = $this->urlForFacets($tableName, $query);
				break;
			default :
				throw new Exception(__METHOD__ . " class type '" . get_class($query) . "' not recognized");
				$res = false;
		}
		return $res;
	}

	/**
	 * Resolves and returns resolved entity or null (shortcut method -- experimental)
	 * @param string tableName Table name
	 * @param array vars Attributes of entity to be matched in key=>value pairs
	 * @return array | null
	 */
	public function resolve($tableName, $vars) {
		$query = new ResolveQuery();
		foreach ($vars as $key => $value) {
			$query->add($key, $value);
		}
		$res = new ResolveResponse($this->request($this->urlForResolve($tableName, $query)));
		return $res->getResolved();
	}

	/**
	 * @return object SchemaResponse object
	 */
	public function schema($tableName) {
		return new SchemaResponse($this->request($this->urlForSchema($tableName)));
	}

	protected function urlForSchema($tableName) {
		return $this->factHome . "t/" . $tableName . "/schema";
	}

	protected function urlForCrosswalk($tableName, $query) {
		return $this->factHome . $tableName . "/crosswalk?" . $query->toUrlQuery();
	}

	protected function urlForResolve($tableName, $query) {
		return $this->factHome . $tableName . "/resolve?" . $query->toUrlQuery();
	}

	protected function urlForFetch($tableName, $query) {
		return $this->factHome . "t/" . $tableName . "?" . $query->toUrlQuery();
	}

	protected function urlForFacets($tableName, $query) {
		return $this->factHome . "t/" . $tableName . "/facets?" . $query->toUrlQuery();
	}

	protected function urlForMulti() {
		$homeLen = strlen($this->factHome) - 1;
		foreach ($this->fetchQueue as $index => $mQuery) {
			$call = rawurlencode(substr($this->buildQuery($mQuery['table'], $mQuery['query']), $homeLen));
			$queryStrings[] = "\"" . $index . "\":" . "\"" . $call . "\"";
			$res['response'][] = $mQuery['query']->getResponseType();
		}
		$res['url'] = "http://api.v3.factual.com/multi?queries={" . implode(",", $queryStrings) . "}";
		return $res;
	}

	/*
	 protected function urlForGeocode() {
	   return "places/geocode";
	 }
	*/

	protected function urlForGeopulse($tableName, $query) {
		return "places/geopulse?" . $query->toUrlQuery();
	}

	/**
	 * Queue a request for inclusion in a multi request.
	 * @param string table The name of the table you wish to use crosswalk against (e.g., "places")
	 * @param obj query Query object to run against <tt>table</tt>.
	 * @param string name Name of this query to help you distinguish return values
	 */
	public function multiQueue($table, $query, $name) {
		$this->fetchQueue[$name] = array (
			'query' => $query,
			'table' => $table
		);
		return $this->fetchQueue;
	}

	/**
	  * Use this to send all queued reads as a multi request
	  * @return response for a multi request
	  */
	public function multiFetch() {
		//get response types required for multi objects
		$res = $this->urlForMulti();
		return new MultiResponse($this->request($res['url']), $res['response']);
	}

	/**
	 * Signs a 'raw' request (a complete query) and returns the JSON results
	 * Note that this does not process the quey at all -- just signs and returns results
	 * @param string urlStr unsigned URL request. Must be correctly escaped.
	 * @return string JSON reponse
	 */
	public function rawGet($urlStr) {
		$res = $this->request($urlStr);
		return $res['body'];
	}

	/**
	 * Sign the request, perform a curl request and return the results
	 * @param string urlStr unsigned URL request
	 * @return array ex: array ('code'=>int, 'headers'=>array(), 'body'=>string)
	 */
	protected function request($urlStr) {
		$requestMethod = "GET";
		$params = null;
		$customHeaders[CURLOPT_HTTPHEADER] = array (
			"X-Factual-Lib: " . $this->config['factual']['driverversion']
		); //custom header
		// Build request with OAuth request params
		$request = new OAuthRequester($urlStr, $requestMethod, $params);
		//Make request
		$result = $request->doRequest(0, $customHeaders);
		$result['request'] = $urlStr; //pass request string onto response
		$result['tablename'] = $this->lastTable; //pass table name to result object (not available with rawGet())
		//exception handling
		if ($result['code'] >= 400) {
			$body = json_decode($result['body'], true);
			//get a boatload of debug data
			$info['code'] = $result['code'];
			$info['version'] = $body['version'];
			$info['status'] = $body['status'];
			$info['error_type'] = $body['error_type'];
			$info['message'] = $body['message'];
			$info['headers'] = $result['headers'];
			$info['request'] = $result['request'];
			$info['driver'] = $this->config['factual']['driverversion'];
			if (!empty ($result['tablename'])) {
				$info['tablename'] = $result['tablename'];
			}
			$info['method'] = $requestMethod;
			//chuck exception
			$factualE = new FactualApiException($info);
			throw $factualE;
		}
		return $result;
	}

	/**
	 * Gets driver version
	 * @return string
	 */
	public function version() {
		return $this->config['factual']['driverversion'];
	}

	//The following methods are included as handy convenience; unsupported and experimental
	//They rely on a loosely-coupled third-party service that can be easily swapped out

	/**
	* Geocodes address string or placename
	* @param string q 
	* @return array
	*/
	public function geocode($address) {
		return $this->getGeocoder()->geocode($address);
	}
	/**
	* Reverse geocodes long/lat to the smallest bounding WOEID
	* @param real long Decimal Longitude
	* @param real lat Decimal Latitude
	* @return array single result
	*/
	public function reverseGeocode($lon, $lat) {
		return $this->getGeocoder()->reversegeocode($lon, $lat);
	}

	/**
	* Geocodes address string or placename
	* @param string q 
	* @return array
	*/
	protected function getGeocoder() {
		if (!$this->geocoder) {
			$this->geocoder = new GeocoderWrapper;
		}
		return $this->geocoder;
	}

	/**
	 * Autoloader for file dependencies
	 * Called by spl_autoload_register() to avoid conflicts with autoload() methods from other libs
	 */
	public static function factualAutoload($className) {
        $filename = dirname(__FILE__)."/".$className . ".php";

        // don't interfere with other classloaders
        if(!file_exists($filename)) {
            return;
        }

        include $filename;
	}

}
?>
