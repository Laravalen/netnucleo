function openModal(id){const x=document.getElementById(id);if(x)x.classList.add('show')}
function closeModal(id){const x=document.getElementById(id);if(x)x.classList.remove('show')}
window.addEventListener('click',e=>{if(e.target.classList.contains('modal'))e.target.classList.remove('show')})
function togglePassword(btn){const i=btn.parentElement.querySelector('input');i.type=i.type==='password'?'text':'password'}
function filterCards(input,selector){const q=document.getElementById(input).value.toLowerCase();document.querySelectorAll(selector).forEach(x=>x.style.display=x.innerText.toLowerCase().includes(q)?'':'none')}
function editTurma(r){openModal('turmaModal');document.getElementById('turmaTitle').innerText='Editar Turma';[['turma_id','id'],['turma_codigo','codigo'],['turma_nome','nome'],['turma_curso','curso_id'],['turma_periodo','periodo'],['turma_inicio','data_inicio'],['turma_fim','data_fim'],['turma_cap','capacidade'],['turma_status','status']].forEach(([a,b])=>document.getElementById(a).value=r[b]??'')}
function editCurso(r){openModal('cursoModal');[['curso_id','id'],['curso_codigo','codigo'],['curso_nome','nome'],['curso_desc','descricao'],['curso_carga','carga_horaria']].forEach(([a,b])=>document.getElementById(a).value=r[b]??'')}
function editDisciplina(r){openModal('disciplinaModal');[['disciplina_id','id'],['disciplina_codigo','codigo'],['disciplina_nome','nome'],['disciplina_desc','descricao'],['disciplina_carga','carga_horaria']].forEach(([a,b])=>document.getElementById(a).value=r[b]??'')}
function editSala(r){openModal('salaModal');[['sala_id','id'],['sala_nome','nome'],['sala_bloco','bloco'],['sala_tipo','tipo'],['sala_cap','capacidade'],['sala_desc','descricao'],['sala_status','status']].forEach(([a,b])=>document.getElementById(a).value=r[b]??'')}
function viewMov(r){openModal('viewMov');document.getElementById('movDetails').innerHTML='<div class="detail-list"><p><b>Tipo:</b> '+escapeHtml(r.tipo)+'</p><p><b>Data:</b> '+escapeHtml(r.data_movimentacao)+'</p><p><b>Turma:</b> '+escapeHtml(r.turma)+'</p><p><b>Motivo:</b> '+escapeHtml(r.motivo)+'</p><p><b>Status:</b> '+escapeHtml(r.status)+'</p></div>'}
function escapeHtml(s){return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
function exportTable(id,name){const t=document.getElementById(id);if(!t)return;const rows=[...t.querySelectorAll('tr')].map(r=>[...r.children].map(c=>'"'+c.innerText.replaceAll('"','""').replaceAll('\n',' ')+'"').join(';')).join('\n');const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([rows],{type:'text/csv;charset=utf-8'}));a.download=name;a.click()}

function editAula(r){
    openModal('aulaModal');
    const fields = {
        aula_id: 'id',
        aula_data: 'data_aula',
        aula_periodo: 'periodo',
        aula_turma: 'turma_id',
        aula_instrutor: 'instrutor_id',
        aula_disciplina: 'disciplina_id',
        aula_sala: 'sala_id',
        aula_inicio: 'inicio',
        aula_fim: 'fim',
        aula_tipo: 'tipo',
        aula_status: 'status',
        aula_conteudo: 'conteudo',
        aula_observacoes: 'observacoes'
    };
    Object.entries(fields).forEach(([elementId, key]) => {
        const el = document.getElementById(elementId);
        if (el) el.value = r[key] ?? '';
    });
}

function showFlash(type, message){
    const box = document.createElement('div');
    box.className = 'alert ' + (type === 'success' ? 'success' : 'danger') + ' flash-fixed';
    box.textContent = message;
    document.body.appendChild(box);
    setTimeout(() => box.classList.add('hide'), 3500);
    setTimeout(() => box.remove(), 3900);
}
