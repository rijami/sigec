<?php

namespace Reportes\Modelo\DAO;

use Exception;
use Indicadores\Modelo\Entidades\Indicador;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Indicadores\Modelo\Entidades\Indicadores;
use Reportes\Modelo\Entidades\Reporte;

class ReportesDAO extends AbstractTableGateway
{

    protected $table = 'reportes';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    public function getReportes($filtro = '')
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
            'respon_reporta',
        ])->join("reporte_coordinacion", "reportes.idReporte = reporte_coordinacion.idReporte", [
                ])->join("Coordinacion", "reporte_coordinacion.idCoordinacion = Coordinacion.idCoordinacion", [
                    'Coordinacion'
                ]);

        if ($filtro !== '') {
            $select->where("Coordinacion.idCoordinacion = $filtro");
        } else {
            //$select->where($filtro);
        }

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getReportesEstadistica($filtro = '')
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
            'respon_reporta',
        ]);

        if ($filtro !== '') {
            $select->where($filtro);
        } else {
            //$select->where($filtro);
        }

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getReporteById($idReporte = 0)
    {

        if ($idReporte === 0) {
            return null;
        }
        $this->table = 'reportes';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns(
            [
                'idReporte',
                'nombre_reporte',
                'nombre_archivo',
                'plataforma',
                'firmas_requeridas',
                'periodicidad',
                'normatividad',
                'estado',
                'respon_reporta',
                'fechahorareg',
                'fechahoramod',
                'registradopor',
                'modificadopor'
            ]
        )->where(['reportes.idReporte' => (int) $idReporte]);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Reporte $reporteOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'reportes';
            $insert = new Insert($this->table);
            //$datos = $indicadorOBJ->getArrayCopy();
            $datos = [
                'nombre_reporte' => $reporteOBJ->getNombreReporte(),
                'nombre_archivo' => $reporteOBJ->getNombreArchivo(),
                'plataforma' => $reporteOBJ->getPlataforma(),
                'firmas_requeridas' => $reporteOBJ->getFirmasRequeridas(),
                'periodicidad' => $reporteOBJ->getPeriodicidad(),
                'normatividad' => $reporteOBJ->getNormatividad(),
                'estado' => $reporteOBJ->getEstado(),
                'fechahorareg' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'registradopor' => $reporteOBJ->getRegistradopor(),
                'modificadopor' => $reporteOBJ->getModificadopor(),
                'respon_reporta' => $reporteOBJ->getResponReporta(),
            ];
            unset($datos['idReporte']);

            if (empty($datos['fechahorareg'])) {
                $datos['fechahorareg'] = new Expression('GETDATE()');
            }

            if (empty($datos['fechahoramod'])) {
                $datos['fechahoramod'] = new Expression('GETDATE()');
            }
            if (!isset($datos['modificadopor']) || empty($datos['modificadopor'])) {
                $datos['modificadopor'] = $datos['registradopor'] ?? 'Sistema';
            }

            $insert->values($datos);
            $this->insertWith($insert);
            $idReporteInsertado = $this->getLastInsertValue();
            //echo $insert->getSqlString();
            $connection->commit();
            return $idReporteInsertado;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al registrar el reporte: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function editar(Reporte $reporteOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'reportes';
            $update = new Update($this->table);
            //$datos = $indicadorOBJ->getArrayCopy();
            $datos = [
                'nombre_reporte' => $reporteOBJ->getNombreReporte(),
                'nombre_archivo' => $reporteOBJ->getNombreArchivo(),
                'plataforma' => $reporteOBJ->getPlataforma(),
                'firmas_requeridas' => $reporteOBJ->getFirmasRequeridas(),
                'periodicidad' => $reporteOBJ->getPeriodicidad(),
                'normatividad' => $reporteOBJ->getNormatividad(),
                'estado' => $reporteOBJ->getEstado(),
                //'fechahorareg' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'registradopor' => $reporteOBJ->getRegistradopor(),
                'modificadopor' => $reporteOBJ->getModificadopor(),
                'respon_reporta' => $reporteOBJ->getResponReporta(),
            ];
            unset($datos['idReporte']);
            unset($datos['FechaRegistro']);
            unset($datos['registradopor']);

            $datos['fechahoramod'] = new Expression('GETDATE()');
            if (empty($datos['modificadopor'])) {
                $datos['modificadopor'] = null;
            }

            $update->set($datos)->where(['idReporte' => (int) $reporteOBJ->getIdReporte()]);
            /* echo $update->getSqlString(); */
            $this->updateWith($update);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al editar el reporte: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function eliminarLogico(Indicador $indicadorOBJ)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores';
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $indicadorOBJ->getModificadoPor(),
                'fechahoramod' => new Expression('GETDATE()'),
            ])->where(['id_indicador' => (int) $indicadorOBJ->getIdIndicador()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Error al realizar eliminación del indicador: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
    public function asignar($idReporte, $asignacion, $responsableOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();

        try {

            // =========================
            // CREAR RESPONSABLE SI NO EXISTE
            // =========================
            if (empty($responsableOBJ->getIdResponsable())) {

                $this->table = 'responsables';

                $insert = new Insert($this->table);
                $insert->values([
                    'correo' => $asignacion['correo'],
                    'estado' => 'Activo'
                ]);

                $this->insertWith($insert);

                $idResponsable = $this->getLastInsertValue();

            } else {

                $idResponsable = (int) $responsableOBJ->getIdResponsable();
            }

            // =========================
            // RELACION REPORTE - RESPONSABLE
            // =========================
            $this->table = 'reporte_responsable';

            $insert = new Insert($this->table);
            $insert->values([
                'idReporte' => (int) $idReporte,
                'idResponsable' => $idResponsable
            ]);

            $this->insertWith($insert);

            // =========================
            // RELACION REPORTE - DIRECCION
            // =========================
            $this->table = 'reporte_direccion';

            $insert = new Insert($this->table);
            $insert->values([
                'idReporte' => (int) $idReporte,
                'idDireccion' => (int) $asignacion['idDireccion']
            ]);

            $this->insertWith($insert);

            // =========================
            // RELACION REPORTE - COORDINACION
            // =========================
            $this->table = 'reporte_coordinacion';

            $insert = new Insert($this->table);
            $insert->values([
                'idReporte' => (int) $idReporte,
                'idCoordinacion' => (int) $asignacion['idCoordinacion']
            ]);

            $this->insertWith($insert);

            $connection->commit();

        } catch (\Exception $e) {

            $connection->rollback();

            if ($e->getCode() == 23000) {
                throw new \Exception(
                    "El responsable ya está asignado al reporte o existe un dato duplicado."
                );
            }

            throw new \Exception(
                "Error al asignar responsable: " . $e->getMessage(),
                $e->getCode(),
                $e
            );

        } finally {

            $this->table = 'reportes';
        }
    }
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
    public function getDirecciones()
    {
        $this->table = 'Direccion';
        $select = new Select($this->table);
        $select->columns([
            'idDireccion',
            'nombre'
        ]);
        //->where("historia_usuario.estado != 'Eliminado'");

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
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
    public function getResponsables()
    {
        $this->table = 'responsables';
        $select = new Select($this->table);
        $select->columns([
            'idResponsable',
            'correo'

        ]);
        //->where("historia_usuario.estado != 'Eliminado'");

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCoordinacionesByDireccion($idDireccion)
    {
        $this->table = 'Coordinacion';
        $select = new Select($this->table);

        $select->columns([
            'idCoordinacion',
            'Coordinacion'
        ]);
        $select->where(['idDireccion' => $idDireccion]);

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
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

    public function getProgramacionByReporte($idReporte)
    {
        $select = new Select('reporte_programacion');
        $select->columns([
            'idProgramacion',
            'mes',
            'fecha_corte',
            'fecha_solicitud',
            'fecha_limite',
            'dia_semana',
            'semana_mes',
            'estado',
            'fecha_efectiva',
            'evidencia',

        ])
            ->where(['reporte_programacion.idReporte' => (int) $idReporte])
            ->order('reporte_programacion.mes ASC');

        return $this->selectWith($select)->toArray();
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
    public function getEmpleadoByEstadistica($idCoordinacion = 23)
    {
        $select = new Select('empleado');
        $select->columns([
            'empleado' => new Expression("CONCAT(empleado.nombre1, ' ', empleado.apellido1)"),
        ])
            ->where(['idCoordinacion' => $idCoordinacion]);

        return $this->selectWith($select)->toArray();
    }
    public function actualizarEstados()
    {
        $sql = "
        UPDATE indicadores
        SET estado = 'Desactivado'
        WHERE estado = 'Activado'
        AND fechalimactivacion <= GETDATE()
    ";
        $this->getAdapter()->query($sql, \Laminas\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getIndicadoresExport($filtro = '')
    {
        $this->table = 'indicadores';
        $select = new Select($this->table);
        $select->columns([
            'id_indicador',
            'idProceso',
            'idCoordinacion',
            'codigo',
            'nombre_indicador',
            'objetivo',
            'periodicidad',
            'fuente_informacion',
            'meta',
            'FechaRegistroIndicador' => new Expression("indicadores.FechaRegistro"),
            'TIPO_INDICADOR',
            'SENTIDO',
            'estado',
        ])->join(
                'indicadores_resultado',
                'indicadores.id_indicador = indicadores_resultado.id_indicador',
                [
                    'mes',
                    'num',
                    'dem',
                    'resultado',
                    'analisis',
                    'FechaRegistro'
                ],
            )->join(
                'Proceso',
                'indicadores.idProceso = Proceso.idProceso',
                ['Proceso'],
            )->join(
                'Coordinacion',
                'indicadores.idCoordinacion = Coordinacion.idCoordinacion',
                ['Coordinacion'],
            );
        if ($filtro !== '') {
            $select->where($filtro);
        } else {
            //$select->where($filtro);
        }

        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getResponsableBycorreo($correo = '')
    {
        $this->table = 'responsables';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns([
            'idResponsable',
            'correo',
        ])->where("responsables.correo = '$correo'");
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getResponsableById($idResponsable = 0)
    {
        $this->table = 'responsables';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns([
            'idResponsable',
            'correo',
        ])->where("responsables.idResponsable = $idResponsable")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
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

}
