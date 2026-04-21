<?php

namespace Usuarios\Modelo\DAO;

use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Usuarios\Modelo\Entidades\Usuario;

class UsuarioDAO extends AbstractTableGateway
{

    protected $table = 'usuario';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------

    public function getAuditoriaIngreso($login = '')
    {
        try {
            $this->table = 'usuario';
            $select = new Select($this->table);
            $select->quantifier('TOP 1');
            $select->columns([
                'fechaultingreso',
                'contFallidos',
            ])->where("usuario.login = '$login'");
            //echo $select->getSqlString();
            $this->selectWith($select);
            $datos = $this->selectWith($select)->toArray();
            if (count($datos) > 0) {
                return $datos[0];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------

    public function setFechaUltIngreso($login = '')
    {
        try {
            $this->table = 'usuario';
            $update = new Update($this->table);
            $update->set([
                'fechaultingreso' => new Expression('GETDATE()'),
                'contFallidos' => 0
            ])->where([
                        'usuario.login' => $login
                    ]);
            //     echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------

    public function setLoginFallido($login = '')
    {
        try {
            $this->table = 'usuario';

            $update = new Update($this->table);

            $update->set([
                // Si contFallidos es NULL lo convierte en 0 antes de sumar
                'contFallidos' => new Expression("ISNULL(contFallidos,0) + 1"),
                // Fecha del servidor SQL
                'fechaultfallido' => new Expression("GETDATE()"),
            ])
                ->where([
                    'usuario.login' => $login
                ]);

            $this->updateWith($update);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //------------------------------------------------------------------------------ 

    public function getUsuarios($filtro = '')
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns([
            '*',
            'roles' => new Expression('roles.rol'),
            'estadoU' => new Expression('usuario.estado')
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
                    'nombre1',
                    'nombre2',
                    'apellido1',
                    'apellido2',
                    'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),
                ])->join(
                'usuario_rol',
                'usuario.idUsuario = usuario_rol.idUsuario'
            )->join(
                'roles',
                'usuario_rol.idRol=roles.idRol'
            )->where("usuario.estado != 'Eliminado'");
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("usuario.idUsuario DESC");
        }
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
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
    public function getRolesByID($idUsuario = 0)
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
                    'nombre1',
                    'nombre2',
                    'apellido1',
                    'apellido2',
                    'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),
                ])->join('usuario_rol', 'usuario.idUsuario = usuario_rol.idUsuario', [
                ])->join('roles', 'usuario_rol.idRol = roles.idRol', [
                    'idRol',
                    'rol'
                ])->where(['usuario.idUsuario' => $idUsuario]);
        // echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------

    public function getUsuarioByID($idUsuario = 0)
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns([
            '*'
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
                    'nombre1',
                    'nombre2',
                    'apellido1',
                    'apellido2',
                    'idEmpleado',
                    'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))"),
                ])->where(['usuario.idUsuario' => $idUsuario]);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    //------------------------------------------------------------------------------

    public function getEmpleadoByID($idEmpleado = 0)
    {
        $this->table = 'empleado';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns(array(
            'idEmpleado',
            'empleado' => new Expression("
    LTRIM(RTRIM(
        empleado.nombre1 + ' ' +
        ISNULL(empleado.nombre2, '') + ' ' +
        empleado.apellido1 + ' ' +
        ISNULL(empleado.apellido2, '')
    ))
"),
            'nombre1',
            'nombre2',
            'apellido1',
            'apellido2'
        ))->where(['empleado.idEmpleado' => $idEmpleado]);
        // echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    //------------------------------------------------------------------------------
    public function getUsuarioByIdentificacion($identificacion = '')
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->quantifier('TOP 1');
        $select->columns([
            '*'
        ])->join('empleado', 'usuario.idEmpleado = empleado.idEmpleado', [
                    'idEmpleado',
                    'identificacion',
                    'empleado' => new Expression("LTRIM(RTRIM(empleado.nombre1 + ' ' +ISNULL(empleado.nombre2, '') + ' ' +empleado.apellido1 + ' ' +ISNULL(empleado.apellido2, '')))"),
                    'nombre1',
                    'nombre2',
                    'apellido1',
                    'apellido2',
                    'idCoordinacion',
                    'idProceso'
                ])->join('usuario_rol', 'usuario.idUsuario = usuario_rol.idUsuario', [
                ])->join('roles', 'usuario_rol.idRol = roles.idRol', [
                    'idRol',
                    'rol'
                ])->where("empleado.identificacion = '$identificacion'");
        //echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    //------------------------------------------------------------------------------

    public function getExisteLogin($login = '')
    {
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

    public function registrar($infoEmpleado = [], Usuario $usuarioOBJ = null, $idRol = null)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //------------------------------------------------------------------
            $this->table = 'empleado';
            $insert = new Insert($this->table);
            $insert->values(
                [
                    'identificacion' => $infoEmpleado['identificacion'],
                    'nombre1' => strtoupper($infoEmpleado['nombre1']),
                    'nombre2' => strtoupper($infoEmpleado['nombre2']),
                    'apellido1' => strtoupper($infoEmpleado['apellido1']),
                    'apellido2' => strtoupper($infoEmpleado['apellido2']),
                    'genero' => $infoEmpleado['genero'],
                    'telefono' => $infoEmpleado['telefono'],
                    'estado' => "Activo",
                    'fechahorareg' => new Expression('GETDATE()'),
                    'fechanacimiento' => new Expression('GETDATE()'),
                    'idProceso' => $infoEmpleado['idProceso'],
                    'idCoordinacion' => $infoEmpleado['idCoordinacion'],
                ]
            );
            $this->insertWith($insert);
            $idEmpleadoInsert = $this->getLastInsertValue();
            //------------------------------------------------------------------
            //$datosUsuario = $usuarioOBJ->getArrayCopy();
            $datosUsuario = [
                'idEmpleado' => $idEmpleadoInsert,
                'login' => $usuarioOBJ->getLogin(),
                'password' => $usuarioOBJ->getPassword(),
                'estado' => $usuarioOBJ->getEstado(),
                'registradopor' => $usuarioOBJ->getRegistradopor(),
                'fechahorareg' => new Expression('GETDATE()'),
                'fechahoramod' => new Expression('GETDATE()'),
                'modificadopor' => $usuarioOBJ->getModificadopor()
            ];
            unset($datosUsuario['idUsuario']);
            $this->table = 'usuario';
            $insert = new Insert($this->table);
            $insert->values($datosUsuario);
            $this->insertWith($insert);
            $idUsuarioInsert = $this->getLastInsertValue();
            //------------------------------------------------------------------
            // Inserta los datos en la tabla usuario_roles
            $this->table = 'usuario_rol';
            $insert = new Insert($this->table);
            $insert->values([
                'idUsuario' => $idUsuarioInsert,
                'idRol' => $idRol
            ]);
            $this->insertWith($insert);

            // ------------ EJECUCION COMMIT ------------
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
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
    public function getEmpleadosSinUsuario()
    {
        $this->table = 'empleado';
        $select = new Select($this->table);
        $select->columns(array(
            'idEmpleado',
            'empleado' => new Expression("CONCAT(TRIM(CONCAT(empleado.nombre1, ' ', empleado.nombre2)), ' ', TRIM(CONCAT(empleado.apellido1, ' ', empleado.apellido2)))")
        ))->where("empleado.estado != 'Eliminado' AND NOT EXISTS (
            SELECT 1 
            FROM usuario 
            WHERE usuario.idEmpleado = empleado.idEmpleado
        )")->order("empleado.nombre1 ASC");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------

    public function bloquearUsuario(Usuario $usuarioOBJ = null)
    {
        try {
            $this->table = "usuario";
            $update = new Update($this->table);
            $datos = $usuarioOBJ->getArrayCopy();
            unset($datos['idUsuario']);
            $update->set($datos)->where("usuario.idUsuario = " . $usuarioOBJ->getIdUsuario());
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------

    public function desbloquearUsuario(Usuario $usuarioOBJ = null)
    {
        try {
            $this->table = "usuario";
            $update = new Update($this->table);
            $datos = $usuarioOBJ->getArrayCopy();
            unset($datos['idUsuario']);
            $update->set($datos)->where("usuario.idUsuario = " . $usuarioOBJ->getIdUsuario());
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function actualizarContrasenaUsuario(Usuario $usuarioOBJ = null)
    {
        try {
            $this->table = "usuario";
            $update = new Update($this->table);

            $datos = [
                'password' => $usuarioOBJ->getPassword(),
                'fechahoramod' => $usuarioOBJ->getFechahoramod(),
            ];

            $update->set($datos)->where("usuario.idUsuario = " . (int) $usuarioOBJ->getIdUsuario());
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function existeIdentificacion($identificacion = 0)
    {
        $this->table = "empleado";
        $select = new Select($this->table);
        $select->columns(array(
            'existe' => new \Laminas\Db\Sql\Expression("COUNT(empleado.idEmpleado)")
        ))->where("empleado.identificacion = $identificacion");
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        return intval($datos[0]['existe']);
    }
    //------------------------------------------------------------------------------
    public function getTablerosByUsuario($idUsuario = 0)
    {
        $this->table = 'tableros';
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join(
                'usuarios_tableros',
                'tableros.idTablero = usuarios_tableros.idTablero',
                []
            )
            ->where([
                'usuarios_tableros.idUsuario' => $idUsuario
            ]);

        //cho $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------


}
