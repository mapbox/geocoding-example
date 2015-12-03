<?php
/**
 * Basic response from Mapbox API
 * @author twbell
 * @package Mapbox
 * @license Apache 2.0
 */
abstract class MapboxResponse extends ArrayIterator {

  protected $json = null; //string
  protected $body = array();
  protected $headers = array();

  /**
   * Constructor, parses return values from mapbox::request() 
   * @param array response The JSON response String returned by Mapbox.
   */
  public function __construct($apiResponse) {
    try {
    	$this->json = $apiResponse['body']; //raw json
    	$this->body = json_decode($apiResponse['body'],true);
    	$this->info = $apiResponse['info'];
    	$this->headers = $apiResponse['headers'];
    	$this->parseResponse($apiResponse);
    } catch (Exception $e) {
    	//add note about json encoding borking here
      throw $e;
    }
  }

	/**
	 * Parses the entire response, incl metadata
	 * @param array apiResponse response from curl
	 * @return void
	 */
	protected function parseResponse(){
		return true;
	}

	/**
	 * Get HTTP response code
	 * @return int
	 */
	public function getResponseCode(){
		return $this->info['code'];
	}

	/**
	 * Test for success (200 status return)
	 * Note this tests for a successful http call, not a successful program operation
	 */
	 public function success(){
	 	if ($this->info['code'] == 200){
	 		return true;
	 	} else {
	 		return false;
	 	}
	 }

	  /**
	   * Get the entire JSON response from Mapbox
	   * @return string 
	   */
	  public function getJson() {
	    return $this->json;
	  }

	  /**
	   * Gets count of elements returned in this page of result set (not total count)
	   * @return int 
	   */
	  public function size() {
		return count($this);  
	  }

	  /**
	   * Subclasses of MapboxResponse must provide access to the original JSON
	   * representation of Mapbox's response. Alias for getJson()
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
	  	return $this->info['request']['unencoded'];
	  }

	  /**
	   * Get url-encoded request string, does not include auth.
	   * @return string
	   */
	  public function getRawRequest(){
	  	return $this->info['request']['encoded'];
	  }
	  
	   /**
	   * Get http headers returned by Mapbox
	   * @return string
	   */
	  public function getHeaders(){
	  	return $this->headers;
	  }      
	  
	   /**
	   * Get information on the call
	   * @return string
	   */
	  public function getInfo(){
	  	return $this->info;
	  }   

}
?>
