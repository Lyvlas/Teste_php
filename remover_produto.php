<?php
require '../Database/db.php';
session_start();

// Verificar se a sessão está ativa
if (!isset($_SESSION['empresa_id'])) {
    header('Location: index.php');
    exit;
}

// Validar e capturar os parâmetros GET
$empresa_id = $_SESSION['empresa_id'];
$produto_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$apelido_produto = filter_input(INPUT_GET, 'apelido', FILTER_SANITIZE_STRING);

// Verificar se ao menos um identificador foi fornecido
if (!$produto_id && !$apelido_produto) {
    echo "Nenhum identificador válido foi fornecido.";
    exit;
}

try {
    // Montar a consulta de exclusão
    $query = "DELETE FROM PRODUTO WHERE EMPRESA = :empresa_id";
    $params = [':empresa_id' => $empresa_id];

    if ($produto_id) {
        $query .= " AND PRODUTO = :produto_id";
        $params[':produto_id'] = $produto_id;
    } elseif ($apelido_produto) {
        $query .= " AND APELIDO_PRODUTO = :apelido_produto";
        $params[':apelido_produto'] = $apelido_produto;
    }

    // Preparar e executar a exclusão
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Verificar se a exclusão ocorreu
    if ($stmt->rowCount() > 0) {
        // Redirecionar após a remoção
        header('Location: produtos.php');
        exit;
    } else {
        echo "Produto não encontrado ou já foi removido.";
        exit;
    }
} catch (Exception $e) {
    echo "Erro ao remover o produto: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
