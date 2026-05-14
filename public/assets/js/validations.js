document.addEventListener('DOMContentLoaded', function() {
    // Validación de formularios de Bootstrap personalizada
    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Validación de RFC en tiempo real
    const rfcInputs = document.querySelectorAll('input[name="rfc"]');
    rfcInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = this.value.toUpperCase();
            this.value = value;
            const rfcRegex = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
            if (value && !rfcRegex.test(value)) {
                this.setCustomValidity('RFC no válido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Validación de email
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.setCustomValidity('Email no válido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Validación de teléfono
    const phoneInputs = document.querySelectorAll('input[name="telefono"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const phoneRegex = /^[0-9\+\-\(\)\s]{7,20}$/;
            if (this.value && !phoneRegex.test(this.value)) {
                this.setCustomValidity('Teléfono no válido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Confirmación en campos numéricos negativos
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            if (this.hasAttribute('min') && parseFloat(this.value) < parseFloat(this.min)) {
                this.value = this.min;
            }
        });
    });

    // Previsualización de imagen si existe
    const imageInput = document.querySelector('input[type="file"]');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const preview = document.getElementById('imagePreview');
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Validación de número de tarjeta (modal cartera)
    const cardInput = document.querySelector('input[name="numero"]');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '').substring(0, 16);
            this.value = value.replace(/(.{4})/g, '$1 ').trim();
        });
        cardInput.addEventListener('blur', function() {
            const digits = this.value.replace(/\s/g, '');
            if (digits.length > 0 && digits.length !== 16) {
                this.setCustomValidity('El número debe tener 16 dígitos');
                this.classList.add('is-invalid');
            } else if (digits.length > 0 && !/^[0-9]{16}$/.test(digits)) {
                this.setCustomValidity('Solo se permiten dígitos');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }

    // Validación de fecha de expiración MM/AAAA
    const expInput = document.querySelector('input[name="expiracion"]');
    if (expInput) {
        expInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '').substring(0, 6);
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            this.value = value;
        });
        expInput.addEventListener('blur', function() {
            const regex = /^(0[1-9]|1[0-2])\/(20[2-9][0-9])$/;
            if (this.value && !regex.test(this.value)) {
                this.setCustomValidity('Formato inválido (MM/AAAA)');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }

    // Validación de nombre del titular (solo letras)
    const titularInput = document.querySelector('input[name="titular"]');
    if (titularInput) {
        titularInput.addEventListener('blur', function() {
            if (this.value && this.value.trim().length < 3) {
                this.setCustomValidity('Nombre del titular muy corto');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }
});
