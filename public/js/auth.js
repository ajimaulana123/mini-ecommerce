document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const emailInput = document.getElementById('email');

    // Toggle password visibility
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Change icon
            const icon = this.querySelector('svg');
            if (type === 'text') {
                icon.innerHTML = `
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    `;
            } else {
                icon.innerHTML = `
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    `;
            }
        });
    }

    // Auto focus email input
    if (emailInput) {
        emailInput.focus();
    }

    // Add floating label functionality
    const formInputs = document.querySelectorAll('.form-input');
    formInputs.forEach(input => {
        // Check on load
        if (input.value) {
            input.dispatchEvent(new Event('input'));
        }

        // Check on input
        input.addEventListener('input', function () {
            const label = this.parentElement.querySelector('.input-label');
            if (this.value) {
                label.style.top = '8px';
                label.style.fontSize = '13px';
            } else {
                label.style.top = '20px';
                label.style.fontSize = '16px';
            }
        });
    });
});