/* =========================================================
   INICIALIZAÇÃO DE DADOS LOCAIS (Simula o Banco de Dados)
   ========================================================= */
function initDatabase() {
    if (!localStorage.getItem('sys_db_initialized')) {
        const defaultUsers = [
            { cpf: '000.000.000-00', senha: '123', nome: 'Administrador SENAI', perfil: 'Administrador' }
        ];
        localStorage.setItem('users', JSON.stringify(defaultUsers));
        localStorage.setItem('sys_db_initialized', 'true');
    }
}
initDatabase();

/* =========================================================
   SISTEMA DE AUTENTICAÇÃO (Client-side)
   ========================================================= */
function loginUser(cpf, senha) {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const user = users.find(u => u.cpf === cpf && u.senha === senha);
    if (user) {
        localStorage.setItem('logged_user', JSON.stringify(user));
        return true;
    }
    return false;
}

function checkAuth() {
    const user = localStorage.getItem('logged_user');
    if (!user) {
        window.location.href = 'index.html';
    }
}

function getLoggedUser() {
    return JSON.parse(localStorage.getItem('logged_user'));
}

function logoutUser() {
    localStorage.removeItem('logged_user');
    window.location.href = 'index.html';
}

/* =========================================================
   FUNÇÕES ORIGINAIS DO SEU APP.JS
   ========================================================= */
function openModal(id){const x=document.getElementById(id);if(x)x.classList.add('show')}
function closeModal(id){const x=document.getElementById(id);if(x)x.classList.remove('show')}
window.addEventListener('click',e=>{if(e.target.classList.contains('modal'))e.target.classList.remove('show')})

function togglePassword(btn){
    const i=btn.parentElement.querySelector('input');
    i.type=i.type==='password'?'text':'password';
}

function filterCards(input,selector){
    const q=document.getElementById(input).value.toLowerCase();
    document.querySelectorAll(selector).forEach(x=>x.style.display=x.innerText.toLowerCase().includes(q)?'':'none');
}

function escapeHtml(s){
    return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}

function showFlash(type, message){
    const box = document.createElement('div');
    box.className = 'alert ' + (type === 'success' ? 'success' : 'danger') + ' flash-fixed';
    box.textContent = message;
    document.body.appendChild(box);
    setTimeout(() => box.classList.add('hide'), 3500);
    setTimeout(() => box.remove(), 3900);
}
