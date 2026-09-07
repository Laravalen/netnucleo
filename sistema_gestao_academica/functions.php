<?php
require_once __DIR__.'/auth.php';

function count_value(string $sql,array $params=[]): int {
    $st=db()->prepare($sql); $st->execute($params); return (int)$st->fetchColumn();
}
function scalar(string $sql,array $params=[],$default=0) {
    $st=db()->prepare($sql); $st->execute($params); $v=$st->fetchColumn();
    return $v === false ? $default : $v;
}
function audit(string $table,int $id,string $op,?array $old=null,?array $new=null): void {
    $st=db()->prepare("INSERT INTO auditoria(usuario_id,tabela,registro_id,operacao,dados_anteriores,dados_novos) VALUES(?,?,?,?,?,?)");
    $st->execute([user()['id'],$table,$id,$op,$old?json_encode($old,JSON_UNESCAPED_UNICODE):null,$new?json_encode($new,JSON_UNESCAPED_UNICODE):null]);
}
function page_title(string $title): string { return $title.' | Gestão Acadêmica'; }
function fmt_date(?string $d): string { return $d ? date('d/m/Y',strtotime($d)) : '-'; }
