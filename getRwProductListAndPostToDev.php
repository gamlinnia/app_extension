<?php
require_once 'rest/tools.php';

//GET: http://api.newegg.org/ExternalMarketplace/v1/RosewillItem?format=json&Manufactory=2177&ItemCreationDateFrom=2000-01-16
$neweggApiUrl = "http://api.newegg.org/ExternalMarketplace/v1/RosewillItem";
$localApiUrl = "http://10.16.197.90/app_extension/rest/route.php/api/getCombinationInfo";
$devRestApiUrl = "http://rwdev.buyabs.corp/rest/route.php/api/postProductJsonToLocal";

$params['format'] = 'json';
$params['Manufactory'] = '2177';
$params['ItemCreationDateFrom'] = '2000-01-16';
$headers =  array('Content-Type: application/json', 'Accept: application/json', 'Token: rosewill');

// get rosewill product list from newegg
$response = CallAPI('GET', $neweggApiUrl . parseQueryString($params), $headers);

$rwProductList = $response["ResultList"];
if(!empty($rwProductList)) {
    foreach ($rwProductList as $each) {
        $input['ItemNumber'] = $each['ItemNumber'];
        echo $input["ItemNumber"] . PHP_EOL;
        $input['action'] = 'baseinfo';
        //POST: http://10.16.197.90/app_extension/rest/route.php/api/getCombinationInfo
        $response = CallAPI('POST', $localApiUrl, $headers, $input);
        $productInfo = $response;
        //var_dump($productInfo);
        if (!empty($productInfo)) {
            $response = CallAPI('POST', $devRestApiUrl, $headers, $productInfo);
            if($response['message'] == 'Success'){
                echo 'Success' . PHP_EOL;
            }
            else{
                echo 'Error' . PHP_EOL;
                $log = array();
                $log['item number'] = $input['ItemNumber'];
                $log['response'] = $response;
                $now = new DateTime(null, new DateTimeZone('UTC'));
                $log['date'] = $now->format('Y-m-d H:i:s');
                file_put_contents('post.log', json_encode($log) . PHP_EOL, FILE_APPEND);
            }
        }
    }
}
