<?php

namespace Usuarios\Modelo\Entidades;

use DomainException;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class UsuarioRol implements InputFilterAwareInterface {

    private $idUsuario;
    private $idRol;
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
            'name' => 'idUsuario',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                ['name' => GreaterThan::class,
                    'options' => [
                        'min' => 0,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'idRol',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                ['name' => GreaterThan::class,
                    'options' => [
                        'min' => 0,
                    ],
                ],
            ],
        ]);
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

//------------------------------------------------------------------------------

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario): void {
        $this->idUsuario = $idUsuario;
    }

    public function getIdRol() {
        return $this->idRol;
    }

    public function getRol() {
        return $this->rol;
    }

    public function setIdRol($idRol): void {
        $this->idRol = $idRol;
    }

    public function setRol($rol): void {
        $this->rol = $rol;
    }
}
