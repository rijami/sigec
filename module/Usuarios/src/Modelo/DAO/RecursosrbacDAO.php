<?php

namespace Usuarios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Usuarios\Modelo\Entidades\RecursoRbac;

class RecursosrbacDAO extends AbstractTableGateway {

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
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function getRecursoRbacByID($idRecurso = 0) {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->where(['recursos_rbac.idRecurso' => $idRecurso])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

//--------------------------------------------------------------------------

    public function getUsuarioRol($idUsuario = 0, $idRol = 0) {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
            'nombre1', 'nombre2', 'apellido1', 'apellido2',
            'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),
        ])->join('usuario_rol', 'usuario.idUsuario = usuario_rol.idUsuario', [
        ])->join('roles', 'usuario_rol.idRol = roles.idRol', [
            'idRol', 'rol'
        ])->where("usuario_rol.idUsuario = $idUsuario AND usuario_rol.idRol= $idRol")->limit(1);
        // echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

//------------------------------------------------------------------------------

    public function getExisteLogin($login = '') {
        $this->table = "usuario";
        $select = new Select($this->table);
        $select->columns(array(
            'existe' => new \Laminas\Db\Sql\Expression("COUNT(usuario.login)")
        ))->where("usuario.login = '$login'");
        // echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        return intval($datos[0]['existe']);
    }

//------------------------------------------------------------------------------
    public function getExisteRol($idRol = 0) {
        $this->table = "usuario_rol";
        $select = new Select($this->table);
        $select->columns(array(
            'existe' => new \Laminas\Db\Sql\Expression("COUNT(usuario_rol.idRol)")
        ))->where("usuario_rol.idRol = $idRol");
        // echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        return intval($datos[0]['existe']);
    }

//------------------------------------------------------------------------------

    public function getRoles() {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns(array(
            'idRol',
            'rol'
        ))->order("roles.rol ASC");
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------
    public function registrar(RecursoRbac $recursosRbacOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'recursos_rbac';
            $insert = new Insert($this->table);
            $datos = $recursosRbacOBJ->getArrayCopy();
            unset($datos['idRecurso']);
            $insert->values($datos);
            $this->insertWith($insert);
            // ------------ EJECUCION COMMIT ------------
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }

//------------------------------------------------------------------------------

    public function getEmpleadosSinUsuario() {
        $this->table = 'empleado';
        $select = new Select($this->table);
        $select->columns(array(
            'idEmpleado',
            'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))")
        ))->where("empleado.estado != 'Eliminado' AND (SELECT COUNT(usuario.idUsuario) FROM usuario WHERE usuario.idEmpleado = empleado.idEmpleado) = 0;)")->order("empleado.nombre1 ASC");
        //    echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//--------------------------------------------------------------------------
    public function verificarRolSeleccionado($idUsuario = 0, $idRol = 0) {
        $this->table = 'usuario_rol';
        $select = new Select($this->table);
        $select->columns([
            'cont' => new Expression("COUNT(usuario_rol.idUsuario AND usuario_rol.idRol)"),
        ])->where("usuario_rol.idUsuario = $idUsuario AND usuario_rol.idRol = $idRol ");
        // echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return ($datos[0]['cont'] > 0);
        } else {
            return true;
        }
    }

//------------------------------------------------------------------------------
    public function eliminar(RecursoRbac $recursosRbacOBJ = null) {
        try {
            $this->table = "recursos_rbac";
            $delete = new Delete($this->table);
            $delete->where("recursos_rbac.idRecurso = " . $recursosRbacOBJ->getIdRecurso());
            $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

//------------------------------------------------------------------------------
    public function editar(RecursoRbac $recursosRbacOBJ = null) {
        try {
            $this->table = "recursos_rbac";
            $update = new Update($this->table);
            $update->set([
                'metodo' => $recursosRbacOBJ->getMetodo(),
                'recurso' => $recursosRbacOBJ->getRecurso(),
            ])->where([
                'idRecurso' => $recursosRbacOBJ->getIdRecurso()
            ]);

            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

//------------------------------------------------------------------------------

    public function verificarRecursoyMetodoSeleccionado($recurso = '', $metodo = '') {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            'cont' => new Expression("COUNT(recursos_rbac.idRecurso)"),
        ])->where("recursos_rbac.recurso = '$recurso' AND recursos_rbac.metodo = '$metodo' ");

//        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return ($datos[0]['cont'] > 0);
        } else {
            return true;
        }
    }

//------------------------------------------------------------------------------

    public function getRecursosRBACparaCargar() {
        $recursos = [];
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            'recursoRBAC' => new Expression("CONCAT(recurso, ':', metodo)"),
        ])->order('recurso ASC');
//        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        foreach ($datos as $dato) {
            $recursos[] = $dato['recursoRBAC'];
        }
        return $recursos;
    }

//------------------------------------------------------------------------------

    public function setRecursosRBAC($RECURSOS_RBAC = []) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'recursos_rbac';
            foreach ($RECURSOS_RBAC as $recurso) {
                $partesRecurso = explode(':', $recurso);
                if (count($partesRecurso) == 2) {
                    $insert = new Insert($this->table);
                    $insert->values([
                        'recurso' => $partesRecurso[0],
                        'metodo' => $partesRecurso[1],
                    ]);
//                    echo $select->getSqlString();
                    $this->insertWith($insert);
                }
            }
            // ------------ EJECUCION COMMIT ------------
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }

//------------------------------------------------------------------------------
}
