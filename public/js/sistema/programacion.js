
/**
 * Muestra mensajes toastr desde la respuesta AJAX
 */
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
                        if (typeof toastr !== 'undefined') toastr.error(msg, 'Error en ' + field);
                    });
                } else if (typeof msgs === 'string') {
                    if (typeof toastr !== 'undefined') toastr.error(msgs, 'Error en ' + field);
                }
            }
        }
    }
}

/**
 * Bloquea la interfaz de usuario durante las solicitudes AJAX con un mensaje de carga
 */
function bloqueoAjax() {
    if (typeof $.blockUI === 'function') {
        $.blockUI({
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
        });
        $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
    }
}

/**
 * Muestra modal para registrar un nuevo resultado para un indicador si está activado
 */
function verRegistrarRprogramacion(idReporte, estado) {
    $.ajax({
        url: "registrar",
        dataType: "html",
        data: { idReporte: idReporte },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle text-warning"></i> &nbsp; REGISTRAR PROGRAMACIÓN DE REPORTE');
            $('#modalFormulario').modal('show');
        },
        complete: function () { if ($.unblockUI) $.unblockUI(); }
    });
    bloqueoAjax();


}

/**
 * Muestra modal para editar un avance existente
 */
function verReportar(idProgramacion) {
    $.ajax({
        url: "reportar",
        dataType: "html",
        data: { idProgramacion: idProgramacion },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-check text-primary"></i> &nbsp; MARCAR COMO REPORTADO');
            $('#modalFormulario').modal('show');
        },
        complete: function () { if ($.unblockUI) $.unblockUI(); }
    });
    bloqueoAjax();
}
/**
 * Muestra modal para editar un avance existente
 */
function verInformacion(idProgramacion) {
    $.ajax({
        url: "informacion",
        dataType: "html",
        data: { idProgramacion: idProgramacion },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-hand-holding text-primary"></i> &nbsp; MARCAR RECIBIDO DE INFORMACIÓN');
            $('#modalFormulario').modal('show');
        },
        complete: function () { if ($.unblockUI) $.unblockUI(); }
    });
    bloqueoAjax();
}

/**
 * Muestra modal para ver detalles de un avance
 */
function verDetalle(id_result) {
    $.ajax({
        url: "detalle",
        dataType: "html",
        data: { id_result: id_result },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-eye text-warning"></i> &nbsp; DETALLE DEL RESULTADO');
            $('#modalFormulario').modal('show');
        },
        complete: function () { if ($.unblockUI) $.unblockUI(); }
    });
    bloqueoAjax();
}

function verEliminar(id_result) {
    $.ajax({
        url: "eliminar",
        dataType: "html",
        data: { id_result: id_result },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-trash-alt text-danger"></i> &nbsp; ELIMINAR RESULTADO');
            $('#modalFormulario').modal('show');
        },
        complete: function () { if ($.unblockUI) $.unblockUI(); }
    });
    bloqueoAjax();
}


function validarRegistrar(evt, formulario) {
    evt.preventDefault();

    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return false;
    }
    switch (parseInt($(formulario).find('#mes').val())) {
        case 1: mes = 'Enero'; break;
        case 2: mes = 'Febrero'; break;
        case 3: mes = 'Marzo'; break;
        case 4: mes = 'Abril'; break;
        case 5: mes = 'Mayo'; break;
        case 6: mes = 'Junio'; break;
        case 7: mes = 'Julio'; break;
        case 8: mes = 'Agosto'; break;
        case 9: mes = 'Septiembre'; break;
        case 10: mes = 'Octubre'; break;
        case 11: mes = 'Noviembre'; break;
        case 12: mes = 'Diciembre'; break;
    }

    let fecha_corte = $(formulario).find('#fecha_corte').val();
    let fecha_solicitud = $(formulario).find('#fecha_solicitud').val();
    let fecha_limite = $(formulario).find('#fecha_limite').val();



    let msgHtml = `
        <div class="text-left">
            <b>Mes:</b> <br> <i>${mes}</i> <hr>
            <b>Fecha de Corte:</b> ${fecha_corte} <br>
            <b>Fecha de Solicitud:</b> ${fecha_solicitud} <br>
            <b>Fecha Límite:</b> ${fecha_limite} <br>
        </div>
    `;

    Swal.fire({
        title: "&iquest;DESEA REGISTRAR ESTE RESULTADO?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, registrar",
        cancelButtonText: "No, cancelar",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioAjax(formulario);
        }
    });
}

