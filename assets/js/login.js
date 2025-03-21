document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnLogin');
    const spinner = btn.querySelector('.spinner-border');
    const btnText = btn.querySelector('.btn-text');
    
    spinner.classList.remove('d-none');
    btnText.textContent = 'Entrando...';
    btn.disabled = true;

    const formData = new FormData(this);

    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const alertBox = document.getElementById('loginAlert');
            const alertMessage = document.getElementById('loginAlertMessage');
            
            // Esconder alerta existente antes de mostrar novo
            alertBox.classList.add('d-none');
            
            // Forçar reflow para reiniciar animação
            void alertBox.offsetWidth;
            
            alertMessage.textContent = data.message;
            alertBox.classList.add('show');
            alertBox.classList.remove('d-none');
            
            // Animar o alerta
            alertBox.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                alertBox.style.animation = '';
            }, 500);
            
            // Limpar o campo de senha
            document.getElementById('senha').value = '';
            document.getElementById('senha').focus();
            
            // Auto-ocultar após 5 segundos
            setTimeout(() => {
                alertBox.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => {
                    alertBox.classList.add('d-none');
                }, 300);
            }, 5000);
        }
        spinner.classList.add('d-none');
        btnText.textContent = 'Entrar';
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Erro:', error);
        const alertBox = document.getElementById('loginAlert');
        const alertMessage = document.getElementById('loginAlertMessage');
        alertMessage.textContent = 'Erro ao processar login. Por favor, tente novamente.';
        alertBox.classList.remove('d-none');
        alertBox.style.animation = 'shake 0.5s ease-in-out';
        spinner.classList.add('d-none');
        btnText.textContent = 'Entrar';
        btn.disabled = false;
    });
}); 