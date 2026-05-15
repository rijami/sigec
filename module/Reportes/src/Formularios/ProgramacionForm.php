<?php

namespace Reportes\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class ProgramacionForm extends Form implements InputFilterProviderInterface
{

    public function __construct($accion = '', $meses = [])
    {

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
            case 'reportar':
                $onsubmit = 'return validarReportar(event, this)';
                $requiredForEditable = false;
                $readonlyForInputs = true;
                $disabledForSelects = true;
                $id_indicadorDisabled = false;
                break;
            case 'informacion':
                $onsubmit = 'return validarInformacion(event, this)';
                $requiredForEditable = false;
                $readonlyForInputs = true;
                $disabledForSelects = true;
                $id_indicadorDisabled = false;
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


        parent::__construct('formProgramacion');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'idProgramacion',
            'attributes' => [
                'id' => 'idProgramacion'
            ]
        ]);

        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'idReporte',
            'attributes' => [
                'id' => 'idReporte'
            ]
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'mes',
            'options' => [
                'label' => 'Mes *',
                'empty_option' => 'Seleccione...',
                'value_options' => $meses
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'mes',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha_corte',
            'options' => [
                'label' => 'Fecha de Corte *'
            ],
            'attributes' => [
                //'min' => date('Y-m-d'),
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'fecha_corte',
            ]
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha_solicitud',
            'options' => [
                'label' => 'Fecha de Solicitud *'
            ],
            'attributes' => [
                //'min' => date('Y-m-d'),
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'fecha_solicitud',
            ]
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha_limite',
            'options' => [
                'label' => 'Fecha de Límite *'
            ],
            'attributes' => [
                //'min' => date('Y-m-d'),
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'fecha_limite',
            ]
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'dia_semana',
            'options' => [
                'label' => 'Día de la Semana',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    '1' => 'Lunes',
                    '2' => 'Martes',
                    '3' => 'Miércoles',
                    '4' => 'Jueves',
                    '5' => 'Viernes',
                    '6' => 'Sábado',
                    '7' => 'Domingo',
                ]
            ],
            'attributes' => [
                'required' => false,
                'readonly' => $readonlyForInputs,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'dia_semana',
            ]
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'semana_mes',
            'options' => [
                'label' => 'Semana del Mes',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    '1' => 'Semana 1',
                    '2' => 'Semana 2',
                    '3' => 'Semana 3',
                    '4' => 'Semana 4',
                ]
            ],
            'attributes' => [
                'required' => false,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'semana_mes',
            ]
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'evidencia',
            'options' => [
                'label' => 'Evidencia *'
            ],
            'attributes' => [
                'min' => 0,
                'max' => 255,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'evidencia',
            ]
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => [
                'label' => 'Estado *'
            ],
            'attributes' => [
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'estado',
            ]
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fecha_efectiva',
            'options' => [
                'label' => 'Fecha Efectiva *'
            ],
            'attributes' => [
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'fecha_efectiva',
            ]
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'respon_reporta',
            'options' => [
                'label' => 'Responsable del Reporte *'
            ],
            'attributes' => [
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'respon_reporta',
            ]
        ]);

        /*  $this->add(['type' => Element\Hidden::class, 'name' => 'respon_reporta']);
         $this->add(['type' => Element\Hidden::class, 'name' => 'fecha_efectiva']);
         $this->add(['type' => Element\Hidden::class, 'name' => 'recordatorio']); */
    }

    public function getInputFilterSpecification()
    {
        return [];
    }
}
