<?php

declare(strict_types=1);

namespace Indicadores\Controller;

use Exception;
use Indicadores\Formularios\ResultadosForm;
use Indicadores\Modelo\DAO\ResultadosDAO;
use Indicadores\Modelo\Entidades\Resultados;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;


class ResultadosController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = '/var/log/sigec/';

    //------------------------------------------------------------------------------

    /**
     * Inicializa el DAO de resultados
     */
    public function __construct(ResultadosDAO $dao)
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
     * Muestra resultados para un indicador específico
     */
    public function indexAction()
    {
        $idIndicador = (int) $this->params()->fromQuery('id_indicador', 0);

        if ($idIndicador === 0) {
            $this->flashMessenger()->addErrorMessage('Debe seleccionar un Indicador para ver sus Resultados.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }

        $sesion = $this->getInfoSesion();
        $idEmpleado = $sesion['idEmpleado'];
        $idUsuario = $sesion['idUsuario'];
        $empleado = $this->DAO->getEmpleadoById($idEmpleado);
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($idUsuario);

        $infoResultado = $this->DAO->getIndicador($idIndicador);
        if (!$infoResultado) {
            $this->flashMessenger()->addErrorMessage('el indicador solicitado no existe.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }

        $resultados = $this->DAO->getResultados($infoResultado['id_indicador']);

        return new ViewModel([
            'infoIndicador' => $infoResultado,
            'resultados' => $resultados,
            'id_indicador' => $infoResultado['id_indicador'],
            'empleado' => $empleado
        ]);
    }
    //------------------------------------------------------------------------------

    public function registrarAction()
    {
        $mes = date('m');

        $meses = [
            'enero' => 'enero',
            'febrero' => 'febrero',
            'marzo' => 'Marzo',
            'abril' => 'Abril',
            'mayo' => 'Mayo',
            'junio' => 'Junio',
            'julio' => 'Julio',
            'agosto' => 'Agosto',
            'septiembre' => 'Septiembre',
            'octubre' => 'Octubre',
            'noviembre' => 'Noviembre',
            'diciembre' => 'Diciembre'
        ];
        $meses = array_slice($meses, $mes - 1, null, true);


        $idIndicador = (int) $this->params()->fromQuery('id_indicador', $this->params()->fromPost('id_indicador', 0));
        $formResultados = new ResultadosForm('registrar', $meses);
        $formResultados->get('id_indicador')->setValue($idIndicador);


        $request = $this->getRequest();
        if ($request->isPost()) {
            $resultados = new Resultados();
            $formResultados->setInputFilter($resultados->getInputFilter());
            $formResultados->setData($request->getPost());

            if ($formResultados->isValid()) {
                $resultados->exchangeArray($formResultados->getData());
                $infoSesion = $this->getInfoSesion();
                $resultados->setRegistradopor($infoSesion['login']);
                $resultados->setModificadopor($infoSesion['login']);

                try {
                    $this->DAO->registrar($resultados);
                    return new JsonModel(['success' => true, 'message' => 'Resultado registrado correctamente.']);
                } catch (\Exception $e) {
                    return new JsonModel(['success' => false, 'globalMessage' => 'Error: ' . $e->getMessage()]);
                }
            } else {
                return new JsonModel(['success' => false, 'messages' => $formResultados->getMessages()]);
            }
        }

        $view = new ViewModel(['formResultados' => $formResultados]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------

    /**
     * Actualiza la información del resultado
     */
    public function editarAction()
    {

        $idIndicador = (int) $this->params()->fromQuery('id_indicador', $this->params()->fromPost('id_indicador', 0));
        if ($idIndicador === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de indicador válido para edición.');
            return $this->redirect()->toRoute('indicadores/indicadores', ['action' => 'index']);
        }

        $infoIndicador = $this->DAO->getIndicadorById($idIndicador);

        if (empty($infoIndicador)) {
            $this->flashMessenger()->addErrorMessage('El indicador a editar no se encuentra registrado.');
            return $this->redirect()->toRoute('indicadores/indicadores', ['action' => 'index']);
        }

        $procesosRaw = $this->DAO->getProcesos();
        $coordinacionesRaw = $this->DAO->getCoordinaciones();
        $listaProcesos = [];
        foreach ($procesosRaw as $proceso) {
            $listaProcesos[$proceso['idProceso']] = $proceso['Proceso'];
        }
        $listaCoordinaciones = [];
        foreach ($coordinacionesRaw as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        }
        $formIndicadores = new IndicadoresForm('editar', $listaProcesos, $listaCoordinaciones);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $formIndicadores->setData($infoIndicador);
            $view = new ViewModel([
                'formIndicadores' => $formIndicadores,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $idIndicadorFromPost = (int) $request->getPost('id_indicador');
        $idIndicador = ($idIndicadorFromPost !== 0) ? $idIndicadorFromPost : $idIndicador;

        $indicadorOBJ = new Indicador();
        $formIndicadores->setInputFilter($indicadorOBJ->getInputFilter());
        $formIndicadores->setData($request->getPost());

        if (!$formIndicadores->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'messages' => $formIndicadores->getMessages(),
                    'globalMessage' => 'LA INFORMACION DE EDICION DEL INDICADOR NO ES VALIDA.'
                ]);
            } else {
                $this->flashMessenger()->addErrorMessage('LA INFORMACION DE EDICION DEL INDICADOR NO ES VALIDA.');
                return $this->redirect()->toRoute('indicadores/indicadores', ['action' => 'index']);
            }
        }
        try {
            $indicadorOBJ->exchangeArray($formIndicadores->getData());
            $infoSesion = $this->getInfoSesion();
            $indicadorOBJ->setIdIndicador($idIndicador);
            $indicadorOBJ->setModificadopor($infoSesion['login']);
            $indicadorOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($indicadorOBJ);

            if ($request->isXmlHttpRequest()) {
                $this->flashMessenger()->addSuccessMessage('EL INDICADOR FUE ACTUALIZADO EXITOSAMENTE.');
                return new JsonModel([
                    'success' => true,
                    'message' => 'EL INDICADOR FUE ACTUALIZADO EXITOSAMENTE.'
                ]);
            }
            $this->flashMessenger()->addSuccessMessage('EL INDICADOR FUE ACTUALIZADO EXITOSAMENTE.');
            return $this->redirect()->toRoute('indicadores/indicadores', ['action' => 'index']);
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR HISTORIA USUARIO - HistoriasController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
            if ($request->isXmlHttpRequest()) {
                $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>EL INDICADOR NO FUE ACTUALIZADO.';
                return new JsonModel([
                    'success' => false,
                    'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                ]);
            }
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL INDICADOR NO FUE ACTUALIZADO.');
            return $this->redirect()->toRoute('indicadores/indicadores', ['action' => 'index']);
        }
    }


    //------------------------------------------------------------------------------

    /**
     * Muestra detalles del resultado
     */
    public function detalleAction()
    {
        $idIndicador = (int) $this->params()->fromQuery('idIndicador', 0);
        if ($idIndicador === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de indicadores válido.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }
        $infoIndicador = $this->DAO->getIndicadorById($idIndicador);

        if (empty($infoIndicador)) {
            $this->flashMessenger()->addErrorMessage('El indicador solicitado no se encuentra registrado.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }
        $resultadosIndcador = $this->DAO->getResultadosByIndicador($idIndicador);

        $procesosRaw = $this->DAO->getProcesos();
        $coordinacionesRaw = $this->DAO->getCoordinaciones();
        $listaProcesos = [];
        foreach ($procesosRaw as $proceso) {
            $listaProcesos[$proceso['idProceso']] = $proceso['Proceso'];
        }

        $listaCoordinaciones = [];
        foreach ($coordinacionesRaw as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        }

        /* $listaPrioridades = ['Critica' => 'Crítica', 'Alta' => 'Alta', 'Media' => 'Media', 'Baja' => 'Baja'];
         */

        $formIndicadores = new IndicadoresForm('detalle', $listaProcesos, $listaCoordinaciones);

        $formIndicadores->setData($infoIndicador);

        $view = new ViewModel([
            'formIndicadores' => $formIndicadores,
            'resultadosIndcador' => $resultadosIndcador

        ]);
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------

    /**
     * Elimina el registro del resultado
     */
    public function eliminarAction()
    {
        $request = $this->getRequest();
        $idIndicadorFromPost = (int) $this->params()->fromPost('id_indicador', 0);
        $idIndicadorFromQuery = (int) $this->params()->fromQuery('id_indicador', 0);


        $idIndicador = $idIndicadorFromPost !== 0 ? $idIndicadorFromPost : $idIndicadorFromQuery;

        if ($request->isPost()) {
            if ($idIndicador === 0) {
                $this->flashMessenger()->addErrorMessage('Error: ID de indicador no proporcionado en la confirmación para eliminar (POST).');
                return $this->redirect()->toRoute('indicadores/indicadores');
            }
            try {
                $indicadorOBJ = new Indicador();
                $indicadorOBJ->setIdIndicador($idIndicador);
                $indicadorOBJ->setEstado('Eliminado');

                $infoSesion = $this->getInfoSesion();
                $indicadorOBJ->setModificadopor($infoSesion['login']);
                $indicadorOBJ->setFechahoramod(date('Y-m-d H:i:s'));

                $this->DAO->eliminarLogico($indicadorOBJ);

                $this->flashMessenger()->addSuccessMessage('EL INDICADOR FUE ELIMINADO EXITOSAMENTE.');
            } catch (Exception $ex) {
                error_log("Error al eliminar indicador (POST): " . $ex->getMessage());
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL INDICADOR NO PUDO SER ELIMINADO .');
            }
            return $this->redirect()->toRoute('indicadores/indicadores');
        }


        if ($idIndicador === 0) {
            $this->flashMessenger()->addErrorMessage('Error: ID de indicador no proporcionado para la eliminación (GET).');
            $view = new ViewModel([
                'formIndicador' => new IndicadoresForm('eliminar'),
                'idIndicadorPasado' => 0
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $infoIndicador = $this->DAO->getIndicadorById($idIndicador);

        if (empty($infoIndicador)) {
            $this->flashMessenger()->addErrorMessage('Error: El indicador a eliminar no se encuentra registrado (GET).');
            $view = new ViewModel([
                'formIndicadores' => new IndicadoresForm('eliminar'),
                'idIndicadorPasado' => 0
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $procesosRaw = $this->DAO->getProcesos();
        $coordinacionesRaw = $this->DAO->getCoordinaciones();
        $listaProcesos = [];
        foreach ($procesosRaw as $proceso) {
            $listaProcesos[$proceso['idProceso']] = $proceso['Proceso'];
        }

        $listaCoordinaciones = [];
        foreach ($coordinacionesRaw as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        }
        $formIndicadores = new IndicadoresForm('eliminar', $listaProcesos, $listaCoordinaciones);

        $formIndicadores->setData($infoIndicador);

        $view = new ViewModel([
            'formIndicadores' => $formIndicadores,
            'idIndicadorPasado' => $idIndicador
        ]);
        $view->setTerminal(true);
        return $view;
    }


    //------------------------------------------------------------------------------

}
