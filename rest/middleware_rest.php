<?php

$app->post('/api/insertFormValue', function () {
    require_once 'config/db_config.php';
    global $input;
    $formData = parseFormData($input['value']);
    $response = $db->query('INSERT INTO `custom_form` (`form_name`, `value`) VALUES (?, ?)', array($input['form_name'], json_encode($formData)));
    if ($response < 1) {
        echo jsonMessage('danger', 'db error');
        return;
    }

    sendMailByForm($input['form_name'], $formData);
    echo jsonMessage('success', 'Form has been successfully submitted');
    $db->disconnect();
});

function parseFormData ($formArray) {
    $transformArray = array();
    foreach (json_decode($formArray, true) AS $object) {
        $transformArray[$object['name']] = $object['value'];
    }
    return $transformArray;
}

$app->get('/api/getFormList/:formName', function ($formName) {
    require_once 'config/db_config.php';
    global $app;
    $page = $app->request->get('page') ? $app->request->get('page') : 1;
    $page = (int)$page;
    $rowPerPage = $app->request->get('rowPerPage') ? $app->request->get('rowPerPage') : 10;
    $rowPerPage = (int)$rowPerPage;
    $count = $db->getOne('SELECT COUNT(*) FROM `custom_form` WHERE `form_name` = ?', array($formName));
    $response = $db->getAll('SELECT `id`, `form_name`, `value`, `mtime`, `ctime` FROM `custom_form` WHERE `form_name` = ? ORDER BY `id` DESC, `mtime` DESC LIMIT ?, ?', array($formName, ($page-1)*$rowPerPage, $page*$rowPerPage));
    foreach ($response AS $key => $object) {
        if (isJson($object['value'])) {
            $response[$key]['value'] = json_decode($object['value'], true);
        }
    }
    echo json_encode(array(
        'status' => 'success',
        'count' => (int)$count,
        'page' => $page,
        'limit' => $rowPerPage,
        'totalPage' => ceil($count / $rowPerPage),
        'DataCollection' => $response
    ));
    $db->disconnect();
});

$app->get('/api/getForm/:id', function ($id) {
    require_once 'config/db_config.php';
    $response = $db->getRow('SELECT `id`, `form_name`, `value`, `mtime`, `ctime` FROM `custom_form` WHERE `id` = ?', array($id));
    if (isJson($response['value'])) {
        $response['value'] = json_decode($response['value'], true);
    }
    echo jsonResult($response);
    $db->disconnect();
});

$app->get('/api/searchForm/:searchString', function ($searchString) {
    require_once 'config/db_config.php';
    global $app;
    $page = $app->request->get('page') ? $app->request->get('page') : 1;
    $page = (int)$page;
    $rowPerPage = $app->request->get('rowPerPage') ? $app->request->get('rowPerPage') : 10;
    $rowPerPage = (int)$rowPerPage;
    $count = $db->getOne('SELECT COUNT(*) FROM `custom_form` WHERE `value` REGEXP ?', array($searchString));
    $response = $db->getAll('SELECT `id`, `form_name`, `value`, `mtime`, `ctime` FROM `custom_form` WHERE `value` REGEXP ? ORDER BY `id` DESC, `mtime` DESC LIMIT ?, ?', array($searchString, ($page-1)*$rowPerPage, $page*$rowPerPage));
    foreach ($response AS $key => $object) {
        if (isJson($object['value'])) {
            $response[$key]['value'] = json_decode($object['value'], true);
        }
    }
    echo json_encode(array(
        'status' => 'success',
        'count' => (int)$count,
        'page' => $page,
        'limit' => $rowPerPage,
        'totalPage' => ceil($count / $rowPerPage),
        'DataCollection' => $response
    ));
    $db->disconnect();
});

$app->put('/api/updateForm/:id', function ($id) {
    require_once 'config/db_config.php';
    $result = $db->getRow('SELECT `form_name`, `value` FROM `custom_form` WHERE `id` = ?', array($id));
    if (isJson($result['value'])) {
        $result['value'] = json_decode($result['value'], true);
    }
    global $input;
    if (count($input) < 1) {
        echo jsonMessage('danger', 'invalid request body');
        return;
    }
    foreach ($input AS $key => $value) {
        $result['value'][$key] = $value;
    }

    $response = $db->query('UPDATE `custom_form` SET `value` = ? WHERE `id` = ?', array(json_encode($result['value']), $id));
    if ($response < 1) {
        echo jsonMessage('danger', 'db error');
        return;
    }
    echo jsonMessage('success', 'success updated id: ' . $id);
    $db->disconnect();
});

