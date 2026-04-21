<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class RolForm extends Form {

    public function __construct($accion = '') {
        switch (strtolower($accion)) {
            case 'registrar':
                $onsubmit = 'return validarRegistrar(event, this)';
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

        parent::__construct('formUsuario');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'rol',
            'options' => [
                'label' => 'Rol *',
            ],
            'attributes' => [
                'maxlength' => 150,
                'required' => $required,
                'readonly' => !$required,
                'oninput' => 'seleccionarRol()',
                'class' => 'form-control text-capitalize',
                'id' => 'rol',
            ],
        ]);

//------------------------------------------------------------------------------        
        $this->add([
            'type' => Element\Text::class,
            'name' => 'idRol',
            'options' => [
                'label' => 'id Rol *',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'idRol',
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
                'class' => 'form-control font-weight-bold',
                'id' => 'estado',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => [
                'label' => 'Registrado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'registradopor',
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
    }

//------------------------------------------------------------------------------
}
