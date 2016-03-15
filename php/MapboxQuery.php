<?php


/**
 * Represents a top level Mapbox query. Knows how to represent the query as URL
 * @author twbell
 * @package Mapbox
 * @license Apache 2.0
 */

class MapboxQuery {
	protected $fullTextSearch; //string
	protected $selectFields = null; //otherwise comma-delineated list of fieldnames
	protected $limit; //int
	protected $offset; //int
	protected $threshold = null;
	const RESPONSETYPE = "ReadResponse";

	/**
	 * Whether this lib must perform URL encoding.
	 * Set to avoid double or absent encoding
	 */
	const URLENCODE = true;

	public function getResponseType(){
		return self::RESPONSETYPE;
	}

	/**
	 * Sets the maximum amount of records to return from this Query.
	 * @param int limit The maximum count of records to return from this Query.
	 * @return this Query
	 */
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets how many records in to start getting results (i.e., the page offset) for this Query.
	 * @param int offset The page offset for this Query.
	 * @return obj this Query
	 */
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Builds and returns the query string to represent this Query when talking to
	 * Mapbox's API. Provides proper URL encoding and escaping.
	 * @return string The query string to represent this Query when talking to Mapbox's API.
	 * @internal re-activate geobounds method
	 */
	public function toUrlQuery() {

		$temp['select'] = $this->fieldsJsonOrNull();
		$temp['q'] = $this->fullTextSearch;
		$temp['sort'] = $this->sortsJsonOrNull();
		$temp['limit'] = ($this->limit > 0 ? $this->limit : null);
		$temp['offset'] = ($this->offset > 0 ? $this->offset : null);
		$temp['include_count'] =  ($this->includeRowCount ? "true" : null);
		$temp['filters'] = $this->rowFiltersJsonOrNull();
		$temp['geo'] = $this->geoBoundsJsonOrNull();
		$temp['threshold'] = $this->thresholdOrNull();
		$temp = array_filter($temp); //remove nulls		

		//initialize
		$temp2 = array();

		//encode (cannot use http_build_query() as we need to *raw* encode adn this not provided until PHP v5.4)
		foreach ($temp as $key => $value){
			$temp2[] = $key."=".rawurlencode($value);		
		}	
		
		//process additional kay/value parameters
		foreach ($this->keyValuePairs as $key => $value){
			$temp2[] = $key."=".rawurlencode($value);	
		}
		
		return implode("&", $temp2);
	}

	/**
	 * Adds misc parameters to the URL query
	 * @param string key Key namev
	 * @param string un-URL-encoded value 
	 */
	public function addParam($key,$value){
		$this->keyValuePairs[$key] = $value;
		return $this->keyValuePairs;
	}

	/**
	* Adds array of name/key pairs to query for eventual resolution
 	* @param array keyValueArray A key value array
	* $return object This query object or NULL on failure
 	*/
  	public function addParamArray($keyValueArray) {
	  	if (!is_array($keyValueArray)){
	  		throw new exception (__METHOD__." Parameter must be array: key = attribute name, value = attribute value");
	  	}
	  	foreach($keyValueArray as $key => $value) {
	 		$this->keyValuePairs[$key] = $value;
	 	}
	    	return $this;
 	}


	public function toString() {
		try {
			return urldecode($this->toUrlQuery());
		} catch (Exception $e) {
			throw $e;
		}
	}

	
}
?>
