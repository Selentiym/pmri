<form>
<div class = "row">
	<label>Номера телефонов</label>
	<?php 
		$data = UserPhone::model() -> findAll();
		$data = CHtml::listData($data, 'id', function($phone){
			return $phone -> number.':'.$phone -> i;
		});
		echo CHtml::activeDropDownListChosen2(UserPhone::model(),'id',$data, array('name'=>'phones', 'id' => 'phones'),array(),'');
		echo CHtml::button('Изменить', array('id' => 'changePhone'));
		echo CHtml::button('Создать', array('id' => 'createPhone'));
		echo CHtml::button('Удалить', array('id' => 'deletePhone'));
		Yii::app()->clientScript->registerScript('clickScriptForPhones', "
			$('#changePhone').click(function(){
				alert('check');
				location.replace('".$this -> createUrl('/PhoneUpdate')."/'+$('#phones').val());
			});
			$('#deletePhone').click(function(){
				location.replace('".$this -> createUrl('PhoneDelete')."/'+$('#phones').val());
			});
			$('#createPhone').click(function(){
				location.replace('".$this -> createUrl('PhoneCreate')."');
			}
			//$('#createRT').click(alert('asda'));
		);
		");
	?>
</div>
</form>