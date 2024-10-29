<?php

if (!defined('DB_NAME')) {
	require_once("../../../../wp-config.php");
}

$json = stripslashes($_GET['options']);
$options = json_decode($json, true);

echo AJAXedTwitter::messages($options);