<?php
require_once ('Mapbox.php');

/**
 * Test methods for Mapbox API.
 * @author twbell
 * @package Mapbox
 * @license Apache 2.0
 */
class MapboxTest {

	private $mapbox;
	private $writeToFile = null;

	/**
	 * Primary test function. 
	 */
	public function test() {
		if (!$this->writeToFile) {
			echo "\nTesting Mapbox\n";
			echo "========================\n";
		} else {
			if ($this->writeToFile) {
				//remove extant log file
				@ unlink($this->writeToFile);
			}
		}
		$this->testVersion();
		$this->testExt();
		$authenticated = $this->testConnect();
		if ($authenticated){
			$this->testGeocode();
			$this->testReverseGeocode();
			$this->testGeocodePermanent();
			//$this->testCountries();
		}

		if (!$this->writeToFile) {
			echo "========================\n\n";
		}
	}


	/**
	 * Set file to log report to. Echoes to screen by default
	 * @return void
	 */
	public function setLogFile($fileName = null) {
		if ($fileName) {
			$this->writeToFile = $fileName;
		}
	}

	private function testGeocode() {
		try {
			$res = $this->mapbox->geocode("149 9th St, San Francisco, CA 94103");
		} catch (Exception $e) {
			$this->msg("Geocoder", false, $e->getMessage());
			return false;
		}
		if ($res->success()){
			$this->msg("Geocoder", true);
		} else {
			$this->msg("Geocoder", false);
		}
	}

	private function testGeocodePermanent() {
		try {
			$res = $this->mapbox->geocodePermanent("149 9th St, San Francisco, CA 94103");
		} catch (Exception $e) {
			$this->msg("Permanent Geocoder", false, $e->getMessage());
			return false;
		}
		if ($res->success()){
			$this->msg("Permanent Geocoder", true);
		} else {
			$this->msg("Permanent Geocoder", false);
		}
	}

	private function testReverseGeocode() {
		$lon = -122.143895;
		$lat = 37.425674;
		try {
			$res = $this->mapbox->reverseGeocode($lon, $lat);
		} catch (Exception $e) {
			$this->msg("Reverse Geocoder", false, $e->getMessage());
			return false;
		}
		if ($res->success()){
			$this->msg("Reverse Geocoder", true);
		} else {
			$this->msg("Reverse Geocoder", false);
		}
	}

	public function __construct($token) {
		if (!$token) {
			$this->msg("Token is required in class constructor", null);
			exit;
		}
		$this->mapbox = new mapbox($token);
	}

	/**
	 * Runs a quick query to test token
	 */
	private function testConnect() {
		$str = "Mapbox Authentication";
		if (file_get_contents("https://api.mapbox.com?".$this->mapbox->gettoken()) == "{\"api\":\"mapbox\"}"){
			$this->msg($str, true);
		} else {
			$this->msg($str, false);
		}
		return true;
	}

	/**
	 * Confirms correct extensions (dependencies) are installed
	 */
	private function testExt() {
		$modules = array (
			"SPL",
			"curl",
			"json"
		);
		$ext = array_flip(get_loaded_extensions());
		foreach ($modules as $module) {
			if ($ext[$module]) {
				$this->msg("PHP ".$module . " is loaded", true);
			} else {
				$this->msg("PHP ".$module . " is not loaded", false);
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

	//writes status to stdout or to optional logfile
	private function msg($mesage, $status, $deets = null) {
		$lineLength = 40;
		if (is_bool($status)) {
			//convert to string
			if ($status) {
				$status = "OK";
			} else {
				$status = "Doh";
			}
			//color for cli
			if (!$this->writeToFile) {
				if ($status == "OK") {
					$status = "\033[0;32m" . $status . "\033[0m";
				} else {
					$status = "\033[0;31m" . $status . "\033[0m";
				}
			}
		}
		//fancypants alignment
		$message = $mesage . str_repeat(" ", $lineLength -strlen($mesage)) . $status;
		if ($deets) {
			$message .= "\t" . $deets;
		}
		$message .= "\n";
		if ($this->writeToFile) {
			$fp = fopen($this->writeToFile, 'a');
			fwrite($fp, $message);
			fclose($fp);
		} else {
			echo $message;
		}
	}

}
?>
