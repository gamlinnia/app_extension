<?php

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app -> contentType('application/json');
$input = json_decode($app->request->getBody(), true);

/*CORS*/
require_once 'CORS.php';
/*常用 function*/
require_once ('tools.php');

/*SETUP*/
$intelligenceBaseUrl = 'http://172.16.16.110:8461';
$IMBaseUrl = 'http://apis.newegg.org';
$imageBase = 'http://10.1.39.209/productimage';
//$imageBase = 'http://images10.newegg.com/productimage';

if (session_id() == '') {
    session_start();
}

require_once 'config.inc.php';

$app->get('/api/destroySession', 'destroySession');
$app->get('/api/checkSessionState', 'checkSessionState');

$app->get('/api/getInformationFromIntelligence', 'getInformationFromIntelligence');

$app->get('/api/getProductImageBase64/:itemNumber', 'getProductImageBase64');
$app->get('/api/getProductImageListFromNE/:itemNumber', 'getProductImageListFromNE');
$app->post('/api/getProductImages', 'getProductImages');
$app->post('/api/uploadProductImages', 'uploadProductImages');

require_once 'middleware_rest.php';
require_once 'appBackend_rest.php';

$app->run();

function destroySession () {
    session_destroy();
    echo jsonMessage('success', 'SESSION DESTROYED');
}

function checkSessionState () {
    global $app;
    if ($app->request->get('oauth_token')) {
        $oauth_token = $app->request->get('oauth_token');
    }
    $consumerKey = $app->request->get('consumerKey');
    $consumerSecret = $app->request->get('consumerSecret');
    $callbackUrl = $app->request->get('callbackUrl');
    $temporaryCredentialsRequestUrl = $app->request->get('temporaryCredentialsRequestUrl');
    $adminAuthorizationUrl = $app->request->get('adminAuthorizationUrl');

    if (!isset($oauth_token) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
        $_SESSION['state'] = 0;
    }

    try {
        if (isset($_SESSION['state']) && $_SESSION['state'] == 2) {
            $authType = OAUTH_AUTH_TYPE_AUTHORIZATION;
        } else {
            $authType = OAUTH_AUTH_TYPE_URI;
        }
        $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
        $oauthClient->enableDebug();

        if (!isset($oauth_token) && (!isset($_SESSION['state']) || !$_SESSION['state']) ) {
            $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
            $_SESSION['secret'] = $requestToken['oauth_token_secret'];
            $_SESSION['state'] = 1;

            echo json_encode(array(
                'status' => 'success',
                'state' => $_SESSION['state'],
                'location' => $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']
            ));
        } else if ($_SESSION['state'] == 1) {
            $accessTokenRequestUrl = $app->request->get('accessTokenRequestUrl') ? $app->request->get('accessTokenRequestUrl') : null;
            if (!$accessTokenRequestUrl) {
                echo jsonMessage('danger', 'accessTokenRequestUrl is missing');
                return;
            }
            $oauthClient->setToken($oauth_token, $_SESSION['secret']);
            $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
            $_SESSION['state'] = 2;
            $_SESSION['token'] = $accessToken['oauth_token'];
            $_SESSION['secret'] = $accessToken['oauth_token_secret'];
            echo json_encode(array(
                'status' => 'success',
                'state' => $_SESSION['state'],
                'location' => $callbackUrl
            ));
        } else {
            /*after authentication, get response.*/
            echo json_encode(array(
                'status' => 'success',
                'state' => 'verified'
            ));
        }
    } catch (OAuthException $e) {
        print_r($e);
    }
}

function getInformationFromIntelligence ($returnResponse = false) {
    global $app;
    global $intelligenceBaseUrl;
    $itemNumber = $app->request->get('itemNumber');
    $restPostfix = '/itemservice/detail';
    $data = array(
        "CompanyCode" => 1003,
        "CountryCode" => "USA",
        "Fields" => null,
        "Items" => array(
            array("ItemNumber" => $itemNumber)
        ),
        "RequestModel" => "RW"
    );
    $header = array('Content-Type: application/json', 'Accept: application/json');
    $response = CallAPI('POST', $intelligenceBaseUrl . $restPostfix, $header, $data);
    if ($returnResponse) {
        return $response;
    }
    echo json_encode(array(
        'status' => 'success',
        'DataCollection' => array(
            'ItemNumber' => $response['detailinfo'][0]['ItemNumber'],
            'DetailSpecification' => $response['detailinfo'][0]['DetailSpecification'],
            'Introduction' => $response['detailinfo'][0]['Introduction'],
            'IntroductionImage' => $response['detailinfo'][0]['IntroductionImage'],
            'Intelligence' => $response['detailinfo'][0]['Intelligence']
        )
    ));
}

