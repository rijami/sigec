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

class Responsable implements InputFilterAwareInterface
{

    private $idResponsable;
    private $correo;
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


        $inputFilter = new InputFilter();


        $inputFilter->add([
            'name' => 'idResponsable',
            'required' => false, // No requerido al crear, sí al editar (generalmente se maneja diferente)
            'filters' => [['name' => ToInt::class]],
        ]);

        $inputFilter->add([
            'name' => 'correo',
            'required' => true,
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
    public function getIdResponsable()
    {
        return $this->idResponsable;
    }
    public function setIdResponsable($idResponsable)
    {
        $this->idResponsable = $idResponsable;
    }
    public function getCorreo()
    {
        return $this->correo;
    }
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    }
    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }


}
