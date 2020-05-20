<?php

namespace DocumentManager\Form;

use DocumentManager\Module;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter;
use Zend\Validator;


class UploadForm extends Form
{

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        $file = new Element\File('document-file');
        $hidden = new Element\Hidden('hidden');
        $hidden->setAttribute('id', 'hidden_id');
        $hidden->setValue(0);

        $file->setLabel('Document Upload');
        $file->setAttribute('id', 'document-file');
        $this->add($file);
        $this->add($hidden);
    }

    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        $hiddenInput = new InputFilter\Input('hidden');

        $validHidden  = new Validator\GreaterThan(['min' => 0]);
        $hiddenInput->getValidatorChain()->attach($validHidden);

        $fileInput = new InputFilter\FileInput('document-file');
        $fileInput->setRequired(true);
            $fileInput->getFilterChain()->attachByName(
                'filerenameupload',
                [
                    'target' => Module::UPLOAD_PATH,
                    'randomize' => true,
                    'use_upload_name' => true,
                ]
            );
        $inputFilter->add($fileInput);
        $inputFilter->add($hiddenInput);

        $this->setInputFilter($inputFilter);
    }
}