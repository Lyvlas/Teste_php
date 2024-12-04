<?php
require './Database/db.php';

$empresas = $pdo->query("
    SELECT DISTINCT e.EMPRESA AS id, e.RAZAO_SOCIAL AS razao_social, c.DESCRICAO_CIDADE AS cidade 
    FROM EMPRESA e
    JOIN CIDADE c ON e.CIDADE = c.CIDADE
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $_SESSION['empresa_id'] = $_POST['empresa_id'];
    header('Location: ./php/produtos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Estagio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Escolha a Empresa</h1>
    <form method="POST" class="card shadow p-4">
        <div class="mb-3">
            <label for="empresa_id" class="form-label">Selecione a Empresa</label>
            <select name="empresa_id" id="empresa_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= $empresa['id'] ?>">
                        <?= htmlspecialchars($empresa['razao_social']) ?> - <?= htmlspecialchars($empresa['cidade']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</div>
</body>
</html>
