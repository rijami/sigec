<?php

namespace Reportes\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class ReportesForm extends Form implements InputFilterProviderInterface
{

    public function __construct(
        $accion = '',
        $listaEstadistica = [],

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
                $onsubmit = 'return validarAsignar(event, this)';
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
            'name' => 'idReporte',
            'options' => [
                'label' => 'ID Reporte'
            ],
            'attributes' => [
                'readonly' => true,
                'disabled' => $id_indicadorDisabled,
                'class' => 'form-control',
                'id' => 'idReporte',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'respon_reporta',
            'options' => [
                'label' => 'Reponsable de Reportar *',
                'empty_option' => 'Seleccione una reponsable...',
                'value_options' => $listaEstadistica,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'respon_reporta',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_reporte',
            'options' => [
                'label' => 'Nombre del Reporte *'
            ],
            'attributes' => [
                'min' => 0,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'nombre_reporte',
            ]
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_archivo',
            'options' => [
                'label' => 'Nombre del Archivo *'
            ],
            'attributes' => [
                'maxlength' => 500,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'nombre_archivo',
            ]
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'periodicidad',
            'options' => [
                'label' => 'Periodicidad *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['SEMANAL' => 'SEMANAL', 'MENSUAL' => 'MENSUAL', 'BIMESTRAL' => 'BIMESTRAL', 'TRIMESTRAL' => 'TRIMESTRAL', 'SEMESTRAL' => 'SEMESTRAL', 'ANUAL' => 'ANUAL', 'OCASIONAL' => 'OCASIONAL'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'periodicidad',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'plataforma',
            'options' => [
                'label' => 'Plataforma *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'SUPERSALUD NRVCC' => 'SUPERSALUD NRVCC',
                    'FTPS UGPP' => 'FTPS UGPP',
                    'FTP UGPP' => 'FTP UGPP',
                    'PISIS' => 'PISIS',
                    'STORM LOCAL Y https://rendicion.contraloria.gov.co/stormWeb/' => 'STORM LOCAL Y https://rendicion.contraloria.gov.co/stormWeb/',
                    'aseguramiento@idsn.gov.co' => ' aseguramiento@idsn.gov.co',
                    'AGENTE DE CARGUE' => 'AGENTE DE CARGUE',
                    'APPUI' => 'APPUI',
                    'CAC' => 'CAC',
                    'CHIP LOCAL y RADICACION EN WEB' => 'CHIP LOCAL y RADICACION EN WEB',
                    'DATA Q' => 'DATA Q',
                    'FTP' => 'FTP',
                    'SISCAC' => 'SISCAC',
                    'https://www.cisa.gov.co/sigaweb/#/' => 'https://www.cisa.gov.co/sigaweb/#/',
                    'miseguridadsocial.gov.co' => 'miseguridadsocial.gov.co',
                    'Supersalud y servicios uncologogicos serviciosoncologicos@outlook.com crico@supersalud.gov.co' => 'Supersalud y servicios uncologogicos serviciosoncologicos@outlook.com crico@supersalud.gov.co'
                ],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'plataforma',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'firmas_requeridas',
            'options' => [
                'label' => 'Firmas Requeridas *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['GERENTE' => 'GERENTE', 'GERENTE, REVISOR Y CONTADOR' => 'GERENTE, REVISOR Y CONTADOR', 'NINGUNA' => 'NINGUNA'],
            ],
            'attributes' => [
                'required' => $requiredForEditable,
                'disabled' => $disabledForSelects,
                'class' => 'form-control',
                'id' => 'firmas_requeridas',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'normatividad',
            'options' => [
                'label' => 'Normatividad *'
            ],
            'attributes' => [
                'min' => 0,
                'required' => $requiredForEditable,
                'readonly' => $readonlyForInputs,
                'class' => 'form-control',
                'id' => 'normatividad',
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
            'name' => 'fechahorareg',
            'options' => [
                'label' => 'Fecha de Registro'
            ],
            'attributes' => [
                'readonly' => true,
                'disabled' => true,
                'class' => 'form-control',
                'id' => 'fechahorareg',
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
