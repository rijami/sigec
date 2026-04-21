<?php

namespace Indicadores\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class IndicadoresForm extends Form implements InputFilterProviderInterface
{

    public function __construct(
        $accion = '',
        $listaProyectos = [],
        $listaCoordinaciones = [],
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
            case 'activar':
                $onsubmit = 'return validarActivar(event, this)';
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


        parent::__construct('formIndicadores');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'id_indicador',
            'options' => [
                'label' => 'ID Indicador'
            ],
            'attributes' => [
                'readonly' => true,
                'disabled' => $id_indicadorDisabled,
                'class' => 'form-control',
                'id' => 'id_indicador',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idProceso',
            'options' => [
                'label' => 'Proceso *',
                'empty_option' => 'Seleccione un proceso...',
                'value_options' => $listaProyectos,
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
            'name' => 'codigo',
            'options' => [
                'label' => 'Código del Indicador *'
            ],
            'attributes' => [
                'min' => 0,
                'required' => $requiredForEditable,
                'readonly' => $readonlyCodigo,
                'class' => 'form-control',
                'id' => 'codigo',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_indicador',
            'options' => [
                'label' => 'Nombre del Indicador *'
            ],
            'attributes' => [
                'maxlength' => 255,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'nombre_indicador',
            ]
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'objetivo',
            'options' => [
                'label' => 'Objetivo del Indicador *'
            ],
            'attributes' => [
                'readonly' => $readonlyForInputs,
                'required' => $requiredForEditable,
                'class' => 'form-control',
                'id' => 'objetivo',
                'rows' => 3,
                'style' => 'resize: vertical;',
                'placeholder' => 'Ingrese una descripción detallada del objetivo del indicador...',
            ]
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'fuente_informacion',
            'options' => [
                'label' => 'Fuente de Información *'
            ],
            'attributes' => [
                'readonly' => $readonlyForInputs,
                'required' => $requiredForEditable,
                'class' => 'form-control',
                'id' => 'fuente_informacion',
                'rows' => 3,
                'style' => 'resize: vertical;',
                'placeholder' => 'Defina la fuente de información del indicador...',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'meta',
            'options' => [
                'label' => 'Meta *'
            ],
            'attributes' => [
                'min' => 0,
                'max' => 255,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'placeholder' => 'Ingrese solo el valor sin simbolos',
                'id' => 'meta',
            ]
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'periodicidad',
            'options' => [
                'label' => 'Periodicidad *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['Mensual' => 'Mensual', 'Trimestral' => 'Trimestral', 'Semestral' => 'Semestral', 'Anual' => 'Anual'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'periodicidad',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'TIPO_INDICADOR',
            'options' => [
                'label' => 'Tipo de Indicador *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['Acomulativo' => 'Acomulativo', 'No Acomulativo' => 'No Acomulativo'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'TIPO_INDICADOR',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'SENTIDO',
            'options' => [
                'label' => 'Sentido del Indicador *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['Ascendente' => 'Ascendente', 'Descendente' => 'Descendente'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'SENTIDO',
            ],
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
            'name' => 'FechaRegistro',
            'options' => [
                'label' => 'Fecha de Registro'
            ],
            'attributes' => [
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'FechaRegistro',
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
