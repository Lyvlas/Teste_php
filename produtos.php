<?php
require '../Database/db.php';

$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 1;

// Filtros e parâmetros de busca
$filtro_grupo = isset($_GET['grupo_produto_id']) ? intval($_GET['grupo_produto_id']) : null;
$tipo_complemento = isset($_GET['tipo_complemento']) ? $_GET['tipo_complemento'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'DESCRICAO_PRODUTO'; // Nome correto da coluna

// Colunas permitidas para ordenação
$allowed_sort_columns = ['DESCRICAO_PRODUTO', 'CODIGO_BARRAS'];
if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'DESCRICAO_PRODUTO'; // Valor padrão caso o parâmetro seja inválido
}

// Consulta SQL base
$sql = "SELECT 
    p.PRODUTO, 
    p.DESCRICAO_PRODUTO, 
    p.APELIDO_PRODUTO, 
    p.CODIGO_BARRAS, 
    g.DESCRICAO_GRUPO_PRODUTO AS grupo_produto, 
    g.TIPO_COMPLEMENTO 
FROM PRODUTO p
INNER JOIN GRUPO_PRODUTO g ON p.GRUPO_PRODUTO = g.GRUPO_PRODUTO
WHERE p.EMPRESA = :empresa_id";

// Definir parâmetros
$params = [':empresa_id' => $empresa_id];

// Filtro por grupo
if ($filtro_grupo) {
    $sql .= " AND g.GRUPO_PRODUTO = :grupo_produto_id";
    $params[':grupo_produto_id'] = $filtro_grupo;
}

// Filtro por tipo de complemento
if ($tipo_complemento) {
    $sql .= " AND g.TIPO_COMPLEMENTO = :tipo_complemento";
    $params[':tipo_complemento'] = $tipo_complemento;
}

// Filtro de busca
if ($search) {
    $sql .= " AND (p.DESCRICAO_PRODUTO LIKE :search 
                OR p.APELIDO_PRODUTO LIKE :search 
                OR p.CODIGO_BARRAS LIKE :search)";
    $params[':search'] = "%" . $search . "%"; // Inclui o % para busca parcial
}

// Ordenação
$sql .= " ORDER BY $sort_by"; // Ordena pela coluna validada

// Preparar e executar a consulta
$query = $pdo->prepare($sql);
$query->execute($params);
$produtos = $query->fetchAll(PDO::FETCH_ASSOC);

// Recuperar tipos de complementos
$sql_tipos_complemento = "SELECT DISTINCT TIPO_COMPLEMENTO, DESCRICAO_TIPO_COMPLEMENTO FROM TIPO_COMPLEMENTO";
$query_tipos_complemento = $pdo->query($sql_tipos_complemento);
$tipos_complemento = $query_tipos_complemento->fetchAll(PDO::FETCH_ASSOC);

// Recuperar grupos de produtos para o filtro
$grupos = $pdo->query("SELECT GRUPO_PRODUTO, DESCRICAO_GRUPO_PRODUTO FROM GRUPO_PRODUTO")->fetchAll(PDO::FETCH_ASSOC);

// Recuperar empresas para o filtro de troca de empresa
$empresas = $pdo->query("SELECT EMPRESA, NOME_FANTASIA FROM EMPRESA")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h1 class="mb-4 text-center">Produtos da Empresa</h1>

    <!-- Filtros -->
    <form method="GET" action="produtos.php" class="mb-4">
        <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($empresa_id) ?>">
        <div class="row mb-3">
            <div class="col">
                <label for="grupo_produto_id" class="form-label">Grupo:</label>
                <select name="grupo_produto_id" id="grupo_produto_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?= htmlspecialchars($grupo['GRUPO_PRODUTO']) ?>" <?= $filtro_grupo == $grupo['GRUPO_PRODUTO'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($grupo['DESCRICAO_GRUPO_PRODUTO']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col">
                <label for="tipo_complemento" class="form-label">Tipo Complemento:</label>
                <select name="tipo_complemento" id="tipo_complemento" class="form-select">
                    <option value="">Selecione</option>
                    <?php foreach ($tipos_complemento as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['TIPO_COMPLEMENTO']) ?>" 
                            <?= $tipo_complemento == $tipo['TIPO_COMPLEMENTO'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['DESCRICAO_TIPO_COMPLEMENTO']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="search" class="form-label">Buscar:</label>
                <input type="text" name="search" id="search" class="form-control" value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="col">
                <label for="sort_by" class="form-label">Ordenar por:</label>
                <select name="sort_by" id="sort_by" class="form-select">
                    <option value="DESCRICAO_PRODUTO" <?= $sort_by == 'DESCRICAO_PRODUTO' ? 'selected' : '' ?>>Descrição</option>
                    <option value="CODIGO_BARRAS" <?= $sort_by == 'CODIGO_BARRAS' ? 'selected' : '' ?>>Código</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
            <div class="col text-end">
                <a href="cadastrar_produto.php" class="btn btn-success">Cadastrar Produto</a>
            </div>
        </div>
    </form>

    <!-- Tabela de Produtos -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Apelido</th>
                <th>Código</th>
                <th>Grupo</th>
                <th>Tipo Complemento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= htmlspecialchars($produto['DESCRICAO_PRODUTO']) ?></td>
                    <td><?= htmlspecialchars($produto['APELIDO_PRODUTO']) ?></td>
                    <td><?= htmlspecialchars($produto['CODIGO_BARRAS']) ?></td>
                    <td><?= htmlspecialchars($produto['grupo_produto']) ?></td>
                    <td><?= htmlspecialchars($produto['TIPO_COMPLEMENTO']) ?></td>
                    <td>
                        <a href="editar_produto.php?id=<?= $produto['PRODUTO'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="remover_produto.php?id=<?= $produto['PRODUTO'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?')">Remover</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Trocar Empresa -->
    <form method="GET" action="produtos.php" class="mt-3">
        <div class="form-group">
            <label for="empresa_id">Trocar Empresa:</label>
            <select name="empresa_id" id="empresa_id" class="form-select" onchange="this.form.submit()">
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= $empresa['EMPRESA'] ?>" <?= $empresa_id == $empresa['EMPRESA'] ? 'selected' : '' ?>>
                        <?= $empresa['NOME_FANTASIA'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
