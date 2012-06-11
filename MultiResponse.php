<?php

/**
 * Represents the response from running a fetch request against Factual, such as
 * a geolocation based query for specific places entities.
 * @author Tyler
 * @package Factual
 * @license Apache 2.0
 */
class MultiResponse extends ArrayIterator {
  protected $responses = array();
  protected $objects = array(); 
  protected $version = array(); //string
  protected $status = array();
  protected $data = array();
  protected $json;
  protected $responseHeaders = array();
  protected $responseCode = null;
  protected $request = null;
  protected $responseTypes = array();

  /**
   * Constructor, parses return values from CURL in factual::request() 
   * @param array response The JSON response String returned by Factual.
   */
  public function __construct($apiResponse,$responseTypes) {
    try {
    	$this->json = $apiResponse['body'];
    	$this->responseTypes = $responseTypes; //pass response types from query
    	$this->parseResponse($apiResponse);
    } catch (Exception $e) {
    	//add note about json encoding borking here
      throw $e ();
    }
  }

	/**
	 * Parses response from CURL
	 * @param array apiResponse response from curl
	 * @return void
	 */
	protected function parseResponse($apiResponse){	
		//divide body responses
		$rootJSON = json_decode($apiResponse['body'],true);
		$i=0;
		$singleResponse['headers'] = $apiResponse['headers'];
		$singleResponse['code'] = $apiResponse['code'];
		foreach ($rootJSON as $idx => $response){
			$this->responses[$idx] = json_encode($response); //get separate reponse component		
			$singleResponse['body'] = $this->responses[$idx];
			//$responseObject = new $this->responseTypes[$i]($singleResponse);
			$this->append(new $this->responseTypes[$i]($singleResponse)); //add response object to iterator
			$i++;
		}
		$this->parseJSON($rootJSON);
		$this->responseHeaders = $apiResponse['headers'];
		$this->responseCode = $apiResponse['code'];
		$this->request = $apiResponse['request'];
	}

	/**
	 * Parses JSON as array and assigns object values
	 * @param string json JSON returned from API
	 * @return array structured JSON
	 */
	protected function parseJSON($rootJSON){
    	//structure data
    	foreach ($rootJSON as $index => $multiResponse){
    		$this->data[$index] = 	$multiResponse['response']['data'];
    		$this->version[$index] = $multiResponse['version'];	
    		$this->status[$index] = $multiResponse['status'];
    	}   	
    	return $rootJSON;	
	}
	
	/**
	 * Get response headers sent by Factual
	 * @return array
	 */
	public function getResponseHeaders(){
		return $this->responseHeaders;
	}

	/**
	 * Get HTTP response code
	 * @return int
	 */
	public function getResponseCode(){
		return $this->responseCode;
	}

  /**
   * Get the entire JSON response from Factual
   * @return string 
   */
  public function getJson() {
    return $this->json;
  }

  /**
   * Get the returned entities as an array 
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Get the return entities as JSON 
   * @return the main data returned by Factual.
   */
  public function getDataAsJSON() {
    	return json_encode($this->data);
  }

  /**
   * Subclasses of FactualResponse must provide access to the original JSON
   * representation of Factual's response. Alias for getJson()
   * @return string
   */
  public function toString() {
    return $this->getJson();
  }
  
  /**
   * Get url-decoded request string, does not include auth.
   * @return string
   */
  public function getRequest(){
  	return urldecode($this->request);
  }
  
  /**
   * Get table name queried
   * @return string
   */
  public function getTable(){
  	return $this->tableName;
  }  
  
   /**
   * Get http headers returned by Factual
   * @return string
   */
  public function getHeaders(){
  	return $this->responseHeaders;
  }   
  
   /**
   * Get http status code returned by Factual
   * @return string
   */
  public function getCode(){
  	return $this->responseCode;
  }    	
	
	
	
	
	
}
?>