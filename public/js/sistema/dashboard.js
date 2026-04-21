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

function verRegistrartablero() {
    $.ajax({
        url: "registrar",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle fa-lg" style="color: yellow"></i> &nbsp; REGISTRAR TABLERO');
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
    let nombre = $('#nombre').val();
    let fechaent = $('#fecha_entregada').val();
    let manueal = $('#manual option:selected').text();
    let fuente = $('#fuentes_informacion option:selected').text();
    let historia = $('#historia_usuario option:selected').text();
    let necesidad = $('#necesidad option:selected').text();

    let msgHtml = `
            <b>Proceso:</b> ${idProyecto} <br>
            <b>Coordinación:</b> ${idCordinacion} <br>
            <b>Nombre:</b> ${nombre} <br>
            <b>Fecha de Entrega:</b> ${fechaent} <br>
            <b>Manual:</b> ${manueal} <br>
            <b>Fuente Informacion:</b> ${fuente} <br>
            <b>Historia de Usuario:</b> ${historia} <br>
            <b>Necesidad:</b> ${necesidad} <hr>
    `;

    Swal.fire({
        title: "&iquest;DESEA REGISTRAR ESTE TABLERO?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            $(".sinDiacriticos").each(function () {
                eliminarDiacriticos(this);
            });
            formulario.submit();
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
function verAsignar(idTablero) {
    $.ajax({
        url: "asignar",
        dataType: "html",
        data: { idTablero: idTablero },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-user fa-lg" style="color: yellow"></i> &nbsp; ASIGNAR USUARIO');

            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function validarAsignar(evt, formulario) {
    evt.preventDefault();
    let idCordinacion = $('#idCoordinacion option:selected').text();
    let fechalimite = $('#fechalimactivacion').val();

    let msgHtml = `
    <b>Coordinación:</b> ${idCordinacion} <br>
    <hr>
    Se actualizará la información con los cambios realizados.
    `;

    Swal.fire({
        title: "&iquest;DESEA ASIGNAR EL USUARIO AL TABLERO?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            $(".sinDiacriticos").each(function () {
                eliminarDiacriticos(this);
            });
            formulario.submit();
            bloqueoAjax();
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
function getUsuarios() {
    $.ajax({
        url: "getUsuarios",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenidoAux").html(html);
            $('#lbModalFormularioAux').html('<i class="fas fa-mouse-pointer fa-lg" style="color: yellow"></i> &nbsp; SELECCIONAR USUARIO');
            $("#tblBuscarTarifas").DataTable({
                responsive: true,
                iDisplayLength: 25,
                sPaginationType: "full_numbers",
                oLanguage: {
                    sLengthMenu: "Mostrar: _MENU_ registros por pagina",
                    sZeroRecords: "NO SE HA ENCONTRADO INFORMACION",
                    sInfo: "Mostrando <b>_START_</b> a <b>_END_</b> registros <br>TOTAL REGISTROS: <b>_TOTAL_</b> Registros</b>",
                    sInfoEmpty: "Mostrando 0 A 0 registros",
                    sInfoFiltered: "(Filtrados de un total de <b>_MAX_</b> registros)",
                    sLoadingRecords: "CARGANDO...",
                    sProcessing: "EN PROCESO...",
                    sSearch: "Buscar:",
                    sEmptyTable: "NO HAY INFORMACION DISPONIBLE PARA LA TABLA",
                    oPaginate: {
                        sFirst: "<i class=\'fa fa-fast-backward\' aria-hidden=\'true\' title=\'Inicio\'></i>",
                        sPrevious: "<i class=\'fa fa-step-backward\' aria-hidden=\'true\' title=\'Anterior\'></i>",
                        sNext: "<i class=\'fa fa-step-forward\' aria-hidden=\'true\' title=\'Siguiente\'></i>",
                        sLast: "<i class=\'fa fa-fast-forward\' aria-hidden=\'true\' title=\'Fin\'></i>"
                    }
                },
                aaSorting: [[0, "desc"]]
            });
            $('#modalFormularioAux').modal('show');
        }
    });
    bloqueoAjax();

}
function seleccionarUsuario(idUsuario, login, empleado) {
    $("#idUsuario").val(idUsuario);
    $("#login").val(login);
    $("#empleado").val(empleado);
    $("#empleado").focus();

    $('#modalFormularioAux').modal('hide');
}
//------------------------------------------------------------------------------

