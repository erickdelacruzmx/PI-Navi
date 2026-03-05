document.addEventListener('DOMContentLoaded', () => {
    const setAttrs = (selector, attrs) => {
        document.querySelectorAll(selector).forEach((node) => {
            Object.entries(attrs).forEach(([key, value]) => {
                node.setAttribute(key, value);
            });
        });
    };

    // Align HTML validation with account/user constraints and field intent.
    setAttrs('input[name="login"], input[name="email"], #id_email', {
        maxlength: '254',
        inputmode: 'email',
        autocapitalize: 'none',
        spellcheck: 'false',
    });

    setAttrs('input[name="first_name"]', {
        maxlength: '30',
        autocomplete: 'given-name',
    });

    setAttrs('input[name="last_name"]', {
        maxlength: '30',
        autocomplete: 'family-name',
    });

    setAttrs('input[type="password"]', {
        minlength: '8',
        maxlength: '128',
        autocapitalize: 'none',
        spellcheck: 'false',
    });

    const primaryPassword = document.getElementById('register-password');
    const hiddenConfirm = document.getElementById('register-confirm-password-hidden');
    if (primaryPassword && hiddenConfirm) {
        const syncPassword = () => {
            hiddenConfirm.value = primaryPassword.value;
        };

        primaryPassword.addEventListener('input', syncPassword);
        primaryPassword.addEventListener('change', syncPassword);
        syncPassword();
    }

    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach((input, index) => {
        if (input.dataset.toggleReady === 'true') {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'password-field';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle-btn';
        button.setAttribute('aria-label', 'Mostrar contrasena');
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';

        const btnId = `password-toggle-${index}`;
        button.id = btnId;
        input.setAttribute('aria-describedby', [input.getAttribute('aria-describedby'), btnId].filter(Boolean).join(' '));

        button.addEventListener('click', () => {
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(reveal));
            button.setAttribute('aria-label', reveal ? 'Ocultar contrasena' : 'Mostrar contrasena');
            button.innerHTML = reveal
                ? '<i class="fas fa-eye-slash" aria-hidden="true"></i>'
                : '<i class="fas fa-eye" aria-hidden="true"></i>';
        });

        wrapper.appendChild(button);
        input.dataset.toggleReady = 'true';
    });
});
