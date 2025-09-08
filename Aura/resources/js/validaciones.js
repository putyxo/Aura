/**
 * Sistema de Validaciones de Seguridad - AURA
 * Validaciones para formularios de usuario con seguridad básica
 */

class FormValidator {
    constructor() {
        this.emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        this.usernameRegex = /^[A-Za-z0-9]{8,}$/;
        this.errors = {};
    }

    // Validar email
    validateEmail(email) {
        if (!email || email.trim() === '') {
            return { valid: false, message: 'El correo electrónico es obligatorio' };
        }
        
        if (!this.emailRegex.test(email)) {
            return { valid: false, message: 'Por favor ingresa un correo electrónico válido' };
        }
        
        return { valid: true, message: '' };
    }

    // Validar nombre de usuario
    validateUsername(username) {
        if (!username || username.trim() === '') {
            return { valid: false, message: 'El nombre de usuario es obligatorio' };
        }
        
        if (username.length < 8) {
            return { valid: false, message: 'El usuario debe tener mínimo 8 caracteres' };
        }
        
        if (!this.usernameRegex.test(username)) {
            return { valid: false, message: 'El usuario solo puede contener letras y números (sin espacios, guiones o caracteres especiales)' };
        }
        
        return { valid: true, message: '' };
    }

    // Validar contraseña
    validatePassword(password) {
        if (!password || password.trim() === '') {
            return { valid: false, message: 'La contraseña es obligatoria' };
        }
        
        return { valid: true, message: '' };
    }

    // Mostrar error en el campo
    showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + '-error');
        
        if (field && errorDiv) {
            field.classList.add('error');
            field.classList.remove('valid');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            this.errors[fieldId] = true;
        }
    }

    // Limpiar error del campo
    clearError(fieldId) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + '-error');
        
        if (field && errorDiv) {
            field.classList.remove('error');
            field.classList.add('valid');
            errorDiv.style.display = 'none';
            delete this.errors[fieldId];
        }
    }

    // Validar campo individual
    validateField(fieldId, value, type) {
        let result;
        
        switch (type) {
            case 'email':
                result = this.validateEmail(value);
                break;
            case 'username':
                result = this.validateUsername(value);
                break;
            case 'password':
                result = this.validatePassword(value);
                break;
            default:
                return true;
        }
        
        if (result.valid) {
            this.clearError(fieldId);
        } else {
            this.showError(fieldId, result.message);
        }
        
        return result.valid;
    }

    // Validar formulario completo
    validateForm(formData) {
        let isValid = true;
        
        for (const [fieldId, config] of Object.entries(formData)) {
            const field = document.getElementById(fieldId);
            if (field) {
                const fieldValid = this.validateField(fieldId, field.value, config.type);
                if (!fieldValid) {
                    isValid = false;
                }
            }
        }
        
        return isValid;
    }

    // Configurar validación en tiempo real
    setupRealTimeValidation(formData) {
        for (const [fieldId, config] of Object.entries(formData)) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => {
                    // Validar solo si el campo tiene contenido o ya tiene error
                    if (field.value.length > 0 || this.errors[fieldId]) {
                        this.validateField(fieldId, field.value, config.type);
                    }
                });
                
                field.addEventListener('blur', () => {
                    this.validateField(fieldId, field.value, config.type);
                });
            }
        }
    }

    // Inicializar validador para formulario específico
    init(formId, formData, onSuccess) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Formulario con ID '${formId}' no encontrado`);
            return;
        }

        // Configurar validación en tiempo real
        this.setupRealTimeValidation(formData);

        // Manejar envío del formulario
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const isValid = this.validateForm(formData);
            
            if (isValid) {
                if (typeof onSuccess === 'function') {
                    onSuccess(form);
                } else {
                    alert('Formulario válido. Datos procesados correctamente.');
                }
            } else {
                // Enfocar el primer campo con error
                const firstErrorField = Object.keys(this.errors)[0];
                if (firstErrorField) {
                    document.getElementById(firstErrorField).focus();
                }
            }
        });
    }
}

// Crear instancia global del validador
const formValidator = new FormValidator();

// Configuraciones de formularios
const FORM_CONFIGS = {
    editProfile: {
        'email': { type: 'email' },
        'usuario': { type: 'username' },
        'password': { type: 'password' }
    },
    changeUser: {
        'nuevo-email': { type: 'email' },
        'nuevo-usuario': { type: 'username' },
        'confirmar-password': { type: 'password' }
    }
};

// Funciones de inicialización para cada formulario
function initEditProfileForm() {
    formValidator.init('editForm', FORM_CONFIGS.editProfile, (form) => {
        alert('Perfil actualizado correctamente.');
        // Aquí iría la lógica para enviar al servidor
        console.log('Datos del formulario:', new FormData(form));
    });
}

function initChangeUserForm() {
    formValidator.init('changeUserForm', FORM_CONFIGS.changeUser, (form) => {
        alert('Usuario actualizado correctamente.');
        // Aquí iría la lógica para enviar al servidor
        console.log('Datos del formulario:', new FormData(form));
    });
}

// Auto-inicialización basada en la página actual
document.addEventListener('DOMContentLoaded', function() {
    // Detectar qué formulario está presente y inicializarlo
    if (document.getElementById('editForm')) {
        initEditProfileForm();
    }
    
    if (document.getElementById('changeUserForm')) {
        initChangeUserForm();
    }
});