// Inicialização da "Base de Dados" local (localStorage)
function initDB() {
    if (!localStorage.getItem('sys_admins')) {
        const defaultAdmins = [
            { id: 1, nome: 'Administrador Principal', email: 'admin@senaisesi.br' }
        ];
        localStorage.setItem('sys_admins', JSON.stringify(defaultAdmins));
    }
}

// Mensagens de Alerta
function showAlert(msg, isSuccess = true) {
    const box = document.getElementById('alert-box');
    if (!box) return;
    box.className = `alert ${isSuccess ? 'alert-success' : 'alert-danger'}`;
    box.innerText = msg;
    box.style.display = 'block';
    setTimeout(() => { box.style.display = 'none'; }, 3000);
}

// Login (Substitui autenticação PHP)
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const user = document.getElementById('usuario').value;
    const pass = document.getElementById('senha').value;

    if (user === 'admin' && pass === '123') {
        sessionStorage.setItem('authenticated', 'true');
        loadDashboard();
        showAlert('Login efetuado com sucesso!');
    } else {
        showAlert('Utilizador ou palavra-passe incorretos!', false);
    }
});

// Encerrar Sessão
function logout() {
    sessionStorage.removeItem('authenticated');
    document.getElementById('view-dashboard').classList.add('hidden');
    document.getElementById('view-login').classList.remove('hidden');
    document.getElementById('loginForm').reset();
    showAlert('Sessão encerrada.');
}

// Carregar o Dashboard
function loadDashboard() {
    document.getElementById('view-login').classList.add('hidden');
    document.getElementById('view-dashboard').classList.remove('hidden');
    renderAdmins();
}

// Alternar entre Abas
function switchTab(tab) {
    document.getElementById('tab-admins').classList.add('hidden');
    document.getElementById('tab-cadastros').classList.add('hidden');
    document.getElementById('btn-tab-admins').classList.remove('active');
    document.getElementById('btn-tab-cadastros').classList.remove('active');

    if (tab === 'admins') {
        document.getElementById('tab-admins').classList.remove('hidden');
        document.getElementById('btn-tab-admins').classList.add('active');
    } else if (tab === 'cadastros') {
        document.getElementById('tab-cadastros').classList.remove('hidden');
        document.getElementById('btn-tab-cadastros').classList.add('active');
    }
}

// CRUD de Administradores no localStorage
function renderAdmins() {
    const admins = JSON.parse(localStorage.getItem('sys_admins') || '[]');
    const tbody = document.getElementById('table-admins-body');
    if (!tbody) return;

    tbody.innerHTML = '';
    admins.forEach(admin => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${admin.id}</td>
            <td>${admin.nome}</td>
            <td>${admin.email}</td>
            <td>
                <button onclick="deleteAdmin(${admin.id})" style="background-color: #dc3545; padding: 5px 10px; font-size: 0.8rem;">Remover</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

document.getElementById('adminForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const nome = document.getElementById('admin-nome').value;
    const email = document.getElementById('admin-email').value;

    const admins = JSON.parse(localStorage.getItem('sys_admins') || '[]');
    const newAdmin = {
        id: admins.length ? admins[admins.length - 1].id + 1 : 1,
        nome: nome,
        email: email
    };

    admins.push(newAdmin);
    localStorage.setItem('sys_admins', JSON.stringify(admins));
    this.reset();
    renderAdmins();
    showAlert('Administrador adicionado com sucesso!');
});

function deleteAdmin(id) {
    let admins = JSON.parse(localStorage.getItem('sys_admins') || '[]');
    admins = admins.filter(a => a.id !== id);
    localStorage.setItem('sys_admins', JSON.stringify(admins));
    renderAdmins();
    showAlert('Administrador removido com sucesso!');
}

// Executar verificação ao carregar a página
window.onload = function() {
    initDB();
    if (sessionStorage.getItem('authenticated') === 'true') {
        loadDashboard();
    }
};
