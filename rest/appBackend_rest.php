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
