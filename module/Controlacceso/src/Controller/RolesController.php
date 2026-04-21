<?php

declare(strict_types=1);

namespace Controlacceso\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Controlacceso\Modelo\DAO\RolesDAO;
use Controlacceso\Formularios\RolesForm;
use Controlacceso\Modelo\Entidades\Rol;
use Laminas\View\Model\JsonModel;

class RolesController extends AbstractActionController {

    private $DAO;
    //private $rutaLog = 'C:/ARCHIVOS_JOSANDRO/';
    private $rutaLog = './public/log/';

//------------------------------------------------------------------------------

    /**
     * Inicializa el DAO de roles
     */
    public function __construct(RolesDAO $dao) {
        $this->DAO = $dao;
    }

//------------------------------------------------------------------------------

    /**
     * Obtiene información de la sesión
     */
    public function getInfoSesion() {
        $infoSesion = [
            'login' => 'SIN INICIO DE SESION'
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
        }
        return $infoSesion;
    }

//------------------------------------------------------------------------------

    /**
     * Lista todos los roles de la base de datos
     */
    public function indexAction() {        
        return new ViewModel([
            'roles' => $this->DAO->getRoles(),
        ]);
    }

//------------------------------------------------------------------------------

    /**
     * Crea un nuevo rol con validación de formulario
     */
    public function registrarAction() {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        
        //----------------------------------------------------------------------
        
        $form = new RolesForm('registrar');
        $request = $this->getRequest();
        if(!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
                
        $rolOBJ = new Rol();  
        $form->setInputFilter($rolOBJ->getInputFilter()); 
        $form->setData($request->getPost()); 

        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
//        exit();

        //----------------------------------------------------------------------
        
        $rolOBJ->exchangeArray($form->getData());        
        try {
            $this->DAO->registrar($rolOBJ);  
            $this->flashMessenger()->addSuccessMessage('EL ROL FUE REGISTRADO EN JOSANDRO');
//            return $this->redirect()->toUrl('index');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ROL - RolesController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ROL NO FUE REGISTRADO EN JOSANDRO.');
        }
//        exit();
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------
    
    public function detalleAction() {
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        $datosRol = $this->DAO->getRol($idRol);
        //--------------------------------------
        $form = new RolesForm('detalle');
        $form->setData($datosRol);
        $view = new ViewModel(['form' => $form]);
        $view->setTerminal(true);
        return $view;
    }

//------------------------------------------------------------------------------
    
    public function existerolAction() {
        $rol = $this->params()->fromQuery('rol', '');
        $existe = 1;     
        if ($rol != '') {
            $existe = $this->DAO->existeRol($rol);
        }
        return new JsonModel([
            'error' => 0,
            'existe' => $existe,
            'rol' => $rol
        ]);
    }
    
//------------------------------------------------------------------------------
    
    public function editarAction() {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        if (!$request->isPost()) {
            $idRol = (int) $this->params()->fromQuery('idRol', 0);
            if ($idRol == 0) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL ID ROL');
                return $this->redirect()->toUrl('index');
            }
            //--
            $infoRol = $this->DAO->getRol($idRol);
            if (empty($infoRol)) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL ROL NO SE ENCUENTRA REGISTRADO');
                return $this->redirect()->toUrl('index');
            }            
            
            //------------------------------------------------------------------
            $form = new RolesForm('editar');
            $form->setData($infoRol);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        
        if (!$request->isPost()) {
            $idRol = (int) $this->params()->fromQuery('idRol', 0);
            $infoRol = $this->DAO->getRol($idRol);
            //-- 
            $form = new RolesForm('editar');
            $form->setData($infoRol);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }        
        //----------------------------------------------------------------------
        $form = new RolesForm('editar');
        $rolOBJ = new Rol();
        $form->setInputFilter($rolOBJ->getInputFilter());
        $form->setData($request->getPost());        
        if (!$form->isValid()) {
//            print_r($form->getMessages());
//            return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $rolOBJ->exchangeArray($form->getData());
        $modificadopor = $infosesion['login'];
//            $rolOBJ->setModificadopor($modificadopor);
//            $rolOBJ->setFechahoramod(date('Y-m-d H:i:s'));
        try {            
            $this->DAO->editar($rolOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ROL FUE ACTUALIZADA EN JOSANDRO');
        } catch (\Exception $ex) {            
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR ROL - RolesController->editar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ROL NO FUE ACTUALIZADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }
    
    //------------------------------------------------------------------------------
    
    public function eliminarAction() {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        if (!$request->isPost()) {
            $idRol = (int) $this->params()->fromQuery('idRol', 0);
            if ($idRol == 0) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL ID ROL');
                return $this->redirect()->toUrl('index');
            }
            //--
            $infoRol = $this->DAO->getRol($idRol);
            if (empty($infoRol)) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL ROL NO SE ENCUENTRA REGISTRADO');
                return $this->redirect()->toUrl('index');
            }            
            
            //------------------------------------------------------------------
            $form = new RolesForm('eliminar');
            $form->setData($infoRol);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        
        if (!$request->isPost()) {
            $idRol = (int) $this->params()->fromQuery('idRol', 0);
            $infoRol = $this->DAO->getRol($idRol);
            //-- 
            $form = new RolesForm('eliminar');
            $form->setData($infoRol);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }        
        //----------------------------------------------------------------------
        $form = new RolesForm('eliminar');
        $rolOBJ = new Rol();
        $form->setInputFilter($rolOBJ->getInputFilter());
        $form->setData($request->getPost());        
        if (!$form->isValid()) {
//            print_r($form->getMessages());
//            return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ROL NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $rolOBJ->exchangeArray($form->getData());
        $modificadopor = $infosesion['login'];
//            $rolOBJ->setModificadopor($modificadopor);
//            $rolOBJ->setFechahoramod(date('Y-m-d H:i:s'));
        try {            
            $this->DAO->eliminar($rolOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ROL FUE ELIMINADA EN JOSANDRO');
        } catch (\Exception $ex) {            
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ROL - RolesController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ROL NO FUE ELIMINADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }
    
}
