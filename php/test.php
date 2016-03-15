<?php

/*
 * Run this script in CLI using your Mapbox token as parameter 1 and optional logfile as parameter 2, e.g.
 * 			php test.php yourMapboxToken [logFile]
 */

error_reporting (E_ALL);

if ($argv[1] == "--help" || $argv[1] == "--h"){
	echo "Usage: php test.php <token> [log file]\n";
	echo "Add your token and optional logfile as parameters to this command line script.\n\n";
	exit;
} else {
	if (isset($argv[1])){
		$token = $argv[1];
		if (isset($argv[2])){$logFile = $argv[2];} else {$logFile="";}
	} else {
		echo "Token required:\n";
		echo "Usage: php test.php <token> [log file]\n";
		exit;
	}
}

//Run tests
require_once('MapboxTest.php');	
$mapboxTest = new mapboxTest($token);	
$mapboxTest->setLogFile($logFile);   
$mapboxTest->test();





exit;




	


?>
