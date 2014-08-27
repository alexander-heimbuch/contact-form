# Contact-Form

## PHP library for straight forward contact forms

## Features

* Easy to integrate
* Suports most common input fields
* Uses PHP mail() function
* HTML E-Mails
* Automatic `$_POST` handling (required and missing fields, email validation)
* Adjustable success and error messages
* Simple CSS Bot recognition
* Support of multiple instances on one page

## Installation

Copy the `contact-form` folder to your resources and `require('lib/contact-form.php');` the lib.

## Usage

### Init

First of all create a instance of the contact form:

`$contact = new ContactForm('your.email@domain.tld', 'Your Subject');`

Optional: define a specific action as a third parameter, otherwise PHP_SELF will be used.

### Configuration

Show/Hide labels:

`$contact->showLabels = true/false`

Submit messages, that will be shown on successful submit:

`$contact->submitMessage = '<div class="success">Your inquiry was successfully sent.</div>';`

Missing message, that will be shown if fields are missing or invalid:

`$contact->missingMessage = '<div class="missing">Some fields are missing or invalid.</div>';`

Error messagte, that will be shown if an error while sending occurred:

`$contact->errorMessage = '<div class="error">An error has occurred while sending the message.</div>';`

### Text Fields

`text ($name, $label, $placeholder, $class, $required)`

Example:

`$contact->text('subject', 'Subject:', 'Your request', 'nice-text', true);`

### Selects

`select ($name, $options, $label, $class, $required)`

Example:

    $contact->select(  
        'category-select', 
        array (
            0 => array (
                'value' => 'technical_support',
                'label' => 'Technical Support'
            ),
            1 => array (
                'value' => 'general_question',
                'label' => 'General Question'
            ),
            2 => array (
                'value' => 'customer_care',
                'label' => 'Customer Care'
            )
        ), 'Category', 
    'nice-select');

### Textarea

`textarea ($name, $label, $placeholder, $class, $required)`

Example:

`$contact->textarea('message', 'Message:', 'Your Message', 'nice-textarea', true);`

### E-Mail fields

`email ($name, $label, $placeholder, $class, $required)`

Example:

`$contact->email('sender', 'E-Mail:', 'Your E-Mail Address', 'nice-email', true);`

### Checkboxes

`checkbox ($name, $label, $value, $checked, $class)`

Example:

`$contact->checkbox('newsletter', 'Subscribe Newsletter?', 'subscribe', null, 'nice-checkbox');
$contact->checkbox('information', 'Get further Information?', 'further_information', null, 'nice-checkbox');`

### Radiobuttons

`radio ($name, $label, $class, $value, $required)`

Example:

    $contact->radio('category-radio', 'Technical Support', 'nice-radio', 'technical_support');  
    $contact->radio('category-radio', 'General Question', 'nice-radio', 'general_question');  
    $contact->radio('category-radio', 'Customer Care', 'nice-radio', 'customer_care');

### Custom HTML

`custom ($html)`

Example:

`$contact->custom('<h3>Radiobutton Group</h3>');`

### Submit Button

`submit ($value, $class)`

Example:

`$contact->submit('Send Request', 'nice-button');`

## Form Output

Place the form in your HTML

`$contact->html()`
