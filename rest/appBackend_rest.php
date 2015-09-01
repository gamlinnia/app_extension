<?php

$app->get('/api/getInformationFromIM', 'getInformationFromIM');
function getInformationFromIM ($paramsObj = array(), $returnResponse = false) {
    global $app;
    global $IMBaseUrl;
    $params = $app->request->params() ? $app->request->params() : $paramsObj;
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: b90ecb77fe00ee07f61c22dca5036b93&2d977e9513aaf4df876c3c4b9e1874ac'
    );
    switch ($params['action']) {
        case 'getImages' :
            $restPostfix = '/content/v1/item/' . $params['itemNumber'] . '/image';
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response['Images'];
            }
            echo jsonResult($response['Images']);
            break;
        case 'baseinfo' :
            $restPostfix = '/content/v1/item/baseinfo/' . $params['itemNumber'];
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'price' :
            $restPostfix = '/pricing/v1/item/price' . parseQueryString(array(
                    'ItemNumber' => $params['itemNumber'],
                    'CompanyCode' => $params['CompanyCode'],
                    'CountryCode' => $params['CountryCode']
                ));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber']) && isset($params['CompanyCode']) && isset($params['CountryCode'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'property' :
            $restPostfix = '/content/v1/item/property' . parseQueryString(array('ItemNumbers' => $params['itemNumber']));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'dimension' :
            $restPostfix = '/content/v1/item/dimension' . parseQueryString(array('ItemNumbers' => $params['itemNumber']));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'product' :
            $restPostfix = '/content/v1/item/product/' . $params['itemNumber'];
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'description' :
            $restPostfix = '/content/v1/item/' . $params['itemNumber'] . '/description';
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                if (!$response && isset($params['itemNumber'])) {
                    return getInformationFromIM ($paramsObj, true);
                }
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'owner' :
            $restPostfix = '/pricing/v1/item/owner' . parseQueryString(array(
                    'ItemNumbers' => $params['itemNumber']
                ));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                return $response;
            }
            echo jsonResult($response);
            break;
        case 'inventory' :
            $restPostfix = '/pricing/v1/item/listinginfo' . parseQueryString(array(
                    'ItemNumbers' => $params['itemNumber']
                ));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            if ($returnResponse) {
                return $response;
            }
            echo jsonResult($response);
            break;
        default:
            echo 'Action Exception';
            return null;
    }
}

$app->get('/api/getRwProductList', function () {
    global $app;
    global $config;
    $params = $app->request->params();
    $params['format'] = 'json';
    $params['Manufactory'] = 2177;  //  2177表示rosewill品牌
    if (!isset($params['ItemCreationDateFrom'])) {
        $params['ItemCreationDateFrom'] = '2000-01-16';
    }
    //    ItemCreationDateTo
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );
    $response = CallAPI('GET', $config['rwListUrl'] . parseQueryString($params), $header);

    echo json_encode(array(
        "status" => "success",
        'count' => count($response['ResultList']),
        'DataCollection' => $response['ResultList']
    ));
});

$app->post('/api/proceedRestData', 'proceedRestData');
function proceedRestData () {
    global $app;
    global $input;
    $action = $input['action'];
    $method = $input['method'];
    $requestBody = isset($input['requestBody']) ? $input['requestBody'] : array();
    $apiUrl = $input['apiUrl'];
    $restPostfix = $input['restPostfix'];
    $consumerKey = $input['consumerKey'];
    $consumerSecret = $input['consumerSecret'];
    $page = $app->request->get('page') ? $app->request->get('page') : 1;
    $rowsPerPage = $app->request->get('rowsPerPage') ? $app->request->get('rowsPerPage') : 10;

    try {
        $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
        $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
        $oauthClient->enableDebug();

        $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
        switch (strtoupper($method)) {
            case 'GET':
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

                switch ($action) {
                    case 'getProductList' :
                        $response = parseMagentoJson(json_decode($oauthClient->getLastResponse(), true));
                        if (count($response) > 0) {
                            $response = writeItemNumberToLocal($response);
                        }
                        break;
                    default :
                        $response = json_decode(json_decode($oauthClient->getLastResponse(), true), true);
                }

                echo json_encode(array(
                    'status' => 'success',
                    'url' => $apiUrl . $restPostfix,
                    'DataCollection' => $response
                ));
                break;
            case 'PUT':
                $oauthClient->fetch(
                    $apiUrl . $restPostfix,
                    json_encode($requestBody),
                    OAUTH_HTTP_METHOD_PUT,
                    array('Content-Type' => 'application/json', 'Accept' => 'application/json')
                );
                $responseInfo = $oauthClient->getLastResponseInfo();
                $responseInfo['restPostfix'] = $restPostfix;
                echo json_encode($responseInfo);
                break;
            case 'POST':
                $oauthClient->fetch(
                    $apiUrl . $restPostfix,
                    json_encode($requestBody),
                    OAUTH_HTTP_METHOD_POST,
                    array('Content-Type' => 'application/json', 'Accept' => 'application/json')
                );
                $responseInfo = $oauthClient->getLastResponseInfo();
                $responseInfo['restPostfix'] = $restPostfix;
                echo json_encode($responseInfo);
                break;
        }
    } catch (OAuthException $e) {
        print_r($e);
        echo $e->{'debugInfo'}->{'body_recv'};
    }
}

