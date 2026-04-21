<?php

namespace Usuarios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Usuarios\Modelo\Entidades\Rol;

class RolDAO extends AbstractTableGateway {

    protected $table = 'usuario';

//------------------------------------------------------------------------------

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

//------------------------------------------------------------------------------

    public function getRolByID($idRol = 0) {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns([
            '*'
//        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
//            'nombre1', 'nombre2', 'apellido1', 'apellido2',
//            'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),        
        ])->where(['roles.idRol' => $idRol])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

//------------------------------------------------------------------------------
    public function getRoles() {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->where("roles.estado != 'Eliminado' ");
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function registrar(Rol $rolOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "roles";
            $insert = new Insert($this->table);
            $datos = $rolOBJ->getArrayCopy();
            unset($datos['idRol']);
            $insert->values($datos);
            //            echo $insert->getSqlString();
            $this->insertWith($insert);
            $idRolInsert = $this->getLastInsertValue();
            //------------------------------------------------------------------            
            $connection->commit();
            return $idRolInsert;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }


//--------------------------------------------------------------------------------
    public function eliminar(Rol $rolOBJ = null) {
        try {
            $this->table = 'roles';
            $update = new Update($this->table);
            $datos = $rolOBJ->getArrayCopy();
            unset($datos['idRol']);
            $update->set($datos)->where("roles.idRol = " . $rolOBJ->getIdRol());
//            echo $insert->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //--------------------------------------------------------------------------
    public function verificarRolSeleccionado($rol = '') {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns([
            'cont' => new Expression("COUNT(roles.idRol)"),
        ])->where("roles.rol= '$rol'");
        // echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return ($datos[0]['cont'] > 0);
        } else {
            return true;
        }
    }

//------------------------------------------------------------------------------
}
