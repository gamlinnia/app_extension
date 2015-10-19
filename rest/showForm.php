
<?php

$app->get('/api/testShowForm1', function () {
    require_once 'config/db_config.php';
    //$count = $db->getOne('SELECT COUNT(*) FROM `custom_form` WHERE `form_name` = ?', array($formName));
    $response = $db->getAll('SELECT DISTINCT `form_name` FROM `custom_form`');
    //var_dump($response[0]);
    $result = [];
    foreach ($response AS $key => $object) {
        $result[] = $object['form_name'];
    }
    echo json_encode(array(
        'status' => 'success',
        'DataCollection' => $result
    ));
    $db->disconnect();
});