$app->post('/api/uploadProductImages', function () {
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
});

$app->post('/api/checkServerItemStatus', function () {
    global $input;
    $response = array();
    $count = 0;
    foreach ($input AS $key => $itemObject) {
        $response[$key] = compareLocalItemNumber($itemObject);
        if (!$response[$key]['exists']) {
            $count++;
        }
    }
    echo json_encode(array(
        'status' => 'success',
        'count' => count($response),
        'notExistsCount' => $count,
        'DataCollection' => $response
    ));
});

$app->get('/api/retrieveAttributeSetmappingTable', 'retrieveAttributeSetmappingTable');
function retrieveAttributeSetmappingTable ($returnResponse = false) {
    global $config;
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );
    $response = CallAPI('GET', $config['pimUrlBase'] . $config['pimAttributeSetRestPostfix'], $header);
    if ($returnResponse) {
        return $response;
    }
    echo json_encode($response);
}

$app->post('/api/uploadExcelAndImport', function () {
    global $config;
    global $input;
//    base64_decode($input['base64']), $input['filename'], $input['filetype']
    file_put_contents($config['tempFileDirectory'] . '/' . $input['filename'], base64_decode($input['base64']));
    $excelArray = parseCsvIntoArray($config['tempFileDirectory'] . '/' . $input['filename']);
    $titleInfo = getTitleFromExcelArray($excelArray);
    echo json_encode(array(
        'status' => 'success',
        'TitleInfo' => $titleInfo,
        'DataCollection' => getUsefulDataFromExcel($excelArray, $titleInfo)
    ));
    unlink($config['tempFileDirectory'] . '/' . $input['filename']);
});

function getTitleFromExcelArray ($excelArray) {
    if (!is_array($excelArray)) {
        return false;
    }
    foreach ($excelArray AS $index => $rowData) {
        // 每筆 rowData都是array of array
        $checkResult = checkIsTitleRow($rowData[0], $index);
        if ($checkResult['isTitle']) {
            return $checkResult;
        }
    }
    return false;
}

function checkIsTitleRow ($rowData, $index) {
    $emptyCount = 0;
    $indexFirstWithValue = false;
    $indexLastWithValue = false;
    $title = array();
    foreach ($rowData AS $columnIndex => $column) {
        $columnData = trim($column);
        if (empty($columnData)) {
            $emptyCount++;
        } else {
            if (!$indexFirstWithValue) {
                $indexFirstWithValue = $columnIndex;
            }
            $indexLastWithValue = $columnIndex;
            array_push($title, $column);
        }
    }

    if ($indexFirstWithValue == false || $indexLastWithValue == false) {
        return array('isTitle' => false);
    }
//    if ($emptyCount > count($rowData) /2) {
//        return array('isTitle' => false);
//    }

    return array(
        'isTitle' => true,
        'rowIndex' => $index,
        'indexToStart' => $indexFirstWithValue,
        'indexLastWithValue' => $indexLastWithValue,
        'title' => $title
    );
}

