
(() => {
"use strict";
const STORE="sga_pages_v2", SESSION="sga_pages_session";
const initialPassword="SesiSenai@2026";
const esc=s=>String(s??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m]));
const digits=s=>String(s||"").replace(/\D/g,"");
const id=()=>Date.now()+Math.floor(Math.random()*1000);
const today=()=>{const d=new Date();return d.toISOString().slice(0,10)};
const plus=n=>{const d=new Date();d.setDate(d.getDate()+n);return d.toISOString().slice(0,10)};
const fmt=d=>d?new Date(d+"T12:00:00").toLocaleDateString("pt-BR"):"-";
const dayName=d=>["domingo","segunda-feira","terça-feira","quarta-feira","quinta-feira","sexta-feira","sábado"][new Date(d+"T12:00:00").getDay()];
const cap=s=>String(s||"").toLowerCase().replace(/(^|\s)\S/g,x=>x.toUpperCase());
const initials=n=>(n||"").trim().split(/\s+/).slice(0,2).map(x=>x[0]).join("").toUpperCase();

function seed(){
 return {
 usuarios:[
  {id:1,cpf:"123.456.789-00",nome:"Administrador do Sistema",email:"admin@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"ADMIN",status:"ATIVO"},
  {id:2,cpf:"111.111.111-11",nome:"Aluno Demonstração",email:"aluno@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"ALUNO",status:"ATIVO"},
  {id:3,cpf:"222.222.222-22",nome:"Carlos Mendes",email:"carlos@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"INSTRUTOR",status:"ATIVO"},
  {id:4,cpf:"333.333.333-33",nome:"Ana Paula Souza",email:"ana@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"INSTRUTOR",status:"ATIVO"},
  {id:5,cpf:"444.444.444-44",nome:"Roberto Lima",email:"roberto@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"INSTRUTOR",status:"ATIVO"},
  {id:6,cpf:"555.555.555-55",nome:"Fernanda Castro",email:"fernanda@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"INSTRUTOR",status:"ATIVO"},
  {id:7,cpf:"666.666.666-66",nome:"Marcos Oliveira",email:"marcos@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"INSTRUTOR",status:"ATIVO"},
  {id:8,cpf:"777.777.777-77",nome:"Juliana Ferreira",email:"juliana@senai.local",senha:initialPassword,senha_provisoria:1,perfil:"INSTRUTOR",status:"ATIVO"}
 ],
 alunos:[{id:1,usuario_id:2,matricula:"ALU-2025-001",data_nascimento:"2008-05-10"}],
 instrutores:[3,4,5,6,7,8].map((uid,i)=>({id:i+1,usuario_id:uid,cpf:["222.222.222-22","333.333.333-33","444.444.444-44","555.555.555-55","666.666.666-66","777.777.777-77"][i],area:"Docência",ativo:1})),
 cursos:[
  {id:1,codigo:"ELE",nome:"Técnico em Eletrotécnica",descricao:"",carga_horaria:1200},
  {id:2,codigo:"MEC",nome:"Técnico em Mecânica",descricao:"",carga_horaria:1200},
  {id:3,codigo:"TI",nome:"Técnico em Informática",descricao:"",carga_horaria:1200},
  {id:4,codigo:"SEG",nome:"Técnico em Segurança",descricao:"",carga_horaria:1200}],
 disciplinas:[
  {id:1,codigo:"ELE01",nome:"Eletrotécnica Industrial",descricao:"",carga_horaria:80},
  {id:2,codigo:"CNC01",nome:"Programação CNC",descricao:"",carga_horaria:80},
  {id:3,codigo:"RED01",nome:"Redes de Computadores",descricao:"",carga_horaria:80},
  {id:4,codigo:"DES01",nome:"Desenho Técnico",descricao:"",carga_horaria:60},
  {id:5,codigo:"AUT01",nome:"Automação Industrial",descricao:"",carga_horaria:80},
  {id:6,codigo:"INS01",nome:"Instalações Elétricas",descricao:"",carga_horaria:80}],
 turmas:[
  {id:1,codigo:"ELE-2025-A",nome:"ELE-2025-A",curso_id:1,periodo:"MANHA",data_inicio:plus(-30),data_fim:plus(90),capacidade:30,status:"ATIVA"},
  {id:2,codigo:"MEC-2025-A",nome:"MEC-2025-A",curso_id:2,periodo:"TARDE",data_inicio:plus(-30),data_fim:plus(90),capacidade:30,status:"ATIVA"},
  {id:3,codigo:"MEC-2025-B",nome:"MEC-2025-B",curso_id:2,periodo:"MANHA",data_inicio:plus(-30),data_fim:plus(90),capacidade:30,status:"ATIVA"},
  {id:4,codigo:"TI-2025-A",nome:"TI-2025-A",curso_id:3,periodo:"NOITE",data_inicio:plus(-30),data_fim:plus(100),capacidade:30,status:"ATIVA"},
  {id:5,codigo:"TI-2025-B",nome:"TI-2025-B",curso_id:3,periodo:"NOITE",data_inicio:plus(-30),data_fim:plus(100),capacidade:30,status:"ATIVA"},
  {id:6,codigo:"SEG-2025-A",nome:"SEG-2025-A",curso_id:4,periodo:"NOITE",data_inicio:plus(-30),data_fim:plus(90),capacidade:30,status:"ATIVA"},
  {id:7,codigo:"ELE-2024-B",nome:"ELE-2024-B",curso_id:1,periodo:"TARDE",data_inicio:plus(-400),data_fim:plus(-30),capacidade:30,status:"ENCERRADA"}],
 salas:[
  {id:1,codigo:"LAB03",nome:"Lab 03",bloco:"Bloco A",tipo:"LABORATORIO",capacidade:30,descricao:"Bancadas, multímetros, fontes de tensão",status:"RESERVADA"},
  {id:2,codigo:"LABTI02",nome:"Lab TI 02",bloco:"Bloco C",tipo:"LABORATORIO",capacidade:28,descricao:"30 computadores, servidor de laboratório",status:"DISPONIVEL"},
  {id:3,codigo:"LABMEC01",nome:"Lab Mec 01",bloco:"Bloco B",tipo:"LABORATORIO",capacidade:28,descricao:"Tornos, fresadoras, ferramentas manuais",status:"OCUPADA"},
  {id:4,codigo:"LABTI01",nome:"Lab TI 01",bloco:"Bloco C",tipo:"LABORATORIO",capacidade:24,descricao:"Laboratório de informática",status:"RESERVADA"},
  {id:5,codigo:"SALA05",nome:"Sala 05",bloco:"Bloco D",tipo:"SALA_TEORICA",capacidade:40,descricao:"Projetor 4K, quadro branco, ar-condicionado",status:"DISPONIVEL"},
  {id:6,codigo:"AUD01",nome:"Auditório",bloco:"Bloco Principal",tipo:"AUDITORIO",capacidade:120,descricao:"Auditório principal",status:"DISPONIVEL"}],
 aulas:[
  {id:1,turma_id:1,disciplina_id:1,instrutor_id:1,sala_id:1,data_aula:today(),periodo:"MANHA",inicio:"07:00",fim:"08:40",tipo:"TEORICA",status:"PLANEJADA",conteudo:"Circuitos elétricos e segurança em laboratório",observacoes:""},
  {id:2,turma_id:4,disciplina_id:2,instrutor_id:2,sala_id:3,data_aula:today(),periodo:"MANHA",inicio:"07:00",fim:"08:40",tipo:"PRATICA",status:"PLANEJADA",conteudo:"Programação CNC",observacoes:""},
  {id:3,turma_id:4,disciplina_id:3,instrutor_id:3,sala_id:2,data_aula:today(),periodo:"MANHA",inicio:"07:00",fim:"08:40",tipo:"PRATICA",status:"PLANEJADA",conteudo:"Redes de computadores",observacoes:""},
  {id:4,turma_id:2,disciplina_id:4,instrutor_id:4,sala_id:5,data_aula:today(),periodo:"TARDE",inicio:"13:00",fim:"14:40",tipo:"TEORICA",status:"PLANEJADA",conteudo:"Desenho técnico",observacoes:""},
  {id:5,turma_id:3,disciplina_id:5,instrutor_id:5,sala_id:3,data_aula:today(),periodo:"TARDE",inicio:"13:00",fim:"14:40",tipo:"PRATICA",status:"PLANEJADA",conteudo:"Automação industrial",observacoes:""},
  {id:6,turma_id:1,disciplina_id:6,instrutor_id:1,sala_id:1,data_aula:plus(1),periodo:"MANHA",inicio:"07:00",fim:"08:40",tipo:"PRATICA",status:"PLANEJADA",conteudo:"Instalações elétricas",observacoes:""},
  {id:7,turma_id:4,disciplina_id:3,instrutor_id:3,sala_id:2,data_aula:plus(2),periodo:"NOITE",inicio:"19:00",fim:"20:40",tipo:"PRATICA",status:"PLANEJADA",conteudo:"Configuração de redes locais",observacoes:""},
  {id:8,turma_id:2,disciplina_id:4,instrutor_id:4,sala_id:5,data_aula:plus(3),periodo:"TARDE",inicio:"13:00",fim:"14:40",tipo:"TEORICA",status:"PLANEJADA",conteudo:"Leitura e interpretação de desenho técnico",observacoes:""}],
 eventos:[
  {id:1,titulo:"Reunião pedagógica",descricao:"Reunião de acompanhamento das turmas.",data_inicio:today(),data_fim:today(),tipo:"PEDAGOGICO",ativo:1},
  {id:2,titulo:"Avaliação prática",descricao:"Avaliação prática prevista para a turma.",data_inicio:plus(4),data_fim:plus(4),tipo:"AVALIACAO",ativo:1},
  {id:3,titulo:"Semana acadêmica",descricao:"Atividades acadêmicas e palestras.",data_inicio:plus(8),data_fim:plus(10),tipo:"ACADEMICO",ativo:1}],
 movimentacoes:[{id:1,tipo:"TROCA_HORARIO",status:"PENDENTE",data_movimentacao:plus(5),turma_id:1,motivo:"troca de sala para atividade especial.",criado_por:1}],
 auditoria:[]
 };
}
let db;
try{db=JSON.parse(localStorage.getItem(STORE)||"null")}catch(e){}
if(!db){db=seed();localStorage.setItem(STORE,JSON.stringify(db))}
const save=()=>localStorage.setItem(STORE,JSON.stringify(db));
const session=()=>{try{return JSON.parse(localStorage.getItem(SESSION)||"null")}catch(e){return null}};
const setSession=u=>localStorage.setItem(SESSION,JSON.stringify(u));
const logout=()=>localStorage.removeItem(SESSION);
const me=()=>session(), isAdmin=()=>me()?.perfil==="ADMIN";
const U=id=>db.usuarios.find(x=>x.id==id), I=id=>db.instrutores.find(x=>x.id==id), T=id=>db.turmas.find(x=>x.id==id);
const D=id=>db.disciplinas.find(x=>x.id==id), S=id=>db.salas.find(x=>x.id==id), C=id=>db.cursos.find(x=>x.id==id);
const uname=id=>U(I(id)?.usuario_id)?.nome||"-";
const flash=(type,msg)=>{const x=document.createElement("div");x.className="alert "+(type==="success"?"success":"danger")+" flash-fixed";x.textContent=msg;document.body.appendChild(x);setTimeout(()=>x.remove(),3500)};
const modal=(title,body)=>{document.getElementById("modalHost").innerHTML=`<div class="modal show" id="modal"><div class="modal-box"><button class="modal-close" onclick="closeModal()">×</button><h2>${title}</h2>${body}</div></div>`};
window.closeModal=()=>document.getElementById("modal")?.remove();
window.togglePassword=b=>{const i=b.parentElement.querySelector("input");i.type=i.type==="password"?"text":"password"};

function login(){
 document.getElementById("app").innerHTML=`<div class="login-page">
 <section class="login-brand"><div class="brand login-brand-logos"><div class="brand-logos"><img src="assets/img/logo-sesi.png" class="brand-logo brand-logo-sesi" alt="SESI"><img src="assets/img/logo-senai.png" class="brand-logo brand-logo-senai" alt="SENAI"></div><div class="brand-copy"><small>Sistema de Gestão Acadêmica</small></div></div>
 <div class="login-quote">"Educação profissional que transforma vidas e impulsiona o desenvolvimento do Brasil."</div>
 <div class="login-features"><span>▣ &nbsp; Gerenciamento completo de cursos e turmas</span><span>□ &nbsp; Controle de horários e frequência de aulas</span><span>▥ &nbsp; Relatórios e indicadores de desempenho</span><span>♢ &nbsp; Acesso seguro com perfis por cargo</span></div><div class="login-footer">© 2025 FIEMG · SESI · SENAI · Todos os direitos reservados</div></section>
 <section class="login-card"><div class="card-inner"><h2>Acesse sua conta</h2><p class="muted">Informe seu CPF e sua senha para acessar o sistema.</p><div id="loginError"></div>
 <form id="loginForm"><label>CPF<input name="cpf" placeholder="000.000.000-00" required autofocus></label><label>SENHA<div class="password"><input name="senha" type="password" placeholder="••••••••" required><button type="button" class="eye" onclick="togglePassword(this)">◉</button></div></label><div class="row-between"><span class="hint">Primeiro acesso exige troca de senha.</span></div><button class="btn primary full">Entrar</button></form>
 <div class="notice"><b>Acesso de demonstração</b><br>CPF Admin: 123.456.789-00<br>Senha inicial: ${initialPassword}</div></div></section></div>`;
 document.getElementById("loginForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target),u=db.usuarios.find(x=>digits(x.cpf)===digits(f.get("cpf"))&&x.status==="ATIVO");if(!u||u.senha!==f.get("senha")){document.getElementById("loginError").innerHTML='<div class="alert danger">Usuário/CPF ou senha inválidos.</div>';return}setSession(u);location.hash=u.senha_provisoria?"senha":"dashboard";render()};
}

function shell(page,title,sub,body){
 const u=me();
 document.getElementById("app").innerHTML=`<div class="app"><aside class="sidebar"><div class="brand"><div class="brand-logos"><img src="assets/img/logo-sesi.png" class="brand-logo brand-logo-sesi"><img src="assets/img/logo-senai.png" class="brand-logo brand-logo-senai"></div><div class="brand-copy"><small>Gestão Acadêmica</small></div></div>
 <nav class="sidebar-nav"><a href="#dashboard" class="${page==="dashboard"?"active":""}"><span class="nav-ico">▦</span>Painel Geral</a>
 ${isAdmin()?`<div class="nav-label">ADMINISTRAÇÃO</div><a href="#usuarios" class="${page==="usuarios"?"active":""}"><span class="nav-ico">♙</span>Cadastro de Usuários</a><a href="#cadastros" class="${page==="cadastros"?"active":""}"><span class="nav-ico">▣</span>Cadastros</a><a href="#movimentacoes" class="${page==="movimentacoes"?"active":""}"><span class="nav-ico">↔</span>Movimentação</a>`:""}
 <div class="nav-label">CONSULTAS</div><a href="#horarios" class="${page==="horarios"?"active":""}"><span class="nav-ico">□</span>Consulta de Horários</a><a href="#instrutores" class="${page==="instrutores"?"active":""}"><span class="nav-ico">♙</span>Consulta de Instrutores</a><a href="#salas" class="${page==="salas"?"active":""}"><span class="nav-ico">▥</span>Consulta de Salas</a><div class="nav-label">RELATÓRIOS</div><a href="#relatorios" class="${page==="relatorios"?"active":""}"><span class="nav-ico">▥</span>Relatórios</a></nav><a class="logout" href="#logout"><span class="nav-ico">↪</span>Sair</a></aside>
 <main class="main"><header class="topbar"><div class="crumb"><span class="light">SGA</span><span class="light">›</span> ${esc(title||"Sistema Web de Gestão Acadêmica")}</div><div class="top-actions"><input id="search" class="search-top" placeholder="⌕  Pesquisar..."><div class="user-chip"><span class="avatar">${initials(u?.nome)}</span><div>${esc(u?.nome)}<div class="role">${esc(u?.perfil)}</div></div></div></div></header><div class="content">${body}</div></main></div><div id="modalHost"></div>`;
 document.getElementById("search").oninput=e=>{const q=e.target.value.toLowerCase();document.querySelectorAll(".content tr,.content .room-card,.content .agenda-row").forEach(x=>x.style.display=x.innerText.toLowerCase().includes(q)?"":"none")};
}
function dashboard(){
 const d=new Date(), aulas=db.aulas.filter(a=>a.data_aula===today()), ins=db.instrutores.filter(i=>i.ativo&&U(i.usuario_id)?.status==="ATIVO").length, rooms=db.salas.filter(s=>s.status==="DISPONIVEL").length, turmas=db.turmas.filter(t=>t.status==="ATIVA").length;
 const agenda=aulas.slice(0,6).map(a=>`<div class="agenda-row"><div class="time">${a.inicio}</div><div><b>${esc(D(a.disciplina_id)?.nome)}</b><p>${esc(a.tipo)} · ${esc(S(a.sala_id)?.nome)}</p></div><span class="tag">${esc(T(a.turma_id)?.codigo)}</span></div>`).join("")||'<p class="muted">Nenhuma aula programada para hoje.</p>';
 shell("dashboard","Painel Geral","",`<section class="page-head"><div><h1>Painel Geral</h1><p>${dayName(today())}, ${d.getDate()} de ${d.toLocaleString("pt-BR",{month:"long"})} de ${d.getFullYear()}</p></div><a class="btn light" href="#relatorios">▥ Relatórios</a></section>
 <div class="stats"><div class="stat"><span>AULAS HOJE</span><strong>${aulas.length}</strong><small>Programadas para hoje</small></div><div class="stat"><span>INSTRUTORES ATIVOS</span><strong>${ins}</strong><small>Instrutores disponíveis</small></div><div class="stat"><span>SALAS DISPONÍVEIS</span><strong>${rooms}</strong><small>Salas livres no momento</small></div><div class="stat"><span>TURMAS EM ANDAMENTO</span><strong>${turmas}</strong><small>Turmas com status ativo</small></div></div>
 <div class="grid-2"><section class="panel"><div class="panel-head"><h2>Aulas de Hoje</h2><a href="#horarios">Ver todas →</a></div>${agenda}</section><section class="panel"><div class="panel-head"><h2>Acesso Rápido</h2></div><div class="quick"><a href="#horarios">◷<b>Horários</b><small>Consultar aulas</small></a><a href="#instrutores">♙<b>Instrutores</b><small>Ver equipe</small></a><a href="#salas">▤<b>Salas</b><small>Disponibilidade</small></a><a href="#relatorios">▥<b>Relatórios</b><small>Indicadores</small></a>${isAdmin()?'<a href="#usuarios">♙<b>Usuários</b><small>Cadastrar acesso</small></a>':""}</div></section></div>${calendar()}`);
}
function calendar(){
 const now=new Date(), y=now.getFullYear(),m=now.getMonth(),first=new Date(y,m,1),last=new Date(y,m+1,0), days=last.getDate();
 let cells="";for(let i=0;i<first.getDay();i++)cells+='<div class="calendar-day empty"></div>';
 for(let n=1;n<=days;n++){const ds=`${y}-${String(m+1).padStart(2,"0")}-${String(n).padStart(2,"0")}`,ev=[];
 db.aulas.filter(a=>a.data_aula===ds).forEach(a=>{const sala=S(a.sala_id);ev.push({c:sala?.tipo==="LABORATORIO"?"room":"class",t:sala?.tipo==="LABORATORIO"?`${sala.nome} reservado`:D(a.disciplina_id)?.nome,d:`${a.inicio}–${a.fim} · ${T(a.turma_id)?.codigo}`})});
 db.eventos.filter(e=>e.ativo&&e.data_inicio<=ds&&e.data_fim>=ds).forEach(e=>ev.push({c:"academic",t:e.titulo,d:e.tipo}));
 db.movimentacoes.filter(x=>x.data_movimentacao===ds).forEach(x=>ev.push({c:"movement",t:"Movimentação",d:x.motivo}));
 cells+=`<div class="calendar-day ${ds===today()?"today":""}"><div class="calendar-date">${n}</div><div class="calendar-events">${ev.slice(0,4).map(e=>`<div class="calendar-event ${e.c}"><b>${esc(e.t)}</b><small>${esc(e.d)}</small></div>`).join("")}</div></div>`;
 }
 return `<section class="panel calendar-panel"><div class="panel-head calendar-heading"><div><h2>Calendário acadêmico</h2><p class="muted">Aulas, reservas de laboratórios, eventos e movimentações.</p></div><div class="calendar-nav"><strong>${now.toLocaleString("pt-BR",{month:"long"})} de ${y}</strong></div></div><div class="calendar-grid">${["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"].map(x=>`<div class="calendar-weekday">${x}</div>`).join("")}${cells}</div><div class="calendar-legend"><span><i class="legend-dot class"></i>Aula</span><span><i class="legend-dot room"></i>Laboratório reservado</span><span><i class="legend-dot academic"></i>Evento acadêmico</span><span><i class="legend-dot movement"></i>Movimentação</span></div></section>`;
}
function horarios(){
 const rows=db.aulas;
 const body=`<section class="page-head"><div><h1>Consulta de Horários</h1><p>${isAdmin()?"Filtre, edite e gerencie os horários das aulas":"Consulte os horários das aulas — somente visualização"}</p></div><button class="btn light" onclick="window.print()">♧ Imprimir</button></section>
 <div class="panel"><h3>Filtros de Pesquisa</h3><form class="filters" id="filters"><label>DATA INICIAL<input type="date" name="ini" value="${plus(0)}"></label><label>DATA FINAL<input type="date" name="fim" value="${plus(30)}"></label><label>PERÍODO<select name="periodo"><option value="">Todos os períodos</option><option>MANHA</option><option>TARDE</option><option>NOITE</option></select></label><label>INSTRUTOR<select name="instrutor"><option value="">Todos os instrutores</option>${db.instrutores.map(i=>`<option value="${i.id}">${esc(uname(i.id))}</option>`).join("")}</select></label><label>TURMA<select name="turma"><option value="">Todas</option>${db.turmas.map(t=>`<option value="${t.id}">${esc(t.codigo)}</option>`).join("")}</select></label><button class="btn primary">Pesquisar</button><button type="button" class="btn light" onclick="location.hash='horarios'">Limpar</button></form></div>
 <div class="panel"><div class="panel-head"><h2>${rows.length} resultados encontrados</h2><button class="btn light" onclick="exportTable('hTable','horarios.csv')">⇩ Exportar</button></div><div class="table-wrap"><table id="hTable"><thead><tr><th>AULA</th><th>DATA</th><th>DIA</th><th>INSTRUTOR</th><th>MATÉRIA</th><th>SALA</th><th>TURMA</th><th>TIPO</th><th>PERÍODO</th><th>HORÁRIO</th>${isAdmin()?'<th>AÇÕES</th>':""}</tr></thead><tbody>${rows.map(a=>`<tr><td>#${a.id}</td><td>${fmt(a.data_aula)}</td><td>${dayName(a.data_aula)}</td><td>${esc(uname(a.instrutor_id))}</td><td>${esc(D(a.disciplina_id)?.nome)}</td><td>${esc(S(a.sala_id)?.nome)}</td><td>${esc(T(a.turma_id)?.codigo)}</td><td>${cap(a.tipo)}</td><td>${cap(a.periodo)}</td><td>${a.inicio} – ${a.fim}</td>${isAdmin()?`<td><button class="icon" onclick="editAula(${a.id})">✎</button></td>`:""}</tr>`).join("")}</tbody></table></div></div>`;
 shell("horarios","Consulta de Horários","",body);
 document.getElementById("filters").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target),ini=f.get("ini"),fim=f.get("fim"),p=f.get("periodo"),i=f.get("instrutor"),t=f.get("turma");const r=db.aulas.filter(a=>(!ini||a.data_aula>=ini)&&(!fim||a.data_aula<=fim)&&(!p||a.periodo===p)&&(!i||a.instrutor_id==i)&&(!t||a.turma_id==t));document.querySelector("#hTable tbody").innerHTML=r.map(a=>`<tr><td>#${a.id}</td><td>${fmt(a.data_aula)}</td><td>${dayName(a.data_aula)}</td><td>${esc(uname(a.instrutor_id))}</td><td>${esc(D(a.disciplina_id)?.nome)}</td><td>${esc(S(a.sala_id)?.nome)}</td><td>${esc(T(a.turma_id)?.codigo)}</td><td>${cap(a.tipo)}</td><td>${cap(a.periodo)}</td><td>${a.inicio} – ${a.fim}</td>${isAdmin()?`<td><button class="icon" onclick="editAula(${a.id})">✎</button></td>`:""}</tr>`).join("")||'<tr><td colspan="11" class="muted">Nenhum resultado encontrado.</td></tr>';document.querySelector("#hTable").closest(".panel").querySelector("h2").textContent=`${r.length} resultados encontrados`};
}
window.editAula=n=>{if(!isAdmin())return;const a=db.aulas.find(x=>x.id==n);modal("Editar aula",`<form id="aulaForm"><div class="form-grid"><label>Data<input name="data" type="date" value="${a.data_aula}"></label><label>Período<select name="periodo">${["MANHA","TARDE","NOITE"].map(x=>`<option ${a.periodo===x?"selected":""}>${x}</option>`).join("")}</select></label><label>Turma<select name="turma">${db.turmas.map(x=>`<option value="${x.id}" ${a.turma_id==x.id?"selected":""}>${esc(x.codigo)}</option>`).join("")}</select></label><label>Instrutor<select name="instrutor">${db.instrutores.map(x=>`<option value="${x.id}" ${a.instrutor_id==x.id?"selected":""}>${esc(uname(x.id))}</option>`).join("")}</select></label><label>Disciplina<select name="disciplina">${db.disciplinas.map(x=>`<option value="${x.id}" ${a.disciplina_id==x.id?"selected":""}>${esc(x.codigo+" - "+x.nome)}</option>`).join("")}</select></label><label>Sala<select name="sala">${db.salas.map(x=>`<option value="${x.id}" ${a.sala_id==x.id?"selected":""}>${esc(x.nome)}</option>`).join("")}</select></label><label>Início<input name="inicio" type="time" value="${a.inicio}"></label><label>Fim<input name="fim" type="time" value="${a.fim}"></label><label>Tipo<select name="tipo"><option ${a.tipo==="TEORICA"?"selected":""}>TEORICA</option><option ${a.tipo==="PRATICA"?"selected":""}>PRATICA</option></select></label><label>Status<input name="status" value="${esc(a.status)}"></label></div><label>Conteúdo<textarea name="conteudo">${esc(a.conteudo)}</textarea></label><label>Observações<textarea name="obs">${esc(a.observacoes)}</textarea></label><button class="btn primary full">Salvar alterações</button></form>`);document.getElementById("aulaForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target);Object.assign(a,{data_aula:f.get("data"),periodo:f.get("periodo"),turma_id:+f.get("turma"),instrutor_id:+f.get("instrutor"),disciplina_id:+f.get("disciplina"),sala_id:+f.get("sala"),inicio:f.get("inicio"),fim:f.get("fim"),tipo:f.get("tipo"),status:f.get("status"),conteudo:f.get("conteudo"),observacoes:f.get("obs")});save();closeModal();flash("success","Aula atualizada.");render()}};
function instrutores(){
 const body=`<section class="page-head"><div><h1>Consulta de Instrutores</h1><p>Equipe docente e carga de aulas</p></div></section><div class="panel"><div class="toolbar"><input id="q" placeholder="⌕ Pesquisar instrutor..."></div><div class="table-wrap"><table><thead><tr><th>INSTRUTOR</th><th>CPF</th><th>ÁREA</th><th>AULAS</th><th>STATUS</th></tr></thead><tbody>${db.instrutores.map(i=>{const u=U(i.usuario_id),n=db.aulas.filter(a=>a.instrutor_id===i.id).length;return `<tr><td><div class="person"><span class="person-avatar">${initials(u.nome)}</span><b>${esc(u.nome)}</b></div></td><td>${u.cpf}</td><td>${esc(i.area)}</td><td>${n}</td><td><span class="status green">${i.ativo?"Ativo":"Inativo"}</span></td></tr>`}).join("")}</tbody></table></div></div>`;
 shell("instrutores","Consulta de Instrutores","",body);document.getElementById("q").oninput=e=>{const q=e.target.value.toLowerCase();document.querySelectorAll("tbody tr").forEach(r=>r.style.display=r.innerText.toLowerCase().includes(q)?"":"none")};
}
function salas(){
 const body=`<section class="page-head"><div><h1>Consulta de Salas</h1><p>Disponibilidade, capacidade e recursos</p></div>${isAdmin()?'<button class="btn primary" onclick="editSala(0)">＋ Nova sala</button>':""}</section><div class="tabs"><a class="selected">Todas</a><a>Disponíveis</a><a>Ocupadas</a><a>Reservadas</a></div><div class="rooms">${db.salas.map(r=>`<article class="room-card"><div class="room-icon">▤</div><div class="room-body"><div class="room-title"><h3>${esc(r.nome)}</h3><span class="status ${r.status.toLowerCase()}">${cap(r.status)}</span></div><p><b>${esc(r.bloco)}</b> · ${cap(r.tipo)} · ${r.capacidade} lugares</p><p class="muted">${esc(r.descricao)}</p>${isAdmin()?`<button class="btn small" onclick="editSala(${r.id})">✎ Editar sala</button>`:""}</div></article>`).join("")}</div>`;
 shell("salas","Consulta de Salas","",body);
}
window.editSala=n=>{if(!isAdmin())return;const r=n?S(n):{id:0,nome:"",codigo:"",bloco:"",tipo:"LABORATORIO",capacidade:30,descricao:"",status:"DISPONIVEL"};modal(n?"Editar sala":"Nova sala",`<form id="sForm"><div class="form-grid"><label>Nome<input name="nome" value="${esc(r.nome)}" required></label><label>Código<input name="codigo" value="${esc(r.codigo)}"></label><label>Bloco<input name="bloco" value="${esc(r.bloco)}"></label><label>Tipo<select name="tipo">${["LABORATORIO","SALA_TEORICA","AUDITORIO"].map(x=>`<option ${r.tipo===x?"selected":""}>${x}</option>`).join("")}</select></label><label>Capacidade<input name="cap" type="number" value="${r.capacidade}"></label><label>Status<select name="status">${["DISPONIVEL","OCUPADA","RESERVADA","MANUTENCAO"].map(x=>`<option ${r.status===x?"selected":""}>${x}</option>`).join("")}</select></label></div><label>Descrição<textarea name="desc">${esc(r.descricao)}</textarea></label><button class="btn primary full">Salvar sala</button></form>`);document.getElementById("sForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target),o={id:r.id||id(),nome:f.get("nome"),codigo:f.get("codigo"),bloco:f.get("bloco"),tipo:f.get("tipo"),capacidade:+f.get("cap"),descricao:f.get("desc"),status:f.get("status")};if(n)Object.assign(r,o);else db.salas.push(o);save();closeModal();flash("success","Sala salva.");render()}};
function cadastros(){
 if(!isAdmin())return;
 const body=`<section class="page-head"><div><h1>Cadastros</h1><p>Gerencie turmas, cursos e disciplinas</p></div></section><div class="tabs"><a class="selected" href="#cadastros">Turmas</a><a href="#cursos">Cursos</a><a href="#disciplinas">Disciplinas</a></div><div class="panel"><div class="panel-head"><h2>Turmas</h2><button class="btn primary" onclick="editTurma(0)">＋ Nova Turma</button></div><div class="table-wrap"><table><thead><tr><th>CÓDIGO</th><th>NOME</th><th>CURSO</th><th>PERÍODO</th><th>CAPACIDADE</th><th>STATUS</th><th>AÇÕES</th></tr></thead><tbody>${db.turmas.map(t=>`<tr><td>${t.codigo}</td><td>${t.nome}</td><td>${C(t.curso_id)?.nome}</td><td>${cap(t.periodo)}</td><td>${t.capacidade}</td><td>${cap(t.status)}</td><td><button class="icon" onclick="editTurma(${t.id})">✎</button></td></tr>`).join("")}</tbody></table></div></div>`;
 shell("cadastros","Cadastros","",body);
}
window.editTurma=n=>{if(!isAdmin())return;const r=n?T(n):{id:0,codigo:"",nome:"",curso_id:1,periodo:"MANHA",data_inicio:today(),data_fim:plus(90),capacidade:30,status:"ATIVA"};modal(n?"Editar Turma":"Nova Turma",`<form id="tForm"><div class="form-grid"><label>Código<input name="codigo" value="${esc(r.codigo)}" required></label><label>Nome<input name="nome" value="${esc(r.nome)}" required></label><label>Curso<select name="curso">${db.cursos.map(c=>`<option value="${c.id}" ${r.curso_id==c.id?"selected":""}>${esc(c.nome)}</option>`).join("")}</select></label><label>Período<select name="periodo">${["MANHA","TARDE","NOITE"].map(x=>`<option ${r.periodo===x?"selected":""}>${x}</option>`).join("")}</select></label><label>Data inicial<input name="inicio" type="date" value="${r.data_inicio}"></label><label>Data final<input name="fim" type="date" value="${r.data_fim||""}"></label><label>Capacidade<input name="cap" type="number" value="${r.capacidade}"></label><label>Status<select name="status">${["ATIVA","ENCERRADA","CANCELADA"].map(x=>`<option ${r.status===x?"selected":""}>${x}</option>`).join("")}</select></label></div><button class="btn primary full">Salvar</button></form>`);document.getElementById("tForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target),o={id:r.id||id(),codigo:f.get("codigo"),nome:f.get("nome"),curso_id:+f.get("curso"),periodo:f.get("periodo"),data_inicio:f.get("inicio"),data_fim:f.get("fim"),capacidade:+f.get("cap"),status:f.get("status")};if(n)Object.assign(r,o);else db.turmas.push(o);save();closeModal();flash("success","Turma salva.");render()}};
function usuarios(){
 if(!isAdmin())return;
 const rows=db.usuarios.filter(u=>u.perfil!=="ADMIN");
 shell("usuarios","Cadastro de Usuários","",`<section class="page-head"><div><h1>Cadastro de Usuários</h1><p>Novos alunos e instrutores são cadastrados pelo administrador.</p></div><button class="btn primary" onclick="newUser()">＋ Novo acesso ao sistema</button></section><div class="panel"><div class="table-wrap"><table><thead><tr><th>NOME</th><th>CPF</th><th>E-MAIL</th><th>PERFIL</th><th>STATUS</th><th>AÇÕES</th></tr></thead><tbody>${rows.map(u=>`<tr><td>${esc(u.nome)}</td><td>${u.cpf}</td><td>${esc(u.email)}</td><td>${u.perfil}</td><td>${u.status}</td><td><button class="btn small" onclick="toggleUser(${u.id})">${u.status==="ATIVO"?"Desativar":"Ativar"}</button></td></tr>`).join("")}</tbody></table></div></div>`);
}
window.newUser=()=>{modal("Novo acesso ao sistema",`<form id="uForm"><div class="form-grid"><label>CPF<input name="cpf" required></label><label>Nome completo<input name="nome" required></label><label>E-mail<input name="email" type="email" required></label><label>Perfil<select name="perfil"><option>ALUNO</option><option>INSTRUTOR</option></select></label><label>Matrícula (aluno)<input name="matricula"></label><label>Área (instrutor)<input name="area" value="Docência"></label><label>Data de nascimento<input name="nasc" type="date"></label></div><p class="hint">A senha inicial será <b>${initialPassword}</b> e deverá ser alterada no primeiro acesso.</p><button class="btn primary full">Criar acesso</button></form>`);document.getElementById("uForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target),cpf=f.get("cpf");if(db.usuarios.some(u=>digits(u.cpf)===digits(cpf))){flash("danger","CPF já cadastrado.");return}const u={id:id(),cpf,nome:f.get("nome"),email:f.get("email"),senha:initialPassword,senha_provisoria:1,perfil:f.get("perfil"),status:"ATIVO"};db.usuarios.push(u);if(u.perfil==="ALUNO")db.alunos.push({id:id(),usuario_id:u.id,matricula:f.get("matricula"),data_nascimento:f.get("nasc")});else db.instrutores.push({id:id(),usuario_id:u.id,cpf:u.cpf,area:f.get("area")||"Docência",ativo:1});save();closeModal();flash("success","Acesso criado.");render()}};
window.toggleUser=n=>{if(!isAdmin())return;const u=U(n);u.status=u.status==="ATIVO"?"INATIVO":"ATIVO";save();render()};
function movimentacoes(){
 if(!isAdmin())return;
 shell("movimentacoes","Movimentação","",`<section class="page-head"><div><h1>Movimentação</h1><p>Solicitações e alterações acadêmicas</p></div><button class="btn primary" onclick="newMov()">＋ Nova Movimentação</button></section><div class="panel"><div class="table-wrap"><table><thead><tr><th>TIPO</th><th>DATA</th><th>TURMA</th><th>MOTIVO</th><th>STATUS</th><th>AÇÕES</th></tr></thead><tbody>${db.movimentacoes.map(x=>`<tr><td>${x.tipo}</td><td>${fmt(x.data_movimentacao)}</td><td>${T(x.turma_id)?.codigo}</td><td>${esc(x.motivo)}</td><td>${x.status}</td><td><button class="btn small" onclick="setMov(${x.id})">Alterar status</button></td></tr>`).join("")}</tbody></table></div></div>`);
}
window.newMov=()=>{modal("Nova Movimentação",`<form id="mForm"><div class="form-grid"><label>Tipo<select name="tipo"><option>TROCA_HORARIO</option><option>SUBSTITUICAO</option><option>REAGENDAMENTO</option><option>OUTRA</option></select></label><label>Data<input type="date" name="data" value="${today()}"></label><label>Turma<select name="turma">${db.turmas.map(t=>`<option value="${t.id}">${t.codigo}</option>`).join("")}</select></label><label>Status<select name="status"><option>PENDENTE</option><option>APROVADO</option><option>AGUARDANDO</option><option>CANCELADO</option></select></label></div><label>Motivo<textarea name="motivo" required></textarea></label><button class="btn primary full">Salvar</button></form>`);document.getElementById("mForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target);db.movimentacoes.push({id:id(),tipo:f.get("tipo"),status:f.get("status"),data_movimentacao:f.get("data"),turma_id:+f.get("turma"),motivo:f.get("motivo"),criado_por:me().id});save();closeModal();flash("success","Movimentação criada.");render()}};
window.setMov=n=>{const x=db.movimentacoes.find(a=>a.id==n);modal("Detalhes da movimentação",`<div class="detail-list"><p><b>Tipo:</b> ${esc(x.tipo)}</p><p><b>Data:</b> ${fmt(x.data_movimentacao)}</p><p><b>Turma:</b> ${esc(T(x.turma_id)?.codigo)}</p><p><b>Motivo:</b> ${esc(x.motivo)}</p><label>Status<select id="movStatus"><option>PENDENTE</option><option>APROVADO</option><option>AGUARDANDO</option><option>CANCELADO</option></select></label><button class="btn primary full" onclick="x=document.getElementById('movStatus');saveMov(${n},x.value)">Salvar</button></div>`);document.getElementById("movStatus").value=x.status};
window.saveMov=(n,v)=>{db.movimentacoes.find(a=>a.id==n).status=v;save();closeModal();flash("success","Status atualizado.");render()};
function relatorios(){
 const body=`<section class="page-head"><div><h1>Relatórios</h1><p>Indicadores acadêmicos e administrativos</p></div><button class="btn light" onclick="window.print()">♧ Imprimir</button></section>
 <div class="report-grid"><div class="report-card"><b>Frequência de aulas</b><strong>${db.aulas.length}</strong><small>Aulas cadastradas</small></div><div class="report-card"><b>Carga horária por instrutor</b><strong>${db.instrutores.length}</strong><small>Instrutores cadastrados</small></div><div class="report-card"><b>Ocupação de salas</b><strong>${db.salas.filter(s=>s.status!=="DISPONIVEL").length}</strong><small>Salas ocupadas ou reservadas</small></div></div>
 <div class="grid-2"><section class="panel"><div class="panel-head"><h2>Frequência de aulas</h2></div><div class="table-wrap"><table><thead><tr><th>ALUNO</th><th>TURMA</th><th>AULAS</th><th>PRESENÇAS</th><th>FALTAS</th></tr></thead><tbody>${db.alunos.map(a=>`<tr><td>${esc(U(a.usuario_id)?.nome)}</td><td>${T(1)?.codigo}</td><td>${db.aulas.length}</td><td>—</td><td>—</td></tr>`).join("")}</tbody></table></div></section>
 <section class="panel"><div class="panel-head"><h2>Carga horária por instrutor</h2></div><div class="table-wrap"><table><thead><tr><th>INSTRUTOR</th><th>ÁREA</th><th>AULAS</th><th>HORAS</th></tr></thead><tbody>${db.instrutores.map(i=>{const a=db.aulas.filter(x=>x.instrutor_id===i.id),h=a.reduce((s,x)=>s+(new Date("1970-01-01T"+x.fim)-new Date("1970-01-01T"+x.inicio))/3600000,0);return `<tr><td>${esc(uname(i.id))}</td><td>${esc(i.area)}</td><td>${a.length}</td><td>${h.toFixed(1)}</td></tr>`}).join("")}</tbody></table></div></section></div>`;
 shell("relatorios","Relatórios","",body);
}
function senha(){
 shell("senha","Crie sua senha pessoal","",`<section class="page-head"><div><h1>Crie sua senha pessoal</h1><p>Por segurança, altere a senha inicial antes de continuar.</p></div></section><div class="panel" style="max-width:620px"><form id="passForm"><label>Nova senha<input type="password" name="a" minlength="8" required></label><label>Confirmar senha<input type="password" name="b" minlength="8" required></label><button class="btn primary full">Salvar nova senha</button></form></div>`);
 document.getElementById("passForm").onsubmit=e=>{e.preventDefault();const f=new FormData(e.target);if(f.get("a")!==f.get("b")){flash("danger","As senhas não coincidem.");return}const u=U(me().id);u.senha=f.get("a");u.senha_provisoria=0;setSession(u);save();location.hash="dashboard";render()};
}
function exportTable(id,name){const t=document.getElementById(id);if(!t)return;const csv=[...t.querySelectorAll("tr")].map(r=>[...r.children].map(c=>`"${c.innerText.replaceAll('"','""').replaceAll("\n"," ")}"`).join(";")).join("\n");const a=document.createElement("a");a.href=URL.createObjectURL(new Blob([csv],{type:"text/csv;charset=utf-8"}));a.download=name;a.click()}
window.exportTable=exportTable;
function render(){
 db=JSON.parse(localStorage.getItem(STORE));
 const u=me();if(!u)return login();
 if(u.senha_provisoria&&!location.hash.includes("senha"))return senha();
 let p=(location.hash.replace("#","")||"dashboard").split("?")[0];
 if(p==="logout"){logout();return login()}
 const pages={dashboard,horarios,instrutores,salas,cadastros,usuarios,movimentacoes,relatorios,senha};
 if(!isAdmin()&&["cadastros","usuarios","movimentacoes"].includes(p))p="dashboard";
 (pages[p]||dashboard)();
}
window.addEventListener("hashchange",render);
render();
})();
