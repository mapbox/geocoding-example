<?php

/**
 * Represents an Exception that happened while communicating with Mapbox.
 * Includes information about the request that triggered the problem.
 * @author Tyler
 * @package Mapbox
 * @license Apache 2.0
 */
class MapboxApiException extends Exception {
	protected $info; //debug array
	
	public function __construct($info) {
		$this->info = $info;
		if (isset($info['message'])){
			$this->message = $info['message'];
			$this->code = $info['code'];
		} else {
			$this->message = "Unknown error; no message returned from server";
		}
	}

	public function debug(){
		return $this->info;
	}
}
?>
