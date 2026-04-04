(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('#exampleInputPassword');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });

    // Global Native Validation Indonesian Translation
    document.addEventListener('invalid', (function () {
        return function (e) {
            e.preventDefault();
            const target = e.target;
            if (target.validity.valueMissing) {
                target.setCustomValidity('Harap isi bidang ini.');
            } else if (target.validity.typeMismatch && target.type === 'email') {
                target.setCustomValidity('Harap masukkan alamat email yang valid.');
            }
        };
    })(), true);

    document.addEventListener('input', function (e) {
        e.target.setCustomValidity('');
    });
})();
