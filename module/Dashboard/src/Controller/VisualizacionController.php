<?php

declare(strict_types=1);

namespace Dashboard\Controller;

use Dashboard\Modelo\DAO\DashboardDAO;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Dashboard\Formularios\HistoriaUsuarioForm;
use Dashboard\Modelo\Entidades\HistoriaUsuario;
use Dashboard\Modelo\DAO\HistoriaUsuarioDAO;

class VisualizacionController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = 'C:/LOGS/';

    //------------------------------------------------------------------------------

    /**
     * Inicializa el DAO para operaciones de base de datos
     */
    public function __construct(DashboardDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    /**
     * Obtiene información del usuario autenticado
     */
    public function getInfoSesion()
    {
        $infoSesion = [
            'login' => 'SIN INICIO DE SESION'
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

    /**
     * Muestra un tablero específico con detalles, validando que el tablero exista y mostrando un mensaje de error si no se encuentra, también obtiene información del empleado y roles para mostrar en la vista
     */
    public function indexAction()
    {
        $idTablero = $this->params()->fromQuery('idTablero', 0);

        $sesion = $this->getInfoSesion();
        $idEmpleado = $sesion['idEmpleado'];
        $idUsuario = $sesion['idUsuario'];
        $empleado = $this->DAO->getEmpleadoById($idEmpleado);
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($idUsuario);

        $infotablero = $this->DAO->getTablero($idTablero);
        if (!$infotablero) {
            $this->flashMessenger()->addErrorMessage('el tablero solicitado no existe.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }
        return new ViewModel([
            'infotablero' => $infotablero,
            'idTablero' => $infotablero['idTablero'],
            'empleado' => $empleado
        ]);
    }

    //------------------------------------------------------------------------------

}
