<?php

define('DB', 'assets');
define('COLLECTION', 'list');
define('connectionString', 'mongodb://domain:abc123@10.1.37.154:27017');

function mongoInsertion ($db, $collectionName, $dataArray, $options) {
    $mongo = new Mongo(connectionString);
    $collection = $mongo->$db->$collectionName;
    $rs  = $collection->insert($dataArray, $options);
    $rs['newDocID'] = $dataArray['_id']->{'$id'};
    $mongo->close();
    return $rs;
}

function mongoFindOne ($db, $collectionName, $query, $fields) {
    $mongo = new Mongo(connectionString);
    /*获得一个collection对象 $mongo->dbname->collname*/
    $collection = $mongo->$db->$collectionName;
    $rs = $collection->findOne($query, $fields);
    $mongo->close();
    return $rs;
}

function mongoFind ($db, $collectionName, $query, $fields) {
    $mongo = new Mongo(connectionString);
    /*获得一个collection对象 $mongo->dbname->collname*/
    $collection = $mongo->$db->$collectionName;
    $cursor = $collection->find($query, $fields);
    $mongo->close();
    return $cursor;
}

function mongoGroupByFind ($db, $collectionName, $keys, $initial, $reduce) {
    $mongo = new Mongo(connectionString);
    $collection = $mongo->$db->$collectionName;
    $cursor = $collection->group($keys, $initial, $reduce);
    $mongo->close();
    return $cursor;
}

function mongoUpdate ($db, $collectionName, $query, $newObj, $options) {
    $mongo = new Mongo(connectionString);
    $collection = $mongo->$db->$collectionName;
    $rs = $collection->update($query, $newObj, $options);
    $mongo->close();
    return $rs;
}

function mongoDeletion ($db, $collectionName, $query, $options) {
    $mongo = new Mongo(connectionString);
    $collection = $mongo->$db->$collectionName;
    $rs  = $collection->remove($query, $options);
    $mongo->close();
    return $rs;
}

function listDBs () {
    $mongo = new Mongo(connectionString);
    $dbs = $mongo->listDBs();
    $mongo->close();
    var_dump($dbs);
}
