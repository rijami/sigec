<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class UsuarioForm extends Form implements InputFilterProviderInterface {

    public function __construct($accion = '', $listaEmpleado = [], $listaRol = []) {
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
            case 'bloquear':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'desbloquear':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarEliminar(event, this)';
                $required = false;
                $disabled = true;
                break;
            case 'confirmar':
                $onsubmit = 'return validarConfirmar(event, this)';
                $required = false;
                $disabled = true;
                break;
            case 'cambiarcontrasena':
                $onsubmit = 'return validarCambiarContrasena(event, this)';
                $required = false;
                $disabled = true;
                break;
            case 'rechazar':
                $onsubmit = 'return validarRechazar(event, this)';
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

       /*  $this->add([
            'type' => Element\Select::class,
            'name' => 'idEmpleado',
            'options' => [
                'label' => 'Empleado *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaEmpleado,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idEmpleado',
            ],
        ]); */
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
                'required' => false,
                'class' => 'form-control',
                'id' => 'idRol',
            ],
        ]);

//------------------------------------------------------------------------------        
        $this->add([
            'type' => Element\Password::class,
            'name' => 'nueva_contrasena',
            'options' => [
                'label' => 'Nueva Contraseña',
            ],
            'attributes' => [
                'oninput'=>'checkPasswordStrength(this.value)',
                'required' => $required,
                'class' => 'form-control',
                'autocomplete' => 'new-password',
                'id' => 'nueva_contrasena',
            ],
        ]);

        $this->add([
            'type' => Element\Password::class,
            'name' => 'confirmar_contrasena',
            'options' => [
                'label' => 'Confirmar Contraseña',
            ],
            'attributes' => [
                'required' => $required,
                'class' => 'form-control',
                'autocomplete' => 'new-password',
                'id' => 'confirmar_contrasena',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'idUsuario',
            'options' => [
                'label' => 'Id Usuario',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'idUsuario',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'idEmpleado',
            'options' => [
                'label' => 'Id Empleado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'idEmpleado',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'usuario',
            'options' => [
                'label' => 'Usuario',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'usuario',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'empleado',
            'options' => [
                'label' => 'empleado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'empleado',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'login',
            'options' => [
                'label' => 'Login',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'login',
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
            'name' => 'fechaultingreso',
            'options' => [
                'label' => 'Fecha Ultimo Ingreso',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechaultingreso',
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
            'name' => 'fechaultfallido',
            'options' => [
                'label' => 'Fecha Ultimo Fallido',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechaultfallido',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'contFallidos',
            'options' => [
                'label' => 'Conteo Fallido',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'contFallidos',
            ],
        ]);
    }

//------------------------------------------------------------------------------

    public function getInputFilterSpecification() {
        return [
            'idRol' => ['required' => false],
            'nueva_contrasena' => ['required' => false],
            'confirmar_contrasena' => ['required' => false],
        ];
    }
}
