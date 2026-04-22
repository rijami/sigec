<?php

declare(strict_types=1);

namespace Inicio\Controller;

use Inicio\Modelo\DAO\InicioDAO;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;

class BandejaController extends AbstractActionController
{

    private $DAO;
    private $SESSION = null;
    private $RBAC = null;
    private $rutaLog = '/var/log/sigec/';

    //------------------------------------------------------------------------------

    /**
     * Inicializa el DAO
     */
    public function __construct(InicioDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    /**
     * Valida la sesión activa y carga los permisos RBAC
     */
    private function validarSesion()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $this->SESSION = $auth->getIdentity();
            $container = new Container();
            if (isset($container->rbac)) {
                $this->RBAC = $container->rbac;
            }
        }
    }

    //------------------------------------------------------------------------------

    /**
     * Muestra el dashboard/homepage con información del empleado, último login, intentos fallidos de login y roles de usuario
     */
    public function indexAction()
    {

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $datos = [
            'RBAC' => null,
            'empleado' => '',
            'login' => '',
            'fechaTXT' => $dias[date('w')] . ' ' . date('d') . ' de ' . $meses[date('n') - 1] . ' de ' . date('Y'),
            'fechaultingresoTXT' => '0000-00-00',
            'horaultingreso' => '00:00:00',
            'contFallidos' => 0,
            'SA' => false,
        ];
        $this->validarSesion();
        if (is_null($this->SESSION)) {
            return $this->redirect()->toRoute('login');
        } else {
            $INDICADORES = [];
            $datos['RBAC'] = $this->RBAC;
            $datos['login'] = $this->SESSION->login;
            $datos['roles'] = ['Invitado'];
            $idEmpleado = $this->SESSION->idEmpleado;
            $rolesUsuario = $this->DAO->getRolesByIdUsuario($this->SESSION->idUsuario);
            if (count($rolesUsuario) > 0) {
                $datos['roles'] = [];
                foreach ($rolesUsuario as $rol) {
                    switch (intval($rol['idRol'])) {
                        case 1: // SUPER ADMINISTRADOR
                            $datos['SA'] = true;
                            break;
                        case 2: // ADMINISTRADOR
                            $datos['SA'] = true;
                            break;

                    }
                    $datos['roles'][] = $rol['rol'];
                }
            }
            $datos['INDICADORES'] = $INDICADORES;
            if (!is_null($idEmpleado)) {
                $infoEmpleado = $this->DAO->getInfoEmpleadoByID($idEmpleado);
                if (!is_null($infoEmpleado)) {
                    $datos['empleado'] = $infoEmpleado['empleado'];
                }
            } else {
                $datos['empleado'] = $datos['login'];
            }
            //--
            $container = new Container();
            if (isset($container->fechaultingreso)) {
                $fechaultingreso = $container->fechaultingreso;
            } else {
                $fechaultingreso = '0000-00-00 00:00:00';
            }
            if (isset($container->contFallidos)) {
                $contFallidos = $container->contFallidos;
            } else {
                $contFallidos = -99;
            }
            $partesFechaHoraIN = explode(' ', $fechaultingreso);
            $partesFechaIN = explode('-', $partesFechaHoraIN[0]);
            $horaultingreso = $partesFechaHoraIN[1];
            $datos['fechaultingresoTXT'] = $dias[date('w', strtotime($partesFechaHoraIN[0]))] . ' ' . $partesFechaIN[2] . ' de ' . $meses[date('n', strtotime($partesFechaHoraIN[0])) - 1] . ' de ' . $partesFechaIN[0];
            $datos['horaultingreso'] = date("g:i a", strtotime($horaultingreso));
            $datos['fechaultingresoTXT'] = $dias[date('w', strtotime($partesFechaHoraIN[0]))] . ' ' . $partesFechaIN[2] . ' de ' . $meses[date('n', strtotime($partesFechaHoraIN[0])) - 1] . ' de ' . $partesFechaIN[0];
            $datos['horaultingreso'] = date("g:i a", strtotime($horaultingreso));

            //$partesFechaHoraBAD = explode(' ', $this->SESSION->fechaultfallido);
            ////$partesFechaBAD = explode('-', $partesFechaHoraBAD[0]);
            // $horaultfallido = $partesFechaHoraBAD[1];
            /* $datos['fechaultfallidoTXT'] = $dias[date('w', strtotime($partesFechaHoraBAD[0]))] . ' ' . $partesFechaBAD[2] . ' de ' . $meses[date('n', strtotime($partesFechaHoraBAD[0])) - 1] . ' de ' . $partesFechaBAD[0];
            $datos['horaultfallido'] = date("g:i a", strtotime($horaultfallido)); */
            //--
            $datos['contFallidos'] = $contFallidos;

        }
        return new ViewModel($datos);
    }

    //------------------------------------------------------------------------------

    /**
     * Carga todos los controladores disponibles desde los módulos como recursos RBAC en la base de datos
     */
    public function cargarRecursosRbacAction()
    {
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
        $listaRecursosBD = $this->DAO->getRecursosRBAC();
        $RECURSOS_RBAC = array_diff($listaRecursosRBAC, $listaRecursosBD);
        try {
            $this->DAO->setRecursosRBAC($RECURSOS_RBAC);
            $this->flashMessenger()->addSuccessMessage('RECURSOS RBAC CARGADOS');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " CARGAR RECURSOS RBAC - BandejaController->cargarRecursosRbac \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!<br>RECURSOS RBAC NO CARGADOS EN JOSANDRO');
        }
        return $this->redirect()->toRoute('inicio');
    }

    //------------------------------------------------------------------------------

    /**
     * Muestra las opciones del módulo de dashboards/tableboards (visibilidad basada en roles)
     */
    public function modulotablerosAction()
    {
        $this->validarSesion();
        $sesion = $this->SESSION;
        $permisos = $this->RBAC;
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($this->SESSION->idUsuario);
        if ($rolesUsuario[0]['rol'] == 'SUPER_ADMINISTRADOR') {
            $tableros = $this->DAO->getTableros();
        } else {
            $tableros = $this->DAO->getTablerosByIdUsuario($this->SESSION->idUsuario);
        }

        return new ViewModel(
            [
                'rolesUsuario' => $rolesUsuario[0],
                'tableros' => $tableros
            ]
        );
    }
    //------------------------------------------------------------------------------

    /**
     * Muestra las opciones del módulo de usuarios (visibilidad basada en roles)
     */
    public function modulousuariosAction()
    {
        $this->validarSesion();
        $sesion = $this->SESSION;
        $permisos = $this->RBAC;
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($this->SESSION->idUsuario);
        return new ViewModel(
            [
                'rolesUsuario' => $rolesUsuario[0]
            ]
        );
    }
    //------------------------------------------------------------------------------

}
