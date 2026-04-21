<?php

namespace Usuarios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Delete;
use Usuarios\Modelo\Entidades\UsuarioRol;

class UsuarioRolDAO extends AbstractTableGateway {

    protected $table = 'usuario';

//------------------------------------------------------------------------------

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

//------------------------------------------------------------------------------ 
    public function getUsuarios($filtro = '') {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
            'nombre1', 'nombre2', 'apellido1', 'apellido2',
            'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),
        ])->join('usuario_rol', 'usuario.idUsuario = usuario_rol.idUsuario', [
        ])->join('roles', 'usuario_rol.idRol = roles.idRol', [
            'rol'
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("usuario.idUsuario DESC");
        }
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function getRolesbyid($idUsuario = 0) {
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
        ])->where(['usuario.idUsuario' => $idUsuario]);
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function getUsuarioByID($idUsuario = 0) {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
            'nombre1', 'nombre2', 'apellido1', 'apellido2', 'identificacion',
            'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),
        ])->where(['usuario.idUsuario' => $idUsuario])->limit(1);
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
    public function registrar(UsuarioRol $usuarioRolOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = "usuario_rol";
            $insert = new Insert($this->table);
            $datos = $usuarioRolOBJ->getArrayCopy();
            $insert->values($datos);
            // echo $insert->getSqlString();
            $this->insertWith($insert);
            $idPromocionInsert = $this->getLastInsertValue();
            // ------------ EJECUCION COMMIT ------------
            $connection->commit();
            return $idPromocionInsert;
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
    public function eliminar(UsuarioRol $usuarioRolOBJ = null) {
        try {
            $this->table = "usuario_rol";
            $delete = new Delete($this->table);
            $delete->where("usuario_rol.idUsuario = " . $usuarioRolOBJ->getIdUsuario() . " AND usuario_rol.idRol = " . $usuarioRolOBJ->getIdRol());
            $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
