<?php
//Generate API keys
include(dirname(__FILE__)."/classes/CrossRoads_UUID.php");
echo "api_key: ".CrossRoads_UUID::generate()."\n";
echo "secret : ".CrossRoads_UUID::generate()."\n";
?>
