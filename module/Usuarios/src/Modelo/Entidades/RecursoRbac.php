<?php

namespace Usuarios\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class RecursoRbac implements InputFilterAwareInterface {

    private $idRecurso;
    private $recurso;
    private $metodo;
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
            'name' => 'recurso',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
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
        $inputFilter->add([
            'name' => 'metodo',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
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

    public function getIdRecurso() {
        return $this->idRecurso;
    }

    public function getRecurso() {
        return $this->recurso;
    }

    public function getMetodo() {
        return $this->metodo;
    }

    public function setIdRecurso($idRecurso): void {
        $this->idRecurso = $idRecurso;
    }

    public function setRecurso($recurso): void {
        $this->recurso = $recurso;
    }

    public function setMetodo($metodo): void {
        $this->metodo = $metodo;
    }
}
