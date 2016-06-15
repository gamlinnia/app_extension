<?php
require_once 'rest/tools.php';

function getRwProductList(){
    $neweggApiUrl = "http://api.newegg.org/ExternalMarketplace/v1/RosewillItem";

    $params['format'] = 'json';
    $params['Manufactory'] = '2177';
    $params['ItemCreationDateFrom'] = '2000-01-16';

    $headers =  array('Content-Type: application/json', 'Accept: application/json');

    $response = CallAPI('GET', $neweggApiUrl . parseQueryString($params), $headers);

    return $response["ResultList"];
}

function getProductInfo($input = array()){
    $localApiUrl = "http://10.16.197.90/app_extension/rest/route.php/api/getCombinationInfo";

    $headers =  array('Content-Type: application/json', 'Accept: application/json');

    $response = CallAPI('POST', $localApiUrl, $headers, $input);

    return $response;
}

function postProductInfoToDev($productInfo){
    $devRestApiUrl = "http://rwdev.buyabs.corp/rest/route.php/api/postProductJsonToLocal";

    $headers =  array('Content-Type: application/json', 'Accept: application/json', 'Token: rosewill');

    $response = CallAPI('POST', $devRestApiUrl, $headers, $productInfo);

    return $response;
}

function getImagesFromIM ($itemNumber) {
    $IMBaseUrl = 'https://apis.newegg.org';
//    $imageBase = 'http://images10.newegg.com/productimage';
//    $imageBase = 'http://10.1.39.209/productimage';
    $restPostfix = '/content/v1/item/' . $itemNumber . '/image';

    $header = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: b90ecb77fe00ee07f61c22dca5036b93&2d977e9513aaf4df876c3c4b9e1874ac'
    );

    $restUrl = $IMBaseUrl . $restPostfix;

    $response = CallAPI('GET', $restUrl, $header);
    return json_decode($response, true);
}

function logErrorMessage($input, $response){
    $log = array();
    $log['item number'] = $input['ItemNumber'];
    $log['response'] = $response;
    $now = new DateTime(null, new DateTimeZone('UTC'));
    $log['date'] = $now->format('Y-m-d H:i:s');
    file_put_contents('post.log', json_encode($log) . PHP_EOL, FILE_APPEND);
}

// get rosewill product list from newegg
$rwProductList = getRwProductList();
if(!empty($rwProductList)) {
    foreach ($rwProductList as $each) {
        $input['ItemNumber'] = $each['ItemNumber'];
        echo $input["ItemNumber"] . PHP_EOL;
        $input['action'] = 'baseinfo';
        $productInfo = getProductInfo($input);
        if (!empty($productInfo)) {
            $imagesArray = getImagesFromIM($input['ItemNumber']);
            $productInfo['Images'] = $imagesArray['Images'];

            $response = postProductInfoToDev($productInfo);
            if($response['message'] == 'Success'){
                echo 'Item Number: ' . $input['ItemNumber'] . ' post product info success' . PHP_EOL;
            }
            else{
                echo 'Error' . PHP_EOL;
                logErrorMessage($input, $response);
            }
        }
    }
}
