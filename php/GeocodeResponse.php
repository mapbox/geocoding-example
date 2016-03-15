<?php

/**
 * Identical to a MapboxResponse but contains additional methods/properties for working with returned data
 * @author twbell
 * @package Mapbox
 * @license Apache 2.0
 */
class geocodeResponse extends MapboxResponse {

	protected $resultCount; //int
	protected $attribution; //str
	protected $type; //str
	protected $query; //array
	protected $features; //str
	
	/**
	 * Parses JSON as array and assigns object values
	 * @param string json JSON returned from API
	 * @return array structured JSON
	 */
	protected function parseResponse(){
		$this->resultCount = count($this->body['features']);
		$this->attribution = $this->body['attribution'];
		$this->type = $this->body['type'];
		$this->query = $this->body['query'];
		$this->features = $this->body['features'];

	    	//assign data
	    	$this->assignData($this->body['features']);
	    	
	    	return true;	
	}
	
	/**
	* Gets response
	*/
	public function getData(){
		return $this->features;
	}
	
	/**
	* Gets count
	*/
	public function getCount(){
		return $this->resultCount;
	}
	
	/**
	* Gets attribution
	*/
	public function getAttribution(){
		return $this->attribution;
	}
	
	/**
	 * Assigns data element to object for iterator
	 * @param array data The data array from API response
	 */
	protected function assignData($data){
		if ($data){
		//assign data to iterator
    		foreach ($data as $index => $datum){
    			$this[$index] = $datum;
    		}
    	}
	}

}
?>
