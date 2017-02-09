<?php

require_once 'rest/tools.php';

$reviewJson = file_get_contents('result.json');

$devRestApiUrl = "http://rwdev.buyabs.corp/rest/route.php/api/postReviewJsonToLocal";

$headers =  array('Content-Type: application/json', 'Accept: application/json', 'Token: rosewill');

$response = CallAPI('POST', $devRestApiUrl, $headers, $reviewJson);

var_dump($response);