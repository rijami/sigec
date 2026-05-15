<?php

namespace Reportes\Modelo\DAO;

use Exception;
use Reportes\Modelo\Entidades\Programacion;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;

class ProgramacionDAO extends AbstractTableGateway
{

    protected $table = 'indicadores_resultado';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }


    public function getReporte($idReporte)
    {
        $this->table = 'reportes';
        $select = new Select($this->table);
        $select->columns([
            'idReporte',
            'nombre_reporte',
            'nombre_archivo',
            'plataforma',
            'firmas_requeridas',
            'periodicidad',
            'normatividad',
            'estado',
            'fechahorareg',
            'fechahoramod',
            'registradopor',
            'modificadopor',
            'respon_reporta'
        ])->where(['idReporte' => (int) $idReporte]);

        $rowset = $this->adapter->query($select->getSqlString($this->adapter->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
        $datos = $rowset->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getProgramacion($idReporte)
    {
        $this->table = 'reporte_programacion';
        $select = new Select($this->table);
        $select->columns([
            'idProgramacion',
            'mes',
            'idReporte',
            'fecha_corte',
            'fecha_solicitud',
            'fecha_limite',
            'dia_semana',
            'semana_mes',
            'recordatorio',
            'respon_reporta',
            'estado',
        ])
            ->where(['idReporte' => (int) $idReporte]);

        return $this->selectWith($select)->toArray();
    }

    public function getProgramacionById($idProgramacion = 0)
    {

        if ($idProgramacion === 0) {
            return null;
        }
        $this->table = 'reporte_programacion';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns(
            [
                'idProgramacion',
                'mes',
                'idReporte',
                'fecha_corte',
                'fecha_solicitud',
                'fecha_limite',
                'dia_semana',
                'semana_mes',
                'recordatorio',
                'respon_reporta',
                'estado',
            ]
        )
            ->where(['reporte_programacion.idProgramacion' => (int) $idProgramacion]);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Programacion $programacionOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'reporte_programacion';
            $insert = new Insert($this->table);
            //$datos = $resultadoOBJ->getArrayCopy();
            $datos = [
                'idProgramacion' => $programacionOBJ->getIdProgramacion(),
                'mes' => $programacionOBJ->getMes(),
                'fecha_corte' => $programacionOBJ->getFechaCorte(),
                'fecha_solicitud' => $programacionOBJ->getFechaSolicitud(),
                'fecha_limite' => $programacionOBJ->getFechaLimite(),
                'dia_semana' => $programacionOBJ->getDiaSemana(),
                'semana_mes' => $programacionOBJ->getSemanaMes(),
                'estado' => $programacionOBJ->getEstado(),
                'respon_reporta' => $programacionOBJ->getResponReporta(),
                'recordatorio' => $programacionOBJ->getRecordatorio(),
                'idReporte' => $programacionOBJ->getIdReporte(),

            ];
            unset($datos['idProgramacion']);

            $insert->values($datos);
            $this->insertWith($insert);
            $idProgramacionInsertado = $this->getLastInsertValue();
            //echo $insert->getSqlString();
            $connection->commit();
            return $idProgramacionInsertado;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al registrar la programación: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function editar(Indicador $resultadoOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores';
            $update = new Update($this->table);
            //$datos = $resultadoOBJ->getArrayCopy();
            $datos = [
                'id_indicador' => $resultadoOBJ->getIdIndicador(),
                'codigo' => $resultadoOBJ->getCodigo(),
                'nombre_indicador' => $resultadoOBJ->getNombreIndicador(),
                'objetivo' => $resultadoOBJ->getObjetivo(),
                'periodicidad' => $resultadoOBJ->getPeriodicidad(),
                'fuente_informacion' => $resultadoOBJ->getFuenteInformacion(),
                'meta' => $resultadoOBJ->getMeta(),
                'idProceso' => $resultadoOBJ->getIdProceso(),
                'idCoordinacion' => $resultadoOBJ->getIdCoordinacion(),
                'TIPO_INDICADOR' => $resultadoOBJ->getTipoIndicador(),
                'SENTIDO' => $resultadoOBJ->getSentido(),
                'registradopor' => $resultadoOBJ->getRegistradoPor(),
                'estado' => $resultadoOBJ->getEstado(),
                'FechaRegistro' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'modificadopor' => $resultadoOBJ->getModificadoPor()
            ];
            unset($datos['id_indicador']);
            unset($datos['idProceso']);
            unset($datos['idCoordinacion']);
            unset($datos['codigo']);
            unset($datos['FechaRegistro']);
            unset($datos['registradopor']);

            $datos['fechahoramod'] = new Expression('GETDATE()');
            if (empty($datos['modificadopor'])) {
                $datos['modificadopor'] = null;
            }

            $update->set($datos)->where(['id_indicador' => (int) $resultadoOBJ->getIdIndicador()]);
            /* echo $update->getSqlString(); */
            $this->updateWith($update);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al editar el indicador: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function eliminarLogico(Indicador $resultadoOBJ)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores';
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $resultadoOBJ->getModificadoPor(),
                'fechahoramod' => new Expression('GETDATE()'),
            ])->where(['id_indicador' => (int) $resultadoOBJ->getIdIndicador()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Error al realizar eliminación del indicador: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
    public function reportar(Programacion $programacionOBJ)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'reporte_programacion';
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Reportado',
                'fecha_efectiva' => new Expression('GETDATE()'),
            ])->where(['idProgramacion' => (int) $programacionOBJ->getIdProgramacion()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Error al realizar reporte de programación: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
    public function informacion(Programacion $programacionOBJ)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'reporte_programacion';
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Con Informacion',
            ])->where(['idProgramacion' => (int) $programacionOBJ->getIdProgramacion()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Error al realizar reporte de programación: " . $e->getMessage(), $e->getCode(), $e);
        }
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
    public function getDireccionesByReporte($idReporte)
    {
        $this->table = 'Direccion';
        $select = new Select($this->table);
        $select->columns(['idDireccion', 'nombre'])
            ->join(
                'reporte_direccion',
                'Direccion.idDireccion = reporte_direccion.idDireccion',
                []
            )
            ->where(['reporte_direccion.idReporte' => (int) $idReporte]);

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getCoordinacionesByReporte($idReporte)
    {
        $this->table = 'Coordinacion';
        $select = new Select($this->table);
        $select->columns(['idCoordinacion', 'Coordinacion'])
            ->join(
                'reporte_coordinacion',
                'Coordinacion.idCoordinacion = reporte_coordinacion.idCoordinacion',
                []
            )
            ->where(['reporte_coordinacion.idReporte' => (int) $idReporte]);

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getResponsablesByReporte($idReporte)
    {
        $select = new Select('responsables');
        $select->columns(['idResponsable', 'correo'])
            ->join(
                'reporte_responsable',
                'responsables.idResponsable = reporte_responsable.idResponsable',
                []
            )
            ->where(['reporte_responsable.idReporte' => (int) $idReporte]);

        return $this->selectWith($select)->toArray();
    }


    public function getResultadosByIndicador($idIndicador)
    {
        $select = new Select('indicadores_resultado');
        $select->columns([
            'id_result',
            'mes',
            'num',
            'dem',
            'resultado',
            'analisis',
            'FechaRegistro',
            'registradopor',
            'fechahoramod',
            'modificadopor'
        ])
            ->where(['indicadores_resultado.id_indicador' => (int) $idIndicador])
            ->order('indicadores_resultado.mes ASC');

        return $this->selectWith($select)->toArray();
    }

    public function getProyectos($codigo = '', $nombre = '', $limit = 25, $offset = 0)
    {
        $select = new Select('proyecto');
        $select->columns(['idProyecto', 'codigo', 'nombre', 'descripcion']);

        $where = new \Laminas\Db\Sql\Where();
        if (!empty($codigo)) {
            $where->and->like('codigo', '%' . $codigo . '%');
        }
        if (!empty($nombre)) {
            $where->and->like('nombre', '%' . $nombre . '%');
        }
        $select->where($where);
        $select->order('idProyecto DESC')->limit($limit)->offset($offset);

        return $this->selectWith($select)->toArray();
    }

    public function getProyectobyId($idProyecto = 0)
    {
        if ($idProyecto === 0) {
            return null;
        }
        $select = new Select('proyecto');
        $select->columns(['idProyecto', 'codigo', 'nombre', 'descripcion'])
            ->where(['idProyecto' => (int) $idProyecto])->limit(1);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }
}
