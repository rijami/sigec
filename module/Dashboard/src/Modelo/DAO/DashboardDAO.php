<?php

namespace Dashboard\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Dashboard\Modelo\Entidades\Tablero;

class DashboardDAO extends AbstractTableGateway
{

    protected $table = 'historia_usuario';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    public function getTableros()
    {
        $this->table = 'tableros';
        $select = new Select($this->table);
        $select->columns([
            'idTablero',
            'nombre',
            'fecha_creacion',
            'fecha_entregada',
            'estado',
            'manual',
            'fuentes_informacion',
            'necesidad',
            'historia_usuario',
            'enlace',
            /* 'registradopor',
            'fechahoramod',
            'modificadopor', */
        ])->join(
                'Proceso',
                'tableros.idProceso = Proceso.idProceso',
                ['Proceso'],
            )->join(
                'Coordinacion',
                'tableros.idCoordinacion = Coordinacion.idCoordinacion',
                ['Coordinacion'],
            );
        /*  ->where("tablero.estado != 'Eliminado'");
         */
        return $this->selectWith($select)->toArray();
    }

    public function getTablero($idTablero = 0)
    {

        if ($idTablero === 0) {
            return null;
        }

        $this->table = 'tableros';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns([
            'idTablero',
            'nombre',
            'fecha_creacion',
            'fecha_entregada',
            'estado',
            'manual',
            'fuentes_informacion',
            'necesidad',
            'historia_usuario',
            'enlace',
            'idProceso',
            'idCoordinacion'
            /* 'registradopor',
            'fechahoramod',
            'modificadopor', */
        ])->join(
                'Proceso',
                'tableros.idProceso = Proceso.idProceso',
                ['Proceso'],
            )->join(
                'Coordinacion',
                'tableros.idCoordinacion = Coordinacion.idCoordinacion',
                ['Coordinacion'],
            )
            ->where(['tableros.idTablero' => (int) $idTablero]);


        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Tablero $tableroOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'tableros';
            $insert = new Insert($this->table);
            //$datos = $indicadorOBJ->getArrayCopy();
            $datos = [
                'nombre' => $tableroOBJ->getNombre(),
                'idProceso' => $tableroOBJ->getIdProceso(),
                'idCoordinacion' => $tableroOBJ->getIdCoordinacion(),
                'fecha_creacion' => new Expression('GETDATE()'),
                'fecha_entregada' => $tableroOBJ->getFecha_entregada(),
                'estado' => $tableroOBJ->getEstado(),
                'manual' => $tableroOBJ->getManual(),
                'fuentes_informacion' => $tableroOBJ->getFuentes_informacion(),
                'necesidad' => $tableroOBJ->getNecesidad(),
                'historia_usuario' => $tableroOBJ->getHistoria_usuario(),
                'enlace' => $tableroOBJ->getEnlace()
                /* 'fechahoramod' => new Expression('GETDATE()'),
                'modificadopor' => $indicadorOBJ->getRegistradoPor() */
            ];
            unset($datos['idTablero']);

            if (empty($datos['fecha_creacion'])) {
                $datos['fecha_creacion'] = new Expression('GETDATE()');
            }

            $insert->values($datos);
            $this->insertWith($insert);
            $idTableroInsertado = $this->getLastInsertValue();
            //echo $insert->getSqlString();
            $connection->commit();
            return $idTableroInsertado;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al registrar el indicador: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function editar(HistoriaUsuario $historiaUsuarioOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $update = new Update($this->table);
            $datos = $historiaUsuarioOBJ->getArrayCopy();

            unset($datos['idHistoria']);
            unset($datos['idProyecto']);
            unset($datos['codigo']);
            unset($datos['fechahorareg']);
            unset($datos['registradopor']);

            $datos['fechahoramod'] = new Expression('CURRENT_TIMESTAMP');
            if (empty($datos['modificadopor'])) {
                $datos['modificadopor'] = null;
            }

            $update->set($datos)->where(['idHistoria' => (int) $historiaUsuarioOBJ->getIdHistoria()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al editar la historia de usuario: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function eliminarLogico(HistoriaUsuario $historiaUsuarioOBJ)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $historiaUsuarioOBJ->getModificadopor(),
                'fechahoramod' => $historiaUsuarioOBJ->getFechahoramod(),
            ])->where(['idHistoria' => (int) $historiaUsuarioOBJ->getIdHistoria()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Error al realizar eliminación de la historia de usuario: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function asignar($idTablero, $idUsuario)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'usuarios_tableros';
            $insert = new Insert($this->table);
            $insert->values([
                'idTablero' => (int) $idTablero,
                'idUsuario' => (int) $idUsuario
            ]);
            $this->insertWith($insert);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            if ($e->getCode() == 23000) {
                throw new \Exception("El usuario ya es responsable del tablero o el ID es inválido.");
            }
            throw new \Exception("Error al asignar responsable: " . $e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->table = 'tableros';
        }
    }

    public function removerResponsable($idHistoria, $idUsuario)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'historia_usuario_responsable';
            $delete = new Delete($this->table);
            $delete->where(['historia_id' => (int) $idHistoria, 'usuario_id' => (int) $idUsuario]);
            $this->deleteWith($delete);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al remover responsable: " . $e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->table = 'historia_usuario';
        }
    }

    public function getRolesByIdUsuario($idUsuario = 0)
    {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns([
            'idRol',
            'rol',
        ])->join('usuario_rol', 'roles.idRol = usuario_rol.idRol', [
                ])->where("usuario_rol.idUsuario = $idUsuario");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getEmpleadoById($idEmpleado = 0)
    {
        if ($idEmpleado === 0) {
            return null;
        }
        $select = new Select('empleado');
        $select->quantifier('TOP 1');
        $select->columns(['*'])
            ->where(['idEmpleado' => (int) $idEmpleado]);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    //------------------------------------------------------------------------------
    public function getProcesos()
    {
        $this->table = 'Proceso';
        $select = new Select($this->table);
        $select->columns([
            'idProceso',
            'Proceso'
        ]);
        //->where("historia_usuario.estado != 'Eliminado'");

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();


    }
    //------------------------------------------------------------------------------
    public function getCoordinaciones()
    {
        $this->table = 'Coordinacion';
        $select = new Select($this->table);
        $select->columns([
            'idCoordinacion',
            'Coordinacion'

        ]);
        //->where("historia_usuario.estado != 'Eliminado'");

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function getProcesoById($idProceso)
    {
        $this->table = 'Proceso';
        $select = new Select($this->table);
        $select->columns([
            'idProceso',
            'Proceso'
        ])
            ->where("Proceso.idProceso =" . $idProceso);

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();


    }
    //------------------------------------------------------------------------------
    public function getCoordinacionById($idCoordinacion)
    {
        $this->table = 'Coordinacion';
        $select = new Select($this->table);
        $select->columns([
            'idCoordinacion',
            'Coordinacion'

        ])
            ->where("Coordinacion.idCoordinacion = " . $idCoordinacion);

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getExisteRol($idRol = 0)
    {
        $this->table = "usuario_rol";
        $select = new Select($this->table);
        $select->columns(array(
            'existe' => new \Laminas\Db\Sql\Expression("COUNT(usuario_rol.idRol)")
        ))->where("usuario_rol.idRol = $idRol");
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        return intval($datos[0]['existe']);
    }

    //------------------------------------------------------------------------------
    public function getCoordinacionesByProceso($idProceso)
    {
        $this->table = 'Coordinacion';
        $select = new Select($this->table);

        $select->columns([
            'idCoordinacion',
            'Coordinacion'
        ]);
        $select->where(['idProceso' => $idProceso]);

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------

    public function getRoles($filtro)
    {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns(array(
            'idRol',
            'rol',
            'estado'
        ))->where("roles.estado != 'Eliminado'" . $filtro);
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------
    public function getUsuarios()
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns(array(
            'idUsuario',
            'login',
            'estado'
        ))->join("empleado", "usuario.idEmpleado = empleado.idEmpleado", [
                    'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))")
                ])->where("usuario.estado != 'Eliminado'");
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
}
