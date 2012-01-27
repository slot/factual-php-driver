<?php
error_reporting(E_ERROR);
require_once ('Factual.php');

/**
 * Test methods for Factual API. Not for production use.
 * @author Tyler
 * @package Factual
 * @license Apache 2.0
 */
class FactualTest {

	private $factual;
	private $writeToFile = null;
	private $testTables = array('global'=>"global",'resolve'=>"places",'crosswalk'=>"places",'schema'=>"places");
	private $classes = array (
		"FactualCircle",
		"FactualColumnSchema",
		"Crosswalk",
		"CrosswalkQuery",
		"FactualApiException",
		"FieldFilter",
		"FactualFilter",
		"FilterGroup",
		"GeocoderWrapper",
		"FactualPlace",
		"FactualQuery",
		"QueryBuilder",
		"ReadResponse",
		"ResolveQuery",
		"ResolveResponse",
		"FactualResponse",
		"SchemaResponse"
	);
	private $countries = array (
		//'AF' => 'Afghanistan',
		//'AL' => 'Albania',
		//'DZ' => 'Algeria',
		//'AS' => 'American Samoa',
		//'AD' => 'Andorra',
		//'AO' => 'Angola',
		//'AI' => 'Anguilla',
		//'AQ' => 'Antarctica',
		//'AG' => 'Antigua And Barbuda',
		'AR' => 'Argentina',
		//'AM' => 'Armenia',
		//'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		//'AZ' => 'Azerbaijan',
		//'BS' => 'Bahamas',
		//'BH' => 'Bahrain',
		//'BD' => 'Bangladesh',
		//'BB' => 'Barbados',
		//'BY' => 'Belarus',
		'BE' => 'Belgium',
		//'BZ' => 'Belize',
		//'BJ' => 'Benin',
		//'BM' => 'Bermuda',
		//'BT' => 'Bhutan',
		//'BO' => 'Bolivia',
		//'BA' => 'Bosnia And Herzegovina',
		//'BW' => 'Botswana',
		//'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		//'IO' => 'British Indian Ocean Territory',
		//'BN' => 'Brunei',
		//'BG' => 'Bulgaria',
		//'BF' => 'Burkina Faso',
		//'BI' => 'Burundi',
		//'KH' => 'Cambodia',
		//'CM' => 'Cameroon',
		'CA' => 'Canada',
		//'CV' => 'Cape Verde',
		//'KY' => 'Cayman Islands',
		//'CF' => 'Central African Republic',
		//'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		//'CX' => 'Christmas Island',
		//'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Columbia',
		//'KM' => 'Comoros',
		//'CG' => 'Congo',
		//'CK' => 'Cook Islands',
		//'CR' => 'Costa Rica',
		//'CI' => 'Cote D\'Ivorie (Ivory Coast)',
		'HR' => 'Croatia (Hrvatska)',
		//'CU' => 'Cuba',
		//'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		//'CD' => 'Democratic Republic Of Congo (Zaire)',
		'DK' => 'Denmark',
		//'DJ' => 'Djibouti',
		//'DM' => 'Dominica',
		//'DO' => 'Dominican Republic',
		//'TP' => 'East Timor',
		//'EC' => 'Ecuador',
		'EG' => 'Egypt',
		//'SV' => 'El Salvador',
		//'GQ' => 'Equatorial Guinea',
		//'ER' => 'Eritrea',
		//'EE' => 'Estonia',
		//'ET' => 'Ethiopia',
		//'FK' => 'Falkland Islands (Malvinas)',
		//'FO' => 'Faroe Islands',
		//'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		//'FX' => 'France, Metropolitan',
		//'GF' => 'French Guinea',
		//'PF' => 'French Polynesia',
		//'TF' => 'French Southern Territories',
		//'GA' => 'Gabon',
		//'GM' => 'Gambia',
		//'GE' => 'Georgia',
		'DE' => 'Germany',
		//'GH' => 'Ghana',
		//'GI' => 'Gibraltar',
		'GR' => 'Greece',
		//'GL' => 'Greenland',
		//'GD' => 'Grenada',
		//'GP' => 'Guadeloupe',
		//'GU' => 'Guam',
		//'GT' => 'Guatemala',
		//'GN' => 'Guinea',
		//'GW' => 'Guinea-Bissau',
		//'GY' => 'Guyana',
		//'HT' => 'Haiti',
		//'HM' => 'Heard And McDonald Islands',
		//'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		//'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		//'IR' => 'Iran',
		//'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IT' => 'Italy',
		//'JM' => 'Jamaica',
		'JP' => 'Japan',
		//'JO' => 'Jordan',
		//'KZ' => 'Kazakhstan',
		//'KE' => 'Kenya',
		//'KI' => 'Kiribati',
		//'KW' => 'Kuwait',
		//'KG' => 'Kyrgyzstan',
		//'LA' => 'Laos',
		//'LV' => 'Latvia',
		//'LB' => 'Lebanon',
		//'LS' => 'Lesotho',
		//'LR' => 'Liberia',
		//'LY' => 'Libya',
		//'LI' => 'Liechtenstein',
		//'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		//'MO' => 'Macau',
		//'MK' => 'Macedonia',
		//'MG' => 'Madagascar',
		//'MW' => 'Malawi',
		'MY' => 'Malaysia',
		//'MV' => 'Maldives',
		//'ML' => 'Mali',
		//'MT' => 'Malta',
		//'MH' => 'Marshall Islands',
		//'MQ' => 'Martinique',
		//'MR' => 'Mauritania',
		//'MU' => 'Mauritius',
		//'YT' => 'Mayotte',
		'MX' => 'Mexico',
		//'FM' => 'Micronesia',
		//'MD' => 'Moldova',
		//'MC' => 'Monaco',
		//'MN' => 'Mongolia',
		//'MS' => 'Montserrat',
		//'MA' => 'Morocco',
		//'MZ' => 'Mozambique',
		//'MM' => 'Myanmar (Burma)',
		//'NA' => 'Namibia',
		//'NR' => 'Nauru',
		//'NP' => 'Nepal',
		'NL' => 'Netherlands',
		//'AN' => 'Netherlands Antilles',
		//'NC' => 'New Caledonia',
		//'NZ' => 'New Zealand',
		//'NI' => 'Nicaragua',
		//'NE' => 'Niger',
		//'NG' => 'Nigeria',
		//'NU' => 'Niue',
		//'NF' => 'Norfolk Island',
		//'KP' => 'North Korea',
		//'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		//'OM' => 'Oman',
		//'PK' => 'Pakistan',
		//'PW' => 'Palau',
		//'PA' => 'Panama',
		//'PG' => 'Papua New Guinea',
		//'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		//'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		//'QA' => 'Qatar',
		//'RE' => 'Reunion',
		//'RO' => 'Romania',
		'RU' => 'Russia',
		//'RW' => 'Rwanda',
		//'SH' => 'Saint Helena',
		//'KN' => 'Saint Kitts And Nevis',
		//'LC' => 'Saint Lucia',
		//'PM' => 'Saint Pierre And Miquelon',
		//'VC' => 'Saint Vincent And The Grenadines',
		//'SM' => 'San Marino',
		//'ST' => 'Sao Tome And Principe',
		//'SA' => 'Saudi Arabia',
		//'SN' => 'Senegal',
		//'SC' => 'Seychelles',
		//'SL' => 'Sierra Leone',
		//'SG' => 'Singapore',
		//'SK' => 'Slovak Republic',
		//'SI' => 'Slovenia',
		//'SB' => 'Solomon Islands',
		//'SO' => 'Somalia',
		'SG' => 'Singapore',
		'ZA' => 'South Africa',
		//'GS' => 'South Georgia And South Sandwich Islands',
		'KR' => 'South Korea',
		'ES' => 'Spain',
		//'LK' => 'Sri Lanka',
		//'SD' => 'Sudan',
		//'SR' => 'Suriname',
		//'SJ' => 'Svalbard And Jan Mayen',
		//'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		//'SY' => 'Syria',
		'TW' => 'Taiwan',
		//'TJ' => 'Tajikistan',
		//'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		//'TG' => 'Togo',
		//'TK' => 'Tokelau',
		//'TO' => 'Tonga',
		//'TT' => 'Trinidad And Tobago',
		//'TN' => 'Tunisia',
		'TR' => 'Turkey',
		//'TM' => 'Turkmenistan',
		//'TC' => 'Turks And Caicos Islands',
		//'TV' => 'Tuvalu',
		//'UG' => 'Uganda',
		//'UA' => 'Ukraine',
		//'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		//'UM' => 'United States Minor Outlying Islands',
		//'UY' => 'Uruguay',
		//'UZ' => 'Uzbekistan',
		//'VU' => 'Vanuatu',
		//'VA' => 'Vatican City (Holy See)',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		//'VG' => 'Virgin Islands (British)',
		//'VI' => 'Virgin Islands (US)',
		//'WF' => 'Wallis And Futuna Islands',
		//'EH' => 'Western Sahara',
		//'WS' => 'Western Samoa',
		//'YE' => 'Yemen',
		//'YU' => 'Yugoslavia',
		//'ZM' => 'Zambia',
		//'ZW' => 'Zimbabwe'
	);