function updateProductInfo () {
    global $app;
    global $input;
    $action = $input['action'];
    $method = $input['method'];
    $requestBody = isset($input['requestBody']) ? $input['requestBody'] : array();
    $apiUrl = $input['apiUrl'];
    $restPostfix = $input['restPostfix'];
    $consumerKey = $input['consumerKey'];
    $consumerSecret = $input['consumerSecret'];
    $page = $app->request->get('page');
    $rowsPerPage = $app->request->get('rowsPerPage');

    try {
        $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
        $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
        $oauthClient->enableDebug();

        $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
        $oauthClient->fetch(
            $apiUrl . $restPostfix . '?page=' . $page . '&limit=' . $rowsPerPage,
            array(),
            strtoupper($method),
            array('Content-Type' => 'application/json', 'Accept' => 'application/json')
        );
        if (!isJson($oauthClient->getLastResponse())) {
            echo $oauthClient->getLastResponse();
            return;
        }
        echo json_encode(array(
            'status' => 'success',
            'DataCollection' => parseMagentoJson(json_decode($oauthClient->getLastResponse(), true))
        ));
    } catch (OAuthException $e) {
        print_r($e);
    }
}

function getProductImages ($returnResponse = false) {
    global $input;
    $startCheckingTimeStamp = currentTimeStamp();
    $apiUrl = $input['apiUrl'];
    $itemObj = $input['itemObj'];
    $restPostfix = '/products/' . $itemObj['entity_id'] . '/images';
    $count = 0;
    while ($count < 5) {
        try {
            $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
            $oauthClient = new OAuth($input['consumerKey'], $input['consumerSecret'], OAUTH_SIG_METHOD_HMACSHA1, $authType);
            /*$oauthClient->enableDebug();*/

            $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
            $oauthClient->fetch(
                $apiUrl . $restPostfix,
                array(),
                OAUTH_HTTP_METHOD_GET,
                array('Content-Type' => 'application/json', 'Accept' => 'application/json')
            );
            $response = parseMagentoJson(json_decode($oauthClient->getLastResponse(), true));
            $result = array(
                'status' => 'success',
                'count' => count($response),
                'DataCollection' => $response
            );
            error_log('#' . ($count +1) . ' Spent ' . totalSpendTime($startCheckingTimeStamp) . ' to get magento image list of id: ' . $itemObj['entity_id'] . ', ' . $result['count'] . ' items');
            if ($returnResponse) {
                return $result;
                error_log('need to break');
            }
            echo json_encode($result);
            break;
        } catch (Exception $e) {
            $count++;
            error_log('Sleep 15 seconds to re-fetch magento image list');
            sleep(15);
        }
    }
}

function uploadProductImages () {
    global $input;
    $apiUrl = $input['apiUrl'];
    $itemObj = $input['itemObj'];
    $restPostfix = '/products/' . $itemObj['entity_id'] . '/images';
    error_log('processing ID: ' . $itemObj['entity_id']);
    $currentTimestamp = currentTimeStamp();

    preg_match('/[0-9]{2,3}-[0-9]{2,3}-[0-9]{2,3}/', $itemObj['sku'], $match);
    if (count($match) < 1) {
        echo json_encode(array(
            'status' => 'danger',
            'message' => 'NOT A VALID SKU number'
        ));
        return;
    }

    $magentoImages = getProductImages(true);
    $base64ProductImages = getProductImageBase64($itemObj['sku'], true);


    if ($base64ProductImages['count'] < 1) {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'NO IMAGE UPLOADED'
        ));
        return;
    }

    if (count($magentoImages['DataCollection']) > 0) {
        $base64ProductImages['DataCollection'] = existImageComparison($magentoImages['DataCollection'], $base64ProductImages['DataCollection']);
        error_log(count($base64ProductImages['DataCollection']) . ' to upload, Images exsit: ' . count($magentoImages['DataCollection']));
        if (count($base64ProductImages['DataCollection']) < 1) {
            error_log($itemObj['sku'] . ' checking duration: ' . totalSpendTime($currentTimestamp));
            echo json_encode(array(
                'status' => 'success',
                'message' => 'NO IMAGE NEED TO BE UPLOADED'
            ));
            return;
        }
    }

    try {
        $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
        $oauthClient = new OAuth($input['consumerKey'], $input['consumerSecret'], OAUTH_SIG_METHOD_HMACSHA1, $authType);
        $oauthClient->enableDebug();

        $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
        $result = array();
        foreach ($base64ProductImages['DataCollection'] AS $index => $imageObject) {
            $startUploadTimeStamp = currentTimeStamp();
            $requestBody = array(
                'file_mime_type' => 'image/jpeg',
                'file_content' => $imageObject['base64Content'],
                'file_name' => fileNamePrefix($imageObject['ImageName'])
            );
            $oauthClient->fetch(
                $apiUrl . $restPostfix,
                json_encode($requestBody),
                OAUTH_HTTP_METHOD_POST,
                array('Content-Type' => 'application/json', 'Accept' => 'application/json')
            );
            $responseInfo = $oauthClient->getLastResponseInfo();
            array_push($result, $responseInfo);
            if (isset($responseInfo['http_code']) && $responseInfo['http_code'] == 200) {
                error_log(($index +1) . '/' . count($base64ProductImages['DataCollection']) . ' DONE item: ' . $imageObject['ImageName'] . ', elapsed: ' . totalSpendTime($startUploadTimeStamp));
            } else {
                error_log('Exception occur');
            }
        }
        error_log($itemObj['sku'] . ' DONE' . ', duration: ' . totalSpendTime($currentTimestamp));
        echo json_encode($result);
    } catch (OAuthException $e) {
        print_r($e);
        echo $e->{'debugInfo'}->{'body_recv'};
    }
}

