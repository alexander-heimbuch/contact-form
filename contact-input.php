<?php
/*
 *	Input Fields
 *	
 *	@author		Alexander Heimbuch <alex@zusatzstoff.org>
 *	@created	24th August 2014
 *	@license	WTFPL
 *
 */

class Input {

	private $output = '';

	public 	$name,
			$value,
			$type,
			$options,
			$placeholder,
			$css,
			$required,
			$content,
			$checked;

	/**
	 * Construct
	 * @param [String] $type Type of input
	 */
	public function __construct ($type = null) {
		
		if(!$type) {
			throw new Exception("Missing input type declaration");
			return;
		}

		$this->type = $type;

		if(method_exists($this, $type) === false) {
			throw new Exception("Unkown or unsupported input type: ".$type);
			return;
		}

		// Create the blueprint
		$this->$type();
	}

	// Blueprints for available input types
	private function email() {
		$this->output = '{label}<input type="email" {name} {placeholder} {required} {class} value="{value}">';
	}

	private function text() {
		$this->output = '{label}<input type="text" {name} {placeholder} {required} {class} value="{value}">';
	}

	private function textarea() {
		$this->output = '{label}<textarea {name} {placeholder} {required} {class}>{value}</textarea>';
	}

	private function select() {
		$this->output = '{label}<select {name} {class}>{options}</select>';
	}

	private function checkbox() {
		$this->output = '<input type="checkbox" {name} {required} {class} {checked} value="{value}">{label}';
	}

	private function radio() {
		$this->output = '<input type="radio" {name} {required} {class} {checked} value="{value}">{label}';
	}

	private function submit() {
		$this->output = '<input type="submit" {name} {class} value="{value}">';
	}

	private function custom() {
		$this->output = '{content}';
	}

	// Wildcard replacements
	private function content () {

		if(!$this->content) {
			$replacement = '';
		} else {
			$replacement = $this->content;
		}
		$this->output = str_replace('{content}', $replacement, $this->output);

	}

	private function label () {
		
		if($this->label) {
			$this->output = str_replace('{label}', $this->label, $this->output);
		}

	}

	private function name () {

		if(!$this->name) {
			$replacement = '';
		} else {
			$replacement = 'name="'. $this->name .'"';
		}

		$this->output = str_replace('{name}', $replacement, $this->output);
	}

	private function value () {

		if(!$this->value) {
			$value = '';
		}

		$this->output = str_replace('{value}', $this->value, $this->output);
	}

	private function options () {

		$replacement = '';

		if($this->options) {

			foreach($this->options as $option) {

				$replacement .= '<option value="'. $option['value'] .'"';

				if ($option['value'] === $this->value) {
					$replacement .= 'selected';
				}

				$replacement .= '>'. $option['label'] .'</option>';
			}

		}

		$this->output = str_replace('{options}', $replacement, $this->output);
	}

	private function placeholder () {

		if(!$this->placeholder) {
			$replacement = '';
		} else {
			$replacement = 'placeholder="'. $this->placeholder .'"';
		}

		$this->output = str_replace('{placeholder}', $replacement, $this->output);
	}

	private function required () {

		if($this->required === true) {
			$replacement = 'required="true"';
		} else {
			$replacement = '';
		}

		$this->output = str_replace('{required}', $replacement, $this->output);
	}

	private function css () {

		if(!$this->css) {
			$replacement = '';
		} else {
			$replacement = 'class="'. $this->css .'"';
		}

		$this->output = str_replace('{class}', $replacement, $this->output);
	}

	private function checked () {

		if($this->checked === true) {
			$replacement = 'checked="checked"';
		} else {
			$replacement = '';
		}

		$this->output = str_replace('{checked}', $replacement, $this->output);
	}

	/**
	 * Enrich input fields with post-data
	 * @param  [String] $data POST-Data
	 */
	public function activate ($data) {

		switch ($this->type) {
			case 'checkbox':
			case 'radio':
				if ($this->value === $data) {
					$this->checked = true;
				}
			break;
			default:
				$this->value = $data;
			break;
		}

	}

	/**
	 * Generate html of input field
	 * @return [String] Input html
	 */
	public function html () {

		$attributes = get_object_vars($this);

		foreach ($attributes as $attribute => $value) {
			if(method_exists($this, $attribute) === true) {
				$this->$attribute();
			}
		}

		return $this->output;
	}

}
?>