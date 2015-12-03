<?php
/**
 * Basic response from Mapbox API
 * @author Tyler
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
    	$this->json = $apiResponse['body'];
    	$this->body = json_decode($apiResponse['body'],true);
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
	 * Get response headers sent by Mapbox
	 * @return array
	 */
	public function getResponseHeaders(){
		return $this->headers;
	}

	/**
	 * Get HTTP response code
	 * @return int
	 */
	public function getResponseCode(){
		return $this->headers['code'];
	}

	/**
	 * Test for success (200 status return)
	 * Note this tests for a successful http call, not a successful program operation
	 */
	 public function success(){
	 	if ($this->headers['code'] == 200){
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
	  	return $this->headers['request']['unencoded'];
	  }

	  /**
	   * Get url-encoded request string, does not include auth.
	   * @return string
	   */
	  public function getRawRequest(){
	  	return $this->headers['request']['encoded'];
	  }
	  
	   /**
	   * Get http headers returned by Mapbox
	   * @return string
	   */
	  public function getHeaders(){
	  	return $this->headers;
	  }      

}
?>
