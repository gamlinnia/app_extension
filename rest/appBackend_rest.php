<?php

$app->get('/api/getInformationFromIM', function () {
    global $app;
    global $IMBaseUrl;
    $params = $app->request->params();
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
            echo jsonResult($response['Images']);
            break;
        case 'baseinfo' :
            $restPostfix = '/content/v1/item/baseinfo/' . $params['itemNumber'];
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
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
            echo jsonResult($response);
            break;
        case 'property' :
            $restPostfix = '/content/v1/item/property' . parseQueryString(array('ItemNumbers' => $params['itemNumber']));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            echo jsonResult($response);
            break;
        case 'dimension' :
            $restPostfix = '/content/v1/item/dimension' . parseQueryString(array('ItemNumbers' => $params['itemNumber']));
            /*curl https 會出現問題*/
            $response = CallAPI('GET', $IMBaseUrl . $restPostfix, $header);
            echo jsonResult($response);
            break;
        default:
            echo 'Action Exception';
            return null;
    }
});

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
    $page = $app->request->get('page');
    $rowsPerPage = $app->request->get('rowsPerPage');

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
                echo json_encode(array(
                    'status' => 'success',
                    'DataCollection' => parseMagentoJson(json_decode($oauthClient->getLastResponse(), true))
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
        }
    } catch (OAuthException $e) {
        print_r($e);
        echo $e->{'debugInfo'}->{'body_recv'};
    }
}

function retrieveAttributeSetmappingTable () {
    global $config;
    $header = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );
    $response = CallAPI('GET', $config['pimUrlBase'] . $config['pimAttributeSetRestPostfix'], $header);
}