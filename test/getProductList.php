<?php
/**
 * Example of products list retrieve using Customer account via Magento REST API. OAuth authorization is used
 */
$callbackUrl = "http://10.16.90.47/rosewill/test/getProductList.php";
$temporaryCredentialsRequestUrl = "http://rwpim.silksoftware.net/index.php/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
$adminAuthorizationUrl = 'http://rwpim.silksoftware.net/index.php/admin/oauth_authorize';
$accessTokenRequestUrl = 'http://rwpim.silksoftware.net/index.php/oauth/token';
$apiUrl = 'http://rwpim.silksoftware.net/api/rest';
$consumerKey = 'eefc539175f5024958c657c1aa93c879';
$consumerSecret = 'a9df3a118519c28ca36007f70e039240';

session_start();
if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
    $_SESSION['state'] = 0;
}
try {
    $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
    $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
    $oauthClient->enableDebug();

    if (!isset($_GET['oauth_token']) && !$_SESSION['state']) {
        $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
        $_SESSION['secret'] = $requestToken['oauth_token_secret'];
        $_SESSION['state'] = 1;
        header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
        exit;
    } else if ($_SESSION['state'] == 1) {
        $oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
        $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
        $_SESSION['state'] = 2;
        $_SESSION['token'] = $accessToken['oauth_token'];
        $_SESSION['secret'] = $accessToken['oauth_token_secret'];
        header('Location: ' . $callbackUrl);
        exit;
    } else {
        $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
        $resourceUrl = "$apiUrl/products";
        $oauthClient->fetch($resourceUrl);
        $oauthClient->fetch($resourceUrl, array(), 'GET', array('Content-Type' => 'application/xml', 'Accept' => 'application/xml'));
        var_dump($oauthClient->getLastResponse());
//        var_dump($oauthClient->getLastResponseInfo());
//        $productsList = json_decode($oauthClient->getLastResponse());
//        print_r($productsList);
    }
} catch (OAuthException $e) {
    print_r($e);
}