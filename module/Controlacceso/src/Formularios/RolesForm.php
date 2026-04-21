<?php

namespace Controlacceso\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class RolesForm extends Form {

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

        parent::__construct('formRoles');
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
                'maxlength' => 80,
                'onchange' => 'existeRol(this.value)',
                'onkeyup' => 'eliminarDiacriticos(this)',                
                'required' => $required,
                'readonly' => !$required,                 
                'class' => 'form-control sinDiacriticos',
                'id' => 'rol',
            ],
        ]);  

        //------------------------------------------------------------------------------        

        $this->add([
            'type' => Element\Text::class,
            'name' => 'idRol',
            'options' => [
                'label' => 'ID Rol',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'style' => 'font-weight: bold',
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
