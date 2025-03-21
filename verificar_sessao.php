<?php
session_start();

// Incluir conexão com o banco
require_once 'conexao.php';

// Verificar se o usuário está logado e tem um nível válido
$sessao_ativa = isset($_SESSION['usuario_id']) && 
                isset($_SESSION['usuario_nivel']) && 
                in_array($_SESSION['usuario_nivel'], ['admin', 'instrutor', 'aluno']);

// Se estiver logado, atualizar o timestamp da última atividade
if ($sessao_ativa) {
    $_SESSION['ultimo_acesso'] = time();
    
    // Verificar se o usuário ainda existe no banco
    $sql = "SELECT id, nivel FROM usuarios WHERE id = ? AND nivel = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $_SESSION['usuario_id'], $_SESSION['usuario_nivel']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Se o usuário não existe mais ou o nível mudou, invalidar a sessão
        $sessao_ativa = false;
        session_unset();
        session_destroy();
    }
}

// Retornar resposta em JSON
header('Content-Type: application/json');
echo json_encode([
    'sessao_ativa' => $sessao_ativa,
    'usuario_nivel' => $sessao_ativa ? $_SESSION['usuario_nivel'] : null
]);
?>
