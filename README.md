#yii-autoform (v0.1)

Autoform is a convenience class used to quickly render forms in a Yii view.  It's compatible with YiiStrap (www.getyiistrap.com) and will render fields using TbActiveForm if it's available.

It's very much in it's infancy at the moment, and only contains the core functionality we are actually using ourselves, however suggestions and improvements are very much welcome!


##Installing

Clone or download the extension into a directory called autoform in your project's protected/extensions directory.

Then open protected/config/main.php and modify it as follows:

```<?php
// main configuration
return array(
	...

    'aliases' => array(
        ...
        'autoform' => realpath(__DIR__ . '/../extensions/autoform'), // change this if necessary
    ),

    'import' => array(
        ...
        'autoform.components.*',
    ),
);```


##Usage

The most basic example of using AutoForm is to call the following code in your view where you want to render the form, where $model is your CActiveRecord, CModel or CActiveForm class:

```<?php
	$af = AutoForm($model);
	$af->render();
?>```

This will render every available field on your form using the default options.  You can pass options to the AutoForm class as the second parameter in the form of an associative array.  e.g.

```$af = AutoForm($model, array(
		'title' => 'Form Title'
	));```


##Field Types

By default, AutoForm will attempt to display the fields in your form using the default component for the rules you specify, but you can override this.

To specify AutoForm specific field settings, just add a function to your model class called autoform() and get it to return an associative array .. e.g.

```public function autoform()
{
    return array(
            'username' => array('label'=>'Username', 'widget'=>'activeTextField'),
            'dropdown' => array('label'=>'Choices', 'widget'=>'activeDropDownList','data'=>array('1', '2', '3'))
        );
}```

Note - if you're not using YiiStrap, the widget name calls CHtml.  If you are using YiiStrap, you need to use the corresponding form component name (e.g. textFieldControlGroup)


##Runtime Options

**title**

You can set the display title of the form in two ways:
 - By setting the "title" option when constructing the AutoForm instance.
 - By adding a public $autoformTitle field to the model you pass.

**useTB**

This option if set to false will disable the use of TbActiveForm to render the form.  Otherwise if you have Yiistrap installed, AutoForm will attempt to autodetect and use it.