	/**
	 * Primary test function. 
	 */
	public function test() {

		if (!$this->writeToFile) {
			echo "\n\nTesting Factual\n";
			echo "========================\n";
		} else {
			if ($this->writeToFile) {
				//remove extant log file
				@unlink($this->writeToFile);
			}
		}
		$this->testVersion();
		$this->classConflicts();
		$this->testExt();
		$this->testConnect();
		$this->testQueryFilterLimitSort();
		$this->testMultiFilter();
		$this->testResolve();
		$this->testCrosswalk();
		$this->testSchema();
		$this->testCountries();
		
		if (!$this->writeToFile) {
			echo "========================\n";
		}
	}

	/**
	 * Set file to log report to. Echoes to screen by default
	 * @return void
	 */
	public function setLogFile($fileName = null) {
		$this->writeToFile = $fileName;
	}


	private function testCountries(){
		$requestSample = 3;
		foreach ($this->countries as $key => $value){
			$query = new FactualQuery();
			$query->field("country")->equal($key);
			$query->limit($requestSample);
			$res = $this->factual->fetch($this->testTables['global'], $query);
			if ($res->size() !== $requestSample){
				$this->msg("Checking ".$value, false);
			} else {
				$this->msg("Checking ".$value, true);
			}
		}
	}
	
