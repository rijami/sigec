<?php

namespace Dashboard\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;

class Tablero implements InputFilterAwareInterface
{
    private $inputFilter;

    private $idTablero;
    private $nombre;
    private $idCoordinacion;
    private $idProceso;
    private $fecha_creacion;
    private $fecha_entregada;
    private $estado;
    private $manual;
    private $fuentes_informacion;
    private $necesidad;
    private $historia_usuario;
    private $enlace;
    private $registradopor;
    private $fechahoramod;
    private $modificadopor;

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
        foreach ($data as $key => $value) {
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
        throw new DomainException(sprintf(
            '%s no permite inyección de un input filter alterno',
            __CLASS__
        ));
    }

    //------------------------------------------------------------------------------
    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();


        // NOMBRE
        $inputFilter->add([
            'name' => 'nombre',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class, 'options' => ['encoding' => 'UTF-8']],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        // ID COORDINACION
        $inputFilter->add([
            'name' => 'idCoordinacion',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                [
                    'name' => GreaterThan::class,
                    'options' => ['min' => 0],
                ],
            ],
        ]);

        // ID PROCESO
        $inputFilter->add([
            'name' => 'idProceso',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                [
                    'name' => GreaterThan::class,
                    'options' => ['min' => 0],
                ],
            ],
        ]);

        // FECHA CREACION
        $inputFilter->add([
            'name' => 'fecha_creacion',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);

        // FECHA ENTREGADA
        $inputFilter->add([
            'name' => 'fecha_entregada',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                    ],
                ],
            ],
        ]);

        // ESTADO
        $inputFilter->add([
            'name' => 'estado',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [
                            'Pendiente',
                            'En produccion',
                            'Entregado',

                        ],
                    ],
                ],
            ],
        ]);

        // MANUAL
        $inputFilter->add([
            'name' => 'manual',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [0, 1],
                    ],
                ],
            ],
        ]);

        // FUENTES INFORMACION
        $inputFilter->add([
            'name' => 'fuentes_informacion',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [0, 1],
                    ],
                ],
            ],
        ]);

        // NECESIDAD
        $inputFilter->add([
            'name' => 'necesidad',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [0, 1],
                    ],
                ],
            ],
        ]);

        // HISTORIA USUARIO
        $inputFilter->add([
            'name' => 'historia_usuario',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [0, 1],
                    ],
                ],
            ],
        ]);

        // ENLACE
        $inputFilter->add([
            'name' => 'enlace',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 2000,
                    ],
                ],
            ],
        ]);

        // REGISTRADO POR
        $inputFilter->add([
            'name' => 'registradopor',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class, 'options' => ['encoding' => 'UTF-8']],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 50,
                    ],
                ],
            ],
        ]);

        // FECHA HORA MOD
        $inputFilter->add([
            'name' => 'fechahoramod',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);

        // MODIFICADO POR
        $inputFilter->add([
            'name' => 'modificadopor',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class, 'options' => ['encoding' => 'UTF-8']],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 50,
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------
    // GETTERS Y SETTERS
    //------------------------------------------------------------------------------

    public function getIdTablero()
    {
        return $this->idTablero;
    }
    public function setIdTablero($idTablero)
    {
        $this->idTablero = $idTablero;
    }

    public function getNombre()
    {
        return $this->nombre;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getIdCoordinacion()
    {
        return $this->idCoordinacion;
    }
    public function setIdCoordinacion($idCoordinacion)
    {
        $this->idCoordinacion = $idCoordinacion;
    }

    public function getIdProceso()
    {
        return $this->idProceso;
    }
    public function setIdProceso($idProceso)
    {
        $this->idProceso = $idProceso;
    }

    public function getFecha_creacion()
    {
        return $this->fecha_creacion;
    }
    public function setFecha_creacion($fecha_creacion)
    {
        $this->fecha_creacion = $fecha_creacion;
    }

    public function getFecha_entregada()
    {
        return $this->fecha_entregada;
    }
    public function setFecha_entregada($fecha_entregada)
    {
        $this->fecha_entregada = $fecha_entregada;
    }

    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function getManual()
    {
        return $this->manual;
    }
    public function setManual($manual)
    {
        $this->manual = $manual;
    }

    public function getFuentes_informacion()
    {
        return $this->fuentes_informacion;
    }
    public function setFuentes_informacion($fuentes_informacion)
    {
        $this->fuentes_informacion = $fuentes_informacion;
    }

    public function getNecesidad()
    {
        return $this->necesidad;
    }
    public function setNecesidad($necesidad)
    {
        $this->necesidad = $necesidad;
    }

    public function getHistoria_usuario()
    {
        return $this->historia_usuario;
    }
    public function setHistoria_usuario($historia_usuario)
    {
        $this->historia_usuario = $historia_usuario;
    }

    public function getEnlace()
    {
        return $this->enlace;
    }
    public function setEnlace($enlace)
    {
        $this->enlace = $enlace;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }
    public function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;
    }

    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }
    public function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;
    }

    public function getModificadopor()
    {
        return $this->modificadopor;
    }
    public function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;
    }
}