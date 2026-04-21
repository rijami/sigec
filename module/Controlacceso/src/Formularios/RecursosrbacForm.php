<?php

namespace Controlacceso\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class RecursosrbacForm extends Form {

    public function __construct($accion = '') {
        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarRegistrar(event, this)';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarEditar(event, this)';
                $required = true;
                $disabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarEliminar(event, this)';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formRecursosrbac');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);        
        
        $this->add([
            'type' => Element\Text::class,
            'name' => 'recurso',
            'options' => [
                'label' => 'Recurso *',                
            ],
            'attributes' => [
                'maxlength' => 80,
                'onkeyup' => 'eliminarDiacriticos(this)',                
                'required' => $required,
                'readonly' => !$required,                
                'class' => 'form-control sinDiacriticos',
                'id' => 'recurso',
            ],
        ]);
        
        $this->add([
            'type' => Element\Select::class,
            'name' => 'metodo',
            'options' => [
                'label' => 'Metodo *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'GET' => 'GET',
                    'POST' => 'POST',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'disabled' => $disabled,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'metodo',
            ],
        ]);

        //------------------------------------------------------------------------------        

        $this->add([
            'type' => Element\Text::class,
            'name' => 'idRecurso',
            'options' => [
                'label' => 'ID Recurso',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'style' => 'font-weight: bold',
                'id' => 'idRecurso',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => [
                'label' => 'Estado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'style' => 'font-weight: bold',
                'id' => 'estado',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => [
                'label' => 'Registrado por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'registradopor',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'options' => [
                'label' => 'Modificado por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'modificadopor',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'options' => [
                'label' => 'Fecha de Registro',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahorareg',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'options' => [
                'label' => 'Fecha de Modificacion',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahoramod',
            ],
        ]);
    }
}
