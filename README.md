# About

This is the Factual-supported PHP driver for [Factual's public API](http://developer.factual.com).

This API supports queries to the entirety of Factual's APIs. Full documentation is available on the Factual website:

*   [Read](http://developer.factual.com/display/docs/Factual+Developer+APIs+Version+3): Search the data
*   [Schema](http://developer.factual.com/display/docs/Core+API+-+Schema): Get table metadata
*   [Crosswalk](http://developer.factual.com/display/docs/Places+API+-+Crosswalk): Get third-party IDs
*   [Resolve](http://developer.factual.com/display/docs/Places+API+-+Resolve): Enrich your data and match it against Factual's
*   [Facets](http://developer.factual.com/display/docs/Core+API+-+Facets): Group and count entities
*   [Multi](http://developer.factual.com/display/docs/Core+API+-+Multi): Make multiple queries in a single call

This driver is supported at [http://support.factual.com/](http://support.factual.com/)

# Dependencies
PHP5 is required. The php5-curl module is required. SPL is required (for autoloading).

The package includes [Google's oauth libraries](http://code.google.com/p/oauth-php/)

# Overview

## Basic Design
The driver allows you to create an authenticated handle to Factual. With a Factual handle, you can send queries and get results back. Rockin.

Queries are created using the Query class, which provides a fluent interface to constructing your queries. 

Results are returned as the JSON returned by Factual but you will likely want to employ the JSON parsing conveniences built into the driver.

## Tables
The Factual API is a generic API that sits over all tables available via the Factual v3 API. Some popular ones:

*   Table <tt>global</tt> for international places
*   Table <tt>restaurants-us</tt> for US restaurants
*   Table <tt>places</tt> for US places only

## Setup
Obtain an oauth key and secret from Factual, require the file 'Factual.php, and instantiate a <tt>factual</tt> object with the key and secret as parameters'
    
    //setup
    require_once('Factual.php');

    use Factual\Factual;

    $factual = new Factual("yourOauthKey","yourOauthSecret");


	
The driver creates an authenticated handle to Factual, and addresses class loading, on instantiation, so be sure to always instantiate a Factual object first.

All of the examples below assume this prior creation of a Factual object.
    
## Simple Query Example

    // Find 3 random records 
    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->limit(3);
    $res = $factual->fetch("places", $query);
	print_r($res->getData());
	
## Full Text Search Example

    // Find entities that match a full text search for Sushi in Santa Monica:
    use Factual\FactualQuery;

    $query = new FactualQuery;
	$query->search("Sushi Santa Monica");
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

## Geo Filters
You can query Factual for entities located within a geographic area. For example:

    // Find entities located within 5000 meters of a latitude, longitude
    use Factual\FactualQuery;

    $query = new FactualQuery;
	$query->within(new FactualCircle(34.06018, -118.41835, 5000));
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

The above example queries only our US data (our 'places' table).  Be sure to use our 'global' table when querying international or multiple countries.

    // Search for 'sushi' in the US and Canada

    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->search("Sushi");
    $query->field("country")->in("US,CA");
    $res = $factual->fetch("global", $query);
    print_r($res->getData());


## Results sorting
You can have Factual sort your query results for you, on a field by field basis. Simple example:

    // Build a Query to find 10 random entities and sort them by name, ascending:

    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->limit(10);
    $query->sortAsc("name");
    $res = $factual->fetch("places", $query);
	print_r($res->getData());  
    
You can specify more than one sort, and the results will be sorted with the first sort as primary, the second sort or secondary, and so on:

    // Build a Query to find 20 random entities, sorted ascending primarily by region, then by locality, then by name:

    use Factual\FactualQuery;

	$query = new FactualQuery;
	$query->limit(10);
	$query->sortAsc("region");
	$query->sortAsc("locality");
	$query->sortDesc("name");
	$res = $factual->fetch("places", $query);
	print_r($res->getData());

## Paging: Limit and Offset
You can use limit and offset to support basic results paging. For example:

    // Build a Query with offset of 150, limiting the page size to 10:

    use Factual\FactualQuery;

    $query = new FactualQuery;
	$query->limit(10);
	$query->offset(150);
	$res = $factual->fetch("places", $query);
	print_r($res->getData());	
	
## Field Selection
By default your queries will return all fields in the table. You can use the only modifier to specify the exact set of fields returned. For example:

    // Build a Query that only gets the name, tel, and category fields:

    use Factual\FactualQuery;

	$query = new FactualQuery;
	$query->limit(10);    
    $query->only("name,tel,category");
	$res = $factual->fetch("places", $query);
	print_r($res->getData());    

## Query Results
The drivers parse the JSON for you. On the results of factual::fetch() you can work directly with JSON, Arrays, or Objects
 
	//Get the original JSON (includes status and metadata)
	$res = $res->getJson();
	
	//Get the entities as array of arrays
	$res = $res->getData();
	
	//Get the entities as a JSON array
	$res = $res->getDataAsJSON();	
	
## Query Metadata
To help with debugging, we provide in the response object metadata about the query and the response:

	// Get URL request string
	return $res->getRequest();

	// Get the table name queried
	return $res->getTable();
	
	// Get http headers returned by Factual
	return $res->getHeaders();

	// Get http status code returned by Factual
	return $res->getCode();
	
	//get the total number of results
	//must be explicitly requested in advance of request using Query::includeRowCount()
	return $res->getRowCount();

# Read API
## Top Level Query Parameters

<table>
  <tr>
    <th>Parameter</th>
    <th>Description</th>
    <th>Example</th>
  </tr>
  <tr>
    <td>filters</td>
    <td>Restrict the data returned to conform to specific conditions.</td>
    <td>$query->field("name")->beginsWith("Starbucks")</td>
  </tr>
  <tr>
    <td>include count</td>
    <td>Include a count of the total number of rows in the dataset that conform to the request based on included filters. Requesting the row count will increase the time required to return a response. The default behavior is to NOT include a row count. When the row count is requested, the Response object will contain a valid total row count via <tt>.getTotalRowCount()</tt>.</td>
    <td><tt>$query->includeRowCount()</tt></td>
  </tr>
  <tr>
    <td>geo</td>
    <td>Restrict data to be returned to be within a geographical range based.</td>
    <td>(See the section on Geo Filters)</td>
  </tr>
  <tr>
    <td>limit</td>
    <td>Maximum number of rows to return. Default is 20. The system maximum is 50. For higher limits please contact Factual, however consider requesting a download of the data if your use case is requesting more data in a single query than is required to fulfill a single end-user's request.</td>
    <td><tt>$query->limit(10)</tt></td>
  </tr>
  <tr>
    <td>search</td>
    <td>Full text search query string.</td>
    <td>
      Find "sushi":<br><tt>$query->search("sushi")</tt><p>
      Find "sushi" or "sashimi":<br><tt>$query->search("sushi, sashimi")</tt><p>
      Find "sushi" and "santa" and "monica":<br><tt>$query->search("sushi santa monica")</tt>
    </td>
  </tr>
  <tr>
    <td>offset</td>
    <td>Number of rows to skip before returning a page of data. Maximum value is 500 minus any value provided under limit. Default is 0.</td>
    <td><tt>$query->offset(150)</tt></td>
  </tr>
  <tr>
    <td>only</td>
    <td>What fields to include in the query results.  Note that the order of fields will not necessarily be preserved in the resulting JSON response due to the nature of JSON hashes.</td>
    <td><tt>$query->only("name,tel,category")</tt> or <tt>$query->only(array("name","tel","category")</tt></td>
  </tr>
  <tr>
    <td>sort</td>
    <td>The field (or fields) to sort data on, as well as the direction of sort.  Supports $distance as a sort option if a geo-filter is specified.  Supports $relevance as a sort option if a full text search is specified either using the q parameter or using the $search operator in the filter parameter.  By default, any query with a full text search will be sorted by relevance.  Any query with a geo filter will be sorted by distance from the reference point.  If both a geo filter and full text search are present, the default will be relevance followed by distance.</td>
    <td><tt>$query->sortAsc("name")</tt></td>
  </tr>
</table>  

## Row Filters

The driver supports various row filter logic. Examples:

    // Build a query to find places whose name field starts with "Starbucks"

    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->field("name")->beginsWith("Starbucks");
    $res = $factual->fetch("places", $query);
	print_r($res->getData());  

    // Build a query to find places with a blank telephone number
    $query = new FactualQuery;
    $query->field("tel")->blank();
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

### Supported row filter logic

<table>
  <tr>
    <th>Predicate</th>
    <th>Description</th>
    <th>Example</th>
  </tr>
  <tr>
    <td>equal</td>
    <td>equal to</td>
    <td><tt>$query->field("region")->equal("CA")</tt></td>
  </tr>
  <tr>
    <td>notEqual</td>
    <td>not equal to</td>
    <td><tt>$query->field("region")->notEqual("CA")</tt></td>
  </tr>
  <tr>
    <td>search</td>
    <td>full text search</td>
    <td><tt>$query->field("name")->search("fried chicken")</tt></td>
  </tr>
  <tr>
    <td>in</td>
    <td>equals any of</td>
    <td><tt>$query->field("region")->in(array("MA", "VT", "NH", "RI", "CT"))</tt></td>
  </tr>
  <tr>
    <td>notIn</td>
    <td>does not equal any of</td>
    <td><tt>$query->field("locality")->notIn(array("Los Angeles","Philadelphia")</tt></td>
  </tr>
  <tr>
    <td>beginsWith</td>
    <td>begins with</td>
    <td><tt>$query->field("name")->beginsWith("b")</tt></td>
  </tr>
  <tr>
    <td>notBeginsWith</td>
    <td>does not begin with</td>
    <td><tt>$query->field("name")->notBeginsWith("star")</tt></td>
  </tr>
  <tr>
    <td>beginsWithAny</td>
    <td>begins with any of</td>
    <td><tt>$query->field("name")->beginsWithAny(array("star", "coffee", "tull"))</tt> </td>
  </tr>
  <tr>
    <td>notBeginsWithAny</td>
    <td>does not begin with any of</td>
    <td><tt>$query->field("name")->notBeginsWithAny(array("star", "coffee", "tull"))</tt></td>
  </tr>
  <tr>
    <td>blank</td>
    <td>is blank or null</td>
    <td><tt>$query->field("tel")->blank()</tt></td>
  </tr>
  <tr>
    <td>notBlank</td>
    <td>is not blank or null</td>
    <td><tt>$query->field("tel")->notBlank()</tt></td>
  </tr>
  <tr>
    <td>greaterThan</td>
    <td>greater than</td>
    <td><tt>$query->field("rating")->greaterThan(7.5)</tt></td>
  </tr>
  <tr>
    <td>greaterThanOrEqual</td>
    <td>greater than or equal to</td>
    <td><tt>$query->field("rating")->greaterThanOrEqual(7.5)</tt></td>
  </tr>
  <tr>
    <td>lessThan</td>
    <td>less than</td>
    <td><tt>$query->field("rating")->lessThan(7.5)</tt></td>
  </tr>
  <tr>
    <td>lessThanOrEqual</td>
    <td>less than or equal to</td>
    <td><tt>$query->field("rating")->lessThanOrEqual(7.5)</tt></td>
  </tr>
</table>

### AND
Queries support logical AND'ing your row filters. For example:

    // Build a query to find entities where the name begins with "Coffee" AND the telephone is blank:

    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->_and(
    	array(
       		$query->criteria("name")->beginsWith("Coffee"),
  	   		$query->criteria("tel")->blank()
  	   	)
	);
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
    
Note that all row filters set at the top level of the Query are implicitly AND'ed together, so you could also do this:
	
    //Combined query alternative syntax

    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->field("name")->beginsWith("Coffee");
    $query->field("tel")->blank();
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

### OR
Queries support logical OR'ing your row filters. For example:

    // Build a query to find entities where the name begins with "Coffee" OR the telephone is blank:

    use Factual\FactualQuery;

    $query = new FactualQuery;
    $query->_or(array(
       	$query->criteria("name")->beginsWith("Coffee"),
  	   	$query->criteria("tel")->blank()
  	   )
	);	
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
	
### Combined ANDs and ORs
You can nest AND and OR logic to whatever level of complexity you need. For example:

    // Build a query to find entities where:
    // (name begins with "Starbucks") OR (name begins with "Coffee")
    // OR
    // (name full text search matches on "tea" AND tel is not blank)

    use Factual\FactualQuery;

    $query = new FactualQuery;    
    $query->_or(array(
        $query->_or(array(
            $query->field("name")->beginsWith("Starbucks"),
            $query->field("name")->beginsWith("Coffee")
            )
        ),
        $query->_and(array(
            $query->field("name")->search("tea"),
            $query->field("tel")->notBlank()
        	)
        )
      )
    );
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
	
# Crosswalk
The driver fully support Factual's Crosswalk feature, which lets you "crosswalk" the web and relate entities between Factual's data and that of other web authorities.  See the [API documentation](http://developer.factual.com/display/docs/Places+API+-+Crosswalk) for details.

(See [the Crosswalk Blog](http://blog.factual.com/crosswalk-api) for context.)

## Simple Crosswalk Example

    // Get all Crosswalk data for a specific Places entity, using its Factual ID:

    use Factual\CrosswalkQuery;

	$query = new CrosswalkQuery();
	$query->factualId("97598010-433f-4946-8fd5-4a6dd1639d77");	 
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
	
## Crosswalk Filter Parameters

<table>
  <tr>
    <th>Filter</th>
    <th>Description</th>
    <th>Example</th>
  </tr>
  <tr>
    <td>factualId</td>
    <td>A Factual ID for an entity in the Factual places database</td>
    <td><tt>$query->factualId("97598010-433f-4946-8fd5-4a6dd1639d77")</tt></td>
  </tr>
  <tr>
    <td>limit</td>
    <td>A Factual ID for an entity in the Factual places database</td>
    <td><tt>$query->limit(100)</tt></td>
  </tr>
  <tr>
    <td>namespace</td>
    <td>The namespace to search for a third party ID within. See the [list of currently supported third-party crosswalked services](http://developer.factual.com/display/docs/Places+API+-+Supported+Crosswalk+Services).</td>
    <td><tt>$query->namespace("foursquare")</tt></td>
  </tr>
  <tr>
    <td>namespaceId</td>
    <td>The id used by a third party to identify a place.</td>
    <td><tt>$query->namespaceId("443338")</tt></td>
  </tr>
  <tr>
    <td>only</td>
    <td>A Factual ID for an entity in the Factual places database</td>
    <td><tt>$query->only("foursquare", "yelp")</tt></td>
  </tr>
</table>

NOTE: although these parameters are individually optional, at least one of the following parameter combinations is required:

* factualId
* namespace and namespaceId

## More Crosswalk Examples

    // Get Loopt's Crosswalk data for a specific Places entity, using its Factual ID as input:

    use Factual\FactualQuery;

	$query = new CrosswalkQuery();
	$query->factualId("97598010-433f-4946-8fd5-4a6dd1639d77");
	$query->only("loopt");
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
	        
    // Get all Crosswalk data for a specific Places entity using its Foursquare ID as input:
	$query = new CrosswalkQuery();
	$query->_namespace("foursquare");
	$query->namespaceId("4ae4df6df964a520019f21e3");	
	$res = $factual->fetch("places", $query);
	print_r($res->getData());	
          
# Resolve
The driver fully support Factual's Resolve feature, which lets you start with incomplete data you may have for an entity, and get potential entity matches back from Factual.

Each result record will include a confidence score (<tt>"similarity"</tt>), and a flag indicating whether Factual decided the entity is the correct resolved match with a high degree of accuracy (<tt>"resolved"</tt>).

For any Resolve query, there will be 0 or 1 entities returned with <tt>"resolved"=true</tt>. If there was a full match, it is guaranteed to be the first record in the JSON response.  See the [API documentation](http://developer.factual.com/display/docs/Places+API+-+Resolve) for details.

(See [the Resolve Blog](http://blog.factual.com/factual-resolve) for more background.)

## Simple Resolve Examples
Use the common query structure to add known attributes to the query:

    // Get all entities that are possibly a match

    use Factual\ResolveQuery;

	$query = new ResolveQuery();
	$query->add("name", "Buena Vista Cigar Club");
	$query->add("latitude", 34.06);
	$query->add("longitude", -118.40);
	$res = $factual->fetch("places", $query);	
      
And then use methods on the result object to determine resolution:

    //Did the entity resolve? (returns bool)
    $isResolved = $res->isResolved();
    
    //If so, get it:
    $resolved = $res->getResolved();
    
Alternatively use the shortcut to return the resolved entity OR null if no resolution:
	
	//Resolve and return
	$tableName = "places";
	$vars = array(
		"name"=>"Buena Vista Cigar Club",
		"latitude"=>34.06,
		"longitude"=>-118.40
	);
	$res = $factual->resolve($tableName,$vars);
	print_r($res);	
      
#Schema
The schema endpoint returns table metadata:
  
	$res = $factual->schema("places");
	print_r($res->getColumnSchemas());

# Facets
The driver supports Factual's Facets feature, which returns summary row counts grouped by facets of data (think of this as a combined <tt>count()</tt> and <tt>GROUP BY</tt> function in SQL).  Use Facets to break down the results of your query by count of results. For example, you may want to query all businesses within 500m of a location, group those businesses by category, and get a count of each.   See the [API documentation](http://developer.factual.com/display/docs/Core+API+-+Facets) for details.

## Facets Example

	//Finds the top twenty-five countries containing places with the string 'Starbucks'


    use Factual\FacetQuery;

	$query = new FacetQuery("country"); //name the field to facet on in constructor
	$query->search("starbucks"); //search on 'Starbucks'
	$query->limit(15); //show no more than 15 results
	$query->minCountPerFacet(10); //only show countries with more than 10 results
	$res = $factual->fetch("global", $query); //perform the query
	print_r($res->getData()); //dump results out as an array
	
The response looks like:

	Array
	(
	    [country] => Array
		(
		    [us] => 11019
		    [ca] => 902
		    [gb] => 434
		    [cn] => 194
		    [de] => 174
		    [tw] => 121
		    [ph] => 78
		    [au] => 69
		    [tr] => 68
		    [id] => 55
		    [fr] => 47
		    [sg] => 41
		    [mx] => 33
		    [ch] => 31
		    [hk] => 27
		)
	)



You cannot facet on all fields, only those configured by Factual.  Use the <tt>schema</tt> call to determine the fields for which you can return facets; if the faceted attribute of the schema is <tt>true</tt>, you can facet. 

## Top Level Facets Parameters

<table>
  <tr>
    <th>Parameter</th>
    <th>Description</th>
    <th>Example</th>
  </tr>
  <tr>
    <td>select</td>
    <td>Array of comma-delimited string of field names on which facets should be generated, included as the constructor parameter to the FacetQuery.  The response will not necessarily be ordered identically to this list, nor will it reflect any nested relationships between fields.</td>
    <td><tt>$query = new FacetQuery("region,locality");</tt></td>
  </tr>
  <tr>
    <td>min_count</td>
    <td>Include only facets that have this minimum count. Must be zero or greater. The default is 1. </td>
    <td><tt>$query->minCountPerFacet(2)</tt></td>
  </tr>
  <tr>
    <td>limit</td>
    <td>The maximum number of unique facet values that can be returned for a single field. Range is 1-250. The default is 20.</td>
    <td><tt>$query->limit(10)</tt></td>
  </tr>
</table>  

You can also employ the filters, include count, geo and search parameters like any other Read query.

#Multi Queries

The driver fully supports Factual's Multi feature, which enables your making up to three queries on a single http request.  See the [API documentation for details](http://developer.factual.com/display/docs/Core+API+-+Multi).


## Simple Multi Example
Create your query objects as usual, and add them to the query queue using <tt>multiQueue()</tt>:

	//create first query and add to queue


    use Factual\FactualQuery;

	$query1 = new FactualQuery;
	$query1->limit(3);
	$query1->only("factual_id,name");
	$factual->multiQueue("global", $query1, "global-places");

	//create second query and add to queue
	$query2 = new FactualQuery;
	$query2->limit(3);
	$query2->only("factual_id,name");
	$factual->multiQueue("world-geographies", $query2, "world-geos");


Note that <tt>multiQueue()</tt> parameters are just like those of the <tt>fetch()</tt> method but include a required third parameter: an arbitrary string that you use to identify the results from each query. 

Use <tt>multiFetch()</tt> to send your request:

	//make multi request
	$res = $factual->multiFetch();

You can iterate through the response to obtain each response object:

	//iterate through response objects
	foreach ($res as $queryResponse){
	    print_r($queryResponse->getData());
	} 

or use the <tt>getData()</tt> method as a shortcut to see the results of your multiple requests in a single array:

	//dump results as an array
	print_r($res->getData()); 

The results of the above shortcut look like:

	Array
	(
	    [global-places] => Array
		(
		    [0] => Array
		        (
		            [factual_id] => 698ac9a7-4eb1-4bfa-9d0f-b2518a1e59f8
		            [name] => Solar Devices Inc.
		        )
		    [1] => Array
		        (
		            [factual_id] => 69fa9b61-ca66-48fe-bb10-8414a6f5c951
		            [name] => Guanajibo Carburator
		        )
		    [2] => Array
		        (
		            [factual_id] => 6a778edf-0d07-4de2-8201-32c9f1412a52
		            [name] => Puerto Rico Sentry Dog
		        )
		)
	    [world-geos] => Array
		(
		    [0] => Array
		        (
		            [factual_id] => 1185addc-8f76-11e1-848f-cfd5bf3ef515
		            [name] => Harachandapur
		        )
		    [1] => Array
		        (
		            [factual_id] => 1185a972-8f76-11e1-848f-cfd5bf3ef515
		            [name] => Uttar Jadupur
		        )
		    [2] => Array
		        (
		            [factual_id] => 1179d2fa-8f76-11e1-848f-cfd5bf3ef515
		            [name] => Bawagempol
		        )
		)
	)

#Help, Debugging & Testing

##Exception Handling
If Factual's API indicates an error, a <tt>FactualApiException</tt> unchecked Exception will be thrown. It contains every possible details about the request you sent and the error that Factual returned.  Example:

	PHP Fatal error:  Uncaught exception 'FactualApiException' with message 
	'Requested resource could not be found. Details:

	Array
	(
	    [code] => 404
	    [version] => 3
	    [status] => error
	    [error_type] => Auth
	    [message] => Requested resource could not be found.
	    [headers] => Array
		(
		    [access-control-allow-origin] => *
		    [content-type] => application/json; charset=utf-8
		    [date] => Thu, 31 May 2012 23:50:16 GMT
		    [server] => nginx/1.0.15
		    [content-length] => 101
		    [connection] => keep-alive
		)
	    [method] => GET
	    [query] => http://api.v3.factual.com/t/bglobal
	)

	use FactualApiException::debug() to obtain this information programatically. 

Here is an example of catching a <tt>FactualApiException</tt> and inspecting it:

    try{
    	$query->field("badFieldName")->notIn("Los Angeles"); //this line borks 
    	$res = $factual->fetch("places", $query);
    } catch (FactualApiException $e) {
      	print_r($e->debug());
    }

## Where to Get Help
If you think you've identified a *specific bug* in this driver, please file an issue in this github repo. Provide as much information as you can, including:

  * All of the debug info output by the exception (above)
  * What you did to surface the bug -- specific code with values over variables helps greatly here
  * What you expected to happen & what actually happened
  * Detailed stack trace and/or line numbers

If you have a question or are having any other kind of issue, such as unexpected data or strange behaviour from Factual's API (or you're just not sure WTF is going on), please hit us up on [GetSatisfaction](http://support.factual.com/factual), again -- be sure to include the above information.

## Testing
Add your secret and key to <tt>test.php</tt> and run on the command line: 'php test.php' ('php -f test.php' on Windows).  This checks your PHP install environment and performs a number of ad-hoc unit tests.

## Raw Requests
The driver mainly offers convenence: it signs requests, builds conformant queries, and structures responses. You may however wish to pass a 'raw' query to our API for degugging, testing, or implementing API features not yet codified in the driver:

	$query = "http://api.v3.factual.com/t/global";
	print_r($factual->rawGet($query));	
	exit;

The <tt>rawGet()</tt> method only signs and submits the request; remember to escape your JSON. Responses are raw JSON.

# Geocoding
Factual does not provide a geocoding service, but we've integrated a third-party Web Service that can easily be swapped out.

These methods are experimental and unsupported, but (we hope) helpful:

	//geocode (convert an address to longitude and latitude)
	$res = $factual->geocode("425 Sherman Ave, Palo Alto, CA, USA");
	print_r($res);

	//reverse geocode  (convert a longitude and latitude to an address)
	$lon = -122.143895;
	$lat = 37.425674;
	$res = $factual->reverseGeocode($lon,$lat);
	print_r($res);	

# Notes and Miscellany

##Autoloading

The PHP <tt>__autoload()</tt> method is deprecated; this library uses <tt>spl_autoload_register()</tt>.