function validarEditar(evt, formulario) {
    evt.preventDefault();

    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return false;
    }

    let mes = $(formulario).find('#mes').val();
    let num = $(formulario).find('#fecha_corte').val();
    let dem = $(formulario).find('#fecha_solic').val();
    let resultado = $(formulario).find('#resultado').val();
    let analisis = $(formulario).find('#analisis').val();


    let msgHtml = `
        <div class="text-left">
            <b>Mes:</b> <br> <i>${mes}</i> <hr>
            <b>Numerador:</b> ${num} <br>
            <b>Denominador:</b> ${dem} <br>
            <b>Resultado:</b> ${resultado} <br>
            <b>Análisis del Resultado:</b> ${analisis} <br>
        </div>
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

function validarEliminar(evt, formulario) {
    evt.preventDefault();

    let mes = $(formulario).find('#mes').val();
    let num = $(formulario).find('#num').val();
    let dem = $(formulario).find('#dem').val();
    let resultado = $(formulario).find('#resultado').val();
    let analisis = $(formulario).find('#analisis').val();


    let msgHtml = `
        <div class="text-left">
            <b>Mes:</b> <br> <i>${mes}</i> <hr>
            <b>Numerador:</b> ${num} <br>
            <b>Denominador:</b> ${dem} <br>
            <b>Resultado:</b> ${resultado} <br>
            <b>Análisis del Resultado:</b> ${analisis} <br>
        </div>
    `;

    Swal.fire({
        title: "&iquest;DESEA ELIMINAR ESTE RESULTADO?",
        html: msgHtml,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        confirmButtonColor: '#d33',
        cancelButtonText: "No, cancelar",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioAjax(formulario);
        }
    });
}

function validarReportar(evt, formulario) {
    evt.preventDefault();

    let mes = $(formulario).find('#mes').val();
    let num = $(formulario).find('#num').val();
    let dem = $(formulario).find('#dem').val();
    let resultado = $(formulario).find('#resultado').val();
    let analisis = $(formulario).find('#analisis').val();


    let msgHtml = `
        <div class="text-left">
            <b>Mes:</b> <br> <i>${mes}</i> <hr>
            <b>Numerador:</b> ${num} <br>
            <b>Denominador:</b> ${dem} <br>
            <b>Resultado:</b> ${resultado} <br>
            <b>Análisis del Resultado:</b> ${analisis} <br>
        </div>
    `;

    Swal.fire({
        title: "&iquest;DESEA MARCAR ESTE REPORTE COMO REPORTADO?",
        html: msgHtml,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, marcar como reportado",
        confirmButtonColor: 'rgb(51, 221, 133)',
        cancelButtonText: "No, cancelar",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioAjax(formulario);
        }
    });
}
function validarInformacion(evt, formulario) {
    evt.preventDefault();

    let mes = $(formulario).find('#mes').val();
    let num = $(formulario).find('#num').val();
    let dem = $(formulario).find('#dem').val();
    let resultado = $(formulario).find('#resultado').val();
    let analisis = $(formulario).find('#analisis').val();


    let msgHtml = `
        <div class="text-left">
            <b>Mes:</b> <br> <i>${mes}</i> <hr>
            <b>Numerador:</b> ${num} <br>
            <b>Denominador:</b> ${dem} <br>
            <b>Resultado:</b> ${resultado} <br>
            <b>Análisis del Resultado:</b> ${analisis} <br>
        </div>
    `;

    Swal.fire({
        title: "&iquest;DESEA MARCAR RECIBIDO DE INFORMACIÓN DEL REPORTE?",
        html: msgHtml,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, marcar recibido",
        confirmButtonColor: 'rgb(51, 221, 133)',
        cancelButtonText: "No, cancelar",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioAjax(formulario);
        }
    });
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

function calcularResultado() {
    let num = parseFloat($('#num').val()) || 0;
    let dem = parseFloat($('#dem').val()) || 0;

    let resultado = num && dem ? (num / dem) * 100 : 0;
    $('#resultado').val(resultado.toFixed(1));
}

function validarAnalisis() {
    let analisis = $('#analisis').val().trim();
    if (analisis.length < 500) {
        $('#analisis').val(analisis.substring(0, 500));
        Swal.fire({
            title: "Límite de caracteres",
            text: "El análisis del resultado no puede tener menos de 500 caracteres.",
            icon: "warning",
            confirmButtonText: "Aceptar"
        });
    }
    if (analisis.length > 1000) {
        $('#analisis').val(analisis.substring(0, 1000));
        Swal.fire({
            title: "Límite de caracteres excedido",
            text: "El análisis del resultado no puede exceder los 1000 caracteres.",
            icon: "warning",
            confirmButtonText: "Aceptar"
        });
        $('#analisis').val('');
        $('#analisis').focus();
    }

}

$(document).ready(function () {
    if ($('#modalFormulario').length > 0) {
        $('#modalFormulario').appendTo("body");
    }
});