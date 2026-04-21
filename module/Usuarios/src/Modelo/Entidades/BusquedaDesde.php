<?php

namespace Equipos\Modelo\Entidades;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Date;

class BusquedaDesde implements InputFilterAwareInterface {

    private $desdeBusq;
    private $hastaBusq;
    private $estadoBusq;
    //--
    private $filtroBusq = '';
    private $limitBusq = 0;
    private $orderBusq = '';
//------------------------------------------------------------------------------
    private $inputFilter;

//------------------------------------------------------------------------------

    public function __construct(array $datos = null) {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
        $this->filtroBusq = '';
        $this->limitBusq = 0;
        $this->orderBusq = '';
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
            'name' => 'desdeBusq',
            'required' => false,
            'validators' => [
                ['name' => Date::class, 'options' => ['format' => 'Y-m-d']],
            ],
        ]);

        $inputFilter->add([
            'name' => 'hastaBusq',
            'required' => false,
            'validators' => [
                ['name' => Date::class, 'options' => ['format' => 'Y-m-d']],
            ],
        ]);
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

//------------------------------------------------------------------------------

 public function getFiltroBusq() {
    $filtros = [];

    if (!empty($this->desdeBusq)) {
        $filtros[] = "DATE(celular.fechahorareg) >= '" . $this->desdeBusq . "'";
    }

    if (!empty($this->hastaBusq)) {
        $filtros[] = "DATE(celular.fechahorareg) <= '" . $this->hastaBusq . "'";
    }

    if (!empty($this->estadoBusq)) {
        $filtros[] = "celular.estado = '" . $this->estadoBusq . "'";
    }

    $this->filtroBusq = implode(" AND ", $filtros);

    return $this->filtroBusq;
}

//------------------------------------------------------------------------------

    public function getDesdeBusq() {
        return $this->desdeBusq;
    }

    public function getHastaBusq() {
        return $this->hastaBusq;
    }

    public function getEstadoBusq() {
        return $this->estadoBusq;
    }

    public function getLimitBusq() {
        return $this->limitBusq;
    }

    public function getOrderBusq() {
        return $this->orderBusq;
    }

    public function setDesdeBusq($desdeBusq): void {
        $this->desdeBusq = $desdeBusq;
    }

    public function setHastaBusq($hastaBusq): void {
        $this->hastaBusq = $hastaBusq;
    }

    public function setEstadoBusq($estadoBusq): void {
        $this->estadoBusq = $estadoBusq;
    }

    public function setLimitBusq($limitBusq): void {
        $this->limitBusq = $limitBusq;
    }

    public function setOrderBusq($orderBusq): void {
        $this->orderBusq = $orderBusq;
    }


}
