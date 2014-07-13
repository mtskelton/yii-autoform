<?php
	if ($autoform->hasTB()) {
		$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
			'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
			'action' => $autoform->getAction(),
			'htmlOptions'=>array('enctype'=>'multipart/form-data')
		));
	} else {
		$form = $this->beginWidget('CActiveForm', array(
			'action' => $autoform->getAction(),
			'htmlOptions'=>array('enctype'=>'multipart/form-data')
		));
	}
?>

<?php
	if($autoform->hasFieldSets()) {
		foreach($autoform->getFieldSets() as $fsId => $fs) {
?>
			<fieldset class="<?php if(array_key_exists('class', $fs)) { echo $fs['class']; } ?>">
				<?php if ($autoform->hasTitle($fsId)) { ?>
					<legend><?php echo $autoform->getTitle($fsId); ?></legend>
				<?php } ?>

			<?php
				foreach($autoform->getFields($fsId) as $field) {
					echo $autoform->renderField($form, $field);
				}
			?>
			</fieldset>
<?php
		}
	} else {
?>
		<fieldset>
			<?php if ($autoform->hasTitle()) { ?>
				<legend><?php echo $autoform->getTitle(); ?></legend>
			<?php } ?>

		<?php
			foreach($autoform->getFields() as $field) {
				echo $autoform->renderField($form, $field);
			}
		?>

		</fieldset>
<?php
	}
?>

<?php
	if($autoform->hasAdditional())
		include $autoform->getAdditional();
?>


<?php
	if($autoform->hasTB()) {
		$btns = array(TbHtml::submitButton($autoform->getSubmitText(), array('color' => TbHtml::BUTTON_COLOR_PRIMARY)));
		if($autoform->hasResetText())
			$btns[] = TbHtml::resetButton($autoform->getResetText());
		echo TbHtml::formActions($btns);
	} else {
		echo CHtml::submitButton($autoform->getSubmitText());
		if($autoform->hasResetText())
			echo CHtml::resetButton($autoform->getResetText);
	}
?>

<?php
	$this->endWidget();
?>
