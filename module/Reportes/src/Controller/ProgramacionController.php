<?php

declare(strict_types=1);

namespace Reportes\Controller;

use Exception;
use Reportes\Formularios\ProgramacionForm;
use Reportes\Modelo\DAO\ProgramacionDAO;
use Reportes\Modelo\Entidades\Programacion;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;


class ProgramacionController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = '/var/log/sigec/';

    //------------------------------------------------------------------------------

    /**
     * Inicializa el DAO de resultados
     */
    public function __construct(ProgramacionDAO $dao)
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
        $idReporte = (int) $this->params()->fromQuery('idReporte', 0);

        if ($idReporte === 0) {
            $this->flashMessenger()->addErrorMessage('Debe seleccionar un Reporte para ver su Programación.');
            return $this->redirect()->toRoute('reportes/reportes');
        }

        $sesion = $this->getInfoSesion();
        $idEmpleado = $sesion['idEmpleado'];
        $idUsuario = $sesion['idUsuario'];
        $empleado = $this->DAO->getEmpleadoById($idEmpleado);
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($idUsuario);

        $infoReporte = $this->DAO->getReporte($idReporte);
        if (!$infoReporte) {
            $this->flashMessenger()->addErrorMessage('el reporte solicitado no existe.');
            return $this->redirect()->toRoute('reportes/reportes');
        }
        $direcciones = $this->DAO->getDireccionesByReporte($infoReporte['idReporte']);
        $coordinaciones = $this->DAO->getCoordinacionesByReporte($infoReporte['idReporte']);
        $responsables = $this->DAO->getResponsablesByReporte($infoReporte['idReporte']);
        $programaciones = $this->DAO->getProgramacion($infoReporte['idReporte']);
        return new ViewModel([
            'infoReporte' => $infoReporte,
            'programaciones' => $programaciones,
            'idReporte' => $infoReporte['idReporte'],
            'empleado' => $empleado,
            'direcciones' => $direcciones,
            'coordinaciones' => $coordinaciones,
            'responsables' => $responsables
        ]);
    }
    //------------------------------------------------------------------------------

    public function registrarAction()
    {

        $mes = date('m');

        $meses = [
            '1' => 'Enero',
            '2' => 'Febrero',
            '3' => 'Marzo',
            '4' => 'Abril',
            '5' => 'Mayo',
            '6' => 'Junio',
            '7' => 'Julio',
            '8' => 'Agosto',
            '9' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];
        //$meses = array_slice($meses, $mes - 1, null, true);
        $idReporte = (int) $this->params()->fromQuery('idReporte', $this->params()->fromPost('idReporte', 0));
        $reporte = $this->DAO->getReporte($idReporte);
        $formProgramacion = new ProgramacionForm('registrar', $meses);
        $formProgramacion->get('idReporte')->setValue($idReporte);


        $request = $this->getRequest();
        if ($request->isPost()) {
            $programacionOBJ = new Programacion();
            $formProgramacion->setInputFilter($programacionOBJ->getInputFilter());
            $formProgramacion->setData($request->getPost());

            if ($formProgramacion->isValid()) {
                $programacionOBJ->exchangeArray($formProgramacion->getData());
                $infoSesion = $this->getInfoSesion();
                $programacionOBJ->setIdReporte($idReporte);
                $programacionOBJ->setEstado("Sin Informacion");
                $programacionOBJ->setRecordatorio(0);
                $programacionOBJ->setResponReporta($reporte['respon_reporta']);

                try {
                    $this->DAO->registrar($programacionOBJ);

                    if ($request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN DEL REPORTE FUE REGISTRADA EXITOSAMENTE.');
                        return new JsonModel([
                            'success' => true,
                            'message' => 'LA PROGRAMACIÓN DEL REPORTE FUE REGISTRADA EXITOSAMENTE.'
                        ]);
                    }
                    $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN DEL REPORTE FUE REGISTRADA EXITOSAMENTE.');
                    return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
                } catch (Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR PROGRAMACIÓN - ProgramacionController->editar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
                    if ($request->isXmlHttpRequest()) {
                        $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>LA PROGRAMACIÓN NO FUE REGISTRADA.';
                        return new JsonModel([
                            'success' => false,
                            'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                        ]);
                    }
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>LA PROGRAMACIÓN NO FUE REGISTRADA.');
                return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
            }

        }

        $view = new ViewModel(['formProgramacion' => $formProgramacion]);
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
        $idResultFromPost = (int) $this->params()->fromPost('id_result', 0);
        $idResultFromQuery = (int) $this->params()->fromQuery('id_result', 0);


        $idResultado = $idResultFromPost !== 0 ? $idResultFromPost : $idResultFromQuery;

        if ($request->isPost()) {
            if ($idResultado === 0) {
                $this->flashMessenger()->addErrorMessage('Error: ID de resultado no proporcionado en la confirmación para eliminar (POST).');
                return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
            }
            try {
                $resultadoOBJ = new Resultados();
                $resultadoOBJ->setId_result($idResultado);
                $this->DAO->eliminar($resultadoOBJ);

                if ($request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('EL RESULTADO FUE ELIMINADO EXITOSAMENTE.');
                    return new JsonModel([
                        'success' => true,
                        'message' => 'EL RESULTADO FUE ELIMINADO EXITOSAMENTE.'
                    ]);
                }
                $this->flashMessenger()->addSuccessMessage('EL RESULTADO FUE ELIMINADO EXITOSAMENTE.');
                return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
            } catch (Exception $ex) {
                $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR RESULTADO - ResultadosController->eliminar \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
                file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
                if ($request->isXmlHttpRequest()) {
                    $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>EL RESULTADO NO FUE ELIMINADO.';
                    return new JsonModel([
                        'success' => false,
                        'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                    ]);
                }
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RESULTADO NO FUE ELIMINADO.');
                return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
            }
        }


        if ($idResultado === 0) {
            $this->flashMessenger()->addErrorMessage('Error: ID de resultado no proporcionado para la eliminación (GET).');
            $view = new ViewModel([
                'formResultados' => new ResultadosForm('eliminar'),
                'idResultadoPasado' => 0
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $infoResultado = $this->DAO->getResultadoById($idResultado);

        if (empty($infoResultado)) {
            $this->flashMessenger()->addErrorMessage('Error: El resultado a eliminar no se encuentra registrado (GET).');
            $view = new ViewModel([
                'formResultados' => new ResultadosForm('eliminar'),
                'idResultadoPasado' => 0
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $mes = date('m');

        $meses = [
            'enero' => 'Enero',
            'febrero' => 'Febrero',
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
        //$meses = array_slice($meses, $mes - 1, null, true);

        $formResultados = new ResultadosForm('eliminar', $meses);

        $formResultados->setData($infoResultado);

        $view = new ViewModel([
            'formResultados' => $formResultados,
            'idResultadoPasado' => $idResultado
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------

    /**
     * Marcar como reportado 
     */
    public function reportarAction()
    {
        $idProgramacion = (int) $this->params()->fromQuery('idProgramacion', $this->params()->fromPost('idProgramacion', 0));
        $mes = date('m');

        $meses = [
            '1' => 'Enero',
            '2' => 'Febrero',
            '3' => 'Marzo',
            '4' => 'Abril',
            '5' => 'Mayo',
            '6' => 'Junio',
            '7' => 'Julio',
            '8' => 'Agosto',
            '9' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];
        //$meses = array_slice($meses, $mes - 1, null, true);

        $infoProgramacion = $this->DAO->getProgramacionById($idProgramacion);
        $formProgramacion = new ProgramacionForm('reportar', $meses);
        $formProgramacion->setData($infoProgramacion);
        $formProgramacion->get('idProgramacion')->setValue($idProgramacion);


        $request = $this->getRequest();
        if ($request->isPost()) {
            $programacionOBJ = new Programacion();
            $formProgramacion->setInputFilter($programacionOBJ->getInputFilter());
            $formProgramacion->setData($request->getPost());

            if ($formProgramacion->isValid()) {
                $programacionOBJ->exchangeArray($formProgramacion->getData());
                $infoSesion = $this->getInfoSesion();
                $programacionOBJ->setEstado("Reportado");
                $programacionOBJ->setFechaEfectiva(date('Y-m-d H:i:s'));

                try {
                    $this->DAO->reportar($programacionOBJ);
                    if ($request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('EL REPORTE YA FUE MARCADO COMO REPORTADO EXITOSAMENTE.');
                        return new JsonModel([
                            'success' => true,
                            'message' => 'REGISTRO EXITOSO'
                        ]);
                    }
                    return new JsonModel(['success' => true, 'message' => 'Resultado registrado correctamente.']);
                } catch (\Exception $e) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ERROR REGISTRAR - " . $ex->getMessage() . "\n";
                    file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);

                    if ($request->isXmlHttpRequest()) {
                        return new JsonModel([
                            'success' => false,
                            'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                        ]);
                    }
                    return new JsonModel(['success' => false, 'globalMessage' => 'Error: ' . $e->getMessage()]);
                }
            } else {
                return new JsonModel(['success' => false, 'messages' => $formProgramacion->getMessages()]);
            }
        }

        $view = new ViewModel(['formProgramacion' => $formProgramacion]);
        $view->setTerminal(true);
        return $view;
    }


    //------------------------------------------------------------------------------
    public function informacionAction()
    {
        $idProgramacion = (int) $this->params()->fromQuery('idProgramacion', $this->params()->fromPost('idProgramacion', 0));
        $mes = date('m');

        $meses = [
            '1' => 'Enero',
            '2' => 'Febrero',
            '3' => 'Marzo',
            '4' => 'Abril',
            '5' => 'Mayo',
            '6' => 'Junio',
            '7' => 'Julio',
            '8' => 'Agosto',
            '9' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];
        //$meses = array_slice($meses, $mes - 1, null, true);

        $infoProgramacion = $this->DAO->getProgramacionById($idProgramacion);
        $formProgramacion = new ProgramacionForm('informacion', $meses);
        $formProgramacion->setData($infoProgramacion);
        $formProgramacion->get('idProgramacion')->setValue($idProgramacion);


        $request = $this->getRequest();
        if ($request->isPost()) {
            $programacionOBJ = new Programacion();
            $formProgramacion->setInputFilter($programacionOBJ->getInputFilter());
            $formProgramacion->setData($request->getPost());

            if ($formProgramacion->isValid()) {
                $programacionOBJ->exchangeArray($formProgramacion->getData());
                try {
                    $this->DAO->informacion($programacionOBJ);
                    if ($request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('LA INFORMACION FUE RECIBIDA EXITOSAMENTE.');
                        return new JsonModel([
                            'success' => true,
                            'message' => 'REGISTRO EXITOSO'
                        ]);
                    }
                    return new JsonModel(['success' => true, 'message' => 'Resultado registrado correctamente.']);
                } catch (\Exception $e) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ERROR REGISTRAR - " . $ex->getMessage() . "\n";
                    file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);

                    if ($request->isXmlHttpRequest()) {
                        return new JsonModel([
                            'success' => false,
                            'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                        ]);
                    }
                    return new JsonModel(['success' => false, 'globalMessage' => 'Error: ' . $e->getMessage()]);
                }
            } else {
                return new JsonModel(['success' => false, 'messages' => $formProgramacion->getMessages()]);
            }
        }

        $view = new ViewModel(['formProgramacion' => $formProgramacion]);
        $view->setTerminal(true);
        return $view;
    }


}
