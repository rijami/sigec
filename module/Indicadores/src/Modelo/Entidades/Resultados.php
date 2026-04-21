<?php

namespace Indicadores\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class Resultados implements InputFilterAwareInterface {

    private $id_result;
    private $id_indicador;
    private $mes;
    private $num;
    private $dem;
    private $resultado;
    private $analisis;
    private $registradopor;
    private $modificadopor;
    private $FechaRegistro;
    private $fechahoramod;
    private $inputFilter;

    public function __construct(array $datos = null) {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
    }

    public function exchangeArray($data) {
        $this->id_result = $data['id_result'] ?? null;
        $this->id_indicador = $data['id_indicador'] ?? null;
        $this->mes = $data['mes'] ?? null;
        $this->num = $data['num'] ?? null;
        $this->dem = $data['dem'] ?? null;
        $this->resultado = $data['resultado'] ?? null;
        $this->analisis = $data['analisis'] ?? null;
        $this->registradopor = $data['registradopor'] ?? null;
        $this->modificadopor = $data['modificadopor'] ?? null;
        $this->FechaRegistro = $data['FechaRegistro'] ?? null;
        $this->fechahoramod = $data['fechahoramod'] ?? null;
    }

    public function getArrayCopy() {
        $datos = [
            'id_result' => $this->id_result,
            'id_indicador' => $this->id_indicador,
            'mes' => $this->mes,
            'num' => $this->num,
            'dem' => $this->dem,
            'resultado' => $this->resultado,
            'analisis' => $this->analisis,
            'registradopor' => $this->registradopor,
            'modificadopor' => $this->modificadopor,
            'FechaRegistro' => $this->FechaRegistro,
            'fechahoramod' => $this->fechahoramod
        ];
        return $datos;
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new DomainException(sprintf('%s no permite inyección', __CLASS__));
    }

    public function getInputFilter() {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'id_result',
            'required' => false,
            'filters' => [['name' => ToInt::class]],
        ]);
        $inputFilter->add([
            'name' => 'id_indicador',
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
            'name' => 'num',
            'required' => true,
            'filters' => [['name' => ToInt::class]],
        ]);
        $inputFilter->add([
            'name' => 'dem',
            'required' => true,
            'filters' => [['name' => ToInt::class]],
        ]);
        $inputFilter->add([
            'name' => 'resultado',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'analisis',
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
        ]);
        $inputFilter->add([
            'name' => 'modificadopor',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'FechaRegistro',
            'required' => false,
            'filters' => [],
        ]);
        $inputFilter->add([
            'name' => 'fechahoramod',
            'required' => false,
            'filters' => [],
        ]);
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // Getters y Setters
    public function getId_result() { return $this->id_result; }
    public function setId_result($id_result) { $this->id_result = $id_result; }

    public function getId_indicador() { return $this->id_indicador; }
    public function setId_indicador($id_indicador) { $this->id_indicador = $id_indicador; }

    public function getMes() { return $this->mes; }
    public function setMes($mes) { $this->mes = $mes; }

    public function getNum() { return $this->num; }
    public function setNum($num) { $this->num = $num; }

    public function getDem() { return $this->dem; }
    public function setDem($dem) { $this->dem = $dem; }

    public function getResultado() { return $this->resultado; }
    public function setResultado($resultado) { $this->resultado = $resultado; }

    public function getAnalisis() { return $this->analisis; }
    public function setAnalisis($analisis) { $this->analisis = $analisis; }

    public function getRegistradopor() { return $this->registradopor; }
    public function setRegistradopor($registradopor) { $this->registradopor = $registradopor; }

    public function getModificadopor() { return $this->modificadopor; }
    public function setModificadopor($modificadopor) { $this->modificadopor = $modificadopor; }

    public function getFechaRegistro() { return $this->FechaRegistro; }
    public function setFechaRegistro($FechaRegistro) { $this->FechaRegistro = $FechaRegistro; }

    public function getFechahoramod() { return $this->fechahoramod; }
    public function setFechahoramod($fechahoramod) { $this->fechahoramod = $fechahoramod; }
}
