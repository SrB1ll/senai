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
            alert(data.message);
            spinner.classList.add('d-none');
            btnText.textContent = 'Entrar';
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar login. Por favor, tente novamente.');
        spinner.classList.add('d-none');
        btnText.textContent = 'Entrar';
        btn.disabled = false;
    });
}); 