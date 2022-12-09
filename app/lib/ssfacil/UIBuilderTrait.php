<?php

use Adianti\Control\TAction;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TText;
use Adianti\Base\TStandardSeek;
use Adianti\Widget\Form\TColor;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TImage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TSelect;
use Adianti\Widget\Form\TSlider;
use Adianti\Widget\Form\TNumeric;
use Adianti\Widget\Form\TSpinner;
use Adianti\Widget\Form\TPassword;
use Adianti\Widget\Form\TSortList;
use Adianti\Core\AdiantiCoreLoader;
use Adianti\Widget\Form\TCheckGroup;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Form\TSeekButton;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Form\TMultiSearch;
use Adianti\Widget\Wrapper\TDBSelect;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Widget\Wrapper\TDBSortList;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Wrapper\TDBCheckGroup;
use Adianti\Widget\Wrapper\TDBRadioGroup;
use Adianti\Core\AdiantiApplicationConfig;
use Adianti\Widget\Wrapper\TDBMultiSearch;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Form\TBarCodeInputReader;
use Linfo\OS\Windows;

/**
 * UI Builder Trait
 *
 * @version    1.0
 * @package    ed2info
 * @subpackage lib
 * @author     Edson ALanis
 * @copyright  Copyright (c) 2021 ED2Info.
 */

trait UIBuilderTrait
{
    protected $fields = [];
    protected $fieldsByName = [];

    public function getWidgets()
    {
        return $this->fields;
    }
    
    public function getWidget($name)
    {
        if (isset($this->fieldsByName[$name]))
        {
            return $this->fieldsByName[$name];
        }
        else
        {
            throw new Exception("Widget {$name} not found");
        } 
    }

    private function validateProperties($classname, $variables, $properties)
    {
        TUtils::validateProperties($classname, $variables, $properties);
    }

