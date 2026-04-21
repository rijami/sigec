<?php

namespace Controlacceso\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Controlacceso\Modelo\Entidades\Recursorbac;

class RecursoRbacDAO extends AbstractTableGateway {

    protected $table = 'recursos_rbac';

//------------------------------------------------------------------------------

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

//------------------------------------------------------------------------------

    public function getRecursosRbac() {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
//        echo $select->getSqlString();
        $select->order('recursos_rbac.idRecurso DESC')->limit(25);
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function registrar(RecursoRbac $recursoRbacOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "recursos_rbac";
            $insert = new Insert($this->table);
            $datos = $recursoRbacOBJ->getArrayCopy();
            unset($datos['idRecurso']);
            $insert->values($datos);
            //            echo $insert->getSqlString();
            $this->insertWith($insert);
            $idRecursoRbacInsert = $this->getLastInsertValue();

            // ------------ EJECUCION COMMIT ------------
            
            $connection->commit();
            return $idRecursoRbacInsert;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }
    
//------------------------------------------------------------------------------
    
    public function getRecurso($idRecurso = 0) {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
//        $select->columns(array(
//            'idRecurso',
//            'recurso',
//            'metodo',
//        ))->where("recursos_rbac.idRecurso = $idRecurso")->limit(1);
        $select->where("recursos_rbac.idRecurso = $idRecurso")->limit(1);
//        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    
//------------------------------------------------------------------------------
    
    public function editar(RecursoRbac $recursoRbacOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "recursos_rbac";
            $update = new Update($this->table);
            $datos = $recursoRbacOBJ->getArrayCopy();
            unset($datos['idRecurso']);
            $update->set($datos)->where("recursos_rbac.idRecurso = " . $recursoRbacOBJ->getIdRecurso());
            //echo $update->getSqlString();            exit();
            $this->updateWith($update);

            // ------------ EJECUCION COMMIT ------------
            
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }
    
//------------------------------------------------------------------------------
    
    public function eliminar(RecursoRbac $recursoRbacOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "recursos_rbac";
            $delete = new Delete($this->table);
            $datos = $recursoRbacOBJ->getArrayCopy();
            unset($datos['idRecurso']);
            $delete->set($datos)->where("recursos_rbac.idRecurso = " . $recursoRbacOBJ->getIdRecurso());
            //echo $update->getSqlString();            exit();
            $this->deleteWith($delete);

            // ------------ EJECUCION COMMIT ------------
            
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }
    
}
