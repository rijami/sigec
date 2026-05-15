<?php

namespace Reportes\Modelo\Entidades;

use DomainException;
use Laminas\Filter\Digits;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\StringLength;

class Programacion implements InputFilterAwareInterface
{


    private $idProgramacion;
    private $idReporte;
    private $mes;
    private $fecha_corte;
    private $fecha_solicitud;
    private $fecha_limite;
    private $dia_semana;
    private $semana_mes;
    private $fecha_efectiva;
    private $evidencia;
    private $respon_reporta;
    private $recordatorio;
    private $estado;

    //------------------------------------------------------------------------------

    private $inputFilter;
    //------------------------------------------------------------------------------

    public function __construct(array $datos = null)
    {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
    }

    //------------------------------------------------------------------------------

    public function exchangeArray($data)
    {
        $metodos = get_class_methods($this);
        foreach ($data as $key => $value) {
            $metodo = 'set' . ucfirst(str_replace('_', '', $key));
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    //------------------------------------------------------------------------------

    public function getArrayCopy()
    {
        $datos = get_object_vars($this);
        unset($datos['inputFilter']);
        return $datos;
    }

    //------------------------------------------------------------------------------

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }


    //------------------------------------------------------------------------------

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'idProgramacion',
            'required' => false,
            'filters' => [['name' => ToInt::class]],
        ]);

        $inputFilter->add([
            'name' => 'idReporte',
            'required' => true,
            'filters' => [['name' => ToInt::class]],
        ]);

        $inputFilter->add([
            'name' => 'mes',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fecha_corte',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fecha_solicitud',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fecha_limite',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'dia_semana',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'semana_mes',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fecha_efectiva',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'evidencia',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'respon_reporta',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'recordatorio',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'estado',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 50]],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    // Getters y Setters (Ajustados a las propiedades reales)
    //------------------------------------------------------------------------------

    public function getIdProgramacion()
    {
        return $this->idProgramacion;
    }

    public function setIdProgramacion($idProgramacion)
    {
        $this->idProgramacion = $idProgramacion;
        return $this;
    }

    public function getIdReporte()
    {
        return $this->idReporte;
    }

    public function setIdReporte($idReporte)
    {
        $this->idReporte = $idReporte;
        return $this;
    }

    public function getMes()
    {
        return $this->mes;
    }

    public function setMes($mes)
    {
        $this->mes = $mes;
        return $this;
    }

    public function getFechaCorte()
    {
        return $this->fecha_corte;
    }

    public function setFechaCorte($fechaCorte)
    {
        $this->fecha_corte = $fechaCorte;
        return $this;
    }

    public function getFechaSolicitud()
    {
        return $this->fecha_solicitud;
    }

    public function setFechaSolicitud($fechaSolicitud)
    {
        $this->fecha_solicitud = $fechaSolicitud;
        return $this;
    }

    public function getFechaLimite()
    {
        return $this->fecha_limite;
    }

    public function setFechaLimite($fechaLimite)
    {
        $this->fecha_limite = $fechaLimite;
        return $this;
    }

    public function getDiaSemana()
    {
        return $this->dia_semana;
    }

    public function setDiaSemana($diaSemana)
    {
        $this->dia_semana = $diaSemana;
        return $this;
    }

    public function getSemanaMes()
    {
        return $this->semana_mes;
    }

    public function setSemanaMes($semanaMes)
    {
        $this->semana_mes = $semanaMes;
        return $this;
    }

    public function getFechaEfectiva()
    {
        return $this->fecha_efectiva;
    }

    public function setFechaEfectiva($fechaEfectiva)
    {
        $this->fecha_efectiva = $fechaEfectiva;
        return $this;
    }

    public function getEvidencia()
    {
        return $this->evidencia;
    }

    public function setEvidencia($evidencia)
    {
        $this->evidencia = $evidencia;
        return $this;
    }

    public function getResponReporta()
    {
        return $this->respon_reporta;
    }

    public function setResponReporta($responReporta)
    {
        $this->respon_reporta = $responReporta;
        return $this;
    }

    public function getRecordatorio()
    {
        return $this->recordatorio;
    }

    public function setRecordatorio($recordatorio)
    {
        $this->recordatorio = $recordatorio;
        return $this;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
        return $this;
    }
}
