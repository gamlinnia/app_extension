<?php


$file1 = fopen('Download_1400_SKUs.csv','r'); 

$file2 = fopen('export_all_products.csv','r'); 



$index2=0;

while ($data = fgetcsv($file2)) { //每次读取CSV里面的一行内容

$index2++;
	if($index2>1) {
		break;
	}

$data_new=array();

foreach($data as $key=>$val) {
	 $data_new[]='"' . str_replace(array('"', '\\'), array('""', '\\\\'), trim($val)) . '"';
}
$data =$data_new;



	//print_r($data);

	echo(implode(',',$data));

}


$index1=0;

while ($data = fgetcsv($file1)) { //每次读取CSV里面的一行内容





$index1++;

	if($index1==1) {
		continue;
	}

$data_new=array();

foreach($data as $key=>$val) {
	 $data_new[]='"' . str_replace(array('"', '\\'), array('""', '\\\\'), trim($val)) . '"';
}
$data =$data_new;





echo('
');


$data2=array();
$data2[0]='admin';//store
$data2[1]='base';//websites
$data2[2]=$data[48];//attribute_set
$data2[3]='simple';//type
$data2[4]='';//category_ids
$data2[5]=$data[6];//sku
$data2[6]=0;//has_options
$data2[7]=$data[0];//name
$data2[8]='';//meta_title
$data2[9]='';//meta_description
$data2[10]='';//image
$data2[11]='';//small_image
$data2[12]='';//thumbnail
$data2[13]='';//url_key
$data2[14]='';//url_path
$data2[15]='';//image_label
$data2[16]='';//small_image_label
$data2[17]='';//thumbnail_label
$data2[18]=$data[25];//country_of_manufacture
$data2[19]='';//msrp_enabled
$data2[20]='';//msrp_display_actual_price_type
$data2[21]='';//gift_message_available
$data2[22]=$data[11];//name_long
$data2[23]=$data[5];//model_number
$data2[24]='';//product_number
$data2[25]=$data[19];//upc_number
$data2[26]=$data[7];//weight_net
$data2[27]='';//pspec_connection_type
$data2[28]='';//pspec_wireless_standard
$data2[29]='';//pspec_networking_protocol
$data2[30]='';//pspec_video_capture_resolution
$data2[31]='';//pspec_os_supported
$data2[32]=$data[9];//price
$data2[33]=$data[23];//weight
$data2[34]=$data[8];//msrp
$data2[35]=$data[16];//manufacturer
$data2[36]='Enabled';//status
$data2[37]='"Catalog, Search"';//visibility
$data2[38]='None';//tax_class_id
$data2[39]=$data[4];//brand
$data2[40]='';//enable_rma
$data2[41]='';//pspec_networked_camera
$data2[42]='';//pspec_max_resolution
$data2[43]='';//pspec_frame_rate
$data2[44]='';//pspec_lens_type
$data2[45]='';//pspec_night_vision
$data2[46]='';//pspec_audio
$data2[47]='';//pspec_power
$data2[48]=$data[2];//description
$data2[49]=$data[1];//short_description
$data2[50]='';//meta_keyword
$data2[51]='';//pspec_pan_tilt_zoom
$data2[52]='';//pspec_night_vision_distance
$data2[53]='';//news_from_date
$data2[54]='';//news_to_date
$data2[55]=1000;//qty
$data2[56]=0;//min_qty
$data2[57]='';//use_config_min_qty
$data2[58]='';//is_qty_decimal
$data2[59]='';//backorders
$data2[60]='';//use_config_backorders
$data2[61]='';//min_sale_qty
$data2[62]='';//use_config_min_sale_qty
$data2[63]='';//max_sale_qty
$data2[64]='';//use_config_max_sale_qty
$data2[65]=1;//is_in_stock
$data2[66]='';//low_stock_date
$data2[67]='';//notify_stock_qty
$data2[68]='';//use_config_notify_stock_qty
$data2[69]='';//manage_stock
$data2[70]='';//use_config_manage_stock
$data2[71]='';//stock_status_changed_auto
$data2[72]='';//use_config_qty_increments
$data2[73]='';//qty_increments
$data2[74]='';//use_config_enable_qty_inc
$data2[75]='';//enable_qty_increments
$data2[76]='';//is_decimal_divided
$data2[77]='';//stock_status_changed_automatically
$data2[78]='';//use_config_enable_qty_increments
$data2[79]='';//product_name
$data2[80]=0;//store_id
$data2[81]='simple';//product_type_id
$data2[82]='';//product_status_changed
$data2[83]='';//product_changed_websites
$data2[84]=$data[3];//NE Description
$data2[85]=$data[10];//NE Item Descriptoin
$data2[86]=$data[14];//subcategory

	//print_r($data);

	echo(implode(',',$data2));


}


?>