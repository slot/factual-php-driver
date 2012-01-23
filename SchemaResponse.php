<?php


/**
 * Represents the response from running a schema request against Factual.
 * This is a refactoring of the Factual Driver by Aaron: https://github.com/Factual/factual-java-driver
 * @author Tyler
 * @package Factual
 * @license Apache 2.0
 */
class SchemaResponse extends Response {
	protected $json; //string
	private $columnSchemas = array(); //array or ColumnSchema objects
	private $title; //string
	private $searchEnabled; //bool
	private $geoEnabled; //bool
	private $description; //string

	/**
	 * Constructor, parses from a JSON response String.
	 * @param json the JSON response String returned by Factual.
	 */
	public function __construct($json) {
		try{
			$this->json = $json;
			$rootJSON = json_decode($json,true);
			$this->title = $rootJSON['response']['view']['title'];
			$this->description = $rootJSON['response']['view']['description'];
			$this->makeColumnSchemas($rootJSON['response']['view']['fields']);
			$this->searchEnabled = (bool)$rootJSON['response']['view']['search_enabled'];
			$this->geoEnabled = (bool) $rootJSON['response']['view']['geo_enabled'];
		} catch (Exception $e) {
		  throw new Exception($e);
		}
	}

	/**
	 * Gets objects describing column schemas
	 */
	  private function makeColumnSchemas($fields) {
	  	foreach ($fields as $column){
	  		$this->columnSchemas[$column['name']] = new ColumnSchema($column);
	  	}
	  }

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	/**
	 * @return bool
	 */
	public function isSearchEnabled() {
		return $this->searchEnabled;
	}
	/**
	 * @return bool
	 */
	public function isGeoEnabled() {
		return $this->geoEnabled;
	}

	/**
	 * @return int The size of the schema (that is, the number of columns in the
	 *         table)
	 */
	public function size() {
		return $this->count($this->columnSchemas);
	}
	/**
	 * Get all column schemas
	 * @return array
	 */
	public function getColumnSchemas() {
		return $this->columnSchemas;
	}
	/**
	 * @param string columnName Column name
	 * @return array
	 */
	public function getColumnSchema($columnName) {
		return $this->columnSchemas[$columnName];
	}
}
?>