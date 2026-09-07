<?php
require_once __DIR__.'/functions.php'; require_instructor(); $title='Cadastros';
$tab=$_GET['tab']??'turmas';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $action=$_POST['action']??'';
 try{
  if($action==='delete'){
   $table=$_POST['table']; $id=(int)$_POST['id'];
   if(!in_array($table,['turmas','cursos','disciplinas'],true)) throw new Exception('Operação inválida.');
   $old=db()->query("SELECT * FROM `$table` WHERE id=$id")->fetch();
   db()->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]); audit($table,$id,'DELETE',$old,null); flash('success','Registro excluído.');
  } elseif($action==='save_turma'){
   $id=(int)($_POST['id']??0); $data=[trim($_POST['codigo']),trim($_POST['nome']),(int)$_POST['curso_id'],$_POST['periodo'],$_POST['data_inicio'],$_POST['data_fim']?:null,(int)$_POST['capacidade'],$_POST['status']];
   if($id){$old=db()->query("SELECT * FROM turmas WHERE id=$id")->fetch();$st=db()->prepare("UPDATE turmas SET codigo=?,nome=?,curso_id=?,periodo=?,data_inicio=?,data_fim=?,capacidade=?,status=? WHERE id=?");$st->execute([...$data,$id]);audit('turmas',$id,'UPDATE',$old,$data);}else{$st=db()->prepare("INSERT INTO turmas(codigo,nome,curso_id,periodo,data_inicio,data_fim,capacidade,status) VALUES(?,?,?,?,?,?,?,?)");$st->execute($data);audit('turmas',(int)db()->lastInsertId(),'INSERT',null,$data);} flash('success','Turma salva.');
  } elseif($action==='save_curso'){
   $id=(int)($_POST['id']??0);$data=[trim($_POST['codigo']),trim($_POST['nome']),trim($_POST['descricao']),($_POST['carga_horaria']?:null)];
   if($id){$st=db()->prepare("UPDATE cursos SET codigo=?,nome=?,descricao=?,carga_horaria=? WHERE id=?");$st->execute([...$data,$id]);}else{$st=db()->prepare("INSERT INTO cursos(codigo,nome,descricao,carga_horaria) VALUES(?,?,?,?)");$st->execute($data);} flash('success','Curso salvo.');
  } elseif($action==='save_disciplina'){
   $id=(int)($_POST['id']??0);$data=[trim($_POST['codigo']),trim($_POST['nome']),trim($_POST['descricao']),($_POST['carga_horaria']?:null)];
   if($id){$st=db()->prepare("UPDATE disciplinas SET codigo=?,nome=?,descricao=?,carga_horaria=? WHERE id=?");$st->execute([...$data,$id]);}else{$st=db()->prepare("INSERT INTO disciplinas(codigo,nome,descricao,carga_horaria) VALUES(?,?,?,?)");$st->execute($data);} flash('success','Disciplina salva.');
  } elseif($action==='save_instrutor'){
   $cpf=trim($_POST['cpf']); $nome=trim($_POST['nome']); $email=trim($_POST['email']); $area=trim($_POST['area']); $senha=$_POST['senha']?:'123456';
   $pdo=db(); $pdo->beginTransaction();
   $st=$pdo->prepare("SELECT id FROM usuarios WHERE REPLACE(REPLACE(cpf,'.',''),'-','')=?");$st->execute([cpf_digits($cpf)]);$uid=$st->fetchColumn();
   if($uid){$pdo->prepare("UPDATE usuarios SET nome=?,email=?,perfil='INSTRUTOR',status='ATIVO',senha_hash=? WHERE id=?")->execute([$nome,$email,password_hash($senha,PASSWORD_DEFAULT),$uid]);}
   else {$pdo->prepare("INSERT INTO usuarios(cpf,nome,email,senha_hash,perfil) VALUES(?,?,?,?, 'INSTRUTOR')")->execute([$cpf,$nome,$email,password_hash($senha,PASSWORD_DEFAULT)]);$uid=$pdo->lastInsertId();}
   $pdo->prepare("INSERT INTO instrutores(usuario_id,cpf,area,email) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE area=VALUES(area),email=VALUES(email),ativo=1")->execute([$uid,$cpf,$area,$email]);
   $pdo->commit(); flash('success','Instrutor cadastrado.');
  }
 }catch(Throwable $e){ if(db()->inTransaction())db()->rollBack(); flash('danger',$e->getCode()==23000?'Já existe um registro com esses dados.':$e->getMessage()); }
 header('Location: cadastros.php?tab='.urlencode($tab));exit;
}
$cursos=db()->query("SELECT * FROM cursos ORDER BY nome")->fetchAll();
$disciplinas=db()->query("SELECT * FROM disciplinas ORDER BY nome")->fetchAll();
$turmas=db()->query("SELECT t.*,c.nome curso FROM turmas t JOIN cursos c ON c.id=t.curso_id ORDER BY t.codigo")->fetchAll();
require 'partials/header.php';require 'partials/sidebar.php';$fl=flashes();
?>
<section class="page-head"><div><h1>Cadastros</h1><p>Turmas, cursos, disciplinas e instrutores</p></div></section>
<?php foreach($fl as $f): ?><div class="alert <?=$f['type']?>"><?=e($f['message'])?></div><?php endforeach;?>
<div class="tabs"><a class="<?=$tab==='turmas'?'selected':''?>" href="?tab=turmas">Turmas</a><a class="<?=$tab==='cursos'?'selected':''?>" href="?tab=cursos">Cursos</a><a class="<?=$tab==='disciplinas'?'selected':''?>" href="?tab=disciplinas">Disciplinas</a><a href="instrutores.php">Instrutores</a></div>
<div class="panel">
<?php if($tab==='turmas'): ?><div class="panel-head"><h2>Turmas</h2><button class="btn primary" onclick="openModal('turmaModal')">＋ Nova turma</button></div><div class="table-wrap"><table><thead><tr><th>CÓDIGO</th><th>CURSO</th><th>PERÍODO</th><th>INÍCIO</th><th>FIM</th><th>STATUS</th><th>AÇÕES</th></tr></thead><tbody><?php foreach($turmas as $r): ?><tr><td><b><?=e($r['codigo'])?></b></td><td><?=e($r['curso'])?></td><td><?=e(ucfirst(strtolower($r['periodo'])))?></td><td><?=fmt_date($r['data_inicio'])?></td><td><?=fmt_date($r['data_fim'])?></td><td><span class="status green"><?=e($r['status'])?></span></td><td><button class="icon" onclick='editTurma(<?=json_encode($r)?>)'>✎</button><form class="inline" method="post" onsubmit="return confirm('Excluir turma?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="turmas"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="icon danger-text">⌫</button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php elseif($tab==='cursos'): ?><div class="panel-head"><h2>Cursos</h2><button class="btn primary" onclick="openModal('cursoModal')">＋ Novo curso</button></div><div class="cards-grid"><?php foreach($cursos as $r): ?><div class="entity-card"><b><?=e($r['codigo'])?></b><h3><?=e($r['nome'])?></h3><p><?=e($r['descricao']??'')?></p><small><?=$r['carga_horaria']?> horas</small><div class="card-actions"><button class="icon" onclick='editCurso(<?=json_encode($r)?>)'>✎</button><form class="inline" method="post" onsubmit="return confirm('Excluir curso?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="cursos"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="icon danger-text">⌫</button></form></div></div><?php endforeach;?></div>
<?php else: ?><div class="panel-head"><h2>Disciplinas</h2><button class="btn primary" onclick="openModal('disciplinaModal')">＋ Nova disciplina</button></div><div class="cards-grid"><?php foreach($disciplinas as $r): ?><div class="entity-card"><b><?=e($r['codigo'])?></b><h3><?=e($r['nome'])?></h3><p><?=e($r['descricao']??'')?></p><small><?=$r['carga_horaria']?> horas</small><div class="card-actions"><button class="icon" onclick='editDisciplina(<?=json_encode($r)?>)'>✎</button><form class="inline" method="post" onsubmit="return confirm('Excluir disciplina?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="disciplinas"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="icon danger-text">⌫</button></form></div></div><?php endforeach;?></div><?php endif;?>
</div>
<?php if($tab==='turmas'): ?><div class="modal" id="turmaModal"><div class="modal-box"><button class="modal-close" onclick="closeModal('turmaModal')">×</button><h2 id="turmaTitle">Nova Turma</h2><form method="post"><input type="hidden" name="action" value="save_turma"><input type="hidden" name="id" id="turma_id"><div class="form-grid"><label>Código<input name="codigo" id="turma_codigo" required></label><label>Nome<input name="nome" id="turma_nome" required></label><label>Curso<select name="curso_id" id="turma_curso" required><?php foreach($cursos as $c):?><option value="<?=$c['id']?>"><?=e($c['nome'])?></option><?php endforeach;?></select></label><label>Período<select name="periodo" id="turma_periodo"><option>MANHA</option><option>TARDE</option><option>NOITE</option></select></label><label>Data inicial<input type="date" name="data_inicio" id="turma_inicio" required></label><label>Data final<input type="date" name="data_fim" id="turma_fim"></label><label>Capacidade<input type="number" name="capacidade" id="turma_cap" value="30"></label><label>Status<select name="status" id="turma_status"><option>ATIVA</option><option>ENCERRADA</option><option>CANCELADA</option></select></label></div><button class="btn primary full">Salvar</button></form></div></div><?php endif;?>
<div class="modal" id="cursoModal"><div class="modal-box"><button class="modal-close" onclick="closeModal('cursoModal')">×</button><h2>Curso</h2><form method="post"><input type="hidden" name="action" value="save_curso"><input type="hidden" name="id" id="curso_id"><label>Código<input name="codigo" id="curso_codigo" required></label><label>Nome<input name="nome" id="curso_nome" required></label><label>Descrição<textarea name="descricao" id="curso_desc"></textarea></label><label>Carga horária<input type="number" name="carga_horaria" id="curso_carga"></label><button class="btn primary full">Salvar</button></form></div></div>
<div class="modal" id="disciplinaModal"><div class="modal-box"><button class="modal-close" onclick="closeModal('disciplinaModal')">×</button><h2>Disciplina</h2><form method="post"><input type="hidden" name="action" value="save_disciplina"><input type="hidden" name="id" id="disciplina_id"><label>Código<input name="codigo" id="disciplina_codigo" required></label><label>Nome<input name="nome" id="disciplina_nome" required></label><label>Descrição<textarea name="descricao" id="disciplina_desc"></textarea></label><label>Carga horária<input type="number" name="carga_horaria" id="disciplina_carga"></label><button class="btn primary full">Salvar</button></form></div></div>
<?php require 'partials/footer.php';?>
