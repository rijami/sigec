<?php

declare(strict_types=1);

namespace Indicadores\Controller;

use Exception;
use Indicadores\Formularios\IndicadoresForm;
use Indicadores\Modelo\DAO\IndicadoresDAO;
use Indicadores\Modelo\Entidades\Indicador;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class IndicadoresController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = '/var/log/sigec/';

    //------------------------------------------------------------------------------

    /**
     * Inicializa el DAO de indicadores
     */
    public function __construct(IndicadoresDAO $dao)
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
     * Lista indicadores con filtrado basado en roles y actualizaciones de estado
     */
    public function indexAction()
    {
        $this->DAO->actualizarEstados();
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
            'indicadores' => $this->DAO->getIndicadores($filtro),
            'rolesUsuario' => $rolesUsuario[0]
        ]);
    }

    //------------------------------------------------------------------------------

    /**
     * Crea un nuevo indicador con selección de proceso/coordinación
     */
    public function registrarAction()
    {
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


        $formIndicadores = new IndicadoresForm('registrar', $listaProcesos, $listaCoordinaciones);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $view = new ViewModel(['formIndicadores' => $formIndicadores]);
            $view->setTerminal(true);
            return $view;
        }

        $indicadorOBJ = new Indicador();
        $formIndicadores->setInputFilter($indicadorOBJ->getInputFilter());
        $formIndicadores->setData($request->getPost());
        if (!$formIndicadores->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'messages' => $formIndicadores->getMessages(),
                    'globalMessage' => 'LA INFORMACIÓN DE REGISTRO NO ES VÁLIDA.'
                ]);
            }
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL INDICADOR NO ES VALIDA.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }

        try {
            $indicadorOBJ->exchangeArray($formIndicadores->getData());
            $infoSesion = $this->getInfoSesion();
            $indicadorOBJ->setEstado('Registrado');
            $indicadorOBJ->setRegistradopor($infoSesion['login']);
            $indicadorOBJ->setModificadopor($infoSesion['login']);
            $indicadorOBJ->setFechaRegistro(date('Y-m-d H:i:s'));
            $indicadorOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $indicadorOBJ->setMeta($indicadorOBJ->getMeta() . '%');

            $this->DAO->registrar($indicadorOBJ);

            if ($request->isXmlHttpRequest()) {
                $this->flashMessenger()->addSuccessMessage('EL INDICADOR FUE REGISTRADO EXITOSAMENTE.');
                return new JsonModel([
                    'success' => true,
                    'message' => 'REGISTRO EXITOSO'
                ]);
            }

            $this->flashMessenger()->addSuccessMessage('EL INDICADOR FUE REGISTRADO EXITOSAMENTE.');
            return $this->redirect()->toRoute('indicadores/indicadores');

        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ERROR REGISTRAR - " . $ex->getMessage() . "\n";
            file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);

            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                ]);
            }
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE EN EL SERVIDOR.');
            return $this->redirect()->toRoute('indicadores/indicadores');
        }
    }
    //------------------------------------------------------------------------------

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
     * Muestra detalles del indicador con resultados asociados
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
     * Realiza eliminación lógica del indicador
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
    public function ActivarAction()
    {
        $request = $this->getRequest();
        $fechaLimiteActivacion = $request->getPost('fechalimactivacion', '');
        if ($request->isPost()) {
            try {
                $indicadorOBJ = new Indicador();
                $infoSesion = $this->getInfoSesion();
                $indicadorOBJ->setEstado('Activado');
                $indicadorOBJ->setIdCoordinacion($request->getPost('idCoordinacion', 0));
                $indicadorOBJ->setModificadopor($infoSesion['login']);
                $indicadorOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                $fecha = date('Y-m-d H:i:s', strtotime($fechaLimiteActivacion));
                /*  print_r($fecha);
                 die(); */
                $this->DAO->activar($indicadorOBJ, $fecha);
                $this->flashMessenger()->addSuccessMessage('EL INDICADOR FUE ACTIVADO EXITOSAMENTE.');
            } catch (Exception $ex) {
                error_log("Error al activar indicador (POST): " . $ex->getMessage());
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL INDICADOR NO PUDO SER ACTIVADO .');
            }
            return $this->redirect()->toRoute('indicadores/indicadores');
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
        $formIndicadores = new IndicadoresForm('activar', $listaProcesos, $listaCoordinaciones);
        //print_r($formIndicadores);
        /* $formIndicadores->setData($infoIndicador); */

        $view = new ViewModel([
            'formIndicadores' => $formIndicadores,
        ]);
        $view->setTerminal(true);
        return $view;
    }


    //------------------------------------------------------------------------------
    public function exportarAction()
    {
        $request = $this->getRequest();
        //$fechaLimiteActivacion = $request->getPost('fechalimactivacion', '');
        if ($request->isPost()) {
            $this->layout()->setTerminal(true);
            try {
                $datos = $request->getPost();
                $filtros = [];

                if (!empty($datos['procesoBusq']) && $datos['procesoBusq'] != 0) {
                    $filtros[] = "indicadores.idProceso = " . $datos['procesoBusq'];
                }

                if (!empty($datos['CoordinacionBusq']) && $datos['CoordinacionBusq'] != 0) {
                    $filtros[] = "indicadores.idCoordinacion = " . $datos['CoordinacionBusq'];
                }

                if (!empty($datos['periodicidadBusq'])) {
                    $filtros[] = "periodicidad = '" . $datos['periodicidadBusq'] . "'";
                }

                if (!empty($datos['tipoindicadorBusq'])) {
                    $filtros[] = "TIPO_INDICADOR = '" . $datos['tipoindicadorBusq'] . "'";
                }

                if (!empty($datos['sentidoBusq'])) {
                    $filtros[] = "SENTIDO = '" . $datos['sentidoBusq'] . "'";
                }

                if (!empty($datos['fechainiBusq']) && !empty($datos['fechafinBusq'])) {
                    $filtros[] = "indicadores.fechaRegistro BETWEEN '" . $datos['fechainiBusq'] . "' AND '" . $datos['fechafinBusq'] . "'";
                }

                $filtro = "";

                if (!empty($filtros)) {
                    $filtro = "" . implode(" AND ", $filtros);
                }
                $resultado = $this->DAO->getIndicadoresExport($filtro);
                /* CREAR EXCEL */
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                /* TITULO */
                $sheet->setCellValue('A1', 'REPORTE DE INDICADORES');
                $sheet->mergeCells('A1:Q1');

                $sheet->setCellValue('A2', 'Fecha generación: ' . date('Y-m-d'));
                $sheet->setCellValue('A3', 'Total registros: ' . count($resultado));

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                /* ENCABEZADOS */
                $headers = [
                    'A5' => 'ID',
                    'B5' => 'PROCESO',
                    'C5' => 'COORDINACION',
                    'D5' => 'CODIGO',
                    'E5' => 'INDICADOR',
                    'F5' => 'OBJETIVO',
                    'G5' => 'PERIODICIDAD',
                    'H5' => 'FUENTE INFORMACION',
                    'I5' => 'META',
                    'J5' => 'TIPO INDICADOR',
                    'K5' => 'SENTIDO',
                    'L5' => 'FECHA',
                    'M5' => 'MES',
                    'N5' => 'NUMERADOR',
                    'O5' => 'DENOMINADOR',
                    'P5' => 'RESULTADO',
                    'Q5' => 'ANALISIS'
                ];

                foreach ($headers as $cell => $text) {
                    $sheet->setCellValue($cell, $text);
                }

                /* ESTILO ENCABEZADOS */
                $sheet->getStyle('A5:Q5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0E961E']
                    ]
                ]);

                /* CONGELAR ENCABEZADO */
                $sheet->freezePane('A6');

                /* FILTROS */
                $sheet->setAutoFilter('A5:Q5');

                /* DATOS */
                $fila = 6;

                foreach ($resultado as $row) {

                    $sheet->setCellValue('A' . $fila, $row['id_indicador']);
                    $sheet->setCellValue('B' . $fila, $row['Proceso']);
                    $sheet->setCellValue('C' . $fila, $row['Coordinacion']);
                    $sheet->setCellValue('D' . $fila, $row['codigo']);
                    $sheet->setCellValue('E' . $fila, $row['nombre_indicador']);
                    $sheet->setCellValue('F' . $fila, $row['objetivo']);
                    $sheet->setCellValue('G' . $fila, $row['periodicidad']);
                    $sheet->setCellValue('H' . $fila, $row['fuente_informacion']);
                    $sheet->setCellValue('I' . $fila, $row['meta']);
                    $sheet->setCellValue('J' . $fila, $row['TIPO_INDICADOR']);
                    $sheet->setCellValue('K' . $fila, $row['SENTIDO']);
                    $sheet->setCellValue('L' . $fila, $row['FechaRegistroIndicador']);
                    $sheet->setCellValue('M' . $fila, $row['mes']);
                    $sheet->setCellValue('N' . $fila, $row['num']);
                    $sheet->setCellValue('O' . $fila, $row['dem']);
                    $sheet->setCellValue('P' . $fila, $row['resultado']);
                    $sheet->setCellValue('Q' . $fila, $row['analisis']);

                    $fila++;
                }

                /* ANCHO COLUMNAS */
                foreach (range('A', 'Q') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(18);
                }

                /* DESCARGAR */
                $filename = "Indicadores_" . date('Ymd') . ".xlsx";

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                exit;
            } catch (Exception $ex) {
                error_log("Error al activar indicador (POST): " . $ex->getMessage());
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL INDICADOR NO PUDO SER ACTIVADO .');
            }
            return $this->redirect()->toRoute('indicadores/indicadores');
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
        $formIndicadores = new IndicadoresForm('exportar', $listaProcesos, $listaCoordinaciones);
        //print_r($formIndicadores);
        /* $formIndicadores->setData($infoIndicador); */

        $view = new ViewModel([
            'formIndicadores' => $formIndicadores,
            'procesos' => $procesosRaw,
            'coordinaciones' => $coordinacionesRaw
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function getCoordinacionesAction()
    {
        $idProceso = (int) trim($this->params()->fromQuery('idProceso', 0));
        $view = new ViewModel([
            'coordinaciones' => $this->DAO->getCoordinacionesByProceso($idProceso),
        ]);
        $view->setTerminal(true);
        return $view;
    }

}