    public function makeTLabel($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        if (!isset($properties->{'name'}))
            $properties->{'name'} = 'label_' . uniqid();

        $this->validateProperties('TLabel', ['value'], $properties);

        $widget = new TLabel((string) $properties->{'value'});
        if (isset($properties->{'color'}))
            $widget->setFontColor((string) $properties->{'color'});
        if (isset($properties->{'size'}))
            $widget->setFontSize((string) $properties->{'size'});
        if (isset($properties->{'style'}))
            $widget->setFontStyle((string) $properties->{'style'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->setFontColor((string) 'red');

        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        return $widget;
    }

    public function makeTButton($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TButton', ['name', 'icon', 'value', 'action'], $properties);

        $widget = new TButton((string) $properties->{'name'});
        $widget->setImage((string) $properties->{'icon'});
        $widget->setLabel((string) $properties->{'value'});
        if (is_callable((array) $properties->{'action'}))
        {
            if (!isset($properties->{'action_params'}))
                $properties->{'action_params'} = [];
            $widget->setAction(new TAction((array) $properties->{'action'}, (array) $properties->{'action_params'}), (string) $properties->{'value'});
        }
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    private function createTEntry($class, $properties)
    {
        $widget = new $class((string) $properties->{'name'});
        if (isset($properties->{'value'}))
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'mask'}))
        {
            $replaceOnPost = false;
            if (isset($properties->{'replaceOnPost'})) 
                $replaceOnPost = (boolean) $properties->{'replaceOnPost'};
            $widget->setMask((string) $properties->{'mask'}, $replaceOnPost);
        }
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'maxlen'})) 
            $widget->setMaxLength((int) $properties->{'maxlen'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        if (isset($properties->{'editable'})) 
            $widget->setEditable((string) $properties->{'editable'});

        return $widget;
    }

    public function makeTEntry($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TEntry', ['name'], $properties);

        // $widget = new TEntry((string) $properties->{'name'});
        // if (isset($properties->{'value'}))
        //     $widget->setValue((string) $properties->{'value'});
        // if (isset($properties->{'mask'}))
        // {
        //     $replaceOnPost = false;
        //     if (isset($properties->{'replaceOnPost'})) 
        //         $replaceOnPost = (boolean) $properties->{'replaceOnPost'};
        //     $widget->setMask((string) $properties->{'mask'}, $replaceOnPost);
        // }
        // if (isset($properties->{'width'}))
        //     $widget->setSize($properties->{'width'});
        // if (isset($properties->{'maxlen'})) 
        //     $widget->setMaxLength((int) $properties->{'maxlen'});
        // if (isset($properties->{'tip'})) 
        //     $widget->setTip((string) $properties->{'tip'});
        // if (isset($properties->{'required'}) AND $properties->{'required'}) 
        //     if (isset($properties->{'label'}))
        //         $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
        //     else
        //         $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        // if (isset($properties->{'editable'})) 
        //     $widget->setEditable((string) $properties->{'editable'});
        $widget = $this->createTEntry('TEntry', $properties);
       
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string)$properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTNumeric($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TNumeric', ['name', 'decimals', 'decimalsSeparator', 'thousandSeparator'], $properties);

        $widget = new TNumeric((string)  $properties->{'name'}, 
                               (integer) $properties->{'decimals'},
                               (string)  $properties->{'decimalsSeparator'},
                               (string)  $properties->{'thousandSeparator'},
                               isset($properties->{'decimals'}) ? (boolean) $properties->{'decimals'} : true);

        if (isset($properties->{'value'}))
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'maxlen'})) 
            $widget->setMaxLength((int) $properties->{'maxlen'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        if (isset($properties->{'editable'})) 
            $widget->setEditable((string) $properties->{'editable'});
       
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string)$properties->{'name'}] = $widget;
        
        return $widget;
    }
    
    public function makeTSpinner($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TSpinner', ['name', 'min', 'max', 'step'], $properties);

        $widget = new TSpinner((string) $properties->{'name'});
        $widget->setRange((int) $properties->{'min'}, (int) $properties->{'max'}, (int) $properties->{'step'});
        if (isset($properties->{'value'}))
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'editable'})) 
            $widget->setEditable((string) $properties->{'editable'});
            
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string)$properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTSlider($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TSlider', ['name', 'min', 'max', 'step'], $properties);

        $widget = new TSlider((string) $properties->{'name'});
        $widget->setRange((int) $properties->{'min'}, (int) $properties->{'max'}, (int) $properties->{'step'});
        if (isset($properties->{'value'}))
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'editable'})) 
            $widget->setEditable((string) $properties->{'editable'});

        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string)$properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTPassword($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TPassword', ['name'], $properties);

        $widget = new TPassword((string) $properties->{'name'});
        if (isset($properties->{'value'})) 
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'})) 
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'editable'})) 
            $widget->setEditable((string) $properties->{'editable'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTDate($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDate', ['name'], $properties);

        $widget = new TDate((string) $properties->{'name'});
        if (isset($properties->{'value'}))
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'editable'}))
            $widget->setEditable((string) $properties->{'editable'});
        if (isset($properties->{'mask'}))
        {
            $replaceOnPost = false;
            if (isset($properties->{'replaceOnPost'})) 
                $replaceOnPost = (boolean) $properties->{'replaceOnPost'};
            $widget->setMask((string) $properties->{'mask'}, $replaceOnPost);
        }
        if (isset($properties->{'databaseMask'})) 
            $widget->setDatabaseMask((string) $properties->{'databaseMask'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTFile($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TFile', ['name'], $properties);

        $widget = new TFile((string) $properties->{'name'});
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'editable'}))
            $widget->setEditable((string) $properties->{'editable'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'extensions'})) 
            $widget->setAllowedExtensions((array) $properties->{'extensions'});
        if (isset($properties->{'enableFileHandling'})) 
            $widget->enableFileHandling();
        if (isset($properties->{'enableImageGallery'})) 
            $widget->enableImageGallery(); 
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTColor($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TColor', ['name'], $properties);

        $widget = new TColor((string) $properties->{'name'});
        if (isset($properties->{'value'})) 
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'})) 
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'editable'})) 
            $widget->setEditable((string) $properties->{'editable'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTImage($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TImage', ['image'], $properties);

        if (file_exists((string) $properties->{'image'}))
            $widget = new TImage((string) $properties->{'image'});
        else
            $widget = new TLabel((string) 'Image not found: ' . $properties->{'image'});
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        return $widget;
    }

    public function makeTText($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TText', ['name'], $properties);

        $widget = new TText((string) $properties->{'name'});
        if (isset($properties->{'value'})) 
            $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTCheckGroup($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TCheckGroup', ['name', 'items'], $properties);

        $widget = new TCheckGroup((string) $properties->{'name'});

        $items = $properties->{'items'};
	    $widget->addItems($items);

        $layout = 'vertical';
	    if (isset($properties->{'layout'}))
            $layout = $properties->{'layout'};
        $widget->setLayout($layout);
        
	    if (isset($properties->{'value'}))
	        $widget->setValue(explode(',', (string) $properties->{'value'}));
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTDBCheckGroup($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBCheckGroup', ['name', 'database', 'model', 'key', 'display'], $properties);

        $ordercolumn = NULL;
        $criteria = NULL;
	    if (isset($properties->{'ordercolumn'}))
            $ordercolumn = (string) $properties->{'ordercolumn'};
	    if (isset($properties->{'criteria'}))
            $criteria = $properties->{'criteria'};
        $widget = new TDBCheckGroup((string) $properties->{'name'},
                                    (string) $properties->{'database'},
                                    (string) $properties->{'model'},
                                    (string) $properties->{'key'},
                                    (string) $properties->{'display'},
                                    (string) $ordercolumn,
                                    $criteria);
        
        $layout = 'vertical';
	    if (isset($properties->{'layout'}))
            $layout = $properties->{'layout'};
        $widget->setLayout($layout);
        
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTRadioGroup($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TRadioGroup', ['name', 'items'], $properties);

        $widget = new TRadioGroup((string) $properties->{'name'});
        
        $items = $properties->{'items'};
	    $widget->addItems($items);

        $layout = 'vertical';
	    if (isset($properties->{'layout'}))
            $layout = $properties->{'layout'};
        $widget->setLayout($layout);
        
        if (isset($properties->{'value'})) 
	        $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'useButton'}) && $properties->{'useButton'} === true) 
            $widget->setUseButton();
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTDBRadioGroup($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBRadioGroup', ['name', 'database', 'model', 'key', 'display'], $properties);

        $ordercolumn = NULL;
        $criteria = NULL;
	    if (isset($properties->{'ordercolumn'}))
            $ordercolumn = (string) $properties->{'ordercolumn'};
	    if (isset($properties->{'criteria'}))
            $criteria = $properties->{'criteria'};
        $widget = new TDBRadioGroup((string) $properties->{'name'},
                                    (string) $properties->{'database'},
                                    (string) $properties->{'model'},
                                    (string) $properties->{'key'},
                                    (string) $properties->{'display'},
                                    (string) $ordercolumn,
                                    $criteria);
        
        $layout = 'vertical';
	    if (isset($properties->{'layout'}))
            $layout = $properties->{'layout'};
        $widget->setLayout($layout);
        
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTCombo($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TCombo', ['name', 'items'], $properties);

        $widget = new TCombo((string) $properties->{'name'});
	    $items = $properties->{'items'};
	    $widget->addItems($items);

	    if (isset($properties->{'value'}))
	        $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'width'})) 
	        $widget->setSize($properties->{'width'});
        if (isset($properties->{'defaultOption'}))
            $widget->setDefaultOption($properties->{'defaultOption'});
        if (isset($properties->{'enableSearch'}))
            $widget->enableSearch();
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTDBCombo($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBRadioGroup', ['name', 'database', 'model', 'key', 'display'], $properties);

        $ordercolumn = NULL;
        $criteria = NULL;
	    if (isset($properties->{'ordercolumn'}))
            $ordercolumn = (string) $properties->{'ordercolumn'};
	    if (isset($properties->{'criteria'}))
            $criteria = $properties->{'criteria'};
        $widget = new TDBCombo((string) $properties->{'name'},
                               (string) $properties->{'database'},
                               (string) $properties->{'model'},
                               (string) $properties->{'key'},
                               (string) $properties->{'display'},
                               (string) $ordercolumn,
                               $criteria);
	    
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
        if (isset($properties->{'width'})) 
	        $widget->setSize($properties->{'width'});
        if (isset($properties->{'defaultOption'}))
            $widget->setDefaultOption($properties->{'defaultOption'});
        if (isset($properties->{'enableSearch'}))
            $widget->enableSearch();
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTSelect($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TSelect', ['name', 'items'], $properties);

        $widget = new TSelect((string) $properties->{'name'});
	    $items = $properties->{'items'};
	    $widget->addItems($items);

	    if (isset($properties->{'value'}))
	        $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        if (isset($properties->{'defaultOption'}))
            $widget->setDefaultOption($properties->{'defaultOption'});
        if (isset($properties->{'disableMultiple'}))
            $widget->setDefaultOption($properties->{'disableMultiple'});
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTDBSelect($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBSelect', ['name', 'database', 'model', 'key', 'display'], $properties);

        $ordercolumn = NULL;
        $criteria = NULL;
	    if (isset($properties->{'ordercolumn'}))
            $ordercolumn = (string) $properties->{'ordercolumn'};
	    if (isset($properties->{'criteria'}))
            $criteria = $properties->{'criteria'};
        $widget = new TDBSelect((string) $properties->{'name'},
                               (string) $properties->{'database'},
                               (string) $properties->{'model'},
                               (string) $properties->{'key'},
                               (string) $properties->{'display'},
                               (string) $ordercolumn,
                               $criteria);

	    if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        if (isset($properties->{'defaultOption'}))
            $widget->setDefaultOption($properties->{'defaultOption'});
        if (isset($properties->{'disableMultiple'}))
            $widget->disableMultiple();

        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTSortList($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TSortList', ['name', 'items'], $properties);

        $widget = new TSortList((string) $properties->{'name'});
        $items = $properties->{'items'};
	    $widget->addItems($items);

	    if (isset($properties->{'value'}))
	        $widget->setValue((string) $properties->{'value'});
        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        
        $widget->setProperty('style', 'box-sizing: border-box !important', FALSE);

        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTDBSortList($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBSortList', ['name', 'database', 'model', 'key', 'display'], $properties);

        $ordercolumn = NULL;
        $criteria = NULL;
	    if (isset($properties->{'ordercolumn'}))
            $ordercolumn = (string) $properties->{'ordercolumn'};
	    if (isset($properties->{'criteria'}))
            $criteria = $properties->{'criteria'};
        $widget = new TDBSortList((string) $properties->{'name'},
                               (string) $properties->{'database'},
                               (string) $properties->{'model'},
                               (string) $properties->{'key'},
                               (string) $properties->{'display'},
                               (string) $ordercolumn,
                               $criteria );

	    if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        
        $widget->setProperty('style', 'box-sizing: border-box !important', FALSE);

        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTMultiSearch($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TMultiSearch', ['name', 'items'], $properties);

        $widget = new TMultiSearch((string) $properties->{'name'});
	    $items = $properties->{'items'};
	    $widget->addItems($items);

        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        if (isset($properties->{'minlen'})) 
            $widget->setMinLength( (int) $properties->{'minlen'} );
        if (isset($properties->{'maxsize'})) 
	        $widget->setMaxSize( (int) $properties->{'maxsize'} );
	    
        $widget->setProperty('style', 'box-sizing: border-box !important', FALSE);
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTDBMultiSearch($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBMultiSearch', ['name', 'database', 'model', 'key', 'display'], $properties);

        $ordercolumn = NULL;
        $criteria = NULL;
	    if (isset($properties->{'ordercolumn'}))
            $ordercolumn = (string) $properties->{'ordercolumn'};
	    if (isset($properties->{'criteria'}))
            $criteria = $properties->{'criteria'};
        $widget = new TDBMultiSearch((string) $properties->{'name'},
                               (string) $properties->{'database'},
                               (string) $properties->{'model'},
                               (string) $properties->{'key'},
                               (string) $properties->{'display'},
                               (string) $ordercolumn,
                               $criteria );

        if (isset($properties->{'tip'})) 
            $widget->setTip((string) $properties->{'tip'});
	    if (isset($properties->{'width'})) 
        {   
            $height = NULL;
            if (isset($properties->{'height'}))
                $height = $properties->{'height'};
            $widget->setSize($properties->{'width'}, $height);
        }
        if (isset($properties->{'minlen'})) 
            $widget->setMinLength( (int) $properties->{'minlen'} );
        if (isset($properties->{'maxsize'})) 
	        $widget->setMaxSize( (int) $properties->{'maxsize'} );
	    
        $widget->setProperty('style', 'box-sizing: border-box !important', FALSE);
	    
        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
	    $this->fieldsByName[(string) $properties->{'name'}] = $widget;
	    
        return $widget;
    }

    public function makeTBarCodeInputReader($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;
        
        $this->validateProperties('TBarCodeInputReader', ['name'], $properties);

        // $widget = new TBarCodeInputReader($properties->{'name'});
        $widget = $this->createTEntry('TBarCodeInputReader', $properties);

        if (isset($properties->{'onChangeAction'}))
            $widget->setChangeAction(new TAction($properties->{'onChangeAction'}));
        if (isset($properties->{'onChangeFunction'}))
            $widget->setChangeFunction($properties->{'onChangeFunction'});

        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string)$properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTSeekButton($properties, $callback = null)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDBMultiSearch', ['name', 'database', 'model', 'display'], $properties);

        $widget = new TSeekButton((string) $properties->{'name'});
        if (isset($properties->{'width'}))
            $widget->setSize($properties->{'width'});
        if (isset($properties->{'required'}) AND $properties->{'required'}) 
            if (isset($properties->{'label'}))
                $widget->addValidation((string) '<b>' . $properties->{'label'}->getValue() . '</b>', new TRequiredValidator);
            else
                $widget->addValidation((string) $properties->{'name'}, new TRequiredValidator);
        
        if ( ($properties->{'database'}) AND ($properties->{'model'}) )
        {
            $obj = new TStandardSeek;
            $action = new TAction(array($obj, 'onSetup'));
            $action->setParameter('database', (string) $properties->{'database'});
            if (isset($this->form))
            {
                $action->setParameter('parent', $this->form->getName());
            }
            
            $database      = (string) $properties->{'database'};
            $model         = (string) $properties->{'model'};
            $display_field = (string) $properties->{'display'};
            $receive_field = strtolower($model) . '_' . $display_field;
            $label         = 'Descrição';
            
            if (isset($properties->{'prefix'}))
                $receive_field = $properties->{'prefix'} . $receive_field;
            if (isset($properties->{'label'}))
                $label = $properties->{'label'};
            if (isset($properties->{'mask'}))
                $action->setParameter('mask', (string) $properties->{'mask'});
            if (isset($properties->{'criteria'}))
                $action->setParameter('criteria', (string) base64_encode(serialize($properties->{'criteria'})));

            $ini  = AdiantiApplicationConfig::get();
            $seed = APPLICATION_NAME . ( !empty($ini['general']['seed']) ? $ini['general']['seed'] : 's8dkld83kf73kf094' );
            
            $action->setParameter('hash',          md5("{$seed}{$database}{$model}{$display_field}"));
            $action->setParameter('model',         (string) $properties->{'model'});
            $action->setParameter('display_field', (string) $properties->{'display'});
            $action->setParameter('receive_key',   (string) $properties->{'name'});
            $action->setParameter('receive_field', (string) $receive_field);
            $action->setParameter('operator',      'like');
            $action->setParameter('label',         (string) $label);
            $widget->setAction($action);

            $receiver = $this->makeTEntry(['name' => $receive_field, 'editable' => false]);
            $receiver->style .= ';margin-left:3px';
            $receiver->setSize('calc(100% - 120px)');
            $widget->setAuxiliar($receiver);
            $widget->setSize(80);
        }

        if (is_callable($callback))
            call_user_func($callback, $widget);
        
        $this->fields[] = $widget;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        return $widget;
    }

    public function makeTHidden($properties)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('THidden', ['name'], $properties);

        $widget = new THidden($properties->{'name'});

        if (isset($properties->{'value'}))
            $widget->setValue($properties->{'value'});

        return $widget;
    }

    public function makeTDataGrid($properties)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('TDataGrid', ['name', 'columns'], $properties);

        if (!isset($properties->{'title'}))
            $properties->{'title'} = '';

        $panel = new TPanelGroup($properties->{'title'});

        $widget = new BootstrapDatagridWrapper(new TDataGrid);
        $widget->datatable = 'true';
        $widget->style = 'width: 100%';

        if (isset($properties->{'datatable'}))
            $widget->datatable = (string) $properties->{'datatable'};
        if (isset($properties->{'style'}))
            $widget->style = $properties->{'style'};
        if (isset($properties->{'height'}))
            $widget->setHeight((string) $properties->{'height'});
        
        if ($properties->{'columns'})
        {
            $search = [];
            foreach ($properties->{'columns'} as $key => $Column)
            {
                if (is_array($Column))
                    $Column = (object)$Column;

                $this->validateProperties("TDataGridColumn", ['name', 'label', 'align', 'width'], $Column); 

                $dgcolumn = new TDataGridColumn((string) $Column->{'name'},
                                                (string) $Column->{'label'},
                                                (string) $Column->{'align'},
                                                (string) $Column->{'width'} );
                if (isset($Column->{'transformer'}))
                    $dgcolumn->setTransformer($Column->{'transformer'});
                
                if (isset($Column->{'hide'}) AND $Column->{'hide'})
                    $dgcolumn->setVisibility(false);

                if (isset($Column->{'enableSearch'}) and $Column->{'enableSearch'})
                    $search[] = (string) $Column->{'name'};

                if (isset($Column->{'order'}) and $Column->{'order'})
                {
                    $order = new TAction(array($this, 'onReload'));
                    $order->setParameter('order', (string) $Column->{'name'});
                    $dgcolumn->setAction($order);
                }
                
                $widget->addColumn($dgcolumn);
                $this->fieldsByName[(string)$Column->{'name'}] = $dgcolumn;
            }

            if ($search)
            {
                $input_search = new TEntry('input_search');
                $input_search->placeholder = _t('Search');
                $input_search->setSize('100%');
                $widget->enableSearch($input_search, implode(',', $search));
                $panel->addHeaderWidget($input_search);
            }
        }
        
        if (isset($properties->{'actions'}))
        {
            foreach ($properties->{'actions'} as $key => $Action)
            {
                if (is_array($Action))
                    $Action = (object)$Action;

                if (isset($Action->{'visible'}) AND !$Action->{'visible'})
                    continue;
                    
                $this->validateProperties("TDataGridAction($key)", ['label', 'image', 'field', 'action'], $Action); 

                if (is_callable((array) $Action->{'action'}))
                {
                    if (!isset($Action->{'action_params'}))
                        $Action->{'action_params'} = [];

                    // if (isset($Action->{'action_params'}['id']))
                    // {
                    //     $Action->{'action_params'}['fromId'] = $Action->{'action_params'}['id'];
                    //     unset($Action->{'action_params'}['id']);
                    // }

                    $dgaction = new TDataGridAction((array) $Action->{'action'}, (array) $Action->{'action_params'});
                    $dgaction->setLabel((string) $Action->{'label'});
                    $dgaction->setImage((string) $Action->{'image'});
                    $dgaction->setField((string) $Action->{'field'});
                
                    $widget->addAction($dgaction);
                }
            }
        }
        
        if (isset($properties->{'pagenavigator'}))
            if ($properties->{'pagenavigator'})
            {
                $loader = isset($properties->{'loader'}) ? (string) $properties->{'loader'} : 'onReload';
                $pageNavigation = new TPageNavigation;
                $pageNavigation->enableCounters();
                $pageNavigation->setAction(new TAction(array($this, $loader)));
                $pageNavigation->setWidth($widget->getWidth());

                $this->fieldsByName[(string) $properties->{'name'} . '_pnv'] = $pageNavigation;
            }
        
        $widget->createModel();
        
        $panel->add($widget);
        if (isset($pageNavigation))
        {
            $panel->addFooter($pageNavigation);
            $widget->setPageNavigation($pageNavigation);
        }

        $this->fieldsByName[(string) $properties->{'name'} . '_pnl'] = $panel;
        $this->fieldsByName[(string) $properties->{'name'}] = $widget;
        
        $widget = $panel;
        
        return $widget;
    }

    public function createWidget($properties)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $this->validateProperties('createWidget', ['class'], $properties);

        $class      = $properties->{'class'};
        $callback   = isset($properties->{'callback'}) ? $properties->{'callback'} : null;

        $widget = NULL;
        switch ($class)
        {
            case 'T'.'Label':
                $widget = $this->makeTLabel($properties, $callback);
                break;
            case 'T'.'Button':
                $widget = $this->makeTButton($properties, $callback);
                break;
            case 'T'.'Entry':
                $widget = $this->makeTEntry($properties, $callback);
                break;
            case 'T'.'Numeric':
                $widget = $this->makeTNumeric($properties, $callback);
                break;
            case 'T'.'Password':
                $widget = $this->makeTPassword($properties, $callback);
                break;
            case 'T'.'Date':
                $widget = $this->makeTDate($properties, $callback);
                break;
            case 'T'.'File':
                $widget = $this->makeTFile($properties, $callback);
                break;
            case 'T'.'Color':
                $widget = $this->makeTColor($properties, $callback);
                break;
            case 'T'.'SeekButton':
                $widget = $this->makeTSeekButton($properties, $callback);
                break;
            case 'T'.'Image':
                $widget = $this->makeTImage($properties, $callback);
                break;
            case 'T'.'Text':
                $widget = $this->makeTText($properties, $callback);
                break;
            case 'T'.'CheckGroup':
                $widget = $this->makeTCheckGroup($properties, $callback);
                break;
            case 'T'.'DBCheckGroup':
                $widget = $this->makeTDBCheckGroup($properties, $callback);
                break;
            case 'T'.'RadioGroup':
                $widget = $this->makeTRadioGroup($properties, $callback);
                break;
            case 'T'.'DBRadioGroup':
                $widget = $this->makeTDBRadioGroup($properties, $callback);
                break;
            case 'T'.'Combo':
                $widget = $this->makeTCombo($properties, $callback);
                break;
            case 'T'.'DBCombo':
                $widget = $this->makeTDBCombo($properties, $callback);
                break;
            case 'T'.'DataGrid':
                $widget = $this->makeTDataGrid($properties);
                break;
            case 'T'.'Spinner':
                $widget = $this->makeTSpinner($properties, $callback);
                break;
            case 'T'.'Slider':
                $widget = $this->makeTSlider($properties, $callback);
                break;
            case 'T'.'Select':
                $widget = $this->makeTSelect($properties, $callback);
                break;
            case 'T'.'DBSelect':
                $widget = $this->makeTDBSelect($properties, $callback);
                break;
            case 'T'.'SortList':
                $widget = $this->makeTSortList($properties, $callback);
                break;
            case 'T'.'DBSortList':
                $widget = $this->makeTDBSortList($properties, $callback);
                break;
            case 'T'.'MultiSearch':
                $widget = $this->makeTMultiSearch($properties, $callback);
                break;
            case 'T'.'DBMultiSearch':
                $widget = $this->makeTDBMultiSearch($properties, $callback);
                break;
            case 'T'.'Hidder':
                $widget = $this->makeTHidden($properties);
                break;
        }

        return $widget;
    }
}