# About

This is the Factual-supported PHP driver for [Factual's public API](http://developer.factual.com).

This API supports queries to Factual's Read, Schema, Crosswalk, and Resolve APIs. Full documentation is available on the Factual website:
* [Read](http://developer.factual.com/display/docs/Factual+Developer+APIs+Version+3): Search the data
* [Schema](http://developer.factual.com/display/docs/Core+API+-+Schema): Get table metadata
* [Crosswalk](http://developer.factual.com/display/docs/Places+API+-+Crosswalk): Get third-party IDs
* [Resolve](http://developer.factual.com/display/docs/Places+API+-+Resolve): Enrich your data and match it against Factual's

This driver is supported via the [Factual Deverloper Group](https://groups.google.com/group/factual_developers)

# Dependencies
PHP5 is required. The php5-curl module is required.

The package includes [Google's oauth libraries](http://code.google.com/p/oauth-php/)

# Overview

## Basic Design

The driver allows you to create an authenticated handle to Factual. With a Factual handle, you can send queries and get results back. Rockin.

Queries are created using the Query class, which provides a fluent interface to constructing your queries. 

Results are returned as the JSON returned by Factual but you will likely want to employ the JSON parsing conveniences built into the driver.

## Setup

1. Add your Factual key and secret to config.ini
1. Require the file 'Factual.php'
    
    //setup
    require_once('Factual.php');
	$factual = new Factual;
	
The driver creates an authenticated handle to Factual, and addresses class loading, on instantiation, so be sure to always instantiate a Factual object first.
    
## Simple Query Example

    // Print 3 random records 
    $factual = new Factual;
    $query = new Query;
    $query->limit(3);
    $res = $factual->fetch("places", $query);
	print_r($res->getData());
	
## Full Text Search Example

    // Print entities that match a full text search for Sushi in Santa Monica:
    $factual = new Factual;
    $query = new Query;
	$query->search("Sushi Santa Monica");
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

## Geo Filters

You can query Factual for entities located within a geographic area. For example:

    // Build a Query that finds entities located within 5000 meters of a latitude, longitude
    $factual = new Factual;
    $query = new Query;
	$query->within(new Circle(34.06018, -118.41835, 5000));
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

## Results sorting

You can have Factual sort your query results for you, on a field by field basis. Simple example:

    // Build a Query to find 10 random entities and sort them by name, ascending:
    $factual = new Factual;
    $query = new Query;
    $query->limit(10);
    $query->sortAsc("name");
    $res = $factual->fetch("places", $query);
	print_r($res->getData());  
    
You can specify more than one sort, and the results will be sorted with the first sort as primary, the second sort or secondary, and so on:

    // Build a Query to find 20 random entities, sorted ascending primarily by region, then by locality, then by name:
    $factual = new Factual;
	$query = new Query;
	$query->limit(10);
	$query->sortAsc("region");
	$query->sortAsc("locality");
	$query->sortDesc("name");
	$res = $factual->fetch("places", $query);
	print_r($res->getData());

## Limit and Offset

You can use limit and offset to support basic results paging. For example:

    // Build a Query with offset of 150, limiting the page size to 10:
    $factual = new Factual;
    $query = new Query;
	$query->limit(10);
	$query->offset(150);
	$res = $factual->fetch("places", $query);
	print_r($res->getData());	
	
## Field Selection

By default your queries will return all fields in the table. You can use the only modifier to specify the exact set of fields returned. For example:

    // Build a Query that only gets the name, tel, and category fields:
    $factual = new Factual;
	$query = new Query;
	$query->limit(10);    
    $query->only("name,tel,category");
	$res = $factual->fetch("places", $query);
	print_r($res->getData());    

## Query Results
The drivers consume the JSON for you. On the results of factual::fetch() you can work directly with JSON, Arrays, or Objects
 
	//Get the original JSON
	$res = $res->getJson();
	
	//Get the entities as array of arrays
	$res = $res->getData();
	
	//Get the entities as array of objects
	$res = $res->getObjects();	

# Read API
## All Top Level Query Parameters

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
    $factual = new Factual;
    $query = new Query;
    $query->field("name")->beginsWith("Starbucks");
    $res = $factual->fetch("places", $query);
	print_r($res->getData());  

    // Build a query to find places with a blank telephone number
    $factual = new Factual;
    $query = new Query;
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
    <td><tt>$query->field("region")->in("MA,VT,NH,RI,CT")</tt> or <tt>$query->field("region")->in(array("MA", "VT", "NH", "RI", "CT"))</tt></td>
  </tr>
  <tr>
    <td>notIn</td>
    <td>does not equal any of</td>
    <td><tt>$query->field("locality")->notIn("Los Angeles,Philadelphia")</tt> or <tt>$query->field("locality")->notIn(array("Los Angeles","Philadelphia")</tt></td>
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
    <td><tt>$query->field("name")->beginsWithAny("star,coffee,tull")</tt> or <tt>$query->field("name")->beginsWithAny(array("star", "coffee", "tull"))</tt> </td>
  </tr>
  <tr>
    <td>notBeginsWithAny</td>
    <td>does not begin with any of</td>
    <td><tt>$query->field("name")->notBeginsWithAny("star,coffee,tull")</tt> or $query->field("name")->notBeginsWithAny(array("star", "coffee", "tull"))</td>
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
    $factual = new Factual;
    $query = new Query;
    $query->_and(
    	array(
       		$query->criteria("name")->beginsWith("Coffee"),
  	   		$query->criteria("tel")->blank()
  	   	)
	);
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
    
Note that all row filters set at the top level of the Query are implicitly AND'ed together, so you could also do this:
	$factual = new Factual;
    $query = new Query;
    $query->field("name")->beginsWith("Coffee");
    $query->field("tel")->blank();
    $res = $factual->fetch("places", $query);
	print_r($res->getData());

### OR

Queries support logical OR'ing your row filters. For example:

    // Build a query to find entities where the name begins with "Coffee" OR the telephone is blank:
    $factual = new Factual;
    $query = new Query;
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
    $factual = new Factual;
    $query = new Query;    
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

The driver fully support Factual's Crosswalk feature, which lets you "crosswalk" the web and relate entities between Factual's data and that of other web authorities.

(See [the Crosswalk Blog](http://blog.factual.com/crosswalk-api) for more background.)

## Simple Crosswalk Example

    // Get all Crosswalk data for a specific Places entity, using its Factual ID:
    $factual = new Factual;
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
    <td>The namespace to search for a third party ID within. A list of <b>currently supported</b> services is <a href="http://developer.factual.com/display/docs/Places+API+-+Supported+Crosswalk+Services">here</a>.</td>
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
	$factual = new Factual;
	$query = new CrosswalkQuery();
	$query->factualId("97598010-433f-4946-8fd5-4a6dd1639d77");
	$query->only("loopt");
	$res = $factual->fetch("places", $query);
	print_r($res->getData());
	        
    // Get all Crosswalk data for a specific Places entity using its Foursquare ID as input:
	$factual = new Factual;
	$query = new CrosswalkQuery();
	$query->_namespace("foursquare");
	$query->namespaceId("4ae4df6df964a520019f21e3");	
	$res = $factual->fetch("places", $query);
	print_r($res->getData());	
          
# Resolve

The driver fully support Factual's Resolve feature, which lets you start with incomplete data you may have for an entity, and get potential entity matches back from Factual.

Each result record will include a confidence score (<tt>"similarity"</tt>), and a flag indicating whether Factual decided the entity is the correct resolved match with a high degree of accuracy (<tt>"resolved"</tt>).

For any Resolve query, there will be 0 or 1 entities returned with <tt>"resolved"=true</tt>. If there was a full match, it is guaranteed to be the first record in the JSON response.

(See [the Resolve Blog](http://blog.factual.com/factual-resolve) for more background.)

## Simple Resolve Examples

Use a very similar query structure to add known attributes to the query:

    // Get all entities that are possibly a match
	$factual = new Factual;
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
    
Alternatively use the short cut (experimental) to return the resolved entity OR null if no resolution:
	
	//Resolve and return
	$factual = new Factual;
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
  
	$factual = new Factual;
	$tableName = "places";
	$res = $factual->schema($tableName);
	print_r($res->getColumnSchemas());

# Exception Handling

If Factual's API indicates an error, a <tt>FactualApiException</tt> unchecked Exception will be thrown. It will contain details about the request you sent and the error that Factual returned.

Here is an example of catching a <tt>FactualApiException</tt> and inspecting it:

	try{
		$factual = new Factual;
    	$query->field("badFieldName")->notIn("Los Angeles");
    	$res = $factual->fetch("places", $query);
    } catch (FactualApiException $e) {
      	echo "URL:\t" . $e->getRequestUrl()."\n\n";
      	echo "Method:\t" . $e->getRequestMethod()."\n\n";
      	echo "Status:\t" . $e->getStatus()."\n\n";
      	echo "Msg:\t " . $e->getMessage()."\n\n";
      	echo "Code:\t " . $e->getCode()."\n\n";
      	echo "Type:\t" . $e->getErrorType()."\n\n";
      	echo "Ver:\t " . $e->getVersion()."\n\n";
    }

# Geocoding
Factual does not provide a geocoding service, but we've integrated a third-party Web Service that can easily be swapped out.  

These methods are experimental and unsupported:

	//geocode
	$factual = new Factual;
	$res = $factual->geocode("425 Sherman Ave, Palo Alto, CA, USA");
	print_r($res);

	//reverse geocode
	$factual = new Factual;
	$lon = -122.143895;
	$lat = 37.425674;
	$res = $factual->reverseGeocode($lon,$lat);
	print_r($res);	

# More Examples

For more code examples:

* See the standalone demos in <tt>test/factual/demo</tt>
* See the integration tests in <tt>test/factual/FactualTest.java</tt>