	private function testMultiFilter(){
		$query = new FactualQuery;	
	 $query->_and(array(
       	$query->field("name")->equal("Starbucks"),
  	   $query->field("region")->equal("CA"),
  	   $query->field("country")->equal("US")
  	   )
	);
	$query->limit(1);
	$res = $this->factual->fetch($this->testTables['global'], $query);
	$record = $res->getData();
	$record = $record[0];
	if ($record['name'] == "Starbucks" && $record['region'] == "CA" && $record['country'] == "US") {
			$this->msg("Multi Filter", true);
		} else {
			$this->msg("Multi Filter", false);
		}
	}

	private function testSchema() {
		$res = $this->factual->schema($this->testTables['schema']);
		if ($res->getStatus() == "ok") {
			$this->msg("Schema Endpoint", true);
		} else {
			$this->msg("Schema Endpoint", false);
		}
	}

	private function testCrosswalk() {
		$query = $this->getQueryObject();
		$query = new CrosswalkQuery();
		$query->_namespace("foursquare");
		$query->namespaceId("4ae4df6df964a520019f21e3");
		$res = $this->factual->fetch($this->testTables['crosswalk'], $query);
		if ($res->getStatus() == "ok") {
			$this->msg("Crosswalk Endpoint", true);
		} else {
			$this->msg("Crosswalk Endpoint", false);
		}
	}

