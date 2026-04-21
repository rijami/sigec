<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class RecursosrbacForm extends Form {

    public function __construct($accion = '') {
        switch (strtolower($accion)) {
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
            default :
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formRecursosRbac');
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
                'label' => 'Recurso*',
            ],
            'attributes' => [
                'onchange' => 'seleccionarRecursoMetodo()',
                'maxlength' => 100,
                'required' => $required,
                'readonly' => $disabled,
                'class' => 'form-control text-uppercase',
                'id' => 'recurso',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'metodo',
            'options' => [
                'label' => 'Metodo*',
                'label_attributes' => [
                    'class' => 'text-danger'
                ],
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'GET' => 'GET',
                    'POST' => 'POST',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'onchange' => 'seleccionarRecursoMetodo()',
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
                'label' => 'Id Recurso',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'idRecurso',
            ],
        ]);

//------------------------------------------------------------------------------
    }
}
