<?php

namespace Usuarios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Usuarios\Modelo\Entidades\Recursorbacrol;

class PermisosDAO extends AbstractTableGateway {

    protected $table = 'recursos_rbac';

//------------------------------------------------------------------------------

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

//------------------------------------------------------------------------------ 
    public function getRecursosRbac($filtro = '') { //Usado
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("recursos_rbac.idRecurso DESC");
        }
        // echo $select->getSqlString();
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

//-----------------------------------------------------------------------------
    public function getRecursoRbacRolByID($idRecurso = 0) {
        $this->table = 'recursorbac_rol';
        $select = new Select($this->table);
        $select->columns([
            'idRecurso',
            'idRol'
        ])->join('roles', 'roles.idRol = recursorbac_rol.idRol',
                ['rol', 'estado']
        )->join('recursos_rbac', 'recursos_rbac.idRecurso = recursorbac_rol.idRecurso',
                ['recurso', 'metodo']
        )->where(['recursorbac_rol.idRecurso' => $idRecurso])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

//------------------------------------------------------------------------------
    public function getRecursosRbacRolByIdRol($idRol = 0) {
        $this->table = 'recursorbac_rol';
        $select = new Select($this->table);
        $select->columns([
            'idRecurso',
            'idRol'
        ])->where(['recursorbac_rol.idRol' => $idRol]);
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function getRolByID($idRol = 0) { //USADO
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->where(['roles.idRol' => $idRol])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    //--------------------------------------------------------------------------
    public function getRecursosByIdRol($idRol = 0) { //USADO
        $this->table = 'recursorbac_rol';
        $select = new Select($this->table);
        $select->columns([
            'idRol',
            'idRecurso'
        ])->join('recursos_rbac', 'recursos_rbac.idRecurso = recursorbac_rol.idRecurso',
                ['recurso', 'metodo'
                ])->where("recursorbac_rol.idRol = " . (int) $idRol);
//        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function getRoles() {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns(array(
            'idRol',
            'rol'
        ))->where("roles.idRol > 1 AND roles.estado!='Eliminado'")->order("roles.rol ASC ");
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

//------------------------------------------------------------------------------

    public function registrar(Recursorbacrol $recursosRbacOBJ = null) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'recursorbac_rol';
            $insert = new Insert($this->table);
            $datos = $recursosRbacOBJ->getArrayCopy();
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

    public function importar($RECURSOS_IMPORTAR = []) {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'recursorbac_rol';
            foreach ($RECURSOS_IMPORTAR as $recursoRolOBJ) {
                $insert = new Insert($this->table);
                $insert->values($recursoRolOBJ->getArrayCopy());
                $this->insertWith($insert);
            }
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
    public function eliminar(Recursorbacrol $recursosRbacOBJ = null) {
        try {
            $this->table = "recursorbac_rol"; // tabla correcta

            $delete = new Delete($this->table);
            $delete->where([
                'idRecurso' => $recursosRbacOBJ->getIdRecurso(),
                'idRol' => $recursosRbacOBJ->getIdRol(),
            ]);

            $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar el recurso del rol: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    //------------------------------------------------------------------------------
    public function verificaridRecursoyidRolSeleccionado($idRecurso = 0, $idRol = 0) {
        $this->table = 'recursorbac_rol';
        $select = new Select($this->table);
        $select->columns([
            'cont' => new Expression("COUNT(recursorbac_rol.idRecurso)"),
        ])->where("recursorbac_rol.idRecurso = '$idRecurso' AND recursorbac_rol.idRol = '$idRol' ");
//        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return ($datos[0]['cont'] > 0);
        } else {
            return true;
        }
    }
}
