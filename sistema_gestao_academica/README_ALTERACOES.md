# Alterações do Sistema de Gestão Acadêmica

## Novo fluxo de acesso

1. O usuário informa o **CPF**.
2. O sistema procura o CPF na tabela `usuarios`.
3. A senha inicial de novos usuários é **SesiSenai@2026**.
4. O banco marca o usuário com `senha_provisoria = 1`.
5. No primeiro login, o sistema bloqueia o restante da aplicação e abre `alterar_senha.php`.
6. O usuário cria uma senha pessoal.
7. A senha pessoal é armazenada somente como hash e `senha_provisoria` passa para `0`.
8. Depois disso, o usuário acessa normalmente.

## Cadastro de usuários

A opção **Criar nova conta** pública foi removida.

Foi criada a tela `administradores.php`, acessível somente ao perfil `ADMIN`.

Somente o administrador pode cadastrar novos **instrutores** e **alunos**. A tela valida CPF, e-mail, matrícula, data de nascimento e campos obrigatórios no servidor.

A senha inicial é `SesiSenai@2026`; ela é transformada em hash com `password_hash()` antes de ser gravada e `senha_provisoria` fica em `1`. No primeiro acesso, o usuário deve criar sua senha pessoal.

O perfil `ADMIN` foi adicionado à tabela `usuarios`. A conta inicial `123.456.789-00` passa a ser o administrador do sistema.

## Perfil e permissões

O perfil é definido pelo banco:

- `ALUNO`: somente visualização.
- `INSTRUTOR`: somente visualização, com o mesmo nível de acesso de consulta do aluno. Não pode cadastrar, editar, excluir ou aprovar informações.
- `ADMIN`: visualização + edição de todo o conteúdo administrativo do sistema. É também o único perfil autorizado a abrir `administradores.php` e cadastrar novos instrutores e alunos.

A proteção é feita no servidor: as páginas que alteram dados usam `require_editor()`, que aceita somente `ADMIN`. Esconder botões no HTML não é usado como mecanismo de segurança.

O CPF continua sendo a identificação usada no login.

## Banco novo

Se for criar o banco do zero, importe:

`database.sql`

Ele já contém a coluna `senha_provisoria`, os usuários de demonstração e a senha padrão.

## Banco existente

Se você já possui o banco preenchido e não quer apagá-lo, execute:

`migracao_senha_padrao.sql`

Depois execute também:

`migracao_administrador.sql`

Essa segunda migração adiciona o perfil `ADMIN` e transforma a conta inicial `123.456.789-00` em administrador.

Essa migração adiciona a coluna e faz os usuários existentes voltarem para a senha padrão, obrigando a troca no próximo login.

## Observação importante

A senha padrão é apenas uma senha temporária. A aplicação nunca grava a senha pessoal em texto puro; ela é protegida com `password_hash()`.


## Logotipos

As imagens que ficavam fixas no canto inferior direito foram retiradas dessa posição. O logo SENAI existente em `assets/img/foto-canto.png` agora aparece ao lado da marca `SESI SENAI` no cabeçalho lateral, mantendo a proporção da imagem.

## Calendário acadêmico

O `dashboard.php` agora apresenta um calendário mensal para todos os perfis autenticados. Ele reúne:

- aulas por data;
- uso de laboratórios: quando uma aula utiliza uma sala do tipo laboratório, o calendário mostra `Lab X reservado`;
- eventos cadastrados em `eventos_calendario`;
- movimentações com data que não estejam canceladas.

Alunos e instrutores podem consultar e navegar entre os meses, mas não recebem controles de edição. Quando autenticado como administrador, os itens de aula/movimentação podem levar às telas administrativas correspondentes; as páginas de alteração continuam protegidas no servidor por `require_editor()`.

## Acesso inicial do administrador

- **CPF:** `123.456.789-00`
- **Senha inicial:** `SesiSenai@2026`

No primeiro acesso, `senha_provisoria=1` encaminha o administrador para `alterar_senha.php`, onde ele cria a senha pessoal. A senha pessoal é armazenada somente com `password_hash()`.

## Se o ADMIN entrar mas não conseguir editar

O sistema considera o perfil administrativo como `ADMIN`. Se o banco já existia antes da criação do perfil administrativo, execute `corrigir_admin.sql` no phpMyAdmin.

Depois faça logout e login novamente. O arquivo `auth.php` desta versão também sincroniza o perfil da sessão com o banco, evitando que uma sessão antiga continue como INSTRUTOR após a promoção para ADMIN.

Conta inicial:
- CPF: `123.456.789-00`
- Senha padrão: `SesiSenai@2026`

Após o primeiro login, o sistema exige a criação de uma nova senha.

## Dados de demonstração

Foi incluído o arquivo `dados_exemplo.sql` para preencher uma instalação já existente com aulas, reservas de laboratório, eventos e uma movimentação. As datas são calculadas a partir de `CURDATE()`, então os exemplos aparecem no calendário no mês atual.

Se o banco já estiver instalado, execute `dados_exemplo.sql` uma vez no phpMyAdmin, com o banco `gestao_academica` selecionado.

O `database.sql` também foi atualizado para usar datas relativas ao dia da instalação quando o banco for recriado do zero.

## Logo

A logo SENAI de `assets/img/foto-canto.png` foi reposicionada para ficar abaixo do símbolo amarelo na identificação da lateral, mantendo a proporção original da imagem.
