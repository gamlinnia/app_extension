<?php


@set_time_limit(0);

require_once 'app/Mage.php';
umask(0);
 
Mage::app('default'); 


$attributes=array();

$frontend_input_values=array();

$frontend_input_values['Text Area']='textarea';

$frontend_input_values['Dropdown']='select';

$frontend_input_values['Text Field']='text';
$frontend_input_values['text Field']='text';
$frontend_input_values['text']='text';
$frontend_input_values['Multiple Select']='multiselect';



$file = fopen('FinalMagentoPIMData.csv','r'); 

$index=0;

while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容


	$index++;

	if($index==1) {

		continue;

	}


	$attributes[trim($data[2])][]=$data;

}


$attributes_index=0;


foreach($attributes as $attribute_set_name=>$attribute_arr) {


$attributes_index++;


if($attributes_index<=0||$attributes_index>10) {
	//continue;
}



	$attribute_ids=array();


	foreach($attribute_arr as $attribute) {

		if($frontend_input_values[trim($attribute[5])]=='') {


			echo('error('.$attribute[5].')<br/>');
			exit();
			
		}

            //validate attribute_code


$attribute_code=implode('_',explode('-',substr($attribute[3],0,30)));
$attribute_code=implode('_',explode('.',$attribute_code));
$attribute_code=implode('_',explode('#',$attribute_code));
$attribute_code=implode('_',explode('/',$attribute_code));
$attribute_code=implode('_',explode('+',$attribute_code));
$attribute_code=implode('_',explode(' ',$attribute_code));
$attribute_code=implode('_',explode('&',$attribute_code));
$attribute_code=implode('_',explode('(',$attribute_code));
$attribute_code=implode('_',explode(')',$attribute_code));
$attribute_code=implode('_',explode('"',$attribute_code));
$attribute_code=strtolower($attribute_code);



                $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));

                if (!$validatorAttrCode->isValid($attribute_code)) {

                        echo('Attribute code('.$attribute_code .') is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.');

						exit();

                }









            $model = Mage::getModel('catalog/resource_eav_attribute');

			$model->setData('attribute_code',$attribute_code);

		    $model->setData('is_global',2);

		    $model->setData('frontend_input',$frontend_input_values[trim($attribute[5])]);

		    $model->setData('is_unique',$attribute[7]);

		    $model->setData('is_comparable',1);

		    $model->setData('is_visible_on_front',1);

		    $model->setData('used_in_product_listing',1);

		    $model->setData('frontend_label',array($attribute[23],$attribute[23]));




			//multiselect select

		    if($frontend_input_values[trim($attribute[5])]=='multiselect'||$frontend_input_values[trim($attribute[5])]=='select') {

				$option_arr=explode(',',$attribute[24]);

				sort($option_arr);

				$option_value=array();

				$option_order=array();

				foreach($option_arr as $key=>$val) {

					$val=trim($val);

					$option_value['option_'.$key]=array($val,$val);
					$option_order['option_'.$key]=$key;
					
				}


		    $model->setData('option',array('value'=>$option_value,'order'=>$option_order));
				
			}

			//multiselect select




			$model ->setData('entity_type_id',4)->setId(null)->save();


			$attribute_ids[]=$model->getId();

		
	}



//save attribute set

        $entityTypeId = 4;

		$skeleton_set = 4;

        $model  = Mage::getModel('eav/entity_attribute_set')
            ->setEntityTypeId($entityTypeId);

        $helper = Mage::helper('adminhtml');

        $name = $helper->stripTags($attribute_set_name);

        $model->setAttributeSetName(trim($name));

        $model->validate();

        $model->save();

        $model->initFromSkeleton($skeleton_set);

        $model->save();



//save attribute set group

		$group=Mage::getModel('eavattributegroup/group')
			->setData('attribute_set_id',$model->getId())
			->setData('attribute_group_name',$attribute_set_name)			
			->setData('sort_order',100)		
			->setData('default_id',0)	
			->setId(null)->save();
		;

//save attribute set group


//save attribute set group attribute
		foreach($attribute_ids as $attribute_id_index=> $attribute_id) {

		$group_attribute=Mage::getModel('eavattributegroup/attribute')
						->setData('entity_type_id',4)
						->setData('attribute_set_id',$model->getId())
						->setData('attribute_group_id',$group->getId())
						->setData('attribute_id',$attribute_id)			
						->setData('sort_order',$attribute_id_index)		
						->setId(null)->save();
			
		}

//save attribute set group attribute

//save attribute set


}


print_r(count($attributes));

print_r($index);

?>