function existImageComparison ($magentoImagesDataCollection, $base64ProductImagesDataCollection) {
    foreach ($magentoImagesDataCollection AS $magentoObject) {
        foreach ($base64ProductImagesDataCollection AS $key => $imageObject) {
            preg_match('/[\/](' . fileNamePrefix($imageObject['ImageName']) . ')/', $magentoObject['url'], $match);
            if (count($match) > 0) {
                unset($base64ProductImagesDataCollection[$key]);
            }
        }
    }
    return $base64ProductImagesDataCollection;
}

function getProductImageBase64 ($itemNumber, $returnResponse = false) {
    global $imageBase;
    $itemImages = getProductImageListFromNE($itemNumber, true);
    if ($itemImages['count'] < 1) {
        $result = array(
            "status" => "success",
            "count" => 0,
            "DataCollection" => array()
        );
        if ($returnResponse) {
            return $result;
        }
        echo json_encode($result);
        return;
    }

    $count = 0;
    $responseObjects = array();

    $responseObjects = getBase64Content($responseObjects, $itemImages['DataCollection'], $imageBase, $count, count($itemImages['DataCollection']));

    $result = array(
        "status" => "success",
        "count" => count($responseObjects),
        "DataCollection" => $responseObjects
    );
    if ($returnResponse) {
        return $result;
    }
    echo json_encode($result);
}

function getBase64Content ($response, $contentArray, $imageBase, $count, $sentinel) {
    $localPath = '../productImages/';
    $fileName = $contentArray[$count]['ImageName'];
    $startFetchTimeStamp = currentTimeStamp();
    if (file_exists($localPath . $fileName)) {
        error_log($fileName . ' exists in disk');
        $image = file_get_contents($localPath . $fileName);
    } else {

        $fetchCount = 0;
        while ($fetchCount < 10) {
            try {
                $image = file_get_contents($imageBase . '/' . $fileName);
                break;
            } catch (Exception $e) {
                sleep(30);
                $fetchCount++;
                error_log($fetchCount . ' time fetch image error');
                if ($fetchCount == 10) {
                    echo json_encode(array('status' => 'danger', 'message' => 'Can not get the image'));
                    return;
                }
            }
        }

        file_put_contents($localPath . $fileName, $image);
        $fileSize = filesize($localPath . $fileName);
        error_log('Fetching ' . $fileName . ': ' . number_format($fileSize) . ' bytes, elapsed: ' . totalSpendTime($startFetchTimeStamp));
    }

    array_push($response, array(
        'ImageName' => $fileName,
        'imageNumber' => $count + 1,
        'base64Content' => base64_encode($image)
    ));

    $count++;
    if ($count < $sentinel) {
        $response = getBase64Content($response, $contentArray, $imageBase, $count, $sentinel);
    }
    return $response;
}

function getImageFromUrl ($url) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
    $img = curl_exec($ch);
    return $img;
}

function getProductImageListFromNE ($itemNumber, $returnResponse = false) {
    global $IMBaseUrl;
    $startFetchingTimeStamp = currentTimeStamp();
    $restPostfix = '/content/v1/item/' . $itemNumber . '/image';
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: b90ecb77fe00ee07f61c22dca5036b93&2d977e9513aaf4df876c3c4b9e1874ac'
    );
    /*curl https 會出現問題*/
    $count = 0;
    while ($count < 10) {
        if ($count > 0) {
            error_log('Sleep 15 seconds');
            sleep(15);
        }
        $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header, array());
        error_log('Spend ' . totalSpendTime($startFetchingTimeStamp) . ' to get Image List ' . count($response['Images']) . ' items');
        if (count($response['Images']) > 0) {
            break;
        }
        $count++;
    }

    if ($returnResponse) {
        return array(
            'status' => 'success',
            'count' => count($response['Images']),
            'DataCollection' => $response['Images']
        );
    }
    echo json_encode(array(
        'status' => 'success',
        'count' => count($response['Images']),
        'DataCollection' => $response['Images']
    ));
}
