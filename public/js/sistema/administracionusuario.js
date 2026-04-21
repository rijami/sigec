//------------------------------------------------------------------------------
function bloqueoAjax() {
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

//-----------------------------------------------------------------------------

function verRegistrar() {
    $.ajax({
        url: "registrar",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-plus-circle fa-lg" style="color: yellow"></i> &nbsp; REGISTRAR USUARIO');
            $('#modalFormulario').modal('show');
        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------

function validarRegistrar(evt, formulario) {
    evt.preventDefault();
    let msgHtml = '';
    Swal.fire({
        title: "&iquest;DESEA REGISTRAR ESTE NUEVO USUARIO?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Si",
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
function verAsignartc() {
    $.ajax({
        url: "asignartc",
        dataType: "html",
        data: {},
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-check fa-lg" style="color: yellow"></i> &nbsp; ASIGNAR TABLEROS DE CONTROL');
            $('#modalFormulario').modal('show');
        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function validarAsignartc(evt, formulario) {
    evt.preventDefault();
    let msgHtml = '';
    Swal.fire({
        title: "&iquest;DESEA ASIGNAR ESTE TABLERO DE CONTROL?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Si",
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

function verBloquear(idUsuario) {
    $.ajax({
        url: "bloquear",
        dataType: "html",
        data: {
            idUsuario: idUsuario
        },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-lock" style="color: red;"></i>&nbsp;BLOQUEO DE USUARIO');
            $('#modalFormulario').modal('show');

        }
    });
    bloqueoAjax();
}

//------------------------------------------------------------------------------

function validarBloquear(evt, formulario) {
    evt.preventDefault();
    let msgHtml = '';
    Swal.fire({
        title: "&iquest;DESEA  BLOQUEAR ESTE USUARIO?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $('#formUsuario :disabled').each(function () {
                $(this).removeAttr('disabled');
            });
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });

}

//------------------------------------------------------------------------------

function verDesbloquear(idUsuario) {
    $.ajax({
        url: "desbloquear",
        dataType: "html",
        data: { idUsuario: idUsuario },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-unlock" style="color: white;"></i>&nbsp;DESBLOQUEO DE USUARIO');
            $('#modalFormulario').modal('show');
        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------

function validarDesbloquear(evt, formulario) {
    evt.preventDefault();
    let msgHtml = '';
    Swal.fire({
        title: "&iquest;DESEA ACTIVAR ESTE USUARIO?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $('#formUsuario :disabled').each(function () {
                $(this).removeAttr('disabled');
            });
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });
}
//------------------------------------------------------------------------------

function verRol(idUsuario) {
    $.ajax({
        url: "getRolesUsuario",
        dataType: "html",
        data: { idUsuario: idUsuario },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-eye fa-lg" style="color: yellow"></i>&nbsp;Detalle de Roles de Usuario');
            $('#modalFormulario').modal('show');
        }
    });
    bloqueoAjax();
}

//------------------------------------------------------------------------------

function verDetalle(idUsuario, idRol) {
    $.ajax({
        url: "detalle",
        dataType: "html",
        data: {
            idUsuario: idUsuario,
            idRol: idRol
        },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-eye fa-lg" style="color: yellow"></i>&nbsp;DETALLE DEL USUARIO');
            $('#modalFormulario').modal('show');
            $('#idUsuario').prop('disabled', true);
            $('#idRol').prop('disabled', true);
        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------

function verCambiarContrasena(idUsuario, idRol) {
    $.ajax({
        url: "cambiarContrasena",
        dataType: "html",
        data: {
            idUsuario: idUsuario,
            idRol: idRol
        },
        success: function (html) {
            $("#divContenido").html(html);
            $('#lbModalFormulario').html('<i class="fas fa-eye fa-lg" style="color: yellow"></i>&nbsp;CAMBIAR CONTRASEÑA DEL USUARIO');
            $('#modalFormulario').modal('show');
            setTimeout(function () {
                $('#nueva_contrasena').focus(); // Enfocar el campo "nueva_contrasena"
            }, 500);

        }
    });
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function validarCambiarContrasena(evt, formulario) {
    evt.preventDefault();
    let msgHtml = '';
    const nueva = $('#nueva_contrasena').val().trim();
    const confirmar = $('#confirmar_contrasena').val().trim();

    if (nueva === '' || confirmar === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campos vacíos',
            text: 'Debe ingresar y confirmar la nueva contraseña.'
        });
        return;
    }

    if (nueva !== confirmar) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las contraseñas no coinciden.'
        });
        return;
    }

    if (nueva.length < 6) {
        Swal.fire({
            icon: 'warning',
            title: 'Contraseña muy corta',
            html: 'La <strong>contraseña</strong> debe tener al menos 6 caracteres.' // Texto en negrita
        });
        return;
    }

    msgHtml = 'Recuerde que la <strong>contraseña</strong> debe cumplir con los siguientes requisitos:<ul>' +
        '<li>Al menos 6 caracteres</li>' +
        '<li>Contener al menos una mayúscula</li>' +
        '<li>Contener al menos un número</li>' +
        '<li>Contener al menos un símbolo especial (e.g. @, #, $, %, etc.)</li>' +
        '</ul>';

    Swal.fire({
        title: "¿Desea registrar este nuevo usuario?",
        html: msgHtml,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            $("#formUsuario :input[readonly]").each(function () {
                $(this).prop('readonly', false);
            });
            formulario.submit();
            bloqueoAjax();
        }
    });
}
//-----------------------------------------------------------------------------
function buscarByIdentificacion() {
    let identificacion = $.trim($("#identificacionBusqAux").val());
    if (identificacion === '') {
        $("#identificacionBusqAux").focus();
        Swal.fire({
            icon: 'warning',
            title: 'POR FAVOR DIGITE LA IDENTIFICACION',
            html: 'Para iniciar la busqueda es necesario que digite la identificacion del funcionario que desea buscar'
        });
        return false;
    }
    if (identificacion.length < 5) {
        $("#identificacionBusqAux").focus();
        Swal.fire({
            icon: 'warning',
            title: 'LA IDENTIFICACION DIGITADA ES MUY CORTA',
            html: 'Para iniciar la busqueda se requiere que la identificacion tenga al menos <b>5 digitos</b>'
        });
        return false;
    }
    $.ajax({
        url: "getfuncionario",
        dataType: "html",
        data: {
            identificacion: identificacion
        },
        success: function (html) {
            $("#divInfoCliente").html(html);
            $("#identificacion").val(identificacion);
            $("#divInfoCliente").show('slow');
            if ($("#identificacionBusqAux").val() === '111111') {
                $("#identificacionBusqAux").val('');
            }
        }
    });
    bloqueoAjax();
}
//-----------------------------------------------------------------------------
function togglePasswordNueva(inputId, iconSpan) {
    const input = document.getElementById(inputId);
    const icon = iconSpan.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
//------------------------------------------------------------------------------
function togglePasswordConfirmacion(inputId, iconSpan) {
    const input = document.getElementById(inputId);
    const icon = iconSpan.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
//------------------------------------------------------------------------------
function checkPasswordStrength(password) {
    const result = zxcvbn(password);
    const score = result.score;

    const strengthBar = document.getElementById("password-strength-bar");
    const strengthText = document.getElementById("password-strength-text");

    const colors = ["bg-danger", "bg-danger", "bg-warning", "bg-warning", "bg-success"];
    const widths = ["20%", "40%", "60%", "80%", "100%"];
    const labels = ["Muy débil", "Débil", "Aceptable", "Fuerte", "Muy fuerte"];

    strengthBar.className = "progress-bar";
    strengthBar.classList.add(colors[score]);
    strengthBar.style.width = widths[score];

    strengthText.innerText = `Fortaleza: ${labels[score]}`;
    strengthText.className = "form-text";
    strengthText.classList.add(
        score <= 1 ? "text-danger" :
            score <= 3 ? "text-warning" :
                "text-success"
    );
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
function existeIdentificacion() {
    let identificacion = $("#identificacion").val().trim();
    if (identificacion.length < 6) {
        $("#identificacion").val('');
        $("#identificacion").focus();
        Swal.fire({
            icon: 'warning',
            title: 'FORMATO DE CEDULA INCORRECTO',
            html: 'La cedula <b>NO</b> puede tener menos de 6 digitos'
                + '<br><b>Cedula Digitada:</b> ' + identificacion
        });
        return;
    }
    if (identificacion.length > 10) {
        $("#identificacion").val('');
        $("#identificacion").focus();
        Swal.fire({
            icon: 'warning',
            title: 'FORMATO DE CEDULA INCORRECTO',
            html: 'La cedula <b>NO</b> puede tener mas de 10 digitos'
                + '<br><b>Cedula Digitada:</b> ' + identificacion
        });
        return;
    }
    if ($.trim($("#identificacion").val()).length > 0) {
        $.ajax({
            url: "existeidentificacion",
            dataType: "json",
            data: { identificacion: identificacion },
            success: function (datos) {
                if (parseInt(datos['error']) === 0) {
                    if (parseInt(datos['existe']) === 1) {
                        $("#identificacion").val('');
                        $("#identificacion").focus();
                        Swal.fire({
                            icon: 'error',
                            title: 'IDENTIFICACION ENCONTRADA',
                            html: 'La identificacion <b>' + identificacion + '</b> ya se encuentra registrada en el sistema'
                        });
                    }
                }
            }
        });
        bloqueoAjax();
    }
}


