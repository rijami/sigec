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
                    if ($request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('EL RESULTADO FUE REGISTRADO EXITOSAMENTE.');
                        return new JsonModel([
                            'success' => true,
                            'message' => 'REGISTRO EXITOSO'
                        ]);
                    }
                    // return new JsonModel(['success' => true, 'message' => 'Resultado registrado correctamente.']);
                } catch (\Exception $e) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ERROR REGISTRAR - " . $ex->getMessage() . "\n";
                    file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);

                    if ($request->isXmlHttpRequest()) {
                        return new JsonModel([
                            'success' => false,
                            'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                        ]);
                    }
                    //return new JsonModel(['success' => false, 'globalMessage' => 'Error: ' . $e->getMessage()]);
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

        $idResultado = (int) $this->params()->fromQuery('id_result', $this->params()->fromPost('id_result', 0));
        if ($idResultado === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de resultado válido para edición.');
            return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
        }

        $infoResultado = $this->DAO->getResultadoById($idResultado);

        if (empty($infoResultado)) {
            $this->flashMessenger()->addErrorMessage('El resultado a editar no se encuentra registrado.');
            return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
        }

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
        $formResultados = new ResultadosForm('editar', $meses);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $formResultados->setData($infoResultado);
            $view = new ViewModel([
                'formResultados' => $formResultados,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $idResultadoFromPost = (int) $request->getPost('id_result');
        $idResultado = ($idResultadoFromPost !== 0) ? $idResultadoFromPost : $idResultado;

        $resultadoOBJ = new Resultados();
        $formResultados->setInputFilter($resultadoOBJ->getInputFilter());
        $formResultados->setData($request->getPost());

        if (!$formResultados->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'messages' => $formResultados->getMessages(),
                    'globalMessage' => 'LA INFORMACION DE EDICION DEL RESULTADO NO ES VALIDA.'
                ]);
            } else {
                $this->flashMessenger()->addErrorMessage('LA INFORMACION DE EDICION DEL RESULTADO NO ES VALIDA.');
                return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
            }
        }
        $resultadoOBJ->exchangeArray($formResultados->getData());
        $infoSesion = $this->getInfoSesion();
        if (strpos($resultadoOBJ->getResultado(), '%') !== false) {
            // Ya tiene %
            $resultadoFinal = $resultadoOBJ->getResultado();
        } else {
            // No tiene %
            $resultadoFinal = $resultadoOBJ->getResultado() . '%';
        }
        $resultadoOBJ->setResultado(str_replace('.0', '', $resultadoFinal));
        $resultadoOBJ->setModificadopor($infoSesion['login']);
        $resultadoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
        try {

            $this->DAO->editar($resultadoOBJ);

            if ($request->isXmlHttpRequest()) {
                $this->flashMessenger()->addSuccessMessage('EL RESULTADO FUE ACTUALIZADO EXITOSAMENTE.');
                return new JsonModel([
                    'success' => true,
                    'message' => 'EL RESULTADO FUE ACTUALIZADO EXITOSAMENTE.'
                ]);
            }
            $this->flashMessenger()->addSuccessMessage('EL RESULTADO FUE ACTUALIZADO EXITOSAMENTE.');
            return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR RESULTADO - ResultadosController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
            if ($request->isXmlHttpRequest()) {
                $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>EL RESULTADO NO FUE ACTUALIZADO.';
                return new JsonModel([
                    'success' => false,
                    'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                ]);
            }
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL RESULTADO NO FUE ACTUALIZADO.');
            return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
        }
    }


    //------------------------------------------------------------------------------

    /**
     * Muestra detalles del resultado
     */
    public function detalleAction()
    {
        $idResultado = (int) $this->params()->fromQuery('id_result', 0);
        if ($idResultado === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de resultados válido.');
            return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
        }
        $infoResultado = $this->DAO->getResultadoById($idResultado);

        if (empty($infoResultado)) {
            $this->flashMessenger()->addErrorMessage('El resultado solicitado no se encuentra registrado.');
            return $this->redirect()->toRoute('resultados/resultados', ['action' => 'index']);
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


        $formResultados = new ResultadosForm('detalle', $meses);

        $formResultados->setData($infoResultado);

        $view = new ViewModel([
            'formResultados' => $formResultados,

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

}
