<?php

namespace Dashboard\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class TablerosForm extends Form implements InputFilterProviderInterface
{

    public function __construct(
        $accion = '',
        $listaProcesos = [],
        $listaCoordinaciones = [],
        /* $listaPrioridades = [],
         $listaEstados = [] */
    ) {
        $requiredForEditable = true;
        $readonlyForInputs = false;
        $disabledForSelects = false;
        $id_indicadorDisabled = true;

        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarRegistrar(event, this)';
                break;
            case 'editar':
                $onsubmit = 'return validarEditar(event, this)';
                $readonlyCodigo = true;
                $disabledProceso = true;
                $disabledCoordinacion = true;
                $id_indicadorDisabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $requiredForEditable = false;
                $readonlyForInputs = true;
                $disabledForSelects = true;
                $readonlyCodigo = true;
                $disabledProceso = true;
                $disabledCoordinacion = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarEliminar(event, this)';
                $requiredForEditable = false;
                $readonlyForInputs = true;
                $disabledForSelects = true;
                $readonlyCodigo = true;
                $disabledProceso = true;
                $disabledCoordinacion = true;
                $id_indicadorDisabled = false;
                break;
            case 'asignar':
                $onsubmit = '';
                $requiredForEditable = false;
                $readonlyForInputs = true;
                $disabledForSelects = true;
                $readonlyCodigo = true;
                $disabledProceso = true;
                $disabledCoordinacion = true;
                $onsubmit = 'return validarAsignar(event, this)';
                break;
            case 'exportar':
                $onsubmit = 'return validarExportar(event, this)';
                break;
            default:
                $onsubmit = '';
                $requiredForEditable = false;
                $readonlyForInputs = false;
                $disabledForSelects = false;
                $readonlyCodigo = false;
                $disabledProceso = false;
                $disabledCoordinacion = false;
                break;
        }

        if (!isset($readonlyCodigo)) {
            $readonlyCodigo = false;
        }
        if (!isset($disabledProceso)) {
            $disabledProceso = false;
        }
        if (!isset($disabledCoordinacion)) {
            $disabledCoordinacion = false;
        }


        parent::__construct('formTableros');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'idTablero',
            'options' => [
                'label' => 'ID Indicador'
            ],
            'attributes' => [
                'readonly' => true,
                //'disabled' => $id_indicadorDisabled,
                'class' => 'form-control',
                'id' => 'idTablero',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idProceso',
            'options' => [
                'label' => 'Proceso *',
                'empty_option' => 'Seleccione un proceso...',
                'value_options' => $listaProcesos,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'onchange' => 'getCoordinaciones()',
                'class' => 'form-control',
                'id' => 'idProceso',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idCoordinacion',
            'options' => [
                'label' => 'Coordinación *',
                'empty_option' => 'Seleccione una coordinación...',
                'value_options' => $listaCoordinaciones,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledCoordinacion,
                'class' => 'form-control',
                'id' => 'idCoordinacion',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Nombre del Tablero *'
            ],
            'attributes' => [
                'maxlength' => 255,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'nombre',
            ]
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha_entregada',
            'options' => [
                'label' => 'Fecha de Entrega *'
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'fecha_entregada',
            ]
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'manual',
            'options' => [
                'label' => 'Manual *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['1' => 'SI', '0' => 'NO'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'manual',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'fuentes_informacion',
            'options' => [
                'label' => 'Fuente de Informacion *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['1' => 'SI', '0' => 'NO'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'fuentes_informacion',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'necesidad',
            'options' => [
                'label' => 'Necesidad *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['1' => 'SI', '0' => 'NO'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'necesidad',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'historia_usuario',
            'options' => [
                'label' => 'Historias de Usuario *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['1' => 'SI', '0' => 'NO'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'historia_usuario',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'enlace',
            'options' => [
                'label' => 'Enlace *'
            ],
            'attributes' => [
                'readonly' => $readonlyForInputs,
                'required' => $requiredForEditable,
                'class' => 'form-control',
                'id' => 'enlace',
                'rows' => 3,
                'style' => 'resize: vertical;',
                'placeholder' => 'Ingrese el enlace del tablero...',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => [
                'label' => 'Estado *'
            ],
            'attributes' => [
                'min' => 0,
                'max' => 255,
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'estado',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => [
                'label' => 'Registrado por'
            ],
            'attributes' => [
                'maxlength' => 20,
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'registradopor',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fecha_creacion',
            'options' => [
                'label' => 'Fecha de Registro'
            ],
            'attributes' => [
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'fecha_creacion',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'options' => [
                'label' => 'Modificado por'
            ],
            'attributes' => [
                //'maxlength' => 20,
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'modificadopor',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'options' => [
                'label' => 'Fecha de Modificación'
            ],
            'attributes' => [
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'fechahoramod',
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [];
    }
}
