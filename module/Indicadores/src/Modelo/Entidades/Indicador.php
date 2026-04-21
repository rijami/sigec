<?php

namespace Indicadores\Modelo\Entidades;

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

class Indicador implements InputFilterAwareInterface
{

    private $id_indicador;
    private $idProceso;
    private $idCoordinacion;
    private $codigo;
    private $nombre_indicador;
    private $objetivo;
    private $periodicidad;
    private $fuente_informacion;
    private $meta;
    private $TIPO_INDICADOR;
    private $SENTIDO;
    private $FechaRegistro;
    private $registradopor;
    private $fechahoramod;
    private $estado;
    private $modificadopor;

    //------------------------------------------------------------------------------


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
            'name' => 'id_indicador',
            'required' => false, // No requerido al crear, sí al editar (generalmente se maneja diferente)
            'filters' => [['name' => ToInt::class]],
        ]);


        $inputFilter->add([
            'name' => 'idProceso',
            'required' => true,
            'filters' => [['name' => ToInt::class]],
            'validators' => [
                ['name' => Digits::class],
                ['name' => GreaterThan::class, 'options' => ['min' => 0]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'idCoordinacion',
            'required' => true,
            'filters' => [['name' => ToInt::class]],
            'validators' => [
                ['name' => Digits::class],
                ['name' => GreaterThan::class, 'options' => ['min' => 0]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'codigo',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class, 'options' => ['encoding' => 'UTF-8']], // Opcional: forzar mayúsculas
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 50]],
            ],
        ]);


        $inputFilter->add([
            'name' => 'nombre_indicador',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class, 'options' => ['encoding' => 'UTF-8']], // Opcional
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 300]],
            ],
        ]);


        $inputFilter->add([
            'name' => 'objetivo',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],

        ]);
        $inputFilter->add([
            'name' => 'periodicidad',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],

        ]);

        $inputFilter->add([
            'name' => 'fuente_informacion',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 200]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'meta',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 50]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'TIPO_INDICADOR',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => InArray::class, 'options' => ['haystack' => ['Acomulativo', 'No Acomulativo'], 'nullable' => true]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'SENTIDO',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => InArray::class, 'options' => ['haystack' => ['Ascendente', 'Descendente'], 'nullable' => true]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'FechaRegistro',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8']],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fechahoramod',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8']],
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
            'name' => 'registradopor',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8']],
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
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8']],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    // Getters y Setters (Ajustados a las propiedades reales)
    //------------------------------------------------------------------------------

    public function getIdIndicador()
    {
        return $this->id_indicador;
    }
    public function setIdIndicador($id_indicador)
    {
        $this->id_indicador = $id_indicador;
        return $this;
    }

    public function getIdProceso()
    {
        return $this->idProceso;
    }
    public function setIdProceso($idProceso)
    {
        $this->idProceso = $idProceso;
        return $this;
    }

    public function getIdCoordinacion()
    {
        return $this->idCoordinacion;
    }
    public function setIdCoordinacion($idCoordinacion)
    {
        $this->idCoordinacion = $idCoordinacion;
        return $this;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
        return $this;
    }

    public function getNombreIndicador()
    {
        return $this->nombre_indicador;
    }
    public function setNombreIndicador($nombre_indicador)
    {
        $this->nombre_indicador = $nombre_indicador;
        return $this;
    }

    public function getObjetivo()
    {
        return $this->objetivo;
    }
    public function setObjetivo($objetivo)
    {
        $this->objetivo = $objetivo;
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

    public function getFuenteInformacion()
    {
        return $this->fuente_informacion;
    }
    public function setFuenteInformacion($fuente_informacion)
    {
        $this->fuente_informacion = $fuente_informacion;
        return $this;
    }

    public function getMeta()
    {
        return $this->meta;
    }
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function getTipoIndicador()
    {
        return $this->TIPO_INDICADOR;
    }
    public function setTipoIndicador($TIPO_INDICADOR)
    {
        $this->TIPO_INDICADOR = $TIPO_INDICADOR;
        return $this;
    }

    public function getSentido()
    {
        return $this->SENTIDO;
    }
    public function setSentido($SENTIDO)
    {
        $this->SENTIDO = $SENTIDO;
        return $this;
    }

    public function getFechaRegistro()
    {
        return $this->FechaRegistro;
    }
    public function setFechaRegistro($FechaRegistro)
    {
        $this->FechaRegistro = $FechaRegistro;
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

}
