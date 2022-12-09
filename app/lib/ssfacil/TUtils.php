<?php

class TUtils 
{
    public static function createXMLBreadCrumb($xml_file, $controller) {
        $frontpageController = TSession::getValue('frontpage');
        TXMLBreadCrumb::setHomeController($frontpageController);
        return new TXMLBreadCrumb($xml_file, $controller);
    }

    public static function validateProperties($classname, $variables, $properties)
    {
        if (is_array($properties))
            $properties = (object)$properties;

        $erros = [];
        foreach ($variables as $key => $variable) {
            if (!isset($properties->$variable))
              $erros[] = AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', $variable, $classname);
        }

        if ($erros)
            throw new Exception( implode('<br>', $erros) );
    }

    public static function setValidation($form, $field, $validations = []) {
        $control = $form->getField($field);
        foreach ($validations as $validation) {
            if (isset($validation)) {
                $control->addValidation('<b>' . $control->getLabel() . '</b>', $validation );
            }
        }
    }
}