	private function testResolve() {
		$query = $this->getQueryObject();
		$query = new ResolveQuery();
		$query->add("name", "Buena Vista Cigar Club");
		$query->add("latitude", 34.06);
		$query->add("longitude", -118.40);
		$res = $this->factual->fetch($this->testTables['resolve'], $query);
		if ($res->getStatus() == "ok") {
			$this->msg("Resolve Endpoint", true);
		} else {
			$this->msg("Resolve Endpoint", false);
		}
	}

	private function testQueryFilterLimitSort() {
		$limit = 10;
		$query = new FactualQuery;
		$query->limit($limit);
		$query->sortAsc("name");
		$query->field("region")->equal("CA");
		$res = $this->factual->fetch($this->testTables['global'], $query);
		if ($res->getStatus() == "ok" && $res->getIncludedRowCount() == $limit) {
			$this->msg("Limit/Filter/Sort", true);
		} else {
			$this->msg("Limit/Filter/Sort", false);
		}
	}

	public function __construct($key, $secret) {
		$this->factual = new factual($key, $secret);
	}

	/**
	 * Runs a quick query to test key/secret
	 */
	private function testConnect() {
		if ($query = $this->getQueryObject()) {
			$this->setQueryObject($query);
			try {
				$res = $this->factual->fetch($this->testTables['global'], $query); //test query
			} catch (FactualAPIException $e) {
				$this->msg($e->getMessage(), "");
				$this->msg($e->getErrorType(), "");
				$this->msg($e->getCode(), "");
				$this->msg("API Exception", false);
			}
			$this->msg("API Connection", true);
		} else {
			$this->msg("Exiting", "");
			exit;
		}
	}

	/**
	 * Determines whether pre-existing classes with same name will conflict
	 * Only run when no others are loaded, of course
	 */
	private function classConflicts() {
		$extantClasses = array_flip(get_declared_classes()); //case sensitive
		foreach ($this->classes as $className) {
			if ($extantClasses[$className]) {
				$this->msg("Classname conflict exists: " . $className, false);
				$failures = true;
			}
		}
		if (!$failures) {
			$this->msg("No classname conflicts", true);
		} else {
			$this->msg("These conflicts must be resolved before this driver can be employed\n", "");
			exit;
		}
	}

	private function setQueryObject($query) {
		if ($query->search("pizza")) {
			$this->msg("Setting query parameter", true);
		} else {
			$this->msg("Setting query parameter", false);
		}
	}

	private function getQueryObject() {
		$query = new FactualQuery();
		if ($query) {
			$this->msg("Creating Query object", true);
			return $query;
		} else {
			$this->msg("Creating Query object", false);
		}
	}

	/**
	 * Confirms correct extensions (dependencies) are installed
	 */
	private function testExt() {
		$modules = array (
			"SPL",
			"curl"
		);
		$ext = array_flip(get_loaded_extensions());
		foreach ($modules as $module) {
			if ($ext[$module]) {
				$this->msg($module . " is loaded", true);
			} else {
				$this->msg($module . " is not loaded", false);
			}
		}
	}

	private function testVersion() {
		$version = explode('.', phpversion());
		if ((int) $version[0] >= 5) {
			$status = true;
		} else {
			$status = false;
		}
		$this->msg("PHP verison v5+", $status);
	}

	private function msg($mesage, $status) {
		$lineLength = 40;
		if (is_bool($status)) {
			//convert to string
			if ($status) {
				$status = "Pass";
			} else {
				$status = "Fail";
			}
			//color for cli
			if (!$this->writeToFile) {
				if ($status == "Pass") {
					$status = "\033[0;32m" . $status . "\033[0m";
				} else {
					$status = "\033[0;31m" . $status . "\033[0m";
				}
			}
		}
		//fancypants alignment
		$message = $mesage . str_repeat(" ", $lineLength -strlen($mesage)) . $status . "\n";
		if ($this->writeToFile) {
			$fp = fopen($this->writeToFile, 'a');
			fwrite($fp,$message);
			fclose($fp);
		} else {
			echo $message;
		}
	}

}
?>
