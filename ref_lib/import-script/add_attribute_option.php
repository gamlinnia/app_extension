<?php


@set_time_limit(0);

require_once 'app/Mage.php';
umask(0);
 
Mage::app('default');



$attr_model = Mage::getModel('catalog/resource_eav_attribute');
$attr = $attr_model->loadByCode('catalog_product', 'c17080_led_lightbulb_direction');



$attr_id = $attr->getAttributeId();

$option['attribute_id'] = $attr_id;
$option['value']['any_option_name'][0] ='Yes';
$option['value']['any_option_name'][1] ='Yes';
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttributeOption($option);


print_r($attr->getSource()->getAllOptions(false));


print_r($attr->getData());



?>