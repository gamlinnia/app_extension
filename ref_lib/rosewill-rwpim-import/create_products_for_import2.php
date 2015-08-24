<?php


$file = fopen('SKUs.csv','r'); 

$index=0;

while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容

	$index++;

	if($index==1) {

		echo('store,websites,type,status,visibility,tax_class_id,product_type_id,qty,is_in_stock,');
		echo(implode(',',$data));
		
	}
	else {
		
	$data_new=array();

	foreach($data as $key=>$val) {

		if($key==31||$key==32) {

			if($val=='Y') {
				$val='Yes';
			}
			if($val=='N') {
				$val='No';
			}
			
		}

		if($key==29) {


			if($val=='CHN'||$val=='CHN,TWN') {
				$val='China';
			}

			if($val=='HKG') {
				$val='Hong Kong SAR China';
			}

			if($val=='TWN') {
				$val='Taiwan';
			}

			if($val=='USA') {
				$val='United States';
			}

			if($val=='CAN') {
				$val='Canada';
			}

			
		}


		if($key==45) {

			if($val=='S') {
				$val='Small';
			}
			if($val=='L') {
				$val='Large';
			}
			
		}

		 $data_new[]='"' . str_replace(array('"', '\\'), array('""', '\\\\'), trim($val)) . '"';
	}
	$data =$data_new;

		echo('
admin,base,simple,Enabled,"Catalog, Search",None,simple,1000,1,');
		echo(implode(',',$data));


	}







}




?>