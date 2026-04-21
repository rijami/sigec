<?php

namespace Indicadores\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class ResultadosForm extends Form implements InputFilterProviderInterface
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


        parent::__construct('formResultados');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'id_result',
            'attributes' => [
                'id' => 'id_result'
            ]
        ]);

        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'id_indicador',
            'attributes' => [
                'id' => 'id_indicador'
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
                'disabled' => $disabledProceso,
                'class' => 'form-control',
                'id' => 'mes',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'num',
            'options' => [
                'label' => 'Numerador *'
            ],
            'attributes' => [
                'min' => 0,
                'max' => 255,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'num',
            ]
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'dem',
            'options' => [
                'label' => 'Denominador *'
            ],
            'attributes' => [
                'min' => 0,
                'max' => 255,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'onchange' => 'calcularResultado()',
                'id' => 'dem',
            ]
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'resultado',
            'options' => [
                'label' => 'Resultado *'
            ],
            'attributes' => [
                'min' => 0,
                'max' => 255,
                'required' => $requiredForEditable,
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'resultado',
            ]
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'analisis',
            'options' => [
                'label' => 'Análisis del Resultado *'
            ],
            'attributes' => [
                'required' => true,
                'readonly' => $readonlyCodigo,
                'class' => 'form-control',
                'id' => 'analisis',
                'max' => 1000,
                'rows' => 2,
                'style' => 'resize: vertical;',
                'onchange' => 'validarAnalisis()',
                'placeholder' => 'Describa el progreso realizado...',
            ]
        ]);

        $this->add(['type' => Element\Hidden::class, 'name' => 'registradopor']);
        $this->add(['type' => Element\Hidden::class, 'name' => 'FechaRegistro']);
        $this->add(['type' => Element\Hidden::class, 'name' => 'modificadopor']);
        $this->add(['type' => Element\Hidden::class, 'name' => 'fechahoramod']);
    }

    public function getInputFilterSpecification()
    {
        return [];
    }
}
