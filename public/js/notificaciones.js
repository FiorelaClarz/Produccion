/**
 * Sistema de notificaciones para la aplicación
 * Utiliza SweetAlert2 para mostrar mensajes flotantes
 */

// Función para mostrar notificación de éxito
function mostrarExito(mensaje) {
    Swal.fire({
        title: '¡Éxito!',
        text: mensaje,
        icon: 'success',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#e1f5e1',
        iconColor: '#28a745'
    });
}

// Función para mostrar notificación de error
function mostrarError(mensaje) {
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        background: '#f8d7da',
        iconColor: '#dc3545'
    });
}

// Función para mostrar notificación de advertencia
function mostrarAdvertencia(mensaje) {
    Swal.fire({
        title: 'Advertencia',
        text: mensaje,
        icon: 'warning',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#fff3cd',
        iconColor: '#ffc107'
    });
}

// Verifica si hay mensajes flash en el objeto global 'mensajesFlash'
document.addEventListener('DOMContentLoaded', function() {
    if (typeof mensajesFlash !== 'undefined') {
        if (mensajesFlash.success) {
            mostrarExito(mensajesFlash.success);
        }
        if (mensajesFlash.error) {
            mostrarError(mensajesFlash.error);
        }
        if (mensajesFlash.warning) {
            mostrarAdvertencia(mensajesFlash.warning);
        }
    }
});