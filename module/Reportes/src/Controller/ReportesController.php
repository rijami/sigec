<?php

declare(strict_types=1);

namespace Reportes\Controller;

use Exception;
use Reportes\Formularios\ReportesForm;
use Reportes\Formularios\ResponsablesForm;
use Reportes\Modelo\DAO\ReportesDAO;
use Reportes\Modelo\Entidades\Reporte;
use Reportes\Modelo\Entidades\Responsable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportesController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = '/var/log/sigec/';

    //------------------------------------------------------------------------------

    /**
     * Inicializa el DAO de indicadores
     */
    public function __construct(ReportesDAO $dao)
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
        $sesion = $this->getInfoSesion();
        $idEmpleado = $sesion['idEmpleado'];
        $idUsuario = $sesion['idUsuario'];
        $funcionario = $this->DAO->getEmpleadoById($idEmpleado);
        $rolesUsuario = $this->DAO->getRolesByIdUsuario($idUsuario);
        $filtro = '';
        if ($funcionario['idCoordinacion'] == 23) {
            $reportes = $this->DAO->getReportesEstadistica($filtro);
        } else {
            $reportes = $this->DAO->getReportes((int) $funcionario['idCoordinacion']);
        }
        //$estadistica = $this->DAO->getCoordinacionEstadistica($filtro);
        return new ViewModel([
            'reportes' => $reportes,
            'rolesUsuario' => $rolesUsuario[0],
            'funcionario' => (int) $funcionario['idCoordinacion']
        ]);
    }

    //------------------------------------------------------------------------------

    /**
     * Crea un nuevo indicador con selección de proceso/coordinación
     */
    public function registrarAction()
    {
        $empleados = $this->DAO->getEmpleadoByEstadistica();
        $listaestadistica = [];
        foreach ($empleados as $empleado) {
            $funcionario = strtolower($empleado['empleado']);
            $listaestadistica[ucwords($funcionario)] = ucwords($funcionario);
        }
        $formReportes = new ReportesForm('registrar', $listaestadistica);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $reporteOBJ = new Reporte();
            $formReportes->setInputFilter($reporteOBJ->getInputFilter());
            $formReportes->setData($request->getPost());
            if ($formReportes->isValid()) {
                $reporteOBJ->exchangeArray($formReportes->getData());
                $infoSesion = $this->getInfoSesion();
                $reporteOBJ->setEstado('Activo');
                $reporteOBJ->setRegistradopor($infoSesion['login']);
                $reporteOBJ->setModificadopor('');
                $reporteOBJ->setFechahorareg(date('Y-m-d H:i:s'));
                $reporteOBJ->setFechahoramod('0000-00-00 00:00:00');
                try {
                    $this->DAO->registrar($reporteOBJ);
                    if ($request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('EL REPORTE FUE REGISTRADO EXITOSAMENTE.');
                        return new JsonModel([
                            'success' => true,
                            'message' => 'EL REPORTE FUE REGISTRADO EXITOSAMENTE.'
                        ]);
                    }
                    $this->flashMessenger()->addSuccessMessage('EL REPORTE FUE REGISTRADO EXITOSAMENTE.');
                    return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
                } catch (Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR PROGRAMACIÓN - ProgramacionController->editar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
                    if ($request->isXmlHttpRequest()) {
                        $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>EL REPORTE NO FUE REGISTRADO.';
                        return new JsonModel([
                            'success' => false,
                            'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                        ]);
                    }
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL REPORTE NO FUE REGISTRADO.');
                return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
            }
        }
        $view = new ViewModel(['formReportes' => $formReportes]);
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------

    public function editarAction()
    {

        $idReporte = (int) $this->params()->fromQuery('idReporte', $this->params()->fromPost('idReporte', 0));
        if ($idReporte === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de reporte válido para edición.');
            return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
        }

        $infoReporte = $this->DAO->getReporteById($idReporte);

        if (empty($infoReporte)) {
            $this->flashMessenger()->addErrorMessage('El reporte a editar no se encuentra registrado.');
            return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
        }
        $empleados = $this->DAO->getEmpleadoByEstadistica();
        $listaestadistica = [];
        foreach ($empleados as $empleado) {
            $funcionario = strtolower($empleado['empleado']);
            $listaestadistica[ucwords($funcionario)] = ucwords($funcionario);
        }

        $formReportes = new ReportesForm('editar', $listaestadistica);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $formReportes->setData($infoReporte);
            $view = new ViewModel([
                'formReportes' => $formReportes,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $idReporteFromPost = (int) $request->getPost('idReporte');
        $idReporte = ($idReporteFromPost !== 0) ? $idReporteFromPost : $idReporte;

        $reporteOBJ = new Reporte();
        $formReportes->setInputFilter($reporteOBJ->getInputFilter());
        $formReportes->setData($request->getPost());

        if (!$formReportes->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'messages' => $formReportes->getMessages(),
                    'globalMessage' => 'LA INFORMACION DE EDICION DEL REPORTE NO ES VALIDA.'
                ]);
            } else {
                $this->flashMessenger()->addErrorMessage('LA INFORMACION DE EDICION DEL REPORTE NO ES VALIDA.');
                return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
            }
        }
        try {
            $reporteOBJ->exchangeArray($formReportes->getData());
            $infoSesion = $this->getInfoSesion();
            $reporteOBJ->setModificadopor($infoSesion['login']);
            $reporteOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($reporteOBJ);

            if ($request->isXmlHttpRequest()) {
                $this->flashMessenger()->addSuccessMessage('EL REPORTE FUE ACTUALIZADO EXITOSAMENTE.');
                return new JsonModel([
                    'success' => true,
                    'message' => 'EL REPORTE FUE ACTUALIZADO EXITOSAMENTE.'
                ]);
            }
            $this->flashMessenger()->addSuccessMessage('EL REPORTE FUE ACTUALIZADO EXITOSAMENTE.');
            return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR HISTORIA USUARIO - HistoriasController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
            if ($request->isXmlHttpRequest()) {
                $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>EL REPORTE NO FUE ACTUALIZADO.';
                return new JsonModel([
                    'success' => false,
                    'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                ]);
            }
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL REPORTE NO FUE ACTUALIZADO.');
            return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
        }
    }


    //------------------------------------------------------------------------------

    /**
     * Muestra detalles del indicador con resultados asociados
     */
    public function detalleAction()
    {
        $idReporte = (int) $this->params()->fromQuery('idReporte', 0);
        if ($idReporte === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de reportes válido.');
            return $this->redirect()->toRoute('reportes/reportes');
        }
        $infoReporte = $this->DAO->getReporteById($idReporte);

        if (empty($infoReporte)) {
            $this->flashMessenger()->addErrorMessage('El reporte solicitado no se encuentra registrado.');
            return $this->redirect()->toRoute('reportes/reportes');
        }
        $direcciones = $this->DAO->getDireccionesByReporte($infoReporte['idReporte']);
        $coordinaciones = $this->DAO->getCoordinacionesByReporte($infoReporte['idReporte']);
        $responsables = $this->DAO->getResponsablesByReporte($infoReporte['idReporte']);
        $programacionReporte = $this->DAO->getProgramacionByReporte($idReporte);
        $empleados = $this->DAO->getEmpleadoByEstadistica();
        $listaestadistica = [];
        foreach ($empleados as $empleado) {
            $funcionario = strtolower($empleado['empleado']);
            $listaestadistica[ucwords($funcionario)] = ucwords($funcionario);
        }
        $formReportes = new ReportesForm('detalle', $listaestadistica);

        $formReportes->setData($infoReporte);

        $view = new ViewModel([
            'formReportes' => $formReportes,
            'direcciones' => $direcciones,
            'coordinaciones' => $coordinaciones,
            'responsables' => $responsables,
            'programacionReporte' => $programacionReporte
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
    public function AsignarAction()
    {
        $idReporte = (int) $this->params()->fromQuery('idReporte', $this->params()->fromPost('idReporte', 0));
        if ($idReporte === 0) {
            $this->flashMessenger()->addErrorMessage('No se proporcionó un ID de reporte válido para edición.');
            return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
        }

        $infoReporte = $this->DAO->getReporteById($idReporte);

        if (empty($infoReporte)) {
            $this->flashMessenger()->addErrorMessage('El reporte a asignar no se encuentra registrado.');
            return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
        }

        $direcciones = $this->DAO->getDirecciones();
        $coordinacionesRaw = $this->DAO->getCoordinaciones();
        $responsablesRaw = $this->DAO->getResponsables();
        $listaDirecciones = [];
        foreach ($direcciones as $direccion) {
            $listaDirecciones[$direccion['idDireccion']] = $direccion['nombre'];
        }
        $listaCoordinaciones = [];
        foreach ($coordinacionesRaw as $coordinacion) {
            $listaCoordinaciones[$coordinacion['idCoordinacion']] = $coordinacion['Coordinacion'];
        }

        $formResponsable = new ResponsablesForm('asignar', $listaDirecciones, $listaCoordinaciones);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $formResponsable->setData($infoReporte);
            $view = new ViewModel([
                'formResponsable' => $formResponsable,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $Correo = $request->getPost('correo', '');
        $direccion = $request->getPost('idDireccion', 0);
        $coordinacion = $request->getPost('idCoordinacion', 0);

        $responsableOBJ = new Responsable();
        //$formResponsable->setInputFilter($responsableOBJ->getInputFilter());
        $formResponsable->setData($request->getPost());


        if ($formResponsable->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonModel([
                    'success' => false,
                    'messages' => $formResponsable->getMessages(),
                    'globalMessage' => 'LA INFORMACION DE ASIGNACION DEL REPORTE NO ES VALIDA.'
                ]);
            } else {
                $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ASIGNACION DEL REPORTE NO ES VALIDA.');
                return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
            }
        }
        //---
        $asiganacion = [
            'idDireccion' => $direccion,
            'idCoordinacion' => $coordinacion,
            'correo' => $Correo,
        ];
        //---
        $responsableOBJ->exchangeArray($formResponsable->getData());
        $infoSesion = $this->getInfoSesion();
        /* echo "<pre>";

        print_r("idReporte: " . print_r($idReporte, true));
        print_r("asignacion: " . print_r($asiganacion, true));
        print_r("responsableOBJ: " . print_r($responsableOBJ, true));
        die(); */

        try {
            $this->DAO->asignar($idReporte, $asiganacion, $responsableOBJ);
            $this->flashMessenger()->addSuccessMessage('EL REPORTE FUE ASIGNADO EXITOSAMENTE');

        } catch (Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR HISTORIA USUARIO - HistoriasController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            file_put_contents($this->rutaLog . 'app.log', $msgLog, FILE_APPEND);
            if ($request->isXmlHttpRequest()) {
                $errorMessage = 'SE HA PRESENTADO UN INCONVENIENTE! <br>EL REPORTE NO FUE ASIGNADO.';
                return new JsonModel([
                    'success' => false,
                    'globalMessage' => 'ERROR EN SERVIDOR: ' . $ex->getMessage()
                ]);
            }
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL REPORTE NO FUE ASIGNADO.');
        }
        return $this->redirect()->toRoute('reportes/reportes', ['action' => 'index']);
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
        $idDireccion = (int) trim($this->params()->fromQuery('idDireccion', 0));
        $view = new ViewModel([
            'coordinaciones' => $this->DAO->getCoordinacionesByDireccion($idDireccion),
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function getResponsablesAction()
    {
        $idResponsable = (int) $this->params()->fromQuery('idResponsable', 0);
        $correo = $this->params()->fromQuery('correo', '');
        $resposable = null;
        if ($correo !== '') {
            $resposable = $this->DAO->getResponsableBycorreo($correo);
        } else {
            if ($idResponsable !== 0) {
                $resposable = $this->DAO->getResponsableById($idResponsable);
            }
        }
        $view = new ViewModel(array(
            'infoResponsable' => $resposable,
        ));
        $view->setTerminal(true);
        return $view;
    }
}
