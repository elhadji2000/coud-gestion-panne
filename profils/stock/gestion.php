<?php
session_start();
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    .dashboard-header {
        background-color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .dashboard-title {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
        color: #64748b;
        font-size: 1rem;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border-left: 4px solid var(--primary);
        height: 100%;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card.success {
        border-left-color: var(--success);
    }

    .stat-card.warning {
        border-left-color: #f59e0b;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .stat-title {
        color: #64748b;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .action-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s;
        height: 100%;
        border: 1px solid #e2e8f0;
    }

    .action-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .action-icon {
        font-size: 1.75rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .action-title {
        font-weight: 500;
    }

    .date-badge {
        background: #f1f5f9;
        color: #64748b;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    
    <!-- Header simplifié -->
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title">Gestion de Stock</h1>
                    <p class="dashboard-subtitle mb-0">Résumé de votre activité</p>
                </div>
                <div class="date-badge">
                    <i class="fas fa-calendar-day me-2"></i><?= date('d/m/Y') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Actions rapides -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <a href="articles.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon text-primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="action-title">Articles</h3>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="entree_stock.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon text-success">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h3 class="action-title">Entrée Stock</h3>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="sortie_stock.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon text-warning">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h3 class="action-title">Sortie Stock</h3>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="rapports.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon text-info">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3 class="action-title">Rapports</h3>
                    </div>
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-3">
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="stat-title">Articles</p>
                    <h2 class="stat-value"><?= nombreArticles($connexion) ?></h2>
                    <!-- <a href="articles.php" class="btn btn-sm btn-outline-primary">Voir détails</a> -->
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card success">
                    <p class="stat-title">Entrées ce mois</p>
                    <h2 class="stat-value"><?= entreesMois($connexion) ?></h2>
                   <!--  <a href="entree_stock.php" class="btn btn-sm btn-outline-success">Voir détails</a> -->
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card warning">
                    <p class="stat-title">Sorties ce mois</p>
                    <h2 class="stat-value"><?= sortiesMois($connexion) ?></h2>
                    <!-- <a href="sortie_stock.php" class="btn btn-sm btn-outline-warning">Voir détails</a> -->
                </div>
            </div>
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