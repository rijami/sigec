function mostrarMensajesToastr(response) {
    if (response.globalMessage) {
        let type = response.success ? 'success' : 'error';
        let title = response.success ? 'OK' : 'ERROR';

        if (typeof toastr !== 'undefined') {
            toastr[type](response.globalMessage, title, {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-full-width',
                timeOut: '8000',
            });
        } else {
            alert(response.globalMessage);
        }
    }


    if (response.messages) {
        for (let field in response.messages) {
            if (response.messages.hasOwnProperty(field)) {
                let msgs = response.messages[field];

                if (Array.isArray(msgs)) {
                    msgs.forEach(function (msg) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg, 'Error en ' + field.charAt(0).toUpperCase() + field.slice(1));
                        }
                    });
                } else if (typeof msgs === 'string') {

                    if (typeof toastr !== 'undefined') {
                        toastr.error(msgs, 'Error en ' + field.charAt(0).toUpperCase() + field.slice(1));
                    }
                } else if (typeof msgs === 'object') {

                    Object.values(msgs).forEach(function (msg) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg, 'Error en ' + field.charAt(0).toUpperCase() + field.slice(1));
                        }
                    });
                }
            }
        }
    }
}


//------------------------------------------------------------------------------
function bloqueoAjax() {
    $.blockUI(
        {
            message: $('#msgBloqueo'),
            css: {
                border: 'none',
                padding: '15px',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .85,
                color: '#fff',
                'z-index': 10000000
            }
        }
    );
    $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
}

//------------------------------------------------------------------------------

function verRegistrarReporte() {
    $.ajax({
        url: "registrar",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle fa-lg" style="color: yellow"></i> &nbsp; REGISTRAR REPORTE');
            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------

function validarRegistrar(evt, formulario) {
    evt.preventDefault();
    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return false;
    }
    let idProyecto = $('#idProceso option:selected').text();
    let idCordinacion = $('#idCoordinacion option:selected').text();
    let codigo = $('#codigo').val();
    let nombre = $('#nombre_indicador').val();
    let puntos_esfuerzo = $('#meta').val();
    let prioridad = $('#periodicidad option:selected').text();

    let msgHtml = `
            <b>Proceso:</b> ${idProyecto} <br>
            <b>Coordinación:</b> ${idCordinacion} <br>
            <b>Código:</b> ${codigo} <br>
            <b>Nombre:</b> ${nombre} <br>
            <b>Meta:</b> ${puntos_esfuerzo} <br>
            <b>Periodicidad:</b> ${prioridad} <hr>
    `;

    Swal.fire({
        title: "&iquest;DESEA REGISTRAR ESTE REPORTE?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioAjax(formulario);
        }
    });
}
//------------------------------------------------------------------------------

function verEditarReporte(idReporte) {
    $.ajax({
        url: "editar",
        dataType: "html",
        data: { idReporte: idReporte },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-edit fa-lg" style="color: dodgerblue"></i> &nbsp; EDITAR REPORTE');
            $('#modalFormulario').modal('show');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar formulario de edición de reporte: ", textStatus, errorThrown, jqXHR);
            mostrarMensajesToastr({ success: false, globalMessage: 'No se pudo cargar el formulario de edición de reporte.' });
        },
        complete: function () {
            $.unblockUI();
        }
    });
    bloqueoAjax();
}




//------------------------------------------------------------------------------

function validarEditar(event, formulario) {
    event.preventDefault();

    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return false;
    }
    let idProyecto = $('#idProceso option:selected').text();
    let idCordinacion = $('#idCoordinacion option:selected').text();
    let codigo = $('#codigo').val();
    let nombre = $('#nombre_indicador').val();
    let puntos_esfuerzo = $('#meta').val();
    let prioridad = $('#periodicidad option:selected').text();

    let msgHtml = `
            <b>Proceso:</b> ${idProyecto} <br>
            <b>Coordinación:</b> ${idCordinacion} <br>
            <b>Código:</b> ${codigo} <br>
            <b>Nombre:</b> ${nombre} <br>
            <b>Meta:</b> ${puntos_esfuerzo} <br>
            <b>Periodicidad:</b> ${prioridad} <hr>
            Se actualizará la información con los cambios realizados.
    `;

    Swal.fire({
        title: "&iquest;DESEA GUARDAR LOS CAMBIOS?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, guardar cambios",
        cancelButtonText: "No, cancelar",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioAjax(formulario);
        }
    });
}

