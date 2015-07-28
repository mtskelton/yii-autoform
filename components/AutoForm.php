<?php

/**
 * Initial version of a partially automatic form generation component.
 * @author Mark Skelton (mark@software13.co.uk)
 *
 */
class AutoForm {
	private $_model;
	private $_action;
	private $_form = null;
	private $_opts = array();
	private $_fields = null;
	private $_override = null;
	private $_fieldsets = null;

	public function __construct($model, $action = null, $opts = null)
	{
		$this->_model = $model;
		$this->_action = $action;
		$this->_opts['useTB'] = class_exists('TbActiveForm');
		$this->_opts['submitText'] = 'Submit';
		$this->_opts['resetText'] = 'Reset';

		// Get override defs if they exist
		if (method_exists($model, 'autoform'))
			$this->_override = $model->autoform();
		elseif (method_exists($model, 'autoformFields'))
			$this->_override = $model->autoformFields();

		// Fieldsets
		if (method_exists($model, 'autoformFieldsets'))
			$this->_fieldsets = $model->autoformFieldsets();

		if($opts) {
			$this->_opts = array_merge($this->_opts, $opts);
		}
	}

	public function render()
	{
		return Yii::app()->controller->renderPartial('autoform.views._autoform', array(
			'model' => $this->_model,
			'autoform' => $this
		));
	}

	public function getTitle($id = null)
	{
		if ($id)
			if ($this->hasTitle($id))
				return $this->_fieldsets[$id]['legend'];
			else
				return "";
		if (isset($this->_opts['title'])) {
			return $this->_opts['title'];
		} else if (isset($this->_model->autoformTitle)) {
			return $this->_model->autoformTitle;
		}
		return "";
	}

	public function hasTitle($id = null) {
		if ($id)
			return $this->_fieldsets != null && isset($this->_fieldsets[$id]) && isset($this->_fieldsets[$id]['legend']) && strlen($this->_fieldsets[$id]['legend']) > 0;
		return strlen($this->getTitle() > 0);
	}

	public function hasTB()
	{
		return $this->_opts['useTB'];
	}

	public function hasFieldSets()
	{
		return $this->_fieldsets != null;
	}

	public function getFieldSets()
	{
		return $this->_fieldsets;
	}

	public function getFields($id = null)
	{
		if($this->_fields == null) {
			$this->_fields = array();
			foreach($this->_model->rules() as $rule) {
				if(sizeof($rule) > 1) {
					$fieldNames = explode(",", $rule[0]);
					$type = $rule[1];
					foreach($fieldNames as $fieldName) {
						$fieldName = trim($fieldName);
						$this->_fields = $this->_parseField($this->_fields, $fieldName, $type);
					}
				}
			}
		}
		if($id) {
			$fields = array();
			$fieldNames = explode(",", $this->_fieldsets[$id]['fields']);
			foreach($fieldNames as $fieldName) {
				$fieldName = trim($fieldName);
				if(array_key_exists($fieldName, $this->_fields))
					$fields[] = $this->_fields[$fieldName];
			}
			return $fields;
		}
		return $this->_fields;
	}

	private function _parseField($fields, $fieldName, $type) {
		if(!isset($fields[$fieldName])) {
			$fields[$fieldName] = array_merge(array(
				'type' => $this->_parseType($type),
				'id' => $fieldName
			), $this->_getFieldOverride($fieldName));
		} else {
			$type = $this->_parseType($type);
			if($type != 'string')
				$fields[$fieldName]['type'] = $type;
		}
		return $fields;
	}

	/**
	 * If we have an override declared in the model's autoform() function, use it
	 */
	private function _getFieldOverride($fieldName) {
		if (!$this->_override || !isset($this->_override[$fieldName]))
			return array();
		return $this->_override[$fieldName];
	}

	private function _parseType($type) {
		$type = strtolower($type);
		if($type == 'numerical' || $type == 'file' || $type == 'boolean')
			return $type;
		return 'string';
	}

	public function renderField($form, $field) {
		$func = $this->_determineFieldFunc($field);

		if(isset($field['data'])) {
			if($this->hasTB() && !preg_match("/^active/", $func))
				return call_user_func(array($form, $func), $this->_model, $field['id'], $field['data']);
			if($func == "activeHiddenField")
				return call_user_func("CHtml::" . $func, $this->_model, $field['id'], $field['data']);
			return '<div class="row">' . CHtml::activeLabel($this->_model, $field['id']) . call_user_func("CHtml::" . $func, $this->_model, $field['id'], $field['data']) . $form->error($this->_model, $field['id']) . '</div>';
		}
		if($this->hasTB() && !preg_match("/^active/", $func))
			return call_user_func(array($form, $func), $this->_model, $field['id']);
		if($func == "activeHiddenField")
			return call_user_func("CHtml::" . $func, $this->_model, $field['id']);
		return '<div class="row">' . CHtml::activeLabel($this->_model, $field['id']) . call_user_func("CHtml::" . $func, $this->_model, $field['id']) . CHtml::error($this->_model, $field['id']) . '</div>';
	}

	private function _determineFieldFunc($field) {
		if(isset($field['widget'])) {
			return $field['widget'];
		}
		if($field['type'] == 'numerical') {
			if($this->hasTB()) {
				return "numberFieldControlGroup";
			} else {
				return "activeNumberField";
			}
		} else if($field['type'] == 'boolean') {
			if($this->hasTB()) {
				return "checkBoxControlGroup";
			} else {
				return "activeCheckbox";
			}
		} else if($field['type'] == 'file') {
			if($this->hasTB()) {
				return "fileFieldControlGroup";
			} else {
				return "activeFileField";
			}
		} else if($field['type'] == 'hidden') {
			return "hiddenField";
		} else {
			// string
			if($this->hasTB()) {
				return "textFieldControlGroup";
			} else {
				return "activeTextField";
			}
		}
	}

	public function getAction() {
		if($this->_action)
			return $this->_action;
		return "";
	}

	public function getSubmitText() {
		return $this->_opts['submitText'];
	}
	public function getResetText() {
		return $this->_opts['resetText'];
	}
	public function hasSubmitText() {
		return strlen($this->_opts['submitText']) > 0;
	}
	public function hasResetText() {
		return strlen($this->_opts['resetText']) > 0;
	}
	public function hasAdditional() {
		return isset($this->_opts['additional']);
	}
	public function getAdditional() {
		return $this->_opts['additional'];
	}
}