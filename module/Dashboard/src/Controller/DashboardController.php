<?php

declare(strict_types=1);

namespace Dashboard\Controller;

use Dashboard\Modelo\DAO\DashboardDAO;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Dashboard\Formularios\TablerosForm;
use Dashboard\Modelo\Entidades\Tablero;
use Dashboard\Modelo\DAO\TablerosDAO;

class DashboardController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = '/var/log/sigec/';

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
     * Recupera la información de sesión del usuario actual (login, idUsuario, idEmpleado)
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
     * Muestra la lista de Tableros con filtrado basado en roles (ve todos para SUPER_ADMIN, filtrado por proceso/coordinación para otros)
     */
    public function indexAction()
    {
        $sesion = $this->getInfoSesion();
        $idEmpleado = $sesion['idEmpleado'];
        $idUsuario = $sesion['idUsuario'];
        $empleado = $this->DAO->getEmpleadoById($idEmpleado);
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($idUsuario);

        $filtro = '';

        if ($rolesUsuario[0]['rol'] === 'SUPER_ADMINISTRADOR' || $rolesUsuario[0]['rol'] === 'ADMINISTRADOR') {
            $filtro = '';
        } else {
            if ($empleado['idProceso'] != 0 && $empleado['idCoordinacion'] != 0) {

                $filtro = "indicadores.idProceso = {$empleado['idProceso']} 
                   AND indicadores.idCoordinacion = {$empleado['idCoordinacion']}";

            } else if ($empleado['idProceso'] != 0) {

                $filtro = "indicadores.idProceso = {$empleado['idProceso']}";
            }
        }

        return new ViewModel([
            'tableros' => $this->DAO->getTableros($filtro),
            'rolesUsuario' => $rolesUsuario[0]
        ]);
    }

    //------------------------------------------------------------------------------

    /**
     * Crea un nuevo tablero con validación de formulario, guarda en la base de datos
     */
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //--
        $procesos = $this->DAO->getProcesos();
        $listaProcesos = ['' => 'Seleccione un Proceso...'];
        foreach ($procesos as $proceso) {
            $listaProcesos[$proceso['idProceso']] = $proceso['Proceso'];
        }
        $coordinaciones = $this->DAO->getCoordinaciones();
        $listaCoordinaciones = ['' => 'Seleccione una Coordinación...'];
        foreach ($coordinaciones as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        }
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
        $formTablero = new TablerosForm('registrar', $listaProcesos, $listaCoordinaciones);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'formTablero' => $formTablero,
                'procesos' => $procesos,
                'coordinaciones' => $coordinaciones,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //--
        $tableroOBJ = new Tablero();
        //$formTablero->setInputFilter($formTablero->getInputFilter());
        $formTablero->setData($request->getPost());
        $infoEmpleado = $request->getPost()->toArray();

        if (!$formTablero->isValid()) {
            print_r($formTablero->getMessages());
            return ['formTablero' => $formTablero];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $tableroOBJ->exchangeArray($formTablero->getData());
        $tableroOBJ->setFecha_creacion(date("Y-m-d"));
        $tableroOBJ->setEstado('Pendiente');
        try {
            $this->DAO->registrar($tableroOBJ);
            $this->flashMessenger()->addSuccessMessage('EL TABLERO FUE REGISTRADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR USUARIO - AdministracionController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'app.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL TABLERO NO FUE REGISTRADO');
        }
        return $this->redirect()->toUrl('index');

    }
    //------------------------------------------------------------------------------

    /**
     * Modifica tableros existentes
     */
    public function editarAction()
    {
        $idHistoria = (int) $this->params()->fromQuery('idHistoria', $this->params()->fromPost('idHistoria', 0));

        if ($idHistoria === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de historia de usuario válido para edición.');
            return $this->redirect()->toRoute('dashboard/historias', ['action' => 'index']);
        }

        $infoHistoria = $this->DAO->getHistoriaUsuariobyId($idHistoria);

        if (empty($infoHistoria)) {
            $this->flashMessenger()->addErrorMessage('La historia de usuario a editar no se encuentra registrada.');
            return $this->redirect()->toRoute('dashboard/historias', ['action' => 'index']);
        }

        $proyectosRaw = $this->DAO->getProyectos();
        $listaProyectos = ['' => 'Seleccione un proyecto...'];
        foreach ($proyectosRaw as $proyecto) {
            $listaProyectos[$proyecto['idProyecto']] = $proyecto['nombre'];
        }

        $listaPrioridades = ['Critica' => 'Crítica', 'Alta' => 'Alta', 'Media' => 'Media', 'Baja' => 'Baja'];
        $listaEstados = ['Registrado' => 'Registrado', 'En proceso' => 'En Proceso', 'Terminado' => 'Terminado'];

        $formHistoriaUsuario = new HistoriaUsuarioForm('editar', $listaProyectos, $listaPrioridades, $listaEstados);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $formHistoriaUsuario->setData($infoHistoria);
            $view = new ViewModel([
                'formHistoriaUsuario' => $formHistoriaUsuario,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $idHistoriaFromPost = (int) $request->getPost('idHistoria');
        $idHistoria = ($idHistoriaFromPost !== 0) ? $idHistoriaFromPost : $idHistoria;

        $historiaUsuarioOBJ = new HistoriaUsuario();
        $formHistoriaUsuario->setInputFilter($historiaUsuarioOBJ->getInputFilter());
        $formHistoriaUsuario->setData($request->getPost());

        if (!$formHistoriaUsuario->isValid()) {
            error_log(print_r($formHistoriaUsuario->getMessages(), true));
            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'messages' => $formHistoriaUsuario->getMessages(),
                    'globalMessage' => 'LA INFORMACION DE EDICION DE LA HISTORIA DE USUARIO NO ES VALIDA.'
                ]);
            } else {
                $this->flashMessenger()->addErrorMessage('LA INFORMACION DE EDICION DE LA HISTORIA DE USUARIO NO ES VALIDA.');
                return $this->redirect()->toRoute('dashboard/historias', ['action' => 'index']);
            }
        }

        try {
            $historiaUsuarioOBJ->exchangeArray($formHistoriaUsuario->getData());
            $infoSesion = $this->getInfoSesion();

            $historiaUsuarioOBJ->setIdHistoria($idHistoria);

            $historiaUsuarioOBJ->setModificadopor($infoSesion['login']);
            $historiaUsuarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));

            $this->DAO->editar($historiaUsuarioOBJ);
            if ($request->isXmlHttpRequest()) {
                return new \Laminas\View\Model\JsonModel([
                    'success' => true,
                    'globalMessage' => 'EL INDICADOR FUE ACTUALIZADO EXITOSAMENTE.'
                ]);
            }
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR HISTORIA USUARIO - HistoriasController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            file_put_contents($this->rutaLog . 'dashboard.log', $msgLog, FILE_APPEND);
            if ($request->isXmlHttpRequest()) {
                $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>LA HISTORIA DE USUARIO NO FUE ACTUALIZADA.';
                if ($ex->getCode() == 23000) {
                    $errorMessage = 'Error: Ya existe una historia de usuario con ese código. Por favor, use otro.';
                }
                return new JsonModel([
                    'success' => false,
                    'globalMessage' => $errorMessage
                ]);
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>LA HISTORIA DE USUARIO NO FUE ACTUALIZADA.');
            }
        }
        return $this->redirect()->toRoute('dashboard/historias', ['action' => 'index']);
    }
    //------------------------------------------------------------------------------

    /**
     * Muestra detalles de los tableros específicos
     */
    public function detalleAction()
    {
        $idHistoria = (int) $this->params()->fromQuery('idHistoria', 0);

        if ($idHistoria === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de historia de usuario válido.');
            return $this->redirect()->toRoute('dashboard/historias');
        }

        $infoHistoria = $this->DAO->getHistoriaUsuariobyId($idHistoria);

        if (empty($infoHistoria)) {
            $this->flashMessenger()->addErrorMessage('La historia de usuario solicitada no se encuentra registrada.');
            return $this->redirect()->toRoute('dashboard/historias');
        }

        $proyectosRaw = $this->DAO->getProyectos();
        $listaProyectos = [];
        foreach ($proyectosRaw as $proyecto) {
            $listaProyectos[$proyecto['idProyecto']] = $proyecto['nombre'];
        }

        $listaPrioridades = ['Critica' => 'Crítica', 'Alta' => 'Alta', 'Media' => 'Media', 'Baja' => 'Baja'];


        $formHistoriaUsuario = new HistoriaUsuarioForm('detalle', $listaProyectos, $listaPrioridades);

        $formHistoriaUsuario->setData($infoHistoria);




        $view = new ViewModel([
            'formHistoriaUsuario' => $formHistoriaUsuario,

        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------

    /**
     * Elimina un registro de tableros de forma lógica (cambia estado a 'Eliminado') con confirmación, maneja tanto GET para mostrar confirmación como POST para procesar eliminación
     */
    public function eliminarAction()
    {
        $request = $this->getRequest();
        $idHistoriaFromPost = (int) $this->params()->fromPost('idHistoria', 0);
        $idHistoriaFromQuery = (int) $this->params()->fromQuery('idHistoria', 0);


        $idHistoria = $idHistoriaFromPost !== 0 ? $idHistoriaFromPost : $idHistoriaFromQuery;


        if ($request->isPost()) {
            if ($idHistoria === 0) {
                $this->flashMessenger()->addErrorMessage('Error: ID de historia no proporcionado en la confirmación para eliminar (POST).');
                return $this->redirect()->toRoute('dashboard/historias');
            }
            try {
                $historiaUsuarioOBJ = new HistoriaUsuario();
                $historiaUsuarioOBJ->setIdHistoria($idHistoria);
                $historiaUsuarioOBJ->setEstado('Eliminado');

                $infoSesion = $this->getInfoSesion();
                $historiaUsuarioOBJ->setModificadopor($infoSesion['login']);
                $historiaUsuarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));

                $this->DAO->eliminarLogico($historiaUsuarioOBJ);

                $this->flashMessenger()->addSuccessMessage('LA HISTORIA DE USUARIO FUE ELIMINADA EXITOSAMENTE.');
            } catch (Exception $ex) {
                error_log("Error al eliminar historia (POST): " . $ex->getMessage());
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>LA HISTORIA DE USUARIO NO PUDO SER ELIMINADA .');
            }
            return $this->redirect()->toRoute('dashboard/historias');
        }


        if ($idHistoria === 0) {
            $this->flashMessenger()->addErrorMessage('Error: ID de historia no proporcionado para la eliminación (GET).');
            $view = new ViewModel([
                'formHistoriaUsuario' => new HistoriaUsuarioForm('eliminar'),
                'idHistoriaPasada' => 0
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $infoHistoria = $this->DAO->getHistoriaUsuariobyId($idHistoria);

        if (empty($infoHistoria)) {
            $this->flashMessenger()->addErrorMessage('Error: La historia de usuario a eliminar no se encuentra registrada (GET).');
            $view = new ViewModel([
                'formHistoriaUsuario' => new HistoriaUsuarioForm('eliminar'),
                'idHistoriaPasada' => 0
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $proyectosRaw = $this->DAO->getProyectos();
        $listaProyectos = ['' => 'Seleccione un proyecto...'];
        foreach ($proyectosRaw as $proyecto) {
            $listaProyectos[$proyecto['idProyecto']] = $proyecto['nombre'];
        }
        $listaPrioridades = ['' => 'Seleccione...', 'Critica' => 'Crítica', 'Alta' => 'Alta', 'Media' => 'Media', 'Baja' => 'Baja'];
        $listaEstados = ['' => 'Seleccione...', 'Registrado' => 'Registrado', 'En proceso' => 'En Proceso', 'Terminado' => 'Terminado'];

        $formHistoriaUsuario = new HistoriaUsuarioForm('eliminar', $listaProyectos, $listaPrioridades, $listaEstados);

        $formHistoriaUsuario->setData($infoHistoria);

        $view = new ViewModel([
            'formHistoriaUsuario' => $formHistoriaUsuario,
            'idHistoriaPasada' => $idHistoria
        ]);
        $view->setTerminal(true);
        return $view;
    }


    //------------------------------------------------------------------------------
    /**
     * Asignar un tablero a un usuario específico, con validación de formulario y manejo de errores, muestra formulario para GET y procesa asignación para POST
     */
    public function AsignarAction()
    {
        $request = $this->getRequest();
        $infosesion = $this->getInfoSesion();
        $modificadopor = $infosesion['login'];
        //--
        $procesos = $this->DAO->getProcesos();
        $listaProcesos = ['' => 'Seleccione un Proceso...'];
        foreach ($procesos as $proceso) {
            $listaProcesos[$proceso['idProceso']] = $proceso['Proceso'];
        }
        $coordinaciones = $this->DAO->getCoordinaciones();
        $listaCoordinaciones = ['' => 'Seleccione una Coordinación...'];
        foreach ($coordinaciones as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        }
        if (!$request->isPost()) {
            $idTablero = (int) $this->params()->fromQuery('idTablero', $this->params()->fromPost('idTablero', 0));
            $infoTablero = $this->DAO->getTablero($idTablero);
            //-----------------------------------------------------
            if (empty($infoTablero)) {
                $this->flashMessenger()->addErrorMessage('No se encontró el tablero para asignar.');
                return $this->redirect()->toUrl('index');
            }

            $formTablero = new TablerosForm('asignar', $listaProcesos, $listaCoordinaciones);
            $formTablero->setData($infoTablero);
            $view = new ViewModel([
                'formTablero' => $formTablero,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $formTablero = new TablerosForm('asignar', $listaProcesos, $listaCoordinaciones);
        $tableroOBJ = new Tablero();
        $formTablero->setInputFilter($formTablero->getInputFilter());
        $formTablero->setData($request->getPost());
        if ($formTablero->isValid()) {
            print_r($formTablero->getMessages());
            return ['formTablero' => $formTablero];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL TABLERO  NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        $tableroOBJ->exchangeArray($formTablero->getData());
        $idUsuario = (int) $this->params()->fromPost('idUsuario', 0);
        $idTablero = $this->params()->fromPost('idTablero', 0);
        try {
            $this->DAO->asignar($idTablero, $idUsuario);
            $this->flashMessenger()->addSuccessMessage('El TABLERO A SIDO ASIGNADO EXITOSAMENTE');
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ASIGNAR USUARIO - AdministracionController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'josandro.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>NO FUE POSIBLE ASIGNAR EL USUARIO  AL TABLERO');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------
    /**
     * Muestra la lista de coordinciones asociadas a un proceso específico, utilizado para filtrado dinámico en el formulario de asignación, devuelve una vista parcial con las coordinaciones filtradas
     */
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
    /**
     * Muestra la lista de usuarios para asignar a tableros, utilizado para cargar opciones en el formulario de asignación, devuelve una vista parcial con los usuarios disponibles
     */
    public function getusuariosAction()
    {
        $usuarios = $this->DAO->getUsuarios();
        $view = new ViewModel([
            'usuarios' => $usuarios
        ]);
        $view->setTerminal(true);
        return $view;
    }


}
