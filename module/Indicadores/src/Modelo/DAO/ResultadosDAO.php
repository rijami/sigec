<?php

namespace Indicadores\Modelo\DAO;

use Exception;
use Indicadores\Modelo\Entidades\Indicador;
use Indicadores\Modelo\Entidades\Resultados;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Indicadores\Modelo\Entidades\Indicadores;

class ResultadosDAO extends AbstractTableGateway
{

    protected $table = 'indicadores_resultado';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }


    public function getIndicador($idIndicador)
    {

        $select = new Select('indicadores');
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
            'FechaRegistro',
            'TIPO_INDICADOR',
            'SENTIDO',
            'estado',
            'fechahoramod',
            'modificadopor',
            'registradopor',
            'fechalimactivacion',
        ])->join(
                'Proceso',
                'indicadores.idProceso = Proceso.idProceso',
                ['Proceso'],
            )->join(
                'Coordinacion',
                'indicadores.idCoordinacion = Coordinacion.idCoordinacion',
                ['Coordinacion'],
            )->where(['id_indicador' => (int) $idIndicador]);

        $rowset = $this->adapter->query($select->getSqlString($this->adapter->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
        $datos = $rowset->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getResultados($idIndicador)
    {
        $this->table = 'indicadores_resultado';
        $select = new Select($this->table);
        $select->columns(['id_result', 'mes', 'num', 'dem', 'resultado', 'analisis', 'FechaRegistro', 'registradopor'])
            ->where(['id_indicador' => (int) $idIndicador])
            ->order('FechaRegistro DESC');

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

    public function registrar(Resultados $resultadoOBJ)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'indicadores_resultado';
            $insert = new Insert($this->table);
            //$datos = $resultadoOBJ->getArrayCopy();
            $datos = [
                'id_indicador' => $resultadoOBJ->getId_indicador(),
                'mes' => $resultadoOBJ->getMes(),
                'num' => $resultadoOBJ->getNum(),
                'dem' => $resultadoOBJ->getDem(),
                'resultado' => str_replace('.0', '', $resultadoOBJ->getResultado()) . '%',
                'analisis' => $resultadoOBJ->getAnalisis(),
                'registradopor' => $resultadoOBJ->getRegistradoPor(),
                'FechaRegistro' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'modificadopor' => $resultadoOBJ->getRegistradoPor()
            ];
            unset($datos['id_result']);

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
    public function getResponsablesByHistoria($idHistoria)
    {
        $select = new Select('historia_usuario_responsable');
        $select->columns(['usuario_id'])
            ->join(
                'usuario',
                'usuario.idUsuario = historia_usuario_responsable.usuario_id',
                ['nombre_completo' => 'nombre_completo', 'login' => 'login']
            )
            ->where(['historia_usuario_responsable.historia_id' => (int) $idHistoria]);

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