function getUsefulDataFromExcel ($excelArray, $titleInfo) {
    $responseArray = array();
    foreach ($excelArray AS $index => $rowData) {
        //  跳過非要匯入的data
        if ($index > $titleInfo['rowIndex']) {
            $titleKey = 0;
            $columnCount = 0;
            $responseRowData = array();
            for ($x = $titleInfo['indexToStart']; $x <= $titleInfo['indexLastWithValue']; $x++) {
                $responseRowData[$titleInfo['title'][$titleKey]] = trim($excelArray[$index][0][$x]);
                $titleKey++;
                if (empty($excelArray[$index][0][$x])) {
                    $columnCount++;
                }
            }
            if ($columnCount < ($titleInfo['indexLastWithValue'] - $titleInfo['indexToStart'] + 1)) {
                array_push($responseArray, $responseRowData);
            }
        }
    }
    return $responseArray;
}

$app->post('/api/getCombinationInfo', function () {
    global $input;
    $response = $input;

    $actionsArray = array('baseinfo', 'property', 'dimension', 'getImages', 'product', 'description', 'intelligence', 'price', 'inventory');
    foreach ($actionsArray AS $action) {
        $params = array(
            'itemNumber' => $input['ItemNumber'],
            'action' => $action
        );
        switch ($action) {
            case 'baseinfo' :
                $responseBaseinfo = getInformationFromIM($params, true);
                $response['baseinfo'] = $responseBaseinfo;
                break;
            case 'property' :
                $responseProperty = getInformationFromIM($params, true);
                if (isset($responseProperty['ItemProperties'][0]['Properties'])) {
                    $response['property'] = $responseProperty['ItemProperties'][0]['Properties'];
                } else {
                    $response['property'] = null;
                }
                break;
            case 'dimension' :
                $responseDimension = getInformationFromIM($params, true);
                if (isset($responseDimension['ItemDimensions'][0])) {
                    $response['dimension'] = $responseDimension['ItemDimensions'][0];
                } else {
                    $response['dimension'] = null;
                }
                break;
            case 'getImages' :
                $responseImages = getInformationFromIM($params, true);
                $response['images'] = $responseImages;
                break;
            case 'product' :
                $responseProduct = getInformationFromIM($params, true);
                if (isset($responseProduct['ProductInfos'][0])) {
                    $response['ProductInfos'] = $responseProduct['ProductInfos'][0];
                } else {
                    $response['ProductInfos'] = null;
                }
                break;
            case 'description' ;
                $responseDescription = getInformationFromIM($params, true);
                $response['description'] = $responseDescription;
                break;
            case 'intelligence' :
                $responseIntelligence = getInformationFromIntelligence($params['itemNumber'], true);
                if (isset($responseIntelligence['detailinfo'][0])) {
                    $response['intelligence'] = $responseIntelligence['detailinfo'][0];
                } else {
                    $response['dimension'] = null;
                }
                break;
            case 'price' :
                $params['CompanyCode'] = 1003;
                $params['CountryCode'] = 'USA';
                $responsePrice = getInformationFromIM($params, true);
                if (isset($responsePrice['ItemPrices'][0])) {
                    $response['price'] = $responsePrice['ItemPrices'][0];
                }
                break;
            case 'inventory' :
                $responseInventory = getInformationFromIM($params, true);
                $response['inventory'] = $responseInventory;
                break;
        }
    }

    echo json_encode($response);
});

$app->get('/api/getInformationFromIntelligence', 'getInformationFromIntelligence');
function getInformationFromIntelligence ($itemNumberTemp = '', $returnResponse = false) {
    global $app;
    global $intelligenceBaseUrl;
    $itemNumber = $app->request->get('itemNumber') ? $app->request->get('itemNumber') : $itemNumberTemp;
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

$app->get('/api/getAttributesById', function ($returnResponse = false) {
    global $config;
    global $app;
    $params = $app->request->params();
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );
    $response = CallAPI('GET', $config['pimUrlBase'] . $config['pimAttributesByIdRestPostfix'] . parseQueryString(array('id' => $params['id'])), $header);
    if ($returnResponse) {
        return $response;
    }
    echo json_encode($response);
});

$app->get('/api/getAttributeOptionsByAttrCode', function ($returnResponse = false) {
    global $config;
    global $app;
    $params = $app->request->params();
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );
    $response = CallAPI('GET', $config['pimUrlBase'] . $config['pimAttributeOptionsByAttrCodeRestPostfix'] . parseQueryString(array('attributeCodes' => $params['attributeCodes'])), $header);
    if ($returnResponse) {
        return $response;
    }
    echo json_encode($response);
});