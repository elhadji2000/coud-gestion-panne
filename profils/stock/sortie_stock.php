<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /COUD/codif/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

// Récupérer la liste des sorties
$sorties = listeSorties($connexion);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Sorties | Stock</title>

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
        --warning: #ffc107;
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
        background-color: var(--warning);
        color: var(--secondary);
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 500;
        border: none;
    }

    .btn-add:hover {
        background-color: #e0a800;
        color: var(--secondary);
    }

    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        overflow-x: auto;
    }

    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--secondary);
        border-bottom-width: 1px;
        white-space: nowrap;
        font-size: 1.2rem;
    }
    .table td {
        font-size: 1.2rem;
    }

    .badge-status {
        padding: 0.35rem 0.65rem;
        font-weight: 500;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .badge-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-canceled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        margin-right: 0.25rem;
    }

    .search-filter {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .table-responsive {
            border: none;
        }
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h1 class="page-title">
                        <i class="fas fa-arrow-up me-2"></i>Gestion des Sorties
                    </h1>
                    <p class="text-muted mb-0">Historique des sorties de stock</p>
                </div>
                <div>
                    <a href="nouvelle_sortie.php" class="btn btn-add">
                        <i class="fas fa-plus me-2"></i>Nouvelle Sortie
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container mb-5">

        <!-- Tableau des sorties -->
        <div class="table-container">
            <table id="entreesTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>N° Sortie</th>
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Article</th>
                        <th>Quantité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sorties as $sortie): ?>
                    <tr>
                        <td><?= htmlspecialchars($sortie['id']) ?></td>
                        <td><?= date('d/m/Y', strtotime($sortie['date_sortie'])) ?></td>
                        <td><?= htmlspecialchars($sortie['references']) ?></td>
                        <td><?= htmlspecialchars($sortie['article']) ?></td>
                        <td><?= htmlspecialchars($sortie['quantite']) ?></td>
                        <td>
                            <a href="annuler_sortie.php?id=<?= $sortie['id'] ?>"
                                class="btn btn-sm btn-outline-danger action-btn" title="Annuler"
                                onclick="return confirm('Confirmer l\'annulation ?')">
                                supprimer
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

    <script>
    $(document).ready(function() {
        $('#entreesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[1, 'desc']]
        });
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>