<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Usuarios\Modelo\DAO\UsuarioRolDAO;
use Usuarios\Formularios\UsuarioRolForm;
use Usuarios\Modelo\Entidades\UsuarioRol;
use Laminas\View\Model\JsonModel;

class UsuariorolController extends AbstractActionController {

    private $DAO;
    // private $rutaLog = '//';
    private $rutaLog = 'C:/ARCHIVOS_JOSANDRO/';
    private $rutaArchivos = 'C:/ARCHIVOS_JOSANDRO/PAQUETES_TARIFAS/';

    //private $rutaLog = './public/log/';
//------------------------------------------------------------------------------

    public function __construct(UsuarioRolDAO $dao) {
        $this->DAO = $dao;
    }

//------------------------------------------------------------------------------

    public function getInfoSesion() {
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

    public function indexAction() {
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
        $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
        $infoRoles = $this->DAO->getRolesbyid($idUsuario);
        if (is_null($infoUsuario)) {
            $view = new ViewModel([
                'codigoError' => '001',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Usuario',
            ]);
            $view->setTemplate('tarifas/administracion/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        return new ViewModel([
            'infoRoles' => $infoRoles,
            'infoUsuario' => $infoUsuario
        ]);
    }

//------------------------------------------------------------------------------

    public function registrarAction() {
        //--
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', $this->params()->fromPost('idUsuario', 0));
        $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);

        if (is_null($infoUsuario)) {
            $view = new ViewModel([
                'codigoError' => '001',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Usuario',
            ]);
            $view->setTemplate('tarifas/administracion/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        $roles = $this->DAO->getRoles();
        $listaRoles = [];
        foreach ($roles as $rol) {
            $listaRoles[$rol['idRol']] = $rol['rol'];
        }
        //--
        $formUsuarioRol = new UsuarioRolForm('registrar', $listaRoles);
        $formUsuarioRol->setData($infoUsuario);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['formUsuarioRol' => $formUsuarioRol]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $usuarioRolOBJ = new UsuarioRol();
        $formUsuarioRol->setInputFilter($usuarioRolOBJ->getInputFilter());
        $formUsuarioRol->setData($request->getPost());
        if (!$formUsuarioRol->isValid()) {
            print_r($formUsuarioRol->getMessages());
            return ['formUsuarioRol' => $formUsuarioRol];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //--
        $usuarioRolOBJ->exchangeArray($formUsuarioRol->getData());
        try {
            $this->DAO->registrar($usuarioRolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ROL FUE REGISTRADA EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ROL - UsuariorolController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>EL ROL NO FUE REGISTRADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index?idUsuario=' . $idUsuario);
    }

//------------------------------------------------------------------------------

    public function detalleAction() {
        //--
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', $this->params()->fromPost('idUsuario', 0));
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
        $infoRol = $this->DAO->getUsuarioRol($idUsuario, $idRol);
        if (is_null($infoUsuario)) {
            $view = new ViewModel([
                'codigoError' => '001',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Usuario',
            ]);
            $view->setTemplate('tarifas/administracion/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        if (is_null($infoRol)) {
            $view = new ViewModel([
                'codigoError' => '002',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Rol',
            ]);
            $view->setTemplate('tarifas/administracion/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        //--
        $formUsuarioRol = new UsuarioRolForm('detalle');
        $formUsuarioRol->setData($infoUsuario);

        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['formUsuarioRol' => $formUsuarioRol,
                'infoRol' => $infoRol]);
            $view->setTerminal(true);
            return $view;
        }
    }

//------------------------------------------------------------------------------

    public function eliminarAction() {
        //--
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', $this->params()->fromPost('idUsuario', 0));
        $infoUsuario = $this->DAO->getUsuarioByID($idUsuario);
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoRol = $this->DAO->getUsuarioRol($idUsuario, $idRol);
        if (is_null($infoUsuario)) {
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL USUARIO');
            return $this->redirect()->toUrl('index');
        }
        if (is_null($infoRol)) {
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL ROL');
            return $this->redirect()->toUrl('index');
        }
        //--
        $formUsuarioRol = new UsuarioRolForm('eliminar');
        $formUsuarioRol->setData($infoUsuario);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formUsuarioRol' => $formUsuarioRol,
                'infoRol' => $infoRol
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $usuarioRolOBJ = new UsuarioRol();
        $formUsuarioRol->setInputFilter($usuarioRolOBJ->getInputFilter());
        $formUsuarioRol->setData($request->getPost());
        if (!$formUsuarioRol->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL  NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //--
        try {
            $usuarioRolOBJ->exchangeArray($formUsuarioRol->getData());
            $this->DAO->eliminar($usuarioRolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ROL DEL USUARIO FUE ELIMINADO EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ROL DEL USUARIO - ´UsuariorolController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>ROL NO FUE ELIMINADO  EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index?idUsuario=' . $idUsuario);
    }

//------------------------------------------------------------------------------

    public function getVerificarRolAction() {
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        return new JsonModel([
            'yaExiste' => $this->DAO->verificarRolSeleccionado($idUsuario, $idRol),
        ]);
    }

//------------------------------------------------------------------------------    

    public function getBloquearUsuarioAction() {
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        return new JsonModel([
            'yaExiste' => $this->DAO->verificarRolSeleccionado($idUsuario, $idRol),
        ]);
    }
}
