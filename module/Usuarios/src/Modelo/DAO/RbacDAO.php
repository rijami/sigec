<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Usuarios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;

class RbacDAO extends AbstractTableGateway {

    protected $table = '';

//------------------------------------------------------------------------------

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

//------------------------------------------------------------------------------
public function getRecursosRbacByIdUsuario($idUsuario = 0)
{
    $this->table = 'recursos_rbac';

    $select = new \Laminas\Db\Sql\Select($this->table);

    $select->columns([
        'idRecurso',
        'recurso',
        'metodo',
    ])
    ->join(
        'recursorbac_rol',
        'recursos_rbac.idRecurso = recursorbac_rol.idRecurso',
        []
    )
    ->join(
        'roles',
        'recursorbac_rol.idRol = roles.idRol',
        []
    )
    ->join(
        'usuario_rol',
        'roles.idRol = usuario_rol.idRol',
        []
    )
    ->where([
        'usuario_rol.idUsuario' => (int) $idUsuario
    ]);
    //->group('recursos_rbac.idRecurso');
    // echo $select->getSqlString();
    $data = $this->selectWith($select)->toArray();
    // var_dump($data);
//    exit();
    return $data;
//    return $this->selectWith($select)->toArray();
}
//MYSQL
   /* public function getRecursosRbacByIdUsuario($idUsuario = 0) {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            'idRecurso',
            'recurso',
            'metodo',
        ])->join('recursorbac_rol', 'recursos_rbac.idRecurso = recursorbac_rol.idRecurso', [
        ])->join('roles', 'recursorbac_rol.idRol = roles.idRol', [
        ])->join('usuario_rol', 'roles.idRol = usuario_rol.idRol', [
        ])->where("usuario_rol.idUsuario = $idUsuario")->group('recursos_rbac.idRecurso');
//        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
*/
//------------------------------------------------------------------------------
}
