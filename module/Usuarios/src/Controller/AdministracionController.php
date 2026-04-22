<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Db\Sql\Predicate\IsNull;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Usuarios\Modelo\DAO\UsuarioDAO;
use Usuarios\Modelo\Entidades\Usuario;
use Usuarios\Formularios\UsuarioForm;

class AdministracionController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = '/var/log/sigec/';

    //------------------------------------------------------------------------------

    public function __construct(UsuarioDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------


    public function getInfoSesion()
    {
        $infoSesion = [
            'login' => 'SIN INICIO DE SESION',
            'idUsuario' => 0,
            'idEmpleado' => 0
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idUsuario'] = $auth->getIdentity()->idUsuario;
            $infoSesion['idEmpleado'] = $auth->getIdentity()->idEmpleado;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------

    public function indexAction()
    {
        $sesion = $this->getInfoSesion();
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($sesion['idUsuario']);
        if ($rolesUsuario[0]['rol'] !== "SUPER_ADMINISTRADOR") {
            $filtro = "rol != 'SUPER_ADMINISTRADOR'";
        } else {
            $filtro = "";
        }
        $usuarios = $this->DAO->getUsuarios($filtro);
        /* echo "<pre>";
        print_r($usuarios); */
        return new ViewModel([
            'usuarios' => $usuarios,
            'rolesUsuario' => $rolesUsuario[0],
        ]);
    }

    //------------------------------------------------------------------------------

    public function getRolesUsuarioAction()
    {
        $idUsuario = (int) trim($this->params()->fromQuery('idUsuario', 0));
        $view = new ViewModel([
            'roles' => $this->DAO->getRolesByID($idUsuario),
        ]);
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------

    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $empleados = $this->DAO->getEmpleadosSinUsuario();
        $listaEmpleados = array();
        foreach ($empleados as $empleado) {
            $listaEmpleados[$empleado['idEmpleado']] = $empleado['empleado'];
        }
        //--
        $procesos = $this->DAO->getProcesos();
        /* $listaProcesos = ['' => 'Seleccione un Proceso...'];
        foreach ($procesos as $proceso) {
            $listaProcesos[$proceso['idProceso']] = $proceso['Proceso'];
        } */
        $coordinaciones = $this->DAO->getCoordinaciones();
        /* $listaCoordinaciones = ['' => 'Seleccione una Coordinación...'];
        foreach ($coordinaciones as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        } */
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($infosesion['idUsuario']);
        if ($rolesUsuario[0]['rol'] !== "SUPER_ADMINISTRADOR") {
            $filtro = " AND roles.rol != 'SUPER_ADMINISTRADOR'";
        } else {
            $filtro = "";
        }
        $roles = $this->DAO->getRoles($filtro);
        $listaRoles = [];
        foreach ($roles as $rol) {
            $listaRoles[$rol['idRol']] = $rol['rol'];
        }
        //--
        $formUsuario = new UsuarioForm('registrar', $listaEmpleados, $listaRoles);
        $formUsuario->get('idRol')->setAttribute('required', true);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formUsuario' => $formUsuario,
                'procesos' => $procesos,
                'coordinaciones' => $coordinaciones,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $usuarioOBJ = new Usuario();
        //$formUsuario->setInputFilter($formUsuario->getInputFilter());
        $formUsuario->setData($request->getPost());
        $infoEmpleado = $request->getPost()->toArray();

        if (!$formUsuario->isValid()) {
            print_r($formUsuario->getMessages());
            return ['formUsuario' => $formUsuario];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $usuarioOBJ->exchangeArray($formUsuario->getData());
        //--        
        /* $infoEmpleado = $this->DAO->getEmpleadoByID($idEmpleado);
        if (is_null($infoEmpleado)) {
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL ID EMPLEADO');
            return $this->redirect()->toUrl('index');
            } */
        //--
        $usuario = strtoupper($infoEmpleado['nombre1'] . ' ' . $infoEmpleado['apellido1']);
        $login = strtolower(substr($infoEmpleado['nombre1'], 0, 1) . $infoEmpleado['apellido1']);
        $i = 0;
        do {
            switch ($i) {
                case 1:
                    if ($infoEmpleado['nombre2'] != null) {
                        $login = strtolower(substr($infoEmpleado['nombre1'], 0, 1) . substr($infoEmpleado['nombre2'], 0, 1) . $infoEmpleado['apellido1']);
                    } else {
                        $login = strtolower(substr($infoEmpleado['nombre1'], 0, 1) . $infoEmpleado['apellido1']);
                    }
                    break;
                case 2:
                    $login = $login . rand(100, 999);
                    break;
                default:
                    if ($i > 0) {
                        $login = $login . rand(1000, 9999);
                    }
            }
            $i++;
        } while ($this->DAO->getExisteLogin($login));
        $password = $login . "*" . date('Y');
        //--
        //--
        $fechaultingreso = date('Y-m-d H:i:s');
        //$usuarioOBJ->setUsuario($usuario);
        $usuarioOBJ->setLogin($login);
        $usuarioOBJ->setPassword(password_hash($password, PASSWORD_BCRYPT));
        //$usuarioOBJ->setPasswordseguro('');
        $usuarioOBJ->setFechaultingreso('0000-00-00 00:00:00');
        $usuarioOBJ->setFechaultfallido('0000-00-00 00:00:00');
        $usuarioOBJ->setContFallidos(0);
        $usuarioOBJ->setEstado('Activo');
        $usuarioOBJ->setRegistradopor($registradopor);
        $usuarioOBJ->setModificadopor('');
        $usuarioOBJ->setFechahorareg($fechaultingreso);
        $usuarioOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($infoEmpleado, $usuarioOBJ, $idRol);
            $this->flashMessenger()->addSuccessMessage('EL USUARIO FUE REGISTRADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR USUARIO - AdministracionController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'app.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL USUARIO NO FUE REGISTRADO');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------

    public function detalleAction()
    {
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
        $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
        $infoRoles = $this->DAO->getRolesByID($idUsuario);
        if (is_null($infoUsuario)) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DEL USUARIO');
            return $this->redirect()->toUrl('index');
        }
        if (is_null($infoRoles)) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DE LOS ROLES');
            return $this->redirect()->toUrl('index');
        }
        //--
        $formUsuario = new UsuarioForm('detalle');
        $formUsuario->setData($infoUsuario);
        //--
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formUsuario' => $formUsuario,
                'infoRoles' => $infoRoles
            ]);
            $view->setTerminal(true);
            return $view;
        }
    }

    //------------------------------------------------------------------------------

    public function bloquearAction()
    {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        $modificadopor = $infosesion['login'];
        if (!$request->isPost()) {
            $idUsuario = (int) $this->params()->fromQuery('idUsuario', $this->params()->fromPost('idUsuario', 0));
            $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
            $infoRoles = $this->DAO->getRolesByID($idUsuario);
            //-----------------------------------------------------
            if (is_null($infoUsuario)) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DEL USUARIO');
                return $this->redirect()->toUrl('index');
            }
            $formUsuario = new UsuarioForm('bloquear');
            $formUsuario->setData($infoUsuario);
            $view = new ViewModel([
                'formUsuario' => $formUsuario,
                'infoUsuario' => $infoUsuario,
                'infoRoles' => $infoRoles,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $formUsuario = new UsuarioForm('bloquear');
        $usuarioOBJ = new Usuario();
        $formUsuario->setInputFilter($formUsuario->getInputFilter());
        $formUsuario->setData($request->getPost());
        if (!$formUsuario->isValid()) {
            print_r($formUsuario->getMessages());
            return ['formUsuario' => $formUsuario];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        try {
            $usuarioOBJ->exchangeArray($formUsuario->getData());
            $usuarioOBJ->setEstado('Bloqueado');
            $usuarioOBJ->setModificadopor($modificadopor);
            $usuarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->bloquearUsuario($usuarioOBJ);
            $this->flashMessenger()->addSuccessMessage('El USUARIO A SIDO BLOQUEADO');
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESBLOQUEAR USUARIO - AdministracionController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>NO FUE POSIBLE BLOQUEAR EL USUARIO  EN JOSANDRO');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------

    public function desbloquearAction()
    {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        $modificadopor = $infosesion['login'];
        if (!$request->isPost()) {
            $idUsuario = (int) $this->params()->fromQuery('idUsuario', $this->params()->fromPost('idUsuario', 0));
            $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
            $infoRoles = $this->DAO->getRolesByID($idUsuario);
            //-----------------------------------------------------
            if (is_null($infoUsuario)) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DEL USUARIO');
                return $this->redirect()->toUrl('index');
            }
            $formUsuario = new UsuarioForm('desbloquear');
            $formUsuario->setData($infoUsuario);
            $view = new ViewModel([
                'formUsuario' => $formUsuario,
                'infoUsuario' => $infoUsuario,
                'infoRoles' => $infoRoles,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $formUsuario = new UsuarioForm('desbloquear');
        $usuarioOBJ = new Usuario();
        $formUsuario->setInputFilter($formUsuario->getInputFilter());
        $formUsuario->setData($request->getPost());
        if (!$formUsuario->isValid()) {
            print_r($formUsuario->getMessages());
            return ['formUsuario' => $formUsuario];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        try {
            $usuarioOBJ->exchangeArray($formUsuario->getData());
            $usuarioOBJ->setEstado('Activo');
            $usuarioOBJ->setPassword(password_hash($usuarioOBJ->getLogin() . '*' . date('Y'), PASSWORD_BCRYPT));
            $usuarioOBJ->setModificadopor($modificadopor);
            $usuarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->desbloquearUsuario($usuarioOBJ);
            $this->flashMessenger()->addSuccessMessage('El USUARIO A SIDO ACTIVADO');
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESBLOQUEAR USUARIO - AdministracionController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>NO FUE POSIBLE DESBLOQUEAR EL USUARIO  EN JOSANDRO');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------
    public function cambiarContrasenaAction()
    {
        $infosesion = $this->getInfoSesion();
        $cambioclavepor = $infosesion['login'];
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', $this->params()->fromPost('idUsuario', 0));
        $nuevaContrasena = $this->params()->fromPost('nueva_contrasena', '');
        $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
        $infoRoles = $this->DAO->getRolesByID($idUsuario);
        //--
        $request = $this->getRequest();
        if (!$request->isPost()) {
            if (is_null($infoUsuario)) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DEL USUARIO');
                return $this->redirect()->toUrl('index');
            }
            if (is_null($infoRoles)) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DE LOS ROLES');
                return $this->redirect()->toUrl('index');
            }
            $formUsuario = new UsuarioForm('cambiarcontrasena');
            $formUsuario->setData($infoUsuario);
            $view = new ViewModel([
                'formUsuario' => $formUsuario,
                'infoRoles' => $infoRoles,
                'infoUsuario' => $infoUsuario,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $formUsuario = new UsuarioForm('cambiarcontrasena');
        $usuarioOBJ = new Usuario();
        $formUsuario->setInputFilter($formUsuario->getInputFilter());
        $formUsuario->setData($request->getPost());
        if (!$formUsuario->isValid()) {
            print_r($formUsuario->getMessages());
            return ['formUsuario' => $formUsuario];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        try {
            if (empty($nuevaContrasena)) {
                $this->flashMessenger()->addErrorMessage('Debe proporcionar una nueva contraseña.');
                return $this->redirect()->toUrl('index');
            }
            $contrasenaHash = password_hash($nuevaContrasena, PASSWORD_DEFAULT, ['cost' => 10]);
            $usuarioOBJ->exchangeArray($formUsuario->getData());
            $usuarioOBJ->setPassword($contrasenaHash);
            $this->DAO->actualizarContrasenaUsuario($usuarioOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CONTRASEÑA DEL USUARIO FUE CAMBIADA EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " CAMBIAR CONTRASEÑA - AdministracionController->cambiarContrasenaAction \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";

            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);

            $this->flashMessenger()->addErrorMessage('SE PRESENTÓ UN ERROR AL CAMBIAR LA CONTRASEÑA.');
        }

        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------
    public function asignartcAction()
    {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        $modificadopor = $infosesion['login'];
        $formUsuario = new UsuarioForm('asignartc');
        $formUsuario->get('idRol')->setAttribute('required', true);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formUsuario' => $formUsuario,
                //'procesos' => $procesos,
                //'coordinaciones' => $coordinaciones,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $usuarioOBJ = new Usuario();
        //$formUsuario->setInputFilter($formUsuario->getInputFilter());
        $formUsuario->setData($request->getPost());
        $infoEmpleado = $request->getPost()->toArray();

        if (!$formUsuario->isValid()) {
            print_r($formUsuario->getMessages());
            return ['formUsuario' => $formUsuario];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $usuarioOBJ->exchangeArray($formUsuario->getData());
        try {
            $usuarioOBJ->exchangeArray($formUsuario->getData());
            $usuarioOBJ->setEstado('Bloqueado');
            $usuarioOBJ->setModificadopor($modificadopor);
            $usuarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->bloquearUsuario($usuarioOBJ);
            $this->flashMessenger()->addSuccessMessage('El USUARIO A SIDO BLOQUEADO');
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESBLOQUEAR USUARIO - AdministracionController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>NO FUE POSIBLE BLOQUEAR EL USUARIO  EN JOSANDRO');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------


    public function getfuncionarioAction()
    {
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
        $identificacion = $this->params()->fromQuery('identificacion', '');
        $usuario = null;
        if ($identificacion !== '') {
            $usuario = $this->DAO->getUsuarioByIdentificacion($identificacion);
            $proceso = $this->DAO->getProcesoById($usuario['idProceso']);
            $coordinacion = $this->DAO->getCoordinacionById($usuario['idCoordinacion']);
            if ($proceso == array()) {
                $proceso = "SIN PROCESO";
            } else {
                $proceso = $proceso[0]['Proceso'];
            }
            if ($coordinacion == array()) {
                $coordinacion = "SIN COORDINACION";
            } else {
                $coordinacion = $coordinacion[0]['Coordinacion'];
            }
            $tableros = $this->DAO->getTablerosByUsuario($usuario['idUsuario']);
            print_r($tableros);
        } else {
            if ($idUsuario !== 0) {
                $usuario = $this->DAO->getUsuarioByID($idUsuario);
            }
        }
        $view = new ViewModel(array(
            'infoUsuario' => $usuario,
            'proceso' => $proceso,
            'coordinacion' => $coordinacion
        ));
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------
    public function getCoordinacionesAction()
    {
        $idProceso = (int) trim($this->params()->fromQuery('idProceso', 0));
        $view = new ViewModel([
            'coordinaciones' => $this->DAO->getCoordinacionesByProceso($idProceso),
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function existeidentificacionAction()
    {
        $identificacion = $this->params()->fromQuery('identificacion', 0);
        $existe = 1;
        if ($identificacion != 0) {
            $existe = $this->DAO->existeIdentificacion($identificacion);
        }
        return new JsonModel(array(
            'error' => 0,
            'existe' => $existe,
            'identificacion' => $identificacion,
        ));
    }
}