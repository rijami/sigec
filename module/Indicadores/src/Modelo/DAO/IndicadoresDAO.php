<?php

namespace Indicadores\Modelo\DAO;

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

class IndicadoresDAO extends AbstractTableGateway
{

    protected $table = 'indicadores';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    public function getIndicadores($filtro = '')
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
            'fechaRegistro',
            'TIPO_INDICADOR',
            'SENTIDO',
            'estado',
        ])
            ->join(
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

    public function getIndicadorById($idIndicador = 0)
    {

        if ($idIndicador === 0) {
            return null;
        }

        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns(
            [
                'id_indicador',
                'idProceso',
                'idCoordinacion',
                'codigo',
                'nombre_indicador',
                'objetivo',
                'periodicidad',
                'fuente_informacion',
                'meta',
                'FechaRegistro',
                'TIPO_INDICADOR',
                'SENTIDO',
                'estado',
                'fechahoramod',
                'modificadopor',
                'registradopor',
            ]
        )->join(
                'Proceso',
                'indicadores.idProceso = Proceso.idProceso',
                ['Proceso'],
            )->join(
                'Coordinacion',
                'indicadores.idCoordinacion = Coordinacion.idCoordinacion',
                ['Coordinacion'],
            )
            ->where(['indicadores.id_indicador' => (int) $idIndicador]);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Indicador $indicadorOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores';
            $insert = new Insert($this->table);
            //$datos = $indicadorOBJ->getArrayCopy();
            $datos = [
                'codigo' => $indicadorOBJ->getCodigo(),
                'nombre_indicador' => $indicadorOBJ->getNombreIndicador(),
                'objetivo' => $indicadorOBJ->getObjetivo(),
                'periodicidad' => $indicadorOBJ->getPeriodicidad(),
                'fuente_informacion' => $indicadorOBJ->getFuenteInformacion(),
                'meta' => $indicadorOBJ->getMeta(),
                'idProceso' => $indicadorOBJ->getIdProceso(),
                'idCoordinacion' => $indicadorOBJ->getIdCoordinacion(),
                'TIPO_INDICADOR' => $indicadorOBJ->getTipoIndicador(),
                'SENTIDO' => $indicadorOBJ->getSentido(),
                'registradopor' => $indicadorOBJ->getRegistradoPor(),
                'estado' => $indicadorOBJ->getEstado(),
                'FechaRegistro' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'modificadopor' => $indicadorOBJ->getRegistradoPor()
            ];
            unset($datos['id_indicador']);

            if (empty($datos['FechaRegistro'])) {
                $datos['FechaRegistro'] = new Expression('GETDATE()');
            }

            if (empty($datos['fechahoramod'])) {
                $datos['fechahoramod'] = new Expression('GETDATE()');
            }
            if (!isset($datos['modificadopor']) || empty($datos['modificadopor'])) {
                $datos['modificadopor'] = $datos['registradopor'] ?? 'Sistema';
            }

            $insert->values($datos);
            $this->insertWith($insert);
            $idIndicadorInsertado = $this->getLastInsertValue();
            //echo $insert->getSqlString();
            $connection->commit();
            return $idIndicadorInsertado;
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al registrar el indicador: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function editar(Indicador $indicadorOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores';
            $update = new Update($this->table);
            //$datos = $indicadorOBJ->getArrayCopy();
            $datos = [
                'id_indicador' => $indicadorOBJ->getIdIndicador(),
                'codigo' => $indicadorOBJ->getCodigo(),
                'nombre_indicador' => $indicadorOBJ->getNombreIndicador(),
                'objetivo' => $indicadorOBJ->getObjetivo(),
                'periodicidad' => $indicadorOBJ->getPeriodicidad(),
                'fuente_informacion' => $indicadorOBJ->getFuenteInformacion(),
                'meta' => $indicadorOBJ->getMeta(),
                'idProceso' => $indicadorOBJ->getIdProceso(),
                'idCoordinacion' => $indicadorOBJ->getIdCoordinacion(),
                'TIPO_INDICADOR' => $indicadorOBJ->getTipoIndicador(),
                'SENTIDO' => $indicadorOBJ->getSentido(),
                'registradopor' => $indicadorOBJ->getRegistradoPor(),
                'estado' => $indicadorOBJ->getEstado(),
                'FechaRegistro' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'modificadopor' => $indicadorOBJ->getModificadoPor()
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

            $update->set($datos)->where(['id_indicador' => (int) $indicadorOBJ->getIdIndicador()]);
            /* echo $update->getSqlString(); */
            $this->updateWith($update);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception("Error al editar el indicador: " . $e->getMessage(), $e->getCode(), $e);
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
    public function activar(Indicador $indicadorOBJ, $fechaLimiteActivacion = '')
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores';
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activado',
                'modificadopor' => $indicadorOBJ->getModificadoPor(),
                'fechahoramod' => new Expression('GETDATE()'),
                'fechalimactivacion' => new Expression("CONVERT(datetime, ?, 120)", $fechaLimiteActivacion)
            ])->where(['idCoordinacion' => (int) $indicadorOBJ->getIdCoordinacion()]);
            $this->updateWith($update);

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Error al realizar activación del indicador: " . $e->getMessage(), $e->getCode(), $e);
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
}
