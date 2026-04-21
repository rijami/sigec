<?php

declare(strict_types=1);

namespace Controlacceso\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Controlacceso\Modelo\DAO\RecursoRbacDAO;
use Controlacceso\Formularios\RecursosrbacForm;
use Controlacceso\Modelo\Entidades\RecursoRbac;
use Laminas\View\Model\JsonModel;

class RecursosrbacController extends AbstractActionController {

    private $DAO;
    //private $rutaLog = 'C:/ARCHIVOS_JOSANDRO/';
    private $rutaLog = './public/log/';

//------------------------------------------------------------------------------

    /**
     * Inicializa el DAO de recursos RBAC
     */
    public function __construct(RecursoRbacDAO $dao) {
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
     * Lista todos los recursos RBAC
     */
    public function indexAction() {        
        return new ViewModel([
            'recursos' => $this->DAO->getRecursosRbac(),
        ]);
    }

//------------------------------------------------------------------------------

    /**
     * Crea un nuevo recurso RBAC
     */
    public function registrarAction() {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        
        $form = new RecursosrbacForm('registrar');
        $request = $this->getRequest();
        if(!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
                
        $recursoRbacOBJ = new RecursoRbac();  
        $form->setInputFilter($recursoRbacOBJ->getInputFilter()); 
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL RECURSO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
//      exit();

        //----------------------------------------------------------------------
        
        $recursoRbacOBJ->exchangeArray($form->getData());
        try {
            $this->DAO->registrar($recursoRbacOBJ);  
            $this->flashMessenger()->addSuccessMessage('EL RECURSO FUE REGISTRADO EN JOSANDRO');
//            return $this->redirect()->toUrl('index');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR RECURSO RBAC - RecursosrbacController->registrar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RECURSO NO FUE REGISTRADO EN JOSANDRO.');
        }
//        exit();
        return $this->redirect()->toUrl('index');
    }

//------------------------------------------------------------------------------

    /**
     * Muestra detalles de un recurso
     */
        public function detalleAction() {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        $datosRecurso = $this->DAO->getRecurso($idRecurso);
        //--------------------------------------
        $form = new RecursosrbacForm('detalle');
        $form->setData($datosRecurso);
        $view = new ViewModel(['form' => $form]);
        $view->setTerminal(true);
        return $view;
    }

//------------------------------------------------------------------------------

    /**
     * Actualiza la información del recurso
     */
    public function editarAction() {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        if (!$request->isPost()) {
            $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
            if ($idRecurso == 0) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL ID RECURSO');
                return $this->redirect()->toUrl('index');
            }
            //--
            $infoRecurso = $this->DAO->getRecurso($idRecurso);
            if (empty($infoRecurso)) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL RECURSO NO SE ENCUENTRA REGISTRADO');
                return $this->redirect()->toUrl('index');
            }  
                        
            //----------------------------------------------------------------------
            $form = new RecursosrbacForm('editar');
            $form->setData($infoRecurso);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        
        if (!$request->isPost()) {
            $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
            $infoRecurso = $this->DAO->getRecurso($idRecurso);
            //--           
            $form = new RecursosrbacForm('editar');
            $form->setData($infoRecurso);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
               
        $form = new RecursosrbacForm('editar');
        $recursoRbacOBJ = new RecursoRbac();
        $form->setInputFilter($recursoRbacOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
              return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $recursoRbacOBJ->exchangeArray($form->getData());
        $modificadopor = $infosesion['login'];
//        $recursoRbacOBJ->setModificadopor($modificadopor);
//        $recursoRbacOBJ->setFechahoramod(date('Y-m-d H:i:s'));
        try {
            $this->DAO->editar($recursoRbacOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL RECURSO FUE ACTUALIZADA EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR RECURSO - RecursosrbacController->editar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RECURSO NO FUE ACTUALIZADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }
    
    //------------------------------------------------------------------------------
    
    public function eliminarAction() {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        if (!$request->isPost()) {
            $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
            if ($idRecurso == 0) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, NO SE TIENE EL ID RECURSO');
                return $this->redirect()->toUrl('index');
            }
            //--
            $infoRecurso = $this->DAO->getRecurso($idRecurso);
            if (empty($infoRecurso)) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL RECURSO NO SE ENCUENTRA REGISTRADO');
                return $this->redirect()->toUrl('index');
            }  
                        
            //----------------------------------------------------------------------
            $form = new RecursosrbacForm('eliminar');
            $form->setData($infoRecurso);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        
        if (!$request->isPost()) {
            $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
            $infoRecurso = $this->DAO->getRecurso($idRecurso);
            //--           
            $form = new RecursosrbacForm('eliminar');
            $form->setData($infoRecurso);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
               
        $form = new RecursosrbacForm('eliminar');
        $recursoRbacOBJ = new RecursoRbac();
        $form->setInputFilter($recursoRbacOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
              return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A ELIMINAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $recursoRbacOBJ->exchangeArray($form->getData());
        $modificadopor = $infosesion['login'];
//        $recursoRbacOBJ->setModificadopor($modificadopor);
//        $recursoRbacOBJ->setFechahoramod(date('Y-m-d H:i:s'));
        try {
            $this->DAO->eliminar($recursoRbacOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL RECURSO FUE ELIMINADA EN JOSANDRO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR RECURSO - RecursosrbacController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RECURSO NO FUE ELIMINADO EN JOSANDRO.');
        }
        return $this->redirect()->toUrl('index');
    }
}
