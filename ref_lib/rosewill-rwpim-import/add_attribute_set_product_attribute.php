<?php


@set_time_limit(0);

require_once 'app/Mage.php';
umask(0);
 
Mage::app('default'); 


//NEItemPropertiesValues.csv

$file = fopen('NEItemPropertiesValues.csv','r'); 


$products=array();

$products_entity=array();

$index=0;

while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容

$index++;

if($index==1) {
	continue;
}

	$data[0]=trim($data[0]);

	if(!isset($products[$data[0]])) {
			$products[$data[0]]=array();


			//$product=Mage::getModel('catalog/product')->load($data[0],'sku');

$product_collection=Mage::getModel('catalog/product')->getCollection()
		->addFieldToFilter('sku',$data[0]);


			if(count($product_collection)>0) {
				$products_entity[$data[0]]=$product_collection->getFirstItem();
			}
			else {
				//echo($data[0].'can not find.
//<br/>
//');
				
			}


	}

	if(trim($data[4])) {
			$products[$data[0]][$data[2]]=$data[4];
	}
	else {

		if(trim($data[5])) {

			$products[$data[0]][$data[2]]=$data[5];
			
		}

	}




}


$count=0;
$save_text_count=0;

$saveText=false;


foreach($products as $key=>$val) {

$saveText=false;

	
	$product=$products_entity[$key];



	if(!$product) {

		continue;
		
	}

	//$product=Mage::getModel('catalog/product')->load($product->getId());


			$groups=Mage::getModel('eavattributegroup/group')->getCollection()
				->addFieldToFilter('attribute_set_id',$product->getData('attribute_set_id'))
				->setOrder('attribute_group_id','ASC');

			$group=$groups->getLastItem();

					$group_attributes=Mage::getModel('eavattributegroup/attribute')->getCollection()
						->addFieldToFilter('attribute_group_id',$group->getId());





		   foreach($group_attributes as $group_attribute) {

					$attribute=Mage::getModel('catalog/resource_eav_attribute')->load($group_attribute->getData('attribute_id'));

					//print_r($attribute->getData());

					foreach($val as $k=>$v) {


						$count++;


						if(count(explode($attribute->getData('frontend_label'),$k))>1) {


							if($attribute->getData('frontend_input')=='multiselect') {
								
							}


							if($attribute->getData('frontend_input')!='select'&&$attribute->getData('frontend_input')!='multiselect') {



														$save_text_count++;

										$product->setData($attribute->getData('attribute_code'),$v);

										$saveText=true;

										print_r($attribute->getData('frontend_label').'==='.$attribute->getData('attribute_code').'==='.$v.'<br/>');

										print_r($product->getSku().'<br/>');

							}

							if($attribute->getData('frontend_input')=='select') {

										$attributeArray=array();

										foreach($attribute->getSource()->getAllOptions(false) as $option){  
														$attributeArray[$option['label']] = $option['value'];  
										}  

										//$product->setData($attribute->getData('attribute_code'),$attributeArray[$v]);
								
							}




								//print_r($attribute->getData('frontend_input').'<br/>');

							
						}else {


							//print_r($attribute->getData('frontend_label').'==='.$k.'<br/>');


							
						}



					}



		   }



			if($saveText) {
								$product->save();
								//exit();
			}






}

print_r($count.'==='.$save_text_count);



?>