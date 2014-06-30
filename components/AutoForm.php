<?php

/**
 * Initial version of a partially automatic form generation component.
 * @author Mark Skelton (mark@software13.co.uk)
 *
 */
class AutoForm {
	private $_model;
	private $_action;
	private $_opts = array();
	private $_fields = null;
	private $_override = null;

	public function __construct($model, $action = null, $opts = null)
	{
		$this->_model = $model;
		$this->_action = $action;
		$this->_opts['useTB'] = class_exists('TbActiveForm');

		// Get override defs if they exist
		if(method_exists($model, 'autoform'))
			$this->_override = $model->autoform();

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

	public function getTitle()
	{
		if (isset($this->_opts['title'])) {
			return $this->_opts['title'];
		} else if (isset($this->_model->autoformTitle)) {
			return $this->_model->autoformTitle;
		}
		return "";
	}

	public function hasTB() {
		return $this->_opts['useTB'];
	}

	public function getFields() {
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
			if($this->hasTB())
				return call_user_func(array($form, $func), $this->_model, $field['id'], $field['data']);
			return '<div class="row">' . CHtml::activeLabel($this->_model, $field['id']) . call_user_func("CHtml::" . $func, $this->_model, $field['id'], $field['data']) . '</div>';
		}
		if($this->hasTB())
			return call_user_func(array($form, $func), $this->_model, $field['id']);
		return '<div class="row">' . CHtml::activeLabel($this->_model, $field['id']) . call_user_func("CHtml::" . $func, $this->_model, $field['id']) . '</div>';
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
}
