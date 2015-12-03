<?php
/**
 * Requires PHP5, SPL (for autoloading)
 */


/**
 * Represents the public Mapbox API. Supports running queries against Mapbox and inspecting the response. 
 * @author Tyler
 * @package Mapbox
 * @license Apache 2.0
 */
class Mapbox {

	protected $home = "https://api.tiles.mapbox.com"; //URL base
	protected $driverVersion = "mapbox-php-driver-v0.1.0";  //current version of the php wrapper
	protected $versions = array('geocoder'=>'v4'); //versions for endpoint
	protected $debug = false; //debug flag
	public $token; //access token
	protected $curlTimeout = 0; //maximum number of seconds for the network function to execute (0 = no timeout)
	protected $connectTimeout = 0; //maximum number of seconds to connect to the server (0 = no timeout)
	protected $placeTypes = array('country','region','postcode','place','neighborhood','address','poi');
	protected $permanentGeocodes = false;

	/**
	 * Constructor. Creates authenticated access to Mapbox.
	 * @param string token your Mapbox token.
	 */
	public function __construct($token) {
		//register autoloader
		spl_autoload_register(array (
			get_class(),
			'MapboxAutoload'
		));
		$this->token = $token;
	}

	/**
	 * Sets version for endpoint
	 * @param string endpoint endpoint name e.g. 'geocoder'
	 * @param string endpoint version e.g. 'v4'
	**/
	public function setEndpointVersion($endpoint,$version) {
		$this->versions[$endpoint] = $version;
	}

	/**
	 * Turns on debugging for output to stderr
	 */
	public function debug() {
		$this->debug = true;
	}

	/**
	 * Change the base URL at which to contact Mapbox's API. This
	 * may be useful if you want to talk to a test or staging
	 * server withou changing config
	 * Example value: <tt>http://staging.api.v3.Mapbox.com/t/</tt>
	 * @param urlBase the base URL at which to contact Mapbox's API.
	 * @return void
	 */
	public function setHome($urlBase) {
		$this->home = $urlBase;
	}

	protected function getGeocoderDataSet(){
		if ($this->permanentGeocodes == true){
			$dataSet = "mapbox.places-permanent";
		} else {
			$dataSet = "mapbox.places";
		}
		return $dataSet;
	}
	
	protected function urlForGeocode($query) {
		return $this->home ."/".$this->versions['geocoder']."/geocode/".$this->getGeocoderDataSet()."/" . urlencode($query).".json";
	}
	
	protected function urlForReverseGeocode($longitude, $latitude) {
		return $this->home ."/".$this->versions['geocoder']."/geocode/".$this->getGeocoderDataSet()."/" .$longitude.",".$latitude.".json";
	}

	/**
	  * Geocodes by returning a response containing the address nearest a given point.
	  * @param string query The unstructured address or place-name
	  * @param array types containing n of country, region, postcode, place, neighborhood, address, or poi
	  * @param array proximity with keys 'longitude' , 'latitude'
	  * @return the response of a geocode query against Mapbox.
	  */
	public function geocode($query, $types=array(), $proximity=array()) {
		$params = array();
		if (empty($query)){return null;}
		$url = $this->urlForGeocode($query);
		if (!empty($types)){
			$params['types'] = $types; 
		}
		if (!empty($proximity)){
			$params['proximity'] = $proximity['longitude'].",".$proximity['latitude']; 
		}
		$this->permanentGeocodes = false; //set by default to off
		return new GeocodeResponse($this->request($url,"GET",$params));
	}
	

	public function geocodePermanent($query, $types=array(), $proximity=array()) {
		$this->permanentGeocodes = true;
		return $this->geocode($query, $types=array(), $proximity=array()); 
	}

	/**
	  * Reverse geocodes by returning a response containing the resolved entities.
	  * @param obj point The point for which the nearest address is returned
	  * @param string tableName Optional. The tablenae to geocode against.  Currently only 'places' is supported.
	  * @return the response of running a reverse geocode query for <tt>point</tt> against Mapbox.
	  */
	public function reverseGeocode($longitude, $latitude, $types=array()) {
		$params = array();
		$url = $this->urlForReverseGeocode($longitude, $latitude);
		if (!empty($types)){
			$params['types'] = $types; 
		}
		return new GeocodeResponse($this->request($url,"GET",$params));
	}

