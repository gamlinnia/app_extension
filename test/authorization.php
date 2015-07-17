<?php

$nonce = substr(md5(uniqid('nonce_', true)),0,16);
$temprealm="http://10.16.90.47/magento/index.php/api/rest/products";
$realm=urlencode($temprealm);
$oauth_version="1.0";
$oauth_signature_method="HMAC-SHA1";
$oauth_consumer_key="lro2hnoh3c8luvhcr49j6qgygmyvw7e3";
$oauth_access_token="xbqe4wnu3zv357gimpdnuejvcbtk51ni";
$oauth_method="GET";
$oauth_timestamp=time();
$algo="sha1";
$key="sb88hfdihyg25ipt1by559yzbj2m3861&s7uhaheu8nrx961oxg6uc3os4zgyc2tm"; //consumer secret & token secret //Both are used in generate signature
$data="oauth_consumer_key=".$oauth_consumer_key."&oauth_nonce=".$nonce."&oauth_signature_method=".$oauth_signature_method."&oauth_timestamp=".$oauth_timestamp."&oauth_token=".$oauth_access_token."&oauth_version=".$oauth_version;

$send_data=$oauth_method."&".$realm."&".urlencode($data);
$sign=hash_hmac($algo,$send_data,$key,1); // consumer key and token secrat used here
$fin_sign=base64_encode($sign);
$curl = curl_init();



curl_setopt($curl,CURLOPT_HTTPHEADER,array('Authorization : OAuth realm='.$realm.', oauth_version="1.0", oauth_signature_method="HMAC-SHA1", oauth_nonce="'.$nonce.'", oauth_timestamp="'.$oauth_timestamp.'", oauth_consumer_key='.$oauth_consumer_key.', oauth_token='.$oauth_access_token.', oauth_signature="'.$fin_sign.'"'));

curl_setopt ($curl, CURLOPT_URL,$temprealm);
$xml=curl_exec($curl);