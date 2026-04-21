<?php

namespace Usuarios\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Rol implements InputFilterAwareInterface {

    private $idRol;
    private $rol;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;
//------------------------------------------------------------------------------
    private $inputFilter;

//------------------------------------------------------------------------------

    public function __construct(array $datos = null) {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
    }

//------------------------------------------------------------------------------

    public function exchangeArray($data) {
        $metodos = get_class_methods($this);
        foreach ($data as $key => $value) {
            $metodo = 'set' . ucfirst($key);
            if (in_array($metodo, $metodos)) {
                $this->$metodo($value);
            }
        }
    }

//------------------------------------------------------------------------------

    public function getArrayCopy() {
        $datos = get_object_vars($this);
        unset($datos['inputFilter']);
        return $datos;
    }

//------------------------------------------------------------------------------

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new DomainException(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }

//------------------------------------------------------------------------------

    public function getInputFilter() {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }
        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name' => 'rol',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => \Laminas\Filter\Callback::class, 'options' => [
                        'callback' => function ($value) {
                            return ucwords(strtolower($value));
                        }
                    ]],
            ],
            'validators' => [
                ['name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 80,
                    ],
                ],
            ],
        ]);
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

//------------------------------------------------------------------------------
    public function getIdRol() {
        return $this->idRol;
    }

    public function getRol() {
        return $this->rol;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getRegistradopor() {
        return $this->registradopor;
    }

    public function getModificadopor() {
        return $this->modificadopor;
    }

    public function getFechahorareg() {
        return $this->fechahorareg;
    }

    public function getFechahoramod() {
        return $this->fechahoramod;
    }

    public function setIdRol($idRol): void {
        $this->idRol = $idRol;
    }

    public function setRol($rol): void {
        $this->rol = $rol;
    }

    public function setEstado($estado): void {
        $this->estado = $estado;
    }

    public function setRegistradopor($registradopor): void {
        $this->registradopor = $registradopor;
    }

    public function setModificadopor($modificadopor): void {
        $this->modificadopor = $modificadopor;
    }

    public function setFechahorareg($fechahorareg): void {
        $this->fechahorareg = $fechahorareg;
    }

    public function setFechahoramod($fechahoramod): void {
        $this->fechahoramod = $fechahoramod;
    }
}