    /**
     * Sign the request, perform a curl request and return the results
     *
     * @param string $urlStr unsigned URL request
     * @param string $requestMethod
     * @param null $params
     * @return array ex: array ('headers'=>array(), 'body'=>string)
     * @throws MapboxApiException
     */
    protected function request($urlStr, $requestMethod="GET", $params = null) {
    		//custom headers
		$curlOptions[CURLOPT_HTTPHEADER] = array ();
		$curlOptions[CURLOPT_HTTPHEADER][] = "X-Mapbox-Lib: " . $this->driverVersion;
		if ($requestMethod == "POST") {
			$curlOptions[CURLOPT_HTTPHEADER][] = "Content-Type: " . "application/x-www-form-urlencoded";
		}
		
		//other curl options
		$curlOptions[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout; //connection timeout
		$curlOptions[CURLOPT_TIMEOUT] = $this->curlTimeout; //execution timeout
		$curlOptions[CURLOPT_RETURNTRANSFER] = 1; //return contents on success

		//format params and append
		$formattedParams = null;
		if (count($params)>0){
			foreach ($params as $key=>$value){
				if (is_array($value)){
					$keyVal[] = $key."=".implode(",",$value);
				} else {
					$keyVal[] = $key."=".$value;
				}
			}
			$formattedParams .= implode("&",$keyVal);
		}
		
		//url formatting
		if ($formattedParams){
			$urlStr .= "?".$formattedParams;
			$url = $urlStr."&access_token=".$this->token;
		} else {
			$url = $urlStr."?access_token=".$this->token;
		}
		
		//format cURL
		$ch = curl_init($url);
		foreach ($curlOptions as $key=>$value){
			curl_setopt ($ch , $key, $value);
		}
		
		//init metadata
		$info = array();
		$info['request']['encoded'] = $urlStr;
		$info['request']['unencoded'] = urldecode($urlStr);
		$info['driver'] = $this->driverVersion;
		
		//make request
		try {
			$callStart = microtime(true);
			$result = curl_exec($ch);
			$callEnd = microtime(true);
		} catch (Exception $e) {
			//catch client exception
			$info['method'] = $requestMethod;
			$info['message'] = "Service exception.  Client did not connect and returned '" . $e->getMessage() . "'";
			$MapboxE = new MapboxApiException($info);
			throw $MapboxE;
		}
		
		//extract curl headers
		$info['connect_time'] = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
		$info['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($params) {
			$info['request']['parameters'] = $params;
		}
		
		//catch server exception & load up on debug data
		if ($info['code'] >= 400 | $this->debug) {
			$body = json_decode($result, true);
			//get a boatload of debug data
			$info['method'] = $requestMethod;
			$info['curl'] = curl_getinfo($ch);
			//add execution time
			$info['time'] = $callEnd - $callStart;
			//write debug info to stderr if debug mode on
			if ($this->debug) {
				$info = array_filter($info); //remove empty elements for readability
				file_put_contents('php://stderr', "Debug " . print_r($info, true));
			}			
			//chuck exception
			if ($info['code'] >= 400){
				if ($info['code'] == 401){
					$info['message'] = "401 Unauthorized; check your access token";
				} else if ($info['code'] == 403){
					$info['message'] = "403 Unauthorized; you may not have access to this resource";
				} else {
					$info['message'] = "HTTP code ".$info['code'];
				}
				$MapboxE = new MapboxApiException($info);
				throw $MapboxE;
			}
		}
		//check for deprecation, add to stdout
		if ($info['code'] == 301){
			//file_put_contents('php://stderr', "Entity is deprecated");
		}
		
		//close curl
		curl_close($ch);
		
		//format
		$res['headers'] = $info;
		$res['body'] = $result;
		
		return $res;
	}

	/**
	 * Converts and encodes parameter array to a query string
	 * @return string
	 */
	protected function toQueryString($parameters){
		if (count($parameters) > 0){
			foreach ($parameters as $key => $value){
				if (is_bool($value)){ //convert bool to string
					$value = var_export($value, true);
				}
				$temp[] = $key."=".rawurlencode($value);	
			}
			return "?".implode("&", $temp);
		} else {
			return "";
		}
	}

	/**
	 * Autoloader for file dependencies
	 * Called by spl_autoload_register() to avoid conflicts with autoload() methods from other libs
	 */
	public static function mapboxAutoload($className) {
		$filename = dirname(__FILE__) . "/" . $className . ".php";
		// don't interfere with other classloaders
		if (!file_exists($filename)) {
			return;
		}
		include $filename;
	}

	/**
	 * Sets maximum number of seconds to connect to the server before bailing
	 * @param int secs timeout in seconds
	 */
	public function setConnectTimeout($secs){
		$this->connectTimeout = $secs;
		return $this;
	}

	/**
	 * Sets maximum number of seconds to the network function to execute
	 * @param int secs timeout in seconds
	 */
	public function setCurlTimeout($secs){
		$this->curlTimeout = $secs;
		return $this;
	}
}
?>
