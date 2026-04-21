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

function verRegistrarIndicador() {
    $.ajax({
        url: "registrar",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle fa-lg" style="color: yellow"></i> &nbsp; REGISTRAR INDICADOR');
            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------

function validarRegistrar(evt, formulario) {
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
    `;

    Swal.fire({
        title: "&iquest;DESEA REGISTRAR ESTE INDICADOR?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: $(formulario).attr('action'),
                type: $(formulario).attr('method'),
                data: $(formulario).serialize(),
                dataType: 'json',
                success: function (response) {
                    $('#modalFormulario').modal('hide');
                    mostrarMensajesToastr(response);
                    if (response.success) {
                        setTimeout(function () { window.location.reload(); }, 900);
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
    });
}
//------------------------------------------------------------------------------

function verEditarIndicador(id_indicador) {
    $.ajax({
        url: "editar",
        dataType: "html",
        data: { id_indicador: id_indicador },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-edit fa-lg" style="color: dodgerblue"></i> &nbsp; EDITAR INDICADOR');
            $('#modalFormulario').modal('show');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar formulario de edición de indicador: ", textStatus, errorThrown, jqXHR);
            mostrarMensajesToastr({ success: false, globalMessage: 'No se pudo cargar el formulario de edición de indicador.' });
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
            let disabledFields = $(formulario).find(':disabled');
            disabledFields.prop('disabled', false);
            let datosFormulario = $(formulario).serialize();
            disabledFields.prop('disabled', true);
            $.ajax({
                url: $(formulario).attr('action'),
                type: $(formulario).attr('method'),
                data: datosFormulario,
                dataType: 'json',
                success: function (response) {
                    $('#modalFormulario').modal('hide');
                    mostrarMensajesToastr(response);
                    if (response.success) {
                        setTimeout(function () { window.location.reload(); }, 1000);
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
    });
}

//------------------------------------------------------------------------------

function verDetalleIndicador(id_indicador) {
    $.ajax({
        url: "detalle",
        dataType: "html",
        data: { idIndicador: id_indicador },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-eye fa-lg" style="color: yellow"></i> &nbsp; DETALLE DEL INDICADOR');
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
function verActivacion() {
    $.ajax({
        url: "activar",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle fa-lg" style="color: yellow"></i> &nbsp; ACTIVAR INDICADOR');
            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function validarActivar(evt, formulario) {
    evt.preventDefault();
    let idCordinacion = $('#idCoordinacion option:selected').text();
    let fechalimite = $('#fechalimactivacion').val();

    let msgHtml = `
    <b>Coordinación:</b> ${idCordinacion} <br>
    <b>Fecha límite de Activacion:</b> ${fechalimite} <hr>
    Se actualizará la información con los cambios realizados.
    `;

    Swal.fire({
        title: "&iquest;DESEA ACTIVAR ESTOS INDICADORES?",
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
    $("#idCoordinacion").html('<option value="">Seleccione...</option>');
    if ($("#idProceso").val() !== '') {
        $.ajax({
            url: "getCoordinaciones",
            dataType: "html",
            data: {
                idProceso: $("#idProceso").val()
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



