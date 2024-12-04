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
$produto_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;
$apelido_produto = isset($_GET['apelido']) ? trim($_GET['apelido']) : null;

// Verificar se ao menos um identificador foi fornecido
if (!$produto_id && !$apelido_produto) {
    echo "Nenhum identificador válido foi fornecido.";
    exit;
}

// Buscar o produto no banco de dados usando ID ou apelido
if ($produto_id) {
    $query = "SELECT * FROM PRODUTO WHERE EMPRESA = :empresa_id AND PRODUTO = :produto_id";
    $params = [':empresa_id' => $empresa_id, ':produto_id' => $produto_id];
} else {
    $query = "SELECT * FROM PRODUTO WHERE EMPRESA = :empresa_id AND APELIDO_PRODUTO = :apelido_produto";
    $params = [':empresa_id' => $empresa_id, ':apelido_produto' => $apelido_produto];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo "Produto não encontrado ou identificador inválido.";
    exit;
}

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Capturar e validar os dados do formulário
        $descricao = trim($_POST['descricao'] ?? '');
        $apelido = trim($_POST['apelido'] ?? '');
        $codigo_barras = trim($_POST['codigo_barras'] ?? '');
        $grupo_produto_id = intval($_POST['grupo_produto_id'] ?? 0);

        if (empty($descricao) || empty($codigo_barras) || $grupo_produto_id === 0) {
            echo "Por favor, preencha todos os campos obrigatórios.";
            exit;
        }

        // Preparar a consulta para atualizar o produto
        if ($produto_id) {
            $sql = "UPDATE PRODUTO 
                    SET DESCRICAO_PRODUTO = :descricao, 
                        APELIDO_PRODUTO = :apelido, 
                        CODIGO_BARRAS = :codigo_barras, 
                        GRUPO_PRODUTO = :grupo_produto_id
                    WHERE EMPRESA = :empresa_id AND PRODUTO = :produto_id";
            $params[':produto_id'] = $produto_id;
        } else {
            $sql = "UPDATE PRODUTO 
                    SET DESCRICAO_PRODUTO = :descricao, 
                        APELIDO_PRODUTO = :apelido, 
                        CODIGO_BARRAS = :codigo_barras, 
                        GRUPO_PRODUTO = :grupo_produto_id
                    WHERE EMPRESA = :empresa_id AND APELIDO_PRODUTO = :apelido_produto";
            $params[':apelido_produto'] = $apelido_produto;
        }

        // Executar a consulta de atualização
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($params, [
            ':descricao' => $descricao,
            ':apelido' => $apelido,
            ':codigo_barras' => $codigo_barras,
            ':grupo_produto_id' => $grupo_produto_id,
            ':empresa_id' => $empresa_id,
        ]));

        // Redirecionar após a atualização
        header('Location: produtos.php');
        exit;
    } catch (Exception $e) {
        echo "Erro ao atualizar o produto: " . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Buscar grupos de produtos
$grupos = $pdo->prepare("SELECT GRUPO_PRODUTO, DESCRICAO_GRUPO_PRODUTO FROM GRUPO_PRODUTO WHERE EMPRESA = :empresa_id");
$grupos->execute([':empresa_id' => $empresa_id]);
$grupos = $grupos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Editar Produto</h1>
    <form method="POST" class="card p-4 shadow">
        <input type="hidden" name="produto_id" value="<?= htmlspecialchars($produto['PRODUTO'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <input type="text" name="descricao" id="descricao" class="form-control" 
                value="<?= htmlspecialchars($produto['DESCRICAO_PRODUTO'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="apelido" class="form-label">Apelido</label>
            <input type="text" name="apelido" id="apelido" class="form-control" 
                value="<?= htmlspecialchars($produto['APELIDO_PRODUTO'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="codigo_barras" class="form-label">Código de Barras</label>
            <input type="text" name="codigo_barras" id="codigo_barras" class="form-control" 
                value="<?= htmlspecialchars($produto['CODIGO_BARRAS'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="grupo_produto_id" class="form-label">Grupo de Produto</label>
            <select name="grupo_produto_id" id="grupo_produto_id" class="form-select" required>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= htmlspecialchars($grupo['GRUPO_PRODUTO'], ENT_QUOTES, 'UTF-8') ?>" 
                        <?= $grupo['GRUPO_PRODUTO'] == ($produto['GRUPO_PRODUTO'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grupo['DESCRICAO_GRUPO_PRODUTO'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="produtos.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>
</body>
</html>
