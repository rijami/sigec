<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class PermisosForm extends Form {

    public function __construct($accion = '',$recursos=[]) {
        switch (strtolower($accion)) {
            case 'registrar':
                $onsubmit = 'return validarRegistrar(event, this)';
                $required = true;
                $disabled = false;
                break;
            case 'importar':
                $onsubmit = 'return validarImportar(event, this)';
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

        parent::__construct('formPermisos');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);
        
        
        
        
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idRecurso',
            'options' => [
                'label' => 'Recursos *',
                'empty_option' => 'Seleccione...',
                'value_options' => $recursos,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'onchange' => 'seleccionarRecursoMetodo()',
                'disabled' => $disabled,
                'required' => false,
                'class' => 'form-control',
                'id' => 'idRecurso',
            ],
        ]);

//------------------------------------------------------------------------------        

        $this->add([
            'type' => Element\Text::class,
            'name' => 'idRol',
            'options' => [
                'label' => 'Id Rol',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control font-weight-bold',
                'id' => 'idRol',
            ],
        ]);
       

//------------------------------------------------------------------------------
    }
}
