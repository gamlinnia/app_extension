<?php

date_default_timezone_set('UTC');

function moreThanSpecificDays ($date_string_1, $date_string_2, $specific_days = 2) {
    $date_1 = new DateTime($date_string_1);
    $date_2 = new DateTime($date_string_2);

    $interval = $date_1->diff($date_2);
    $days = $interval->days;
    if ($days >= $specific_days) {
//        echo "More than $specific_days days" . PHP_EOL;
        return true;
    }
    return false;
}

$validTitle = array('comments','cons','displayname','itemnumber','loginName','nickname','pros','rating','replycontent','reviewdate','title', 'hasmanufacturerresponse');

$row = 1;
if (($handle = fopen("CustomerReview.csv", "r")) !== FALSE) {
    $result = array();
    $index = array();
    while (($data = fgetcsv($handle, 200000, ",")) !== FALSE) {
        if($row == 1){
            foreach($data as $key => $value){
                foreach($validTitle as $each){
                    if($value == $each){
                        $index[$value] = $key;
                    }
                }
            }
        }
        else{
            $sku = $data[$index['itemnumber']];
            //timestamp in ms
            $reviewdate = $data[$index['reviewdate']];
            $reviewdate = $reviewdate / 1000;
            $reviewdate = date("Y-m-d H:i:s", $reviewdate);
            $moreThan2Days = moreThanSpecificDays($reviewdate, 'now', 10);
            $rating = $data[$index['rating']];
            if($rating > 2 || $moreThan2Days){
                continue;
            }
            $result[$sku][] = array(
                'comments' => $data[$index['comments']],
                'cons' => $data[$index['cons']],
                'displayname' => $data[$index['displayname']],
                'itemnumber'=> $data[$index['itemnumber']],
                'loginName'=> $data[$index['loginName']],
                'nickname'=> $data[$index['nickname']],
                'pros'=> $data[$index['pros']],
                'rating'=> $data[$index['rating']],
                'replycontent'=> $data[$index['replycontent']],
                'reviewdate'=> $reviewdate,
                'title'=> $data[$index['title']],
                'hasmanufacturerresponse' => $data[$index['hasmanufacturerresponse']]
            );
        }
        $row++;
    }
    fclose($handle);
}

if($result){
    file_put_contents('result.json', json_encode($result));
}
var_dump($result);
