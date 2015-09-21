<?php


@set_time_limit(0);

require_once 'app/Mage.php';
umask(0);
 
Mage::app('default'); 


$attribute_code_arr=array();

$attribute_code_arr[2]='us_pm';
//$attribute_code_arr[3]='ap_pm';

        $attribute_collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()->addFieldToFilter('attribute_code',array("in"=>$attribute_code_arr));

$attribute_ids=array();


foreach($attribute_collection as $attribute_id_index=> $attribute) {

	$attribute_ids[$attribute_id_index]   =	$attribute->getId();
}









$sets=Mage::getModel('eav/entity_attribute_set')->getCollection()->addFieldToFilter('entity_type_id',4);


$index=0;
foreach($sets as $set) {

$index++;



		$generals=Mage::getModel('eavattributegroup/group')->getCollection()
			->addFieldToFilter('attribute_group_name','General')
			->addFieldToFilter('attribute_set_id',$set->getId())
		;

		$general=$generals->getFirstItem();


$general_attributes=Mage::getModel('eavattributegroup/attribute')->getCollection()
	->addFieldToFilter('attribute_set_id',$set->getId())
	->addFieldToFilter('entity_type_id',4)
	->addFieldToFilter('attribute_group_id',$general->getId());

$sort_array=array();



foreach($general_attributes as $general_attribute) {


	if($general_attribute->getData('sort_order')!=1) {
		//$general_attribute->setData('sort_order',(int)$general_attribute->getData('sort_order')+2)->save();
	}


}

$sort_order=3;

//save attribute set group attribute
		foreach($attribute_ids as $attribute_id_index=> $attribute_id) {

		$group_attribute=Mage::getModel('eavattributegroup/attribute')
						->setData('entity_type_id',4)
						->setData('attribute_set_id',$set->getId())
						->setData('attribute_group_id',$general->getId())
						->setData('attribute_id',$attribute_id)			
						->setData('sort_order',$sort_order)		
						->setId(null)->save();

			$sort_order++;
			
		}

//save attribute set group attribute



}


print_r(count($sets));




?>