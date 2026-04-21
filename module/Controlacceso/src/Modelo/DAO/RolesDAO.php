<?php

namespace Controlacceso\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Controlacceso\Modelo\Entidades\Rol;

class RolesDAO extends AbstractTableGateway {

    protected $table = 'roles';

//------------------------------------------------------------------------------

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

//------------------------------------------------------------------------------

    public function getRoles() {
        $this->table = 'roles';
        $select = new Select($this->table);
//        echo $select->getSqlString();
        $select->order('roles.idRol DESC')->limit(25);        
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function registrar(Rol $rolOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "roles";
            $insert = new Insert($this->table);
            $info = $rolOBJ->getArrayCopy();
            unset($info['idRol']);
            $insert->values($info);
            //            echo $insert->getSqlString();
            $this->insertWith($insert);
            $idRolInsert = $this->getLastInsertValue();

            // ------------ EJECUCION COMMIT ------------
            
            $connection->commit();
            return $idRolInsert;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }
    
//------------------------------------------------------------------------------
    
    public function getRol($idRol = 0) {
        $this->table = 'roles';
        $select = new Select($this->table);
//        $select->columns(array(
//            'idRol',
//            'rol',
//        ))->where("roles.idRol = $idRol")->limit(1); 
        $select->where("roles.idRol = $idRol")->limit(1);
//        echo $select->getSqlString();
        $info = $this->selectWith($select)->toArray();
        if (count($info) > 0) {
            return $info[0];
        } else {
            return null;
        }
    }
    
//------------------------------------------------------------------------------
    
    public function existeRol($rol = '') {
        $this->table = "roles";
        $select = new Select($this->table);
        $select->columns([
            'existe' => new Expression("COUNT(roles.idRol)")
        ]);
        $select->where("roles.rol = '$rol'");
//        echo $select->getSqlString();
        $info = $this->selectWith($select)->toArray();
        if (intval($info[0]['existe']) > 0) {
            return 1;
        } else {
            return 0;
        }
    }
    
//------------------------------------------------------------------------------
    
    public function editar(Rol $rolOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "roles";
            $update = new Update($this->table);
            $info = $rolOBJ->getArrayCopy();
            unset($info['idRol']);
//            $update->set($info)->where("roles.idRol = " . $rolOBJ->getIdRol());
            $update->set($info)->where(['idRol' => $rolOBJ->getIdRol()]);
//            echo $update->getSqlString();            exit();
            $this->updateWith($update);

            // ------------ EJECUCION COMMIT ------------
            
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
//            echo 'Error: ' . $e->getMessage();
            throw new \Exception($e);
        }
    }
    
//------------------------------------------------------------------------------
    
    public function eliminar(Rol $rolOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "roles";
            $delete = new Delete($this->table);            
            $delete->where(['idRol' => $rolOBJ->getIdRol()]);
//            echo $update->getSqlString();            exit();
            $this->deleteWith($delete);

            // ------------ EJECUCION COMMIT ------------
            
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
//            echo 'Error: ' . $e->getMessage();
            throw new \Exception($e);
        }
    }    

}
