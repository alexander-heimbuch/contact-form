<?php
/*
 *	Contact Form
 *	
 *	@author		Alexander Heimbuch <alex@zusatzstoff.org>
 *	@created	24th August 2014
 *	@license	WTFPL
 *
 */

require('contact-input.php');

class ContactForm {

	private $mail = null;
	private $subject = null;
	private $inputs = array();
	private $submitted = false;
	private $missingFields = array();
	private $formId = '';

	private $formTarget = null;
	private $formData = null;

	public $showLabels = null;
	public $submitMessage = null;
	public $errorMessage = null;
	public $missingMessage = null;

	public $senderField = 'sender';

	/**
	 * Construct
	 * @param [String] $mail 	Receivers mail address
	 * @param [String] $subject Subject of the mail
	 * @param [String] $target  Target of formular (default PHP_SELF)
	 */
	public function __construct($mail = null, $subject = null, $target = null) {
		
		if (!$mail or is_string($mail) === false) {
			throw new Exception('The email address should be a String');
			return false;
		}

		$this->mail = $mail;

		if (!$subject or is_string($subject) === false) {
			throw new Exception('The email subject should be a String');
			return false;
		}

		$this->subject = $subject;

		if ($target) {
			$this->formTarget = $target;
		} else {
			$this->formTarget = $_SERVER["PHP_SELF"];
		}

		// Create unique formId for multiple form identification
		$this->formId = hash('md5', $mail.$subject);
	}

	// Input type creator functions
	public function text ($name = null, $label = null, $placeholder = null, $class = null, $required = null) {
		
		$text = new Input('text');

		$text->name = $name;
		$text->label = $label;
		$text->placeholder = $placeholder;
		$text->css = $class;
		$text->required = $required;

		$this->inputs[] = $text;
	} 

	public function select ($name = null, $options = null , $label = null, $class = null, $required = null) {
		
		$select = new Input('select');

		$select->name = $name;
		$select->options = $options;
		$select->label = $label;
		$select->css = $class;
		$select->required = $required;

		$this->inputs[] = $select;
	}

	public function textarea ($name = null, $label = null, $placeholder = null, $class = null, $required = null) {
		
		$textarea = new Input('textarea');

		$textarea->name = $name;
		$textarea->label = $label;
		$textarea->placeholder = $placeholder;
		$textarea->css = $class;
		$textarea->required = $required;

		$this->inputs[] = $textarea;
	}

	public function email ($name = null, $label = null, $placeholder = null, $class = null, $required = null) {
		
		$email = new Input('email');

		$email->name = $name;
		$email->label = $label;
		$email->placeholder = $placeholder;
		$email->css = $class;
		$email->required = $required;

		$this->inputs[] = $email;
	}

	public function checkbox ($name = null, $label = null, $value = null, $checked = null, $class = null) {
		
		$checkbox = new Input('checkbox');

		$checkbox->name = $name;
		$checkbox->label = $label;
		$checkbox->value = $value;
		$checkbox->css = $class;
		$checkbox->checked = $checked;

		$this->inputs[] = $checkbox;
	}

	public function radio ($name = null, $label = null, $class = null, $value = null, $required = null) {

		$checkbox = new Input('radio');

		$checkbox->name = $name;
		$checkbox->label = $label;
		$checkbox->value = $value;
		$checkbox->css = $class;
		$checkbox->required = $required;

		$this->inputs[] = $checkbox;
	}

	public function submit ($value = null, $class = null) {

		$submit = new Input('submit');

		$submit->value = $value;
		$submit->css = $class;

		$this->inputs[] = $submit;
	}

	// Special function to inject plain html/text
	public function custom ($html = null) {

		$custom = new Input('custom');
		$custom->content = $html;

		$this->inputs[] = $custom;
	}

	// CSS Bot hack, random honeypot only for bots visible
	private function cssBotProtect () {

		$fakeToken = uniqid();

		$botProtect = '<input type="hidden" name="sectoken" value="'. $fakeToken .'">';
		$botProtect .= '<input type="checkbox" style="display:none;" name="'. $fakeToken .'" value="'. hash('md5', $fakeToken) .'">';

		return $botProtect;
	}

