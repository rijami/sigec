<?php

namespace Usuarios\Modelo\Entidades;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class Usuario implements InputFilterAwareInterface
{

    private $idUsuario;
    private $idEmpleado;
    //private $usuario;
    private $login;
    private $password;
    // $passwordseguro;
    private $fechaultingreso;
    private $fechaultfallido;
    private $contFallidos;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;
    //private $fechacambioclave;
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
            $metodo = 'set' . ucfirst($key);
            if (in_array($metodo, $metodos)) {
                $this->$metodo($value);
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
            'name' => 'idEmpleado',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                [
                    'name' => GreaterThan::class,
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
// Getters y Setters generados automáticamente
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function getIdEmpleado()
    {
        return $this->idEmpleado;
    }
    public function setIdEmpleado($idEmpleado)
    {
        $this->idEmpleado = $idEmpleado;
    }

    public function getLogin()
    {
        return $this->login;
    }
    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getPassword()
    {
        return $this->password;
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getFechaultingreso()
    {
        return $this->fechaultingreso;
    }
    public function setFechaultingreso($fechaultingreso)
    {
        $this->fechaultingreso = $fechaultingreso;
    }

    public function getFechaultfallido()
    {
        return $this->fechaultfallido;
    }
    public function setFechaultfallido($fechaultfallido)
    {
        $this->fechaultfallido = $fechaultfallido;
    }

    public function getContFallidos()
    {
        return $this->contFallidos;
    }
    public function setContFallidos($contFallidos)
    {
        $this->contFallidos = $contFallidos;
    }

    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }
    public function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;
    }

    public function getModificadopor()
    {
        return $this->modificadopor;
    }
    public function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;
    }

    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }
    public function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;
    }

    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }
    public function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;
    }

}
