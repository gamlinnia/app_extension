<?php

ini_set('max_execution_time', 2400);

$config = array(
    'debugMode' => false,
    'ItemNumberStoreDirectory' => 'itemNumbers/',
    'tempFileDirectory' => 'tempFiles',
    'rwListUrl' => 'http://api.newegg.org/ExternalMarketplace/v1/RosewillItem',
    'pimUrlBase' => 'http://rwpim.silksoftware.net/',
    'pimAttributeSetRestPostfix' => 'helloworld',
    'pimAttributesByIdRestPostfix' => 'helloworld/index/getAttributes',
    'pimAttributeOptionsByAttrCodeRestPostfix' => 'helloworld/index/getAttributeOptions',
    'apiUrl' => 'http://rwpim.silksoftware.net/api/rest',
    'consumerKey' => 'eefc539175f5024958c657c1aa93c879',
    'consumerSecret' => 'a9df3a118519c28ca36007f70e039240'
);

