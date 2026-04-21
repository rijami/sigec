<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Usuarios\Modelo\DAO\PermisosDAO;
use Usuarios\Formularios\PermisosForm;
use Usuarios\Formularios\ImportarRolForm;
use Usuarios\Modelo\Entidades\Recursorbacrol;

class PermisosController extends AbstractActionController {

    private $DAO;
    private $rutaLog = 'C:/ARCHIVOS_MASCLICK/';

//------------------------------------------------------------------------------

    public function __construct(PermisosDAO $dao) {
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
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        //--
        $infoRol = $this->DAO->getRolByID($idRol);
        if (is_null($infoRol)) {
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO FUE POSIBLE ENCONTRAR EL CONTRATO');
            return $this->redirect()->toUrl('../hogar/index');
        }
        //--
        //--
        return new ViewModel([
            'infoRol' => $infoRol,
            'recursos' => $this->DAO->getRecursosByIdRol($idRol),
        ]);
    }

//------------------------------------------------------------------------------

    public function registrarAction() {
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoRol = $this->DAO->getRolByID($idRol);
        $request = $this->getRequest();
        if (is_null($infoRol)) {
            $msg = 'Se ha presentado un inconveniente al cargar la informacion del Rol';
            if ($request->isPost()) {
                $this->flashMessenger()->addErrorMessage($msg);
                return $this->redirect()->toUrl('../roles/index');
            } else {
                $view = new ViewModel([
                    'codigoError' => '001',
                    'msgError' => $msg,
                ]);
                $view->setTemplate('usuarios/permisos/error.phtml');
                $view->setTerminal(true);
                return $view;
            }
        }
        $recursos = $this->DAO->getRecursosRbac();
        $listaRecursos = array();
        foreach ($recursos as $recurso) {
            $listaRecursos[$recurso['idRecurso']] = $recurso['recurso'] . ' (' . $recurso['metodo'] . ')';
        }
        $formPermiso = new PermisosForm('registrar', $listaRecursos);
        if (!$request->isPost()) {
            $formPermiso->setData($infoRol);
            $view = new ViewModel([
                'infoRol' => $infoRol,
                'formPermiso' => $formPermiso
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $recursoRbacRolOBJ = new Recursorbacrol();
        $formPermiso->setInputFilter($formPermiso->getInputFilter());
        $formPermiso->setData($request->getPost());
        if (!$formPermiso->isValid()) {
            print_r($formPermiso->getMessages());
            return ['formPermiso' => $formPermiso];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO RECURSO ROL NO ES VÁLIDA');
            return $this->redirect()->toUrl('index?idRol=' . $idRol);
        }
        //------
        $recursoRbacRolOBJ->exchangeArray($formPermiso->getData());
        try {
            $this->DAO->registrar($recursoRbacRolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL RECURSO FUE VINCULADO AL ROL EN APPMASCLICK');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR RECURSO ROL - PermisosController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'appmasclick.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RECURSO NO FUE VINCULADO AL ROL EN APPMASCLICK');
        }
        return $this->redirect()->toUrl('index?idRol=' . $idRol);
    }

//------------------------------------------------------------------------------

    public function importarAction() {
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $idRolAux = (int) $this->params()->fromPost('idRolAux', 0);
        $infoRol = $this->DAO->getRolByID($idRol);
        $request = $this->getRequest();
        if (is_null($infoRol)) {
            $msg = 'Se ha presentado un inconveniente al cargar la informacion del Rol';
            if ($request->isPost()) {
                $this->flashMessenger()->addErrorMessage($msg);
                return $this->redirect()->toUrl('../roles/index');
            } else {
                $view = new ViewModel([
                    'codigoError' => '001',
                    'msgError' => $msg,
                ]);
                $view->setTemplate('usuarios/permisos/error.phtml');
                $view->setTerminal(true);
                return $view;
            }
        }
//        $recursos = $this->DAO->getRecursosRbac();
//        $listaRecursos = array();
//        foreach ($recursos as $recurso) {
//            $listaRecursos[$recurso['idRecurso']] = $recurso['recurso'] . ' (' . $recurso['metodo'] . ')';
//        }
        $roles = $this->DAO->getRoles();
        $listaRoles = [];
        foreach ($roles as $roles) {
            $listaRoles[$roles['idRol']] = $roles['rol'];
        }

        $formImportarRol = new ImportarRolForm('importar', $listaRoles);
        if (!$request->isPost()) {
            $formImportarRol->setData($infoRol);
            $view = new ViewModel([
                'infoRol' => $infoRol,
                'formImportarRol' => $formImportarRol
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $recursoRbacRolOBJ = new Recursorbacrol();
        $formImportarRol->setInputFilter($formImportarRol->getInputFilter());
        $formImportarRol->setData($request->getPost());
        if (!$formImportarRol->isValid()) {
            print_r($formImportarRol->getMessages());
            return ['formImportarRol' => $formImportarRol];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO RECURSO ROL NO ES VÁLIDA');
            return $this->redirect()->toUrl('index?idRol=' . $idRol);
        }
        $recursoRbacRolOBJ->exchangeArray($formImportarRol->getData());
        //--
        $recursosRoles = $this->DAO->getRecursosRbacRolByIdRol($idRol);
        $recursosRolesAux = $this->DAO->getRecursosRbacRolByIdRol($idRolAux);
        $idsRol = array_column($recursosRoles, 'idRecurso');
        $idsRolAux = array_column($recursosRolesAux, 'idRecurso');
        $soloEnRolAux = array_diff($idsRolAux, $idsRol);
        if (empty($soloEnRolAux)) {
            $this->flashMessenger()->addErrorMessage('LOS RECURSOS QUE DESEAS ASIGNAR YA LOS TIENE EL ROL ACTUAL.');
            return $this->redirect()->toUrl('index?idRol=' . $idRol);
        }
        //--
        try {
            $RECURSOS_IMPORTAR = [];
            foreach ($soloEnRolAux as $idRecursoNuevo) {
                $RECURSOS_IMPORTAR[] = new Recursorbacrol([
                    'idRol' => $idRol,
                    'idRecurso' => $idRecursoNuevo
                ]);
            }
            $this->DAO->importar($RECURSOS_IMPORTAR);
            $this->flashMessenger()->addSuccessMessage('LOS RECURSOS FUERON IMPORTADOS AL ROL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " IMPORTAR RECURSO ROL - PermisosController->importar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'appmasclick.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>LOS RECURSOS NO FUERON IMPORTADOS AL ROL');
        }
        return $this->redirect()->toUrl('index?idRol=' . $idRol);
    }

//------------------------------------------------------------------------------ 

    public function getRecursosAction() {
        $idRolAux = (int) trim($this->params()->fromQuery('idRolAux', 0));
        $view = new ViewModel([
            'recursosrol' => $this->DAO->getRecursosByIdRol($idRolAux),
        ]);
        $view->setTerminal(true);
        return $view;
    }

//------------------------------------------------------------------------------

    public function detalleAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', $this->params()->fromPost('idRecurso', 0));
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoRol = $this->DAO->getRolByID($idRol);
        $request = $this->getRequest();
        if (is_null($infoRol)) {
            $msg = 'Se ha presentado un inconveniente al cargar la informacion del Rol';
            if ($request->isPost()) {
                $this->flashMessenger()->addErrorMessage($msg);
                return $this->redirect()->toUrl('../roles/index');
            } else {
                $view = new ViewModel([
                    'codigoError' => '001',
                    'msgError' => $msg,
                ]);
                $view->setTemplate('usuarios/permisos/error.phtml');
                $view->setTerminal(true);
                return $view;
            }
        }
        $infoRecurso = $this->DAO->getRecursoRbacByID($idRecurso);
        if (is_null($infoRecurso)) {
            $msg = 'Se ha presentado un inconveniente al cargar la informacion del Rol';
            if ($request->isPost()) {
                $this->flashMessenger()->addErrorMessage($msg);
                return $this->redirect()->toUrl('../roles/index');
            } else {
                $view = new ViewModel([
                    'codigoError' => '001',
                    'msgError' => $msg,
                ]);
                $view->setTemplate('usuarios/permisos/error.phtml');
                $view->setTerminal(true);
                return $view;
            }
        }
        $recursos = $this->DAO->getRecursosRbac();
        $listaRecursos = array();
        foreach ($recursos as $recurso) {
            $listaRecursos[$recurso['idRecurso']] = $recurso['recurso'] . ' (' . $recurso['metodo'] . ')';
        }
        $formPermiso = new PermisosForm('registrar', $listaRecursos);
        if (!$request->isPost()) {
            $formPermiso->setData($infoRecurso);
            $view = new ViewModel([
                'infoRol' => $infoRol,
                'infoRecurso' => $infoRecurso,
                'formPermiso' => $formPermiso
            ]);
            $view->setTerminal(true);
            return $view;
        }
    }

//------------------------------------------------------------------------------

    public function eliminarAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', $this->params()->fromPost('idRecurso', 0));
        $idRol = (int) $this->params()->fromQuery('idRol', $this->params()->fromPost('idRol', 0));
        $infoRol = $this->DAO->getRolByID($idRol);
        $request = $this->getRequest();
        if (is_null($infoRol)) {
            $msg = 'Se ha presentado un inconveniente al cargar la informacion del Rol';
            if ($request->isPost()) {
                $this->flashMessenger()->addErrorMessage($msg);
                return $this->redirect()->toUrl('../roles/index');
            } else {
                $view = new ViewModel([
                    'codigoError' => '001',
                    'msgError' => $msg,
                ]);
                $view->setTemplate('usuarios/permisos/error.phtml');
                $view->setTerminal(true);
                return $view;
            }
        }
        $infoRecurso = $this->DAO->getRecursoRbacRolByID($idRecurso, $idRol);
        if (is_null($infoRecurso)) {
            $msg = 'Se ha presentado un inconveniente al cargar la informacion del Rol';
            if ($request->isPost()) {
                $this->flashMessenger()->addErrorMessage($msg);
                return $this->redirect()->toUrl('../roles/index');
            } else {
                $view = new ViewModel([
                    'codigoError' => '001',
                    'msgError' => $msg,
                ]);
                $view->setTemplate('usuarios/permisos/error.phtml');
                $view->setTerminal(true);
                return $view;
            }
        }
        $recursos = $this->DAO->getRecursosRbac();
        $listaRecursos = array();
        foreach ($recursos as $recurso) {
            $listaRecursos[$recurso['idRecurso']] = $recurso['recurso'] . ' (' . $recurso['metodo'] . ')';
        }
        $formPermiso = new PermisosForm('eliminar', $listaRecursos);
        if (!$request->isPost()) {
            $formPermiso->setData($infoRol);
            $view = new ViewModel([
                'idRol' => $idRol,
                'infoRol' => $infoRol,
                'infoRecurso' => $infoRecurso,
                'formPermiso' => $formPermiso
            ]);
            $view->setTerminal(true);
            return $view;
        }


        $recursoRbacRolOBJ = new Recursorbacrol();
        $formPermiso->setInputFilter($formPermiso->getInputFilter());
        $formPermiso->setData($request->getPost());
        if (!$formPermiso->isValid()) {
            print_r($formPermiso->getMessages());
            return ['formPermiso' => $formPermiso];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO RECURSO ROL NO ES VÁLIDA');
            return $this->redirect()->toUrl('index?idRol=' . $idRol);
        }
//------
        $recursoRbacRolOBJ->exchangeArray($formPermiso->getData());
        try {
            $this->DAO->eliminar($recursoRbacRolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL RECURSO FUE DESVINCULADO DEL ROL EN APPMASCLICK');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR RECURSO ROL - PermisosController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'appmasclick.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RECURSO NO FUE DESVINCULADO DEL ROL EN APPMASCLICK');
        }
        return $this->redirect()->toUrl('index?idRol=' . $idRol);
    }

//------------------------------------------------------------------------------
    public function getVerificarRecursoMetodoAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        return new JsonModel([
            'yaExiste' => $this->DAO->verificaridRecursoyidRolSeleccionado($idRecurso, $idRol),
        ]);
    }
}
