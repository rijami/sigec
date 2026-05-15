<?php

namespace Reportes\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\InArray;

class Reporte implements InputFilterAwareInterface
{

    private $idReporte;
    private $nombre_reporte;
    private $nombre_archivo;
    private $plataforma;
    private $firmas_requeridas;
    private $periodicidad;
    private $normatividad;
    private $fechahorareg;
    private $registradopor;
    private $fechahoramod;
    private $estado;
    private $modificadopor;
    private $respon_reporta;

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
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'idReporte',
            'required' => false,
            'filters' => [['name' => ToInt::class]],
        ]);

        $inputFilter->add([
            'name' => 'nombre_reporte',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 300]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'nombre_archivo',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 255]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'plataforma',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

            $inputFilter->add([
            'name' => 'firmas_requeridas',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);


        $inputFilter->add([
            'name' => 'periodicidad',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'normatividad',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 255]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fechahorareg',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'registradopor',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fechahoramod',
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

        $inputFilter->add([
            'name' => 'modificadopor',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'respon_reporta',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    // Getters y Setters (Ajustados a las propiedades reales)
    //------------------------------------------------------------------------------

    public function getIdReporte()
    {
        return $this->idReporte;
    }
    public function setIdReporte($idReporte)
    {
        $this->idReporte = $idReporte;
        return $this;
    }

    public function getNombreReporte()
    {
        return $this->nombre_reporte;
    }
    public function setNombreReporte($nombre_reporte)
    {
        $this->nombre_reporte = $nombre_reporte;
        return $this;
    }

    public function getNombreArchivo()
    {
        return $this->nombre_archivo;
    }
    public function setNombreArchivo($nombre_archivo)
    {
        $this->nombre_archivo = $nombre_archivo;
        return $this;
    }

    public function getPlataforma()
    {
        return $this->plataforma;
    }
    public function setPlataforma($plataforma)
    {
        $this->plataforma = $plataforma;
        return $this;
    }

    public function getFirmasRequeridas()
    {
        return $this->firmas_requeridas;
    }
    public function setFirmasRequeridas($firmas_requeridas)
    {
        $this->firmas_requeridas = $firmas_requeridas;
        return $this;
    }

    public function getPeriodicidad()
    {
        return $this->periodicidad;
    }
    public function setPeriodicidad($periodicidad)
    {
        $this->periodicidad = $periodicidad;
        return $this;
    }

    public function getNormatividad()
    {
        return $this->normatividad;
    }
    public function setNormatividad($normatividad)
    {
        $this->normatividad = $normatividad;
        return $this;
    }

    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }
    public function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;
        return $this;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }
    public function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;
        return $this;
    }

    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }
    public function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;
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

    public function getModificadopor()
    {
        return $this->modificadopor;
    }
    public function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;
        return $this;
    }

    public function getResponReporta()
    {
        return $this->respon_reporta;
    }
    public function setResponReporta($respon_reporta)
    {
        $this->respon_reporta = $respon_reporta;
        return $this;
    }

}
