# Sistema de Gestão Acadêmica — GitHub Pages

Esta é a conversão estática do SGA para hospedagem no GitHub Pages.

### O que foi mantido
- CSS e identidade visual do projeto original.
- Logos SESI/SENAI.
- Login por CPF.
- Perfis ADMIN, INSTRUTOR e ALUNO.
- Troca obrigatória da senha inicial.
- Painel, calendário acadêmico, horários, instrutores, salas e relatórios.
- Funções administrativas de usuários, cadastros e movimentações.
- Edição de horários/salas/turmas pelo ADMIN.
- Filtros, modais, impressão e exportação CSV.

### Diferença técnica obrigatória
GitHub Pages não executa PHP e não oferece MySQL/MariaDB. Portanto, esta versão substitui o backend PHP/PDO/sessões por JavaScript + `localStorage`.

Isso permite que o site funcione como GitHub Pages, mas os dados ficam somente no navegador de cada visitante. Não é equivalente a um banco centralizado multiusuário e não deve receber dados pessoais reais.

Para preservar autenticação real, banco compartilhado e segurança de produção, o backend PHP/MySQL precisa continuar hospedado em outro serviço.

### Acesso de demonstração
Admin: `123.456.789-00`
Aluno: `111.111.111-11`
Instrutor: `222.222.222-22`
Senha inicial: `SesiSenai@2026`

### Publicação
1. Crie um repositório no GitHub.
2. Envie `index.html` e a pasta `assets` para a raiz.
3. Abra **Settings → Pages**.
4. Selecione **Deploy from a branch**.
5. Escolha a branch principal e `/ (root)`.
6. Salve.

