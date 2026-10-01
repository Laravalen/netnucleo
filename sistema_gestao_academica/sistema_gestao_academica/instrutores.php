<?php
require_once __DIR__.'/functions.php'; require_login(); $title='Consulta de Instrutores';
if($_SERVER['REQUEST_METHOD']==='POST' && is_admin()){
 $id=(int)$_POST['id'];$st=db()->prepare("UPDATE instrutores SET ativo=? WHERE id=?");$st->execute([(int)$_POST['ativo'],$id]);flash('success','Instrutor atualizado.');header('Location: instrutores.php');exit;
}
$rows=db()->query("SELECT i.*,u.nome,u.email as uemail,COUNT(a.id) aulas FROM instrutores i JOIN usuarios u ON u.id=i.usuario_id LEFT JOIN aulas a ON a.instrutor_id=i.id WHERE u.perfil='INSTRUTOR' GROUP BY i.id ORDER BY u.nome")->fetchAll();$fl=flashes();
require 'partials/header.php';require 'partials/sidebar.php';
?>
<section class="page-head"><div><h1>Consulta de Instrutores</h1><p>Equipe docente e carga de aulas</p></div></section>
<?php foreach($fl as $f):?><div class="alert <?=$f['type']?>"><?=e($f['message'])?></div><?php endforeach;?>
<div class="panel"><div class="toolbar"><input id="searchInst" placeholder="⌕ Pesquisar instrutor..." oninput="filterCards('searchInst','.inst-row')"></div><div class="table-wrap"><table><thead><tr><th>INSTRUTOR</th><th>CPF</th><th>ÁREA</th><th>AULAS</th><th>STATUS</th><?php if(is_admin()):?><th>AÇÕES</th><?php endif;?></tr></thead><tbody><?php foreach($rows as $r):?><tr class="inst-row"><td>
    <div class="person">
        <span class="person-avatar"><?=e(mb_strtoupper(mb_substr(trim($r['nome']), 0, 1)))?></span>
        <b><?=e($r['nome'])?></b>
    </div>
</td><td><?=e($r['cpf'])?></td><td><?=e($r['area'])?></td><td><?=$r['aulas']?></td><td><span class="status <?=$r['ativo']?'green':'danger'?>"><?=$r['ativo']?'Ativo':'Inativo'?></span></td><?php if(is_admin()):?><td><form method="post"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="ativo" value="<?=$r['ativo']?0:1?>"><button class="btn small"><?=$r['ativo']?'Desativar':'Ativar'?></button></form></td><?php endif;?></tr><?php endforeach;?></tbody></table></div></div>
<?php require 'partials/footer.php';?>
