<?php

/**
 * Represents an Exception that happened while communicating with Factual.
 * Includes information about the request that triggered the problem.
 * This is a refactoring of the Factual Driver by Aaron: https://github.com/Factual/factual-java-driver
 * @author Tyler
 * @package Factual
 * @license Apache 2.0
 */
class FactualApiException extends Exception {
	protected $info; //debug array
	protected $helpUrl = "https://github.com/Factual/factual-php-driver#help-debugging--testing";

	public function __construct($info) {
		$this->info = $info;
		$this->message = $info['message']." Details:\n".print_r($info,true)."\n use FactualApiException::debug()" .
				" to obtain this information programatically. See ". $this->helpUrl.
				" for information on submitting bugs and questions.\n\n";
	}

	public function debug(){
		return $this->info;
	}
}
?>