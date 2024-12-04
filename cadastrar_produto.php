<?php
require '../Database/db.php';
session_start();

if (!isset($_SESSION['empresa_id'])) {
    header('Location: index.php');
    exit;
}

$empresa_id = $_SESSION['empresa_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $apelido = $_POST['apelido'];
    $codigo = $_POST['codigo'];
    $grupo_produto_id = $_POST['grupo_produto_id'];
    $situacao = $_POST['situacao'];
    $peso_liquido = $_POST['peso_liquido'] ?: null;
    $classificacao_fiscal = $_POST['classificacao_fiscal'] ?: null;
    $colecao = $_POST['colecao'] ?: null;

    try {
    
        $sql = "INSERT INTO PRODUTO (
            DESCRICAO_PRODUTO, APELIDO_PRODUTO, CODIGO_BARRAS, GRUPO_PRODUTO, 
            SITUACAO, PESO_LIQUIDO, CLASSIFICACAO_FISCAL, COLECAO, EMPRESA
        ) VALUES (
            :descricao, :apelido, :codigo, :grupo_produto_id, 
            :situacao, :peso_liquido, :classificacao_fiscal, :colecao, :empresa_id
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':descricao' => $descricao,
            ':apelido' => $apelido,
            ':codigo' => $codigo,
            ':grupo_produto_id' => $grupo_produto_id,
            ':situacao' => $situacao,
            ':peso_liquido' => $peso_liquido,
            ':classificacao_fiscal' => $classificacao_fiscal,
            ':colecao' => $colecao,
            ':empresa_id' => $empresa_id,
        ]);

        // Redirecionar para a página de listagem
        header('Location: produtos.php');
        exit;
    } catch (PDOException $e) {
        echo "Erro ao cadastrar o produto: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT GRUPO_PRODUTO, DESCRICAO_GRUPO_PRODUTO FROM GRUPO_PRODUTO WHERE EMPRESA = :empresa_id");
$stmt->execute([':empresa_id' => $empresa_id]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Cadastrar Produto</h1>
        <form action="cadastrar_produto.php" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição do Produto</label>
                <input type="text" id="descricao" name="descricao" class="form-control" required>
                <div class="invalid-feedback">Por favor, informe a descrição do produto.</div>
            </div>

            <div class="mb-3">
                <label for="apelido" class="form-label">Apelido do Produto</label>
                <input type="text" id="apelido" name="apelido" class="form-control">
            </div>

            <div class="mb-3">
                <label for="codigo" class="form-label">Código de Barras</label>
                <input type="text" id="codigo" name="codigo" class="form-control">
            </div>

            <div class="mb-3">
                <label for="grupo_produto_id" class="form-label">Grupo do Produto</label>
                <select id="grupo_produto_id" name="grupo_produto_id" class="form-select" required>
                    <option value="">Selecione um grupo</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?= htmlspecialchars($grupo['GRUPO_PRODUTO']) ?>">
                            <?= htmlspecialchars($grupo['DESCRICAO_GRUPO_PRODUTO']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Por favor, selecione um grupo.</div>
            </div>

            <div class="mb-3">
                <label for="situacao" class="form-label">Situação</label>
                <select id="situacao" name="situacao" class="form-select" required>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
                <div class="invalid-feedback">Por favor, selecione a situação.</div>
            </div>

            <div class="mb-3">
                <label for="peso_liquido" class="form-label">Peso Líquido (kg)</label>
                <input type="number" id="peso_liquido" name="peso_liquido" step="0.01" class="form-control">
            </div>

            <div class="mb-3">
                <label for="classificacao_fiscal" class="form-label">Classificação Fiscal</label>
                <input type="text" id="classificacao_fiscal" name="classificacao_fiscal" class="form-control">
            </div>

            <div class="mb-3">
                <label for="colecao" class="form-label">Coleção</label>
                <input type="text" id="colecao" name="colecao" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Cadastrar Produto</button>
            <a href="produtos.php" class="btn btn-primary w-100 mb-2">Voltar</a>
        </form>
    </div>

    <script>
        // Script para validação do formulário com Bootstrap
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>

