<?php
	if ($autoform->hasTB()) {
		$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
			'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
			'action' => $autoform->getAction()
		));
	} else {
		$form = $this->beginWidget('CActiveForm', array(
			'action' => $autoform->getAction()
		));
	}
?>

<fieldset>
	<legend><?php echo $autoform->getTitle(); ?></legend>

<?php
	foreach($autoform->getFields() as $field) {
		echo $autoform->renderField($form, $field);
	}
?>

</fieldset>

<?php
	if($autoform->hasAdditional())
		include $autoform->getAdditional(); 
?>


<?php
	if($autoform->hasTB()) {
		echo TbHtml::formActions(array(
			TbHtml::submitButton('Submit', array('color' => TbHtml::BUTTON_COLOR_PRIMARY)),
			TbHtml::resetButton('Reset'),
		));
	} else {
		echo CHtml::submitButton('Login');
		echo CHtml::resetButton('Reset');
	}
?>

<?php
	$this->endWidget();
?>