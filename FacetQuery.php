<?php

/**
 * Represents a top level Factual facet query. Knows how to represent the facet
 * query as URL encoded key value pairs, ready for the query string in a GET
 * request. (See {@link #toUrlQuery()})
 * 
 * @author tyler
 */
class FacetQuery extends FactualQuery {
	const RESPONSETYPE = "FacetResponse";
	

  /**
   * Constructor.
   * @param string fields fields for which facets will be generated
   */
  public function __construct($fields) {
    return $this->only($fields);
  }

  /**
   * For each facet value count, the minimum number of results it must have in order to be 
   * returned in the response. Must be zero or greater. The default is 1.
   * @param int count Min count for each facet
   * @return obj this FacetQuery
   */
  public function minCountPerFacet($count) {
    $this->addParam("min_count",$count);
    return $this;
  }

}
?>
