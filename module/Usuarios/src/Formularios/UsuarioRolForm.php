<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class UsuarioRolForm extends Form {

    public function __construct($accion = '', $listaRol = []) {
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

        parent::__construct('formUsuarioRol');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);
        
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idUsuario',
            'options' => [
                'label' => 'ID Usuario',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'idUsuario',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idRol',
            'options' => [
                'label' => 'Rol *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaRol,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'disabled' => $disabled,
                'onchange' => 'seleccionarRol()',
                'required' =>  $required,
                'class' => 'form-control',
                'id' => 'idRol',
            ],
        ]);
        
//------------------------------------------------------------------------------        

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
            'name' => 'empleado',
            'options' => [
                'label' => 'Empleado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'empleado',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'identificacion',
            'options' => [
                'label' => 'Identificacion',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'identificacion',
            ],
        ]);
    }
}
