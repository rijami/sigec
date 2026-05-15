<?php

namespace Inicio\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Expression;

class InicioDAO extends AbstractTableGateway
{

    protected $table = 'cambio_plan';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function getInfoEmpleadoByID($idEmpleado = 0)
    {
        $this->table = 'empleado';
        $select = new \Laminas\Db\Sql\Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns([
            'idEmpleado',
            //'empleado',
            // Si quieres concatenar nombre completo en SQL Server:
            'empleado' => new Expression("
    LTRIM(RTRIM(CONCAT(
        ISNULL(nombre1,''), ' ',
        ISNULL(nombre2,''), ' ',
        ISNULL(apellido1,''), ' ',
        ISNULL(apellido2,'')
    )))
")
        ])
            ->where([
                'empleado.idEmpleado' => (int) $idEmpleado
            ]);

        //echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        return $datos[0] ?? null;
    }
    //------------------------------------------------------------------------------

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

    //------------------------------------------------------------------------------

    public function getRecursosRBAC()
    {
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
    public function getTableros($filtro = "")
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
            'imagen',
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
    //------------------------------------------------------------------------------
    public function getTablerosByIdUsuario($idUsuario = 0)
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
            'imagen'
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
            )->join(
                'usuarios_tableros',
                'tableros.idTablero = usuarios_tableros.idTablero',
                []
            )->where("usuarios_tableros.idUsuario = $idUsuario");
        /*  ->where("tablero.estado != 'Eliminado'");
         */
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------

    public function setRecursosRBAC($RECURSOS_RBAC = [])
    {
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
    public function getReporte()
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
            'estadoReporte' => new Expression('reportes.estado'),
            'fechahorareg',
            'fechahoramod',
            'registradopor',
            'modificadopor',
            'respon_reporta',
        ])->join(
                'reporte_programacion',
                'reportes.idReporte = reporte_programacion.idReporte',
                ['idProgramacion', 'mes', 'idReporte', 'fecha_corte', 'fecha_solicitud', 'fecha_limite', 'dia_semana', 'semana_mes', 'estado', 'recordatorio']
            )->join(
                'reporte_responsable',
                'reportes.idReporte = reporte_responsable.idReporte',
                ['idResponsable']
            )->join(
                'responsables',
                'reporte_responsable.idResponsable = responsables.idResponsable',
                ['correo']
            );

        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------
    public function getResponsables($idReporte = 0)
    {
        $this->table = 'reporte_responsable';
        $select = new Select($this->table);
        $select->columns([
            'idReporte',
            'idResponsable',
        ])->join(
                'responsables',
                'reporte_responsable.idResponsable = responsables.idResponsable',
                ['correo']
            )->where("reporte_responsable.idReporte = $idReporte");

        return $this->selectWith($select)->toArray();
    }
    public function recordatorioEnviado($idProgramacion)
    {
        $this->table = 'reporte_programacion';
        $update = new \Laminas\Db\Sql\Update($this->table);
        $update->set(['recordatorio' => 1])
            ->where(['idProgramacion' => $idProgramacion]);
        $this->updateWith($update);
    }

}
