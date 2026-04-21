<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\Session\Container;
use Usuarios\Formularios\LoginForm;
use Usuarios\Modelo\RBAC\IdentityManager;
use Usuarios\Modelo\DAO\UsuarioDAO;


class LoginController extends AbstractActionController
{

    private $DAO;
    private ?IdentityManager $identityManager;
    private $rutaLog = 'C:/LOGS/';

    //------------------------------------------------------------------------------

    public function __construct(IdentityManager $identityManager, UsuarioDAO $dao)
    {
        $this->identityManager = $identityManager;
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    public function loginAction()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('inicio');
        }
        $formLogin = new LoginForm('IniciarSesion');
        $viewModel = new ViewModel([
            'formLogin' => $formLogin
        ]);
        $peticion = $this->getRequest();
        if ($peticion->isPost()) {
            $formLogin->setData($peticion->getPost());
            if ($formLogin->isValid()) {
                $datos = $formLogin->getData();
                $login = strtolower($datos['login']);
                $password = $datos['password'];
                // $this->identityManager->login($login, $password);
                // exit();
                if ($this->identityManager->login($login, $password)) {
                    //--
                    $file = fopen($this->rutaLog . 'app.log', 'a');
                    fwrite($file, "USUARIO LOGINEADO: " . $login . " - " . date('Y-m-d H:i:s') . "\n");
                    fclose($file);
                    //--
                    $fechaultingreso = '0000-00-00 00:00:00';
                    $contFallidos = 0;
                    try {
                        $auditoriaIngreso = $this->DAO->getAuditoriaIngreso($login);
                        if (!is_null($auditoriaIngreso)) {
                            $fechaultingreso = $auditoriaIngreso['fechaultingreso'];
                            $contFallidos = $auditoriaIngreso['contFallidos'];
                        }
                        $this->DAO->setFechaUltIngreso($login);
                    } catch (\Exception $ex) {
                        $msgLog = "\n" . date('Y-m-d H:i:s') . " LOGIN OK - LoginController->login \n"
                            . $ex->getMessage()
                            . "\n----------------------------------------------------------------------- \n";
                        $file = fopen($this->rutaLog . 'josandro.log', 'a');
                        fwrite($file, $msgLog);
                        fclose($file);
                    }
                    $container = new Container();
                    if (isset($container->fechaultingreso)) {
                        unset($container->fechaultingreso);
                    }
                    if (isset($container->contFallidos)) {
                        unset($container->contFallidos);
                    }
                    $container->fechaultingreso = $fechaultingreso;
                    $container->contFallidos = $contFallidos;
                    return $this->redirect()->toRoute('inicio');
                    //                    return $this->redirect()->toRoute('inicio1/bandeja', [
//                                'action' => 'index',
//                                'fechaultingreso' => $fechaultingreso
//                    ]);
                } else {
                    try {
                        $this->DAO->setLoginFallido($login);
                    } catch (\Exception $ex) {
                        $msgLog = "\n" . date('Y-m-d H:i:s') . " LOGIN FALLIDO - LoginController->login \n"
                            . $ex->getMessage()
                            . "\n----------------------------------------------------------------------- \n";
                        $file = fopen($this->rutaLog . 'app.log', 'a');
                        fwrite($file, $msgLog);
                        fclose($file);
                    }
                    $this->flashMessenger()->addErrorMessage("USUARIO O CLAVE INCORRECTO");
                }
            } else {
                $this->flashMessenger()->addErrorMessage("SE HA PRESENTADO UN INCONVENIENTE CON EL INICIO DE SU SESION (2)");
            }
        }
        $this->layout()->setTemplate('layout/login');
        if ($peticion->isXmlHttpRequest()) {
            $viewModel->setTemplate('layout/expiredSession');
            $viewModel->setTerminal(true);
        }
        return $viewModel;
    }

    //------------------------------------------------------------------------------

    public function cerrarsesionAction()
    {
        $this->identityManager->logout();
        $this->flashMessenger()->addSuccessMessage("LA SESION HA SIDO CERRADA. <br>HASTA PRONTO!");
        return $this->redirect()->toUrl('login');
    }

    //------------------------------------------------------------------------------

    public function noAutorizadoAction()
    {
        $view = new ViewModel();
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }
        return $view;
    }
}
