<div class="col-xs-3 doctor_shortcut" style="margin-left: 10px; margin-bottom: 10px; border: 1px solid">
	<a href="<?php echo Yii::app() -> baseUrl; ?>/cabinet/<?php echo $user -> username; ?>" target="_blank"><strong><?php echo $user -> fio; ?></strong></a>
	<?php echo CHtml::link('<span class="glyphicon glyphicon-pencil edit-doctor"></span>',Yii::app() -> baseUrl.$user -> parent -> giveUserNameForPage().'/edit/'.$user -> id, array("target" => "_blank"));?>
	<br>
	<small>
	<i><?php echo $user -> userSpeciality -> name; ?></i><br/>
<?php
	//print_r($user -> phones);
	echo $user -> giveStringFromArray($user -> phones, ',', 'number');
	echo "<br/>";
	echo $user -> giveStringFromArray($user -> address_array, ',', 'address');
	/*if (($user -> phones)&&(!empty($user -> phones))) {
		foreach ($this -> phones as $phone) {
			echo $phone -> number.', ';
		}
		
	}*/
?>
	 <?php// echo current($user -> phones) ?>

	</small>
	<br>
	<div style="white-space: nowrap">
	login: <?php echo $user -> username; ?>
	</div>

	<br>
	<?php //if ((($user -> jMin)&&($user -> jMax))||(($user -> jMin_add)&&($user -> jMax_add))) : ?>
	<?php if (count($user -> ranges)) : ?>
	<small>
		<?php
			foreach($user -> ranges as $range) {
				echo "№".$range -> jMin." ... ".$range -> jMax."<br/>";
			}
		?>


	</small>
	<a href="<?php echo Yii::app() -> baseUrl . '/print/'.$user->username;?>" class="print_me"><span>Распечатать направения этого доктора</span><span style="font-size:30px;" class="glyphicon glyphicon-print"></span></a>
	<?php endif; ?>
	

</div>