//------------------------------------------------------------------------------

function verDetalleReporte(idReporte) {
    $.ajax({
        url: "detalle",
        dataType: "html",
        data: { idReporte: idReporte },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-eye fa-lg" style="color: yellow"></i> &nbsp; DETALLE DEL REPORTE');
            $('#modalFormulario').modal('show');
        }
    });
    bloqueoAjax();
}

//------------------------------------------------------------------------------

function verEliminarIndicador(id_indicador) {
    $.ajax({
        url: "eliminar",
        dataType: "html",
        data: { id_indicador: id_indicador },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-trash-alt fa-lg" style="color: red"></i> &nbsp; ELIMINAR INDICADOR');
            $('#modalFormulario').modal('show');
            $('#modalFormulario #idIndicador').val(id_indicador);
        }
    });
    bloqueoAjax();
}

//------------------------------------------------------------------------------

function validarEliminar(evt, formulario) {
    evt.preventDefault();
    let idProyecto = $('#idProceso option:selected').text();
    let idCordinacion = $('#idCoordinacion option:selected').text();
    let codigo = $('#codigo').val();
    let nombre = $('#nombre_indicador').val();
    let puntos_esfuerzo = $('#meta').val();
    let prioridad = $('#periodicidad option:selected').text();

    let msgHtml = `
            <b>Proceso:</b> ${idProyecto} <br>
            <b>Coordinación:</b> ${idCordinacion} <br>
            <b>Código:</b> ${codigo} <br>
            <b>Nombre:</b> ${nombre} <br>
            <b>Meta:</b> ${puntos_esfuerzo} <br>
            <b>Periodicidad:</b> ${prioridad} <hr>
            Se actualizará la información con los cambios realizados.
    `;

    Swal.fire({
        title: "&iquest;DESEA ELIMINAR ESTE INDICADOR?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });
}
//------------------------------------------------------------------------------
function verAsignar(idReporte) {
    $.ajax({
        url: "asignar",
        dataType: "html",
        data: { idReporte: idReporte },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle fa-lg" style="color: yellow"></i> &nbsp; ASIGNAR RESPONSABLES');
            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function validarAsignar(evt, formulario) {
    evt.preventDefault();
    if ($.trim($('#correoBusqAux').val()) !== '') {
        let idDireccion = $('#idDireccion option:selected').text();
        let idCordinacion = $('#idCoordinacion option:selected').text();
        let correo = $('#correo').val();

        let msgHtml = `
    <b>Dirección:</b> ${idDireccion} <br>
    <b>Coordinación:</b> ${idCordinacion} <br>
    <b>Correo:</b> ${correo} <hr>
    Se actualizará la información con los cambios realizados.
    `;

        Swal.fire({
            title: "&iquest;DESEA ASIGNAR ESTE REPORTE?",
            html: msgHtml,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí",
            cancelButtonText: "No",
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                formulario.removeAttribute('onsubmit');
                formulario.submit();
                bloqueoAjax();
            }
        });
    } else {
        Swal.fire({
            title: "Debe buscar un correo válido para asignar el reporte",
            html: 'Para asignar el reporte es necesario que complete los campos requeridos',
            icon: "warning",
            showCancelButton: false,
            confirmButtonText: "OK",
            allowOutsideClick: false
        });
        $('#correoBusqAux').focus();
    }
}
//------------------------------------------------------------------------------
function verExportar() {
    $.ajax({
        url: "exportar",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-file fa-lg" style="color: yellow"></i> &nbsp; EXPORTAR INDICADORES');
            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function validarExportar(evt, formulario) {
    evt.preventDefault();

    Swal.fire({
        title: "&iquest;DESEA EXPORTAR ESTOS INDICADORES?",
        html: '',
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Cerrar modal correctamente
            // mensaje informativo
            Swal.fire({
                title: "Generando archivo...",
                text: "El Excel se descargará en breve",
                icon: "info",
                showConfirmButton: false,
                timer: 1500
            });

            // esperar un momento para que el modal cierre
            setTimeout(function () {
                formulario.removeAttribute('onsubmit');
                formulario.submit();
                //window.location.reload();
            }, 600);
        }
    });
}
//------------------------------------------------------------------------------
function getCoordinaciones() {
    $("#idCoordinacion").html('<option value="">Seleccione una coordinación...</option>');
    if ($("#idDireccion").val() !== '') {
        $.ajax({
            url: "getCoordinaciones",
            dataType: "html",
            data: {
                idDireccion: $("#idDireccion").val()
            },
            success: function (html) {
                $("#idCoordinacion").html(html);
            }
        });
        bloqueoAjax();
    }
}
//------------------------------------------------------------------------------
function validarYBloquear() {
    var hoy = new Date();
    hoy.setDate(hoy.getDate() + 1); // mañana

    var manana = hoy.toISOString().split('T')[0];

    var $input = $("#fechalimactivacion");

    // Bloquea fechas pasadas y hoy
    $input.attr('min', manana);

    // Validación manual
    $input.on('change', function () {

        if ($(this).val() < manana) {

            alert("Debes elegir una fecha desde mañana.");

            $(this).val('');

        }

    });

}
//------------------------------------------------------------------------------
function buscarByCorreo() {
    let correo = $.trim($("#correoBusqAux").val());
    if (correo === '') {
        $("#correoBusqAux").focus();
        Swal.fire({
            icon: 'warning',
            title: 'POR FAVOR DIGITE EL CORREO',
            html: 'Para iniciar la busqueda es necesario que digite el correo que desea buscar'
        });
        return false;
    }
    if (correo.length < 5) {
        $("#correoBusqAux").focus();
        Swal.fire({
            icon: 'warning',
            title: 'EL CORREO DIGITADO ES MUY CORTO',
            html: 'Para iniciar la busqueda se requiere que el correo tenga al menos <b>5 caracteres</b>'
        });
        return false;
    }
    var regexArroba = /.+@.+/;
    if (!regexArroba.test(correo)) {
        $("#correoBusqAux").focus();
        Swal.fire({
            icon: 'warning',
            title: 'CORREO INVÁLIDO',
            html: 'El correo digitado no es válido debe tener @. Por favor, ingrese un correo electrónico correcto.'
        });
        return false; // Detiene la ejecución si falta el @
    }
    $.ajax({
        url: "getResponsables",
        dataType: "html",
        data: {
            correo: correo
        },
        success: function (html) {
            $("#divInfoCorreo").html(html);
            $("#correo").val($("#correoBusqAux").val());
            $("#divInfoCorreo").show('slow');
            $("#correo").focus();

        }
    });
    bloqueoAjax();
}
function enviarFormularioAjax(formulario) {

    let disabledFields = $(formulario).find(':disabled');
    disabledFields.prop('disabled', false);

    let datosFormulario = $(formulario).serialize();

    disabledFields.prop('disabled', true);
    $.ajax({
        url: $(formulario).attr('action'),
        type: 'post',
        data: datosFormulario,
        dataType: 'json',
        success: function (response) {

            window.cierreProgramado = true;

            $('#modalFormulario').modal('hide');


            if (!response.success) {
                mostrarMensajesToastr(response);
            }


            if (response.success) {
                setTimeout(function () { window.location.reload(); }, 500);
            }
        },
        error: function () {
            mostrarMensajesToastr({ success: false, globalMessage: 'Error de comunicación con el servidor.' });
        },
        complete: function () {
            if (typeof $.unblockUI === 'function') $.unblockUI();
        }
    });
    bloqueoAjax();
}



