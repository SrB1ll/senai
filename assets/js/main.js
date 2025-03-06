// Validação de formulários
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para o telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    }

    // Validação de matrícula
    const matriculaInput = document.getElementById('matricula');
    if (matriculaInput) {
        matriculaInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8);
            }
        });
    }

    // Data mínima para agendamentos
    const dataInput = document.getElementById('data_pesquisa');
    if (dataInput) {
        const today = new Date().toISOString().split('T')[0];
        dataInput.min = today;
        
        // Validação adicional no envio do formulário
        dataInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0,0,0,0);
            
            if (selectedDate < today) {
                alert('Por favor, selecione uma data futura.');
                this.value = '';
            }
        });
        
        // Máximo 30 dias no futuro
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + 30);
        dataInput.max = maxDate.toISOString().split('T')[0];
    }

    // Data mínima para agendamentos do instrutor
    const dataInstrutor = document.getElementById('data');
    if (dataInstrutor) {
        const today = new Date().toISOString().split('T')[0];
        dataInstrutor.min = today;
        
        dataInstrutor.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0,0,0,0);
            
            if (selectedDate < today) {
                alert('Por favor, selecione uma data futura.');
                this.value = '';
            }
        });
    }

    // Toast notifications
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} show`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Feedback visual nos formulários
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                showToast('Por favor, preencha todos os campos corretamente.', 'error');
            }
        });
    });
}); 