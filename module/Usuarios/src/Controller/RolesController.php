<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Usuarios\Modelo\DAO\RolDAO;
use Usuarios\Formularios\RolForm;
use Usuarios\Modelo\Entidades\Rol;
use Laminas\View\Model\JsonModel;

class RolesController extends AbstractActionController {

    private $DAO;
    // private $rutaLog = '//';
    private $rutaLog = 'C:/ARCHIVOS_JOSANDRO/';

    //private $rutaLog = './public/log/';
//------------------------------------------------------------------------------

    public function __construct(RolDAO $dao) {
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
        $infoRoles = $this->DAO->getRoles();
        if (is_null($infoRoles)) {
            $view = new ViewModel([
                'codigoError' => '001',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Rol',
            ]);
            $view->setTemplate('usuarios/roles/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        return new ViewModel([
            'roles' => $this->DAO->getRoles(),
        ]);
    }

//------------------------------------------------------------------------------

    public function registrarAction() {
        $formRol = new RolForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['formRol' => $formRol]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $rolOBJ = new Rol();
        $formRol->setInputFilter($rolOBJ->getInputFilter());
        $formRol->setData($request->getPost());
        if (!$formRol->isValid()) {
//            print_r($formRol->getMessages());
//            return ['formRol' => $formRol];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //--
        try {
            $rolOBJ->exchangeArray($formRol->getData());
            $infosesion = $this->getInfoSesion();
            $rolOBJ->setRegistradopor($infosesion['login']);
            $rolOBJ->setEstado('Registrado');
            $rolOBJ->setFechahorareg(date('Y-m-d H:i:s'));
            $rolOBJ->setFechahoramod('0000-00-00 00:00:00');
            $rolOBJ->setModificadopor('');
            $this->DAO->registrar($rolOBJ);

            $this->flashMessenger()->addSuccessMessage('EL ROL FUE REGISTRADA EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ROL - RolesController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>EL ROL NO FUE REGISTRADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------

    public function detalleAction() {
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoRol = $this->DAO->getRolByID($idRol);
        if (is_null($infoRol)) {
            $view = new ViewModel([
                'codigoError' => '001',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Rol',
            ]);
            $view->setTemplate('tarifas/administracion/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        //--
        $formRol = new RolForm('detalle');
        $formRol->setData($infoRol);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['formRol' => $formRol,
                'infoRol' => $infoRol]);
            $view->setTerminal(true);
            return $view;
        }
    }

//------------------------------------------------------------------------------

    public function eliminarAction() {
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoRol = $this->DAO->getRolByID($idRol);
        if (is_null($infoRol)) {
            $view = new ViewModel([
                'codigoError' => '001',
                'msgError' => 'Se ha presentado un inconveniente al cargar la informacion del Rol',
            ]);
            $view->setTemplate('tarifas/administracion/error.phtml');
            $view->setTerminal(true);
            return $view;
        }
        //--
        $formRol = new RolForm('eliminar');
        $formRol->setData($infoRol);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['formRol' => $formRol,
                'infoRol' => $infoRol]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $rolOBJ = new Rol();
        $formRol->setInputFilter($rolOBJ->getInputFilter());
        $formRol->setData($request->getPost());
        if (!$formRol->isValid()) {
            print_r($formRol->getMessages());
            return ['formRol' => $formRol];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //--
        try {
            $rolOBJ->exchangeArray($formRol->getData());
            $infosesion = $this->getInfoSesion();
            $rolOBJ->setEstado('Eliminado');
            $rolOBJ->setModificadopor($infosesion['login']);
            $rolOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($rolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ROL FUE ELIMINADO EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ROL - RolesController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>EL ROL NO FUE ELIMINADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------

    public function getVerificarRolAction() {
        $rol = $this->params()->fromQuery('rol', '');
        return new JsonModel([
            'yaExiste' => $this->DAO->verificarRolSeleccionado($rol),
        ]);
    }

//------------------------------------------------------------------------------
}
