let currentUser = null;

// Execução ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    // Autenticação
    document.getElementById('form-login').addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('api.php?action=login', {
            method: 'POST',
            body: JSON.stringify({
                cpf: document.getElementById('login-cpf').value,
                senha: document.getElementById('login-senha').value
            })
        });
        const data = await res.json();
        
        if (data.success) {
            currentUser = data.user;
            carregarPainel();
        } else {
            alert(data.message);
        }
    });

    // Envio de formulário de aula
    document.getElementById('form-aula').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            id: document.getElementById('aula-id').value,
            turma: document.getElementById('aula-turma').value,
            materia: document.getElementById('aula-materia').value,
            data_aula: document.getElementById('aula-data').value,
            horario: document.getElementById('aula-horario').value,
            conteudo: document.getElementById('aula-conteudo').value,
            status: document.getElementById('aula-status').value
        };

        const res = await fetch('api.php?action=save_aula', {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        
        const result = await res.json();
        if (result.success) {
            document.getElementById('form-aula').reset();
            document.getElementById('aula-id').value = '';
            carregarAulas();
        } else {
            alert(result.message);
        }
    });

    // Encerramento de sessão
    document.getElementById('btn-logout').addEventListener('click', async () => {
        await fetch('api.php?action=logout');
        location.reload();
    });
});

// Renderização do Painel do Usuário
function carregarPainel() {
    document.getElementById('sec-login').classList.add('hidden');
    document.getElementById('sec-painel').classList.remove('hidden');
    
    document.getElementById('user-name').innerText = currentUser.nome;
    const badge = document.getElementById('user-perfil');
    badge.innerText = currentUser.perfil.toUpperCase();
    badge.className = `badge badge-${currentUser.perfil}`;

    // Habilita controles de edição apenas se for Instrutor
    if (currentUser.perfil === 'instrutor') {
        document.getElementById('editor-aula').classList.remove('hidden');
        document.querySelectorAll('.col-acoes').forEach(el => el.classList.remove('hidden'));
    } else {
        document.getElementById('editor-aula').classList.add('hidden');
        document.querySelectorAll('.col-acoes').forEach(el => el.classList.add('hidden'));
    }

    carregarAulas();
}

// Renderização das Aulas
async function carregarAulas() {
    const res = await fetch('api.php?action=get_aulas');
    const aulas = await res.json();
    const tbody = document.getElementById('lista-aulas');
    tbody.innerHTML = '';

    aulas.forEach(aula => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${aula.turma}</td>
            <td>${aula.materia}</td>
            <td>${aula.data_aula} às ${aula.horario}</td>
            <td>${aula.conteudo}</td>
            <td>${aula.status}</td>
            ${currentUser.perfil === 'instrutor' ? `
                <td class="col-acoes">
                    <button class="btn" onclick="prepararEdicao(${JSON.stringify(aula).replace(/"/g, '&quot;')})" aria-label="Editar Aula">Editar</button>
                    <button class="btn btn-danger" onclick="excluirAula(${aula.id})" aria-label="Excluir Aula">Excluir</button>
                </td>
            ` : ''}
        `;
        tbody.appendChild(tr);
    });
}

function prepararEdicao(aula) {
    document.getElementById('aula-id').value = aula.id;
    document.getElementById('aula-turma').value = aula.turma;
    document.getElementById('aula-materia').value = aula.materia;
    document.getElementById('aula-data').value = aula.data_aula;
    document.getElementById('aula-horario').value = aula.horario;
    document.getElementById('aula-conteudo').value = aula.conteudo;
    document.getElementById('aula-status').value = aula.status;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluirAula(id) {
    if (confirm('Tem certeza que deseja excluir esta aula?')) {
        const res = await fetch(`api.php?action=delete_aula&id=${id}`);
        const result = await res.json();
        if (result.success) {
            carregarAulas();
        } else {
            alert(result.message);
        }
    }
}