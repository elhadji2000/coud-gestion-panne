<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /COUD/codif/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

// Récupérer la liste des articles
$articles = listeArticles($connexion);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles | Stock</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
    :root {
        --primary: #3498db;
        --secondary: #2c3e50;
        --success: #28a745;
        --light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #f8fafc;
        color: var(--secondary);
    }

    .page-header {
        background-color: white;
        padding: 1.5rem 0;
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }

    .btn-add {
        background-color: var(--success);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 500;
    }

    .btn-add:hover {
        background-color: #218838;
        color: white;
    }

    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
    }

    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--secondary);
        border-bottom-width: 1px;
    }

    .badge-stock {
        padding: 0.35rem 0.65rem;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-low {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-ok {
        background-color: #d4edda;
        color: #155724;
    }

    .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-boxes me-2"></i>Gestion des Articles
                    </h1>
                    <p class="text-muted mb-0">Liste complète des articles</p>
                </div>
                <div>
                    <a href="nouvel_article.php" class="btn btn-add">
                        <i class="fas fa-plus me-2"></i>Nouvel Article
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container mb-5">
        <div class="table-container">
            <table id="articlesTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>nom</th>
                        <th>Categorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($articles as $article): ?>
                    <tr>
                        <td><?= htmlspecialchars($article['id']) ?></td>
                        <td><?= htmlspecialchars($article['references']) ?></td>
                        <td><?= htmlspecialchars($article['description']) ?></td>
                        <td><?= htmlspecialchars($article['nom']) ?></td>
                        <td><?= htmlspecialchars($article['categorie']) ?></td>
                        <td>
                            <a href="../../traitement/supprimer_article.php?id=<?= $article['id'] ?>"
                                class="btn btn-sm btn-outline-danger action-btn" title="Supprimer"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                <i>supprimer</i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <?php include('../../footer.php'); ?>
</body>

</html>