$app->delete('/api/delForm/:id', function ($id) {
    require_once 'config/db_config.php';
    $response = $db->query('DELETE FROM `custom_form` WHERE `id` = ?', array($id));
    if ($response < 1) {
        echo jsonMessage('danger', 'db error');
        return;
    }
    echo jsonMessage('success', 'success deleted id: ' . $id);
    $db->disconnect();
});

function sendMailByForm ($formName, $formData) {
    global $config;
    $formToEmail = array('contactUs', 'i-reviewed-rosewill');

    if (!in_array($formName, $formToEmail)) {
        return false;
    }

    $actionKeyByForm = array('contactUs' => 'Purpose for Contact');
    $action = isset($actionKeyByForm[$formName]) ? $formData[$actionKeyByForm[$formName]] : $formName;

    if ($config['debugMode']) {
        $recipient_array = array(
            'to' => array('Li.L.Liu@newegg.com'),
            'bcc' => array('Reyna.C.Chu@newegg.com', 'Henry.H.Wu@newegg.com')
        );
    } else {
        switch ($action) {
            case 'Request to Return Merchandise':
                $recipient_array = array(
                    'to' => array('rma@rosewill.com'),
                    'bcc' => array('Li.L.Liu@newegg.com', 'Henry.H.Wu@newegg.com')
                );
                break;
            case 'Request to Review Product':
                $recipient_array = array(
                    'to' => array('review@rosewill.com'),
                    'bcc' => array('Li.L.Liu@newegg.com', 'Henry.H.Wu@newegg.com')
                );
                break;
            case 'Sponsorship Request':
                $recipient_array = array(
                    'to' => array('mkt@rosewill.com'),
                    'bcc' => array('Li.L.Liu@newegg.com', 'Henry.H.Wu@newegg.com')
                );
                break;
            case 'Vendor or Business Contact':
                $recipient_array = array(
                    'to' => array('sales@rosewill.com'),
                    'bcc' => array('Li.L.Liu@newegg.com', 'Henry.H.Wu@newegg.com')
                );
                break;
            case 'Other':
                $recipient_array = array(
                    'to' => array('mkt@rosewill.com'),
                    'bcc' => array('Li.L.Liu@newegg.com', 'Henry.H.Wu@newegg.com')
                );
                break;
            case 'i-reviewed-rosewill' :
                $recipient_array = array(
                    'to' => array('mkt@rosewill.com', 'sales@rosewill.com', 'consumerreviews@rosewill.com'),
                    'bcc' => array('Reyna.C.Chu@newegg.com', 'Henry.H.Wu@newegg.com', 'Li.L.Liu@newegg.com')
                );
                break;
            default:
                return false;
        }
    }

    require_once 'class/Email.class.php';
    require_once 'class/EmailFactory.class.php' ;

    /* SMTP server name, port, user/passwd */
    $smtpInfo = array("host" => "127.0.0.1",
        "port" => "25",
        "auth" => false);
    $emailFactory = EmailFactory::getEmailFactory($smtpInfo);

    /* $email = class Email */
    $email = $emailFactory->getEmail($action, $recipient_array);
    $content = templateReplace($action, $formData);
    $email->setContent($content);
    error_log('rw mail to ' . join(', ', $recipient_array['to']));
    $email->sendMail();

    if (isset($formData['sendACopyToMe']) && $formData['sendACopyToMe'] == 'yes') {
        if (!isset($formData['email'])) {
            return false;
        }
        $copyEmail = $emailFactory->getEmail($action,  array(
            'to' => array($formData['email'])
        ));
        $copyEmail->setContent($content);
        $copyEmail->sendMail();
    }
    return true;
}

function templateReplace ($action, $formData) {
    require_once 'lib/phpQuery/phpQuery/phpQuery.php';
    $content = file_get_contents('email/content/template.html');
    $doc = phpQuery::newDocumentHTML($content);

    $contentTitle = array(
        'i-reviewed-rosewill' => 'I reviewed a Rosewill product!'
    );
    (isset($contentTitle[$action])) ? $doc['.descriptionTitle'] = $contentTitle[$action] : $doc['.descriptionTitle'] = $action;

    $emailContent = array();
    foreach ($formData AS $key => $value) {
        if ($key != 'sendACopyToMe' && trim($value) != '') {
            array_push($emailContent, '<p>' . $key . ': ' . $value . '</p>');
        }
    }
    $doc['.description'] = join('', $emailContent);
    $doc['.logoImage']->attr('src', 'images/rosewilllogo.png');
    return $doc;
}
