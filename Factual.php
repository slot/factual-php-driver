<?php

/**
 * Requires PHP5, php5-curl
 */


//Oauth libs (from http://code.google.com/p/oauth-php/)
require_once('oauth-php/library/OAuthStore.php');
require_once('oauth-php/library/OAuthRequester.php');

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
	
  const DRIVER_HEADER_TAG = "factual-php-driver-v1.0.0"; //Custom header
  private $factHome; //string assigned from config
  private $signer; //OAuthStore object
  private $config; //array from config.ini file on construct
  private $geocoder; //geocoder object (unsupported, experimental)
  const CONFIGPATH = "config.ini"; //where the config file is found: path + file

  /**
   * Constructor. Creates authenticated access to Factual.
   * @param string key your oauth key.
   * @param string secret your oauth secret.
   */
  public function __construct($key,$secret) {
  	$this->loadConfig(); //get deets from files
    $this->factHome = $this->config['factual']['endpoint']; //assign endpoint
    //authentication
    $options = array('consumer_key' => $key, 'consumer_secret' => $secret);
	$this->signer = OAuthStore::instance("2Leg", $options );
  }

 	/**
 	 * Loads config file from ini
 	 * @return void
 	 */
 	protected function loadConfig(){
 		if (!$this->config){
 			$this->config = parse_ini_file(self::CONFIGPATH,true);
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
    case "Query":
    	$res = new ReadResponse($this->request($this->urlForFetch($tableName, $query)));
    	$this->setEntityType($tableName, $res);
        return $res;
        break;
    case "CrosswalkQuery":
        return new CrosswalkResponse($this->request($this->urlForCrosswalk($tableName, $query)));
        break;
    case "ResolveQuery":
        $res = new ResolveResponse($this->request($this->urlForResolve($tableName, $query)));
    	$this->setEntityType($tableName, $res);
        return $res;        
        break;
	} 
  }
  
  /**
   * Resolves and returns resolved entity or null (shortcut method -- experimental)
   * @param string tableName Table name
   * @param array vars Attributes of entity to be matched in key=>value pairs
   * @return array | null
   */
	public function resolve($tableName,$vars){
		$query = new ResolveQuery();
		foreach ($vars as $key => $value){
			$query->add($key,$value);
		}
        $res = new ResolveResponse($this->request($this->urlForResolve($tableName, $query)));
    	$this->setEntityType($tableName, $res);
        return $res->getResolved(); 		
	}

	/**
	 * Assigns entity type by table name to response object according to config
	 * @param string tableName (places, restaurants, etc.)
	 * @param object res query response object
	 * @return void
	 */
	 private function setEntityType($tableName, $res){
	 	if ($this->config['tabletypes'][$tableName]){
	 		$res->setEntityType($this->config['tabletypes'][$tableName]);
	 	}
	 }

	/**
	 * @return object SchemaResponse object
	 */
  public function schema($tableName) {
    return new SchemaResponse($this->request($this->urlForSchema($tableName)));
  }

  private function urlForSchema($tableName) {
    return $this->factHome . "t/" . $tableName . "/schema";
  }

  private function urlForCrosswalk($tableName, $query) {
    return $this->factHome.$tableName."/crosswalk?".$query->toUrlQuery();
  }

  private function urlForResolve($tableName, $query) {
    return $this->factHome.$tableName."/resolve?".$query->toUrlQuery();
  }

  private function urlForFetch($tableName, $query) {
    return $this->factHome."t/".$tableName."?".$query->toUrlQuery();
  }

	/**
	 * Sign the request, perform a curl request and return the results
	 * @param string urlStr unsigned URL request
	 * @return array ex: array ('code'=>int, 'headers'=>array(), 'body'=>string)
	 */
  private function request($urlStr) {
	$requestMethod = "GET";
	$params = null;
	$customHeaders[CURLOPT_HTTPHEADER] = array("X-Factual-Lib: ".self::DRIVER_HEADER_TAG); //crfeate custom header
    // Build request with OAuth request params
    $request = new OAuthRequester($urlStr, $requestMethod, $params);
 	//Make request
    try {
    	$result = $request->doRequest(0,$customHeaders);
    	$response = $result['body']; //employ only body string from here on in
    	return $response;
	} catch(Exception $e) {
		$factualE = new FactualApiException($e);
		$factualE->requestMethod($requestMethod);	
		$factualE->requestUrl($urlStr);
		throw $factualE;
	}
  }
  
  //The following methods are included as handy convenience; unsupported and experimental
  //They rely on a loosely-coupled third-party service that can be easily swapped out
  
  	/**
	* Geocodes address string or placename
	* @param string q 
	* @return array
	*/
  public function geocode($address){
  	return $this->getGeocoder()->geocode($address);
  }
  	/**
	* Reverse geocodes long/lat to the smallest bounding WOEID
	* @param real long Decimal Longitude
	* @param real lat Decimal Latitude
	* @return array single result
	*/
  public function reverseGeocode($lon, $lat){
  	return $this->getGeocoder()->reversegeocode($lon, $lat);
  }
  
  	/**
	* Geocodes address string or placename
	* @param string q 
	* @return array
	*/
  private function getGeocoder(){
  	if (!$this->geocoder){
  		$this->geocoder = new Geocoder;
  	}
  	return $this->geocoder;
  }
  
}

  //extra-class autoloader
  function __autoload($className) {
    include dirname(__FILE__)."/".$className . ".php";
  }
  
?>