	/**
	 * Handles a submitted formular
	 * @param  [Array] $post $_POST data
	 */
	private function handlePost ($post = null) {

		if (!$post) {
			return false;
		}

		// Check if own form was submitted
		if (isset($post[$this->formId])) {
			return false;
		}

		$this->formData = $post;

		$missingFields = $this->isMissing($post);

		if (count($missingFields) > 0) {
			$this->missingFields = $missingFields;
			return false;
		}

		$this->sendRequest();

	}

	/**
	 * Checks if a required field wasn't submitted and validates the email
	 * @param  [Array]  $post $_POST data
	 * @return [Array] 	Array with missing fields
	 */
	public function isMissing ($post) {

		$missingFields = array();

		foreach ($this->inputs as $input) {

			if ($input->required !== true) {
				continue;
			}

			// missing required fields
			if (!isset($post[$input->name])) {
				$missingFields[$input->name] = 'missing';
			}

			// validate email address
			if($input->type === 'email') {
				if(filter_var($post[$input->name], FILTER_VALIDATE_EMAIL) === false) {
					$missingFields[$input->name] = 'invalid';
				}
			}
		}

		return $missingFields;
	}

	/**
	 * Send the contact form to its receiver
	 * @return [Boolean] true/false (success)
	 */
	private function sendRequest () {

		if (!$this->formData) {
			throw new Exception("No data to submit");
			return false;
		}

		if (!isset($this->formData[$this->senderField])) {
			$this->submitted = false;
			return false;
		}

		$botProtect = $this->formData['sectoken'];

		if (isset($this->formData[$botProtect])) {
			$this->submitted = false;
			return false;
		}

		$message = $this->formatMessage();

		$headers = 	'From: '. $this->formData[$this->senderField] ."\r\n".
		 			'Reply-To: '. $this->formData[$this->senderField] ."\r\n".
		 			'X-Mailer: PHP/'.phpversion() ."\r\n".
		 			'Content-type: text/html; charset=utf-8';

		if (mail($this->mail, $this->subject, $message, $headers)) {
			$this->submitted = true;
			return true;
		};

		return false;
	}

	/**
	 * Creates HTML body of message
	 * @return [String] HTML message
	 */
	private function formatMessage () {

		$message = '';
		foreach ($this->formData as $name => $value) {

			$input = null;

			// Find the corresponding input object
			foreach ($this->inputs as $inputObject) {

				// Get only inputs that are registered (not the form identification or the bot protection)
				if ($name !== $inputObject->name) {
					continue;
				}

				// For radio buttons we have to check the value because is insufficient
				if ($inputObject->type !== 'radio') {
					$input = $inputObject;
					break;
				}

				if ($inputObject->value === $value) {
					$input = $inputObject;
					break;
				}

			}

			// Skip unkown and submit data
			if (!$input || $input->type === 'submit') {
				continue;
			}

			$message .= '<p>';

			if (isset($input->label)) {
				$message .= '<strong>'. $input->label .'</strong><br/>'; 
			}

			switch ($input->type) {
				case 'checkbox':
					$message .= 'Yes';
					break;
				case 'radio':
					break;
				default:
					$message .= $value;
			}

			$message .= '</p>';
		}

		return $message;
	}

	/**
	 * Initiates generation of form and input fields
	 */
	public function html () {

		// Handle a submitted contact formular
		$this->handlePost($_POST);

		if (count($this->inputs) === 0) {
			throw new Exception('Input fields are missing');
			return false;
		}

		$formHtml = '';

		// On success the submit message will be displayed
		if ($this->submitted and $this->submitMessage) {
			echo $this->submitMessage;
			return true;
		}

		// In case of missing fields a message will be displayed
		if (count($this->missingFields) > 0 && $this->missingMessage) {
			$formHtml .= '<div class="form missing">'. $this->missingMessage .'</div>';
		}

		$formHtml .= '<form action="'. $this->formTarget .'" method="POST">'. $this->cssBotProtect();

		// Hidden field for form identification
		$formHtml .= '<input type="hidden" name="'. $this->formId .'" value="1">';

		// Create the input fields
		foreach ($this->inputs as $input) {

			// If data was submitted, set the state of input fields
			if (isset($this->formData[$input->name])) {
				$input->activate($this->formData[$input->name]);
			}
			
			// Extend css classes if there are missing fields
			if (isset($this->missingFields[$input->name])) {
				$input->css($input->css .' '.$this->missingFields[$input->name]);
			}

			$formHtml .= $input->html();
		}

		$formHtml .= '</form>';

		echo $formHtml;
	}
}
?>