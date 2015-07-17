<?php
/**
 * Example of products list retrieve using Customer account via Magento REST API. OAuth authorization is used
 */
$callbackUrl = "http://10.16.90.47/rosewill/test/getProductList2.php";
$temporaryCredentialsRequestUrl = "http://10.16.90.47/magento/index.php/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
$adminAuthorizationUrl = 'http://10.16.90.47/magento/index.php/admin/oauth_authorize';
$accessTokenRequestUrl = 'http://10.16.90.47/magento/index.php/oauth/token';
$apiUrl = 'http://10.16.90.47/magento/api/rest';
$consumerKey = 'f5d00e14c41ee9632d528f59b243d2e1';
$consumerSecret = 'bdbd0843217e0189a4f812961ed6b52e';

session_start();

if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
    error_log('state 0');
    $_SESSION['state'] = 0;
}
try {
    $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
    $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
    $oauthClient->enableDebug();

    if (!isset($_GET['oauth_token']) && !$_SESSION['state']) {
        $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
        $_SESSION['secret'] = $requestToken['oauth_token_secret'];
        error_log('state 1:');
        error_log('secret: ' . $_SESSION['secret']);
        $_SESSION['state'] = 1;
        header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
        exit;
    } else if ($_SESSION['state'] == 1) {
        $oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
        error_log('state 1 token secret set: ' . $_SESSION['secret']);
        error_log('state 1 oauth_token: ' . $_GET['oauth_token']);
        error_log('go to accessTokenRequestUrl' . $accessTokenRequestUrl);
        $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
        $_SESSION['state'] = 2;
        $_SESSION['token'] = $accessToken['oauth_token'];
        $_SESSION['secret'] = $accessToken['oauth_token_secret'];
        header('Location: ' . $callbackUrl);
        exit;
    } else {
        $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
        $resourceUrl = "$apiUrl/products";
        $oauthClient->fetch($resourceUrl, array(), 'GET', array('Content-Type' => 'application/json', 'Accept' => 'application/json'));
        var_dump($oauthClient->getLastResponse());
        session_destroy();
    }
} catch (OAuthException $e) {
    print_r($e);
}