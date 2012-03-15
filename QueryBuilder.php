<?php


/**
 * Provides fluent interface to specifying row filter predicate logic
 * Based on Factual Java Driver
 * This is a refactoring of the Factual Driver by Aaron: https://github.com/Factual/factual-java-driver
 * @author Tyler
 * @package Factual
 * @license Apache 2.0
 */
class QueryBuilder {

	private $query;
	private $fieldName;

	/**
	 * Constructor. Specifies the name of the field for which to build filter
	 * logic. Instance methods are used to specify the desired logic.
	 */
	public function __construct($query, $fieldName) {
		$this->query = $query;
		$this->fieldName = $fieldName;
	}

	/**
	 * Specifies a full text search.
	 * @param string arg The term(s) for which to full text search against.
	 * @return the represented query, with the specified full text search added in.
	 */
	public function search($arg) {
		return $this->addFilter("\$search", $arg);
	}

	public function equal($arg) {
		return $this->addFilter("\$eq", $arg);
	}

	public function notEqual($arg) {
		return $this->addFilter("\$neq", $arg);
	}
	/**
	 * @param array arg Array of arguments
	 */
	public function in($args) {
		if (!is_array($args)) {
			throw new Exception("The 'in' operator requires array argument");
			return false;
		}
		return $this->addFilter("\$in", $args);
	}
	/**
	 * @param array arg Array of arguments
	 */
	public function notIn($args) {
		if (!is_array($args)) {
			throw new Exception("The 'nin' operator requires array argument");
			return false;
		}
		return $this->addFilter("\$nin", $args);
	}

	/**
	 * @param string arg
	 */
	public function beginsWith($arg) {
		return $this->addFilter("\$bw", $arg);
	}

	/**
	 * @param string arg
	 */
	public function notBeginsWith($arg) {
		return $this->addFilter("\$nbw", $arg);
	}

	/**
	 * @param mixed arg String of comma-delineated arguments or array therof
	 */
	public function beginsWithAny($args) {
		if (is_array($args)) {
			$args = implode(",", $args);
		}
		return $this->addFilter("\$bwin", $args);
	}

	/**
	 * @param mixed arg String of comma-delineated arguments or array therof
	 */
	public function notBeginsWithAny($args) {
		if (is_array($args)) {
			$args = implode(",", $args);
		}
		return $this->addFilter("\$nbwin", $args);
	}

	public function blank() {
		return $this->addFilter("\$blank", true);
	}

	public function notBlank() {
		return $this->addFilter("\$blank", false);
	}

	public function greaterThan($arg) {
		return $this->addFilter("\$gt", $arg);
	}

	public function greaterThanOrEqual($arg) {
		return $this->addFilter("\$gte", $arg);
	}

	public function lessThan($arg) {
		return $this->addFilter("\$lt", $arg);
	}

	public function lessThanOrEqual($arg) {
		return $this->addFilter("\$lte", $arg);
	}

	/**
	 * Adds filter to query object
	 * @param string op Operator name
	 * @param mixed arg Single arguement or array thereof
	 * @return obj Query object
	 */
	private function addFilter($op, $arg) {
		if (is_array($arg)) {
			foreach ($arg as $a) {
				$this->query->add(new FieldFilter($op, $this->fieldName, $a));
			}
		} else {
			$this->query->add(new FieldFilter($op, $this->fieldName, $arg));
		}

		return $this->query;
	}

}
?>
