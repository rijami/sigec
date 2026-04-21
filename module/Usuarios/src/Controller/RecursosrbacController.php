<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Usuarios\Modelo\DAO\RecursosrbacDAO;
use Usuarios\Modelo\Entidades\RecursoRbac;
use Usuarios\Formularios\RecursosrbacForm;
use Laminas\View\Model\JsonModel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use ReflectionClass;
use ReflectionMethod;

class RecursosrbacController extends AbstractActionController {

    private $DAO;
    private $rutaLog = 'C:/ARCHIVOS_JOSANDRO/';

//------------------------------------------------------------------------------

    public function __construct(RecursosrbacDAO $dao) {
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
        return new ViewModel([
            'recursosrbac' => $this->DAO->getRecursosRbac(),
        ]);
    }

//------------------------------------------------------------------------------

    public function registrarAction() {
        $formRecursosRbac = new RecursosrbacForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formRecursosRbac' => $formRecursosRbac,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $recursoRbacOBJ = new RecursoRbac();
        $formRecursosRbac->setInputFilter($formRecursosRbac->getInputFilter());
        $formRecursosRbac->setData($request->getPost());
        if (!$formRecursosRbac->isValid()) {
            print_r($formRecursosRbac->getMessages());
            return ['formRecursosRbac' => $formRecursosRbac];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE RECURSO RBAC  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //---------------------------------------------------------------------
        $recursoRbacOBJ->exchangeArray($formRecursosRbac->getData());
        try {
            $this->DAO->registrar($recursoRbacOBJ);
            $this->flashMessenger()->addSuccessMessage('EL RECURSO RBAC FUE REGISTRADO EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR RECURSO RBAC - RecursosRbacController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RECURSO RBAC NO FUE REGISTRADO EN JOSANDRO');
        }
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------

    public function detalleAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        $infoRecursoRbac = $this->DAO->getRecursoRbacByID($idRecurso);
        if (is_null($infoRecursoRbac)) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER LA INFORMACION DEL USUARIO');
            return $this->redirect()->toUrl('index');
        }
        //--
        $formRecursosRbac = new RecursosrbacForm('detalle');
        $formRecursosRbac->setData($infoRecursoRbac);
        //--
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formRecursosRbac' => $formRecursosRbac,
            ]);
            $view->setTerminal(true);
            return $view;
        }
    }

//------------------------------------------------------------------------------
    public function eliminarAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        $infoRecursoRbac = $this->DAO->getRecursoRbacByID($idRecurso);
        //--
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $formRecursosRbac = new RecursosrbacForm('eliminar');
            $formRecursosRbac->setData($infoRecursoRbac);
            $view = new ViewModel([
                'formRecursosRbac' => $formRecursosRbac,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //--
        $formRecursosRbac = new RecursosrbacForm('eliminar');
        $recursoRbacOBJ = new RecursoRbac();
        $formRecursosRbac->setInputFilter($formRecursosRbac->getInputFilter());
        $formRecursosRbac->setData($request->getPost());
        if (!$formRecursosRbac->isValid()) {
            print_r($formRecursosRbac->getMessages());
            return ['formRecursosRbac' => $formRecursosRbac];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE RECURSO RBAC  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //---------------------------------------------------------------------
        $recursoRbacOBJ->exchangeArray($formRecursosRbac->getData());
        try {
            $this->DAO->eliminar($recursoRbacOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ROL FUE ELIMINADO EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ROL - RolesController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'appmasclick.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>EL ROL NO FUE ELIMINADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------
    public function editarAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        $infoRecursoRbac = $this->DAO->getRecursoRbacByID($idRecurso);
        //--
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $formRecursosRbac = new RecursosrbacForm('editar');
            $formRecursosRbac->setData($infoRecursoRbac);
            $view = new ViewModel([
                'formRecursosRbac' => $formRecursosRbac,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $formRecursosRbac = new RecursosrbacForm('editar');
        $recursoRbacOBJ = new RecursoRbac();
        $formRecursosRbac->setInputFilter($formRecursosRbac->getInputFilter());
        $formRecursosRbac->setData($request->getPost());
        if (!$formRecursosRbac->isValid()) {
            print_r($formRecursosRbac->getMessages());
            return ['formRecursosRbac' => $formRecursosRbac];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE RECURSO RBAC  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //---------------------------------------------------------------------
        $recursoRbacOBJ->exchangeArray($formRecursosRbac->getData());
        try {
            $this->DAO->editar($recursoRbacOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ROL FUE ELIMINADO EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ROL - RolesController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'appmasclick.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>EL ROL NO FUE ELIMINADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------

    public function getVerificarRecursoMetodoAction() {
        $recurso = $this->params()->fromQuery('recurso', '');
        $recursostr = str_replace('\\', '\\\\', $recurso);
        $metodo = $this->params()->fromQuery('metodo', '');
        return new JsonModel([
            'yaExiste' => $this->DAO->verificarRecursoyMetodoSeleccionado($recursostr, $metodo),
        ]);
    }

//------------------------------------------------------------------------------

    public function cargarRecursosRbacAction() {
        $listaControllers = [];
        $listaRecursosRBAC = [];
        $skipActionsList = ['notFoundAction', 'getMethodFromAction'];
        $sm = $this->getEvent()->getApplication()->getServiceManager();
        $manager = $sm->get('ModuleManager');
        $modules = $manager->getLoadedModules();
        $loadedModules = array_keys($modules);
        foreach ($loadedModules as $loadedModule) {
            if ($loadedModule != 'Layout') {
                if (strpos($loadedModule, 'Laminas') === false) {
                    $moduleClass = '\\' . $loadedModule . '\Module';
                    $moduleObject = new $moduleClass;
                    $config = $moduleObject->getControllerConfig();
                    if (array_key_exists('factories', $config)) {
                        $controllers = array_keys($config['factories']);
                        foreach ($controllers as $controller) {
                            array_push($listaControllers, $controller);
                        }
                    }
                }
            }
        }
        foreach ($listaControllers as $controller) {
            $tmpArray = get_class_methods($controller);
            if (is_array($tmpArray)) {
                foreach ($tmpArray as $action) {
                    if (substr($action, strlen($action) - 6) === 'Action' && !in_array($action, $skipActionsList)) {
                        $action = substr($action, 0, -6);
                        $recurso = $controller . '.' . $action . ':GET';
                        if (!in_array($recurso, $listaRecursosRBAC)) {
                            $listaRecursosRBAC[] = $recurso;
                        }
                        $recurso = $controller . '.' . $action . ':POST';
                        if (!in_array($recurso, $listaRecursosRBAC)) {
                            $listaRecursosRBAC[] = $recurso;
                        }
                    }
                }
            }
        }
        $listaRecursosBD = $this->DAO->getRecursosRBACparaCargar();
        $RECURSOS_RBAC = array_diff($listaRecursosRBAC, $listaRecursosBD);
        try {
            $this->DAO->setRecursosRBAC($RECURSOS_RBAC);
            $this->flashMessenger()->addSuccessMessage('RECURSOS RBAC CARGADOS EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " CARGAR RECURSOS RBAC - BandejaController->cargarRecursosRbac \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>RECURSOS RBAC NO CARGADOS EN JOSANDRO');
        }
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------
}
