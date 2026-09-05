<?php
header('Content-Type: application/json');
session_start();

$host = 'localhost';
$db   = 'gestao_academica';
$user = 'root';
$pass = ''; // Ajuste suas credenciais do MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na conexão com o banco']);
    exit;
}

$action = $_GET['action'] ?? '';

// AUTENTICAÇÃO
if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cpf = trim($data['cpf'] ?? '');
    $senha = trim($data['senha'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ? AND senha = ?");
    $stmt->execute([$cpf, $senha]);
    $user = $stmt->fetch();

    if ($user) {
        // Valida se o CPF pertence à tabela de instrutores
        $checkInst = $pdo->prepare("SELECT id FROM instrutores WHERE cpf = ?");
        $checkInst->execute([$cpf]);
        $isInstrutor = (bool)$checkInst->fetch();

        $_SESSION['user'] = [
            'id' => $user['id'],
            'nome' => $user['nome'],
            'cpf' => $user['cpf'],
            'perfil' => $isInstrutor ? 'instrutor' : 'aluno'
        ];

        echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'CPF ou Senha inválidos.']);
    }
    exit;
}

// LOGOUT
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// LISTAR AULAS
if ($action === 'get_aulas') {
    $stmt = $pdo->query("SELECT * FROM aulas ORDER BY data_aula DESC, horario ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// CRIAR/EDITAR AULA (RESTRITO AO INSTRUTOR)
if ($action === 'save_aula') {
    if (($_SESSION['user']['perfil'] ?? '') !== 'instrutor') {
        echo json_encode(['success' => false, 'message' => 'Acesso negado: Perfil de Aluno não possui permissão para editar.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare("UPDATE aulas SET turma=?, materia=?, data_aula=?, horario=?, conteudo=?, status=? WHERE id=?");
        $stmt->execute([$data['turma'], $data['materia'], $data['data_aula'], $data['horario'], $data['conteudo'], $data['status'], $data['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO aulas (turma, materia, data_aula, horario, conteudo, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['turma'], $data['materia'], $data['data_aula'], $data['horario'], $data['conteudo'], $data['status']]);
    }

    echo json_encode(['success' => true]);
    exit;
}

// DELETAR AULA (RESTRITO AO INSTRUTOR)
if ($action === 'delete_aula') {
    if (($_SESSION['user']['perfil'] ?? '') !== 'instrutor') {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM aulas WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
    exit;
}
?>