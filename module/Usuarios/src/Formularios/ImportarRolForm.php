<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class ImportarRolForm extends Form {

    public function __construct($accion = '',$roles=[]) {
        switch (strtolower($accion)) {
            case 'importar':
                $onsubmit = 'return validarImportar(event, this)';
                $required = true;
                $disabled = false;
                break;
            default :
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formImportarRol');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);
        
        
        
        
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idRolAux',
            'options' => [
                'label' => 'Rol A Importar *',
                'empty_option' => 'Seleccione...',
                'value_options' => $roles,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'onchange' => 'getRecursosByIdRol(this.value)',
                'disabled' => $disabled,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idRolAux',
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
