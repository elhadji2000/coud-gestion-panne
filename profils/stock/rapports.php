<?php
session_start();
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

// Récupérer les données pour les rapports
$entrees = listeEntrees($connexion);
$sorties = listeSorties($connexion);
$articles = listeArticles($connexion);
$statsGlobales = getStatsGlobales($connexion);
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
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <style>
    :root {
        --primary: #3498db;
        --secondary: #2c3e50;
        --success: #28a745;
        --danger: #dc3545;
        --warning: #ffc107;
        --light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #f8fafc;
        color: var(--secondary);
    }

    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--secondary);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e9ecef;
    }

    .table td {
        vertical-align: middle;
    }

    .card-stat {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        border: none;
    }

    .card-stat .card-body {
        padding: 1.5rem;
    }

    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 600;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .card-entree {
        border-left: 4px solid var(--success);
    }

    .card-sortie {
        border-left: 4px solid var(--danger);
    }

    .card-stock {
        border-left: 4px solid var(--primary);
    }

    .card-article {
        border-left: 4px solid var(--warning);
    }

    .section-title {
        font-weight: 600;
        color: var(--secondary);
        margin: 30px 0 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 30px;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
        border-bottom: 3px solid var(--primary);
    }

    .badge-entree {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .badge-sortie {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger);
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Rapports de Stock</h2>

        <!-- Statistiques globales -->
        <div class="row">
            <div class="col-md-3">
                <div class="card card-stat card-entree">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="stat-label">Entrées totales</h6>
                                <h3 class="stat-value"><?= $statsGlobales['total_entrees'] ?></h3>
                            </div>
                            <i class="fas fa-boxes stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat card-sortie">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="stat-label">Sorties totales</h6>
                                <h3 class="stat-value"><?= $statsGlobales['total_sorties'] ?></h3>
                            </div>
                            <i class="fas fa-truck-loading stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat card-stock">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="stat-label">Articles en stock</h6>
                                <h3 class="stat-value"><?= $statsGlobales['total_articles'] ?></h3>
                            </div>
                            <i class="fas fa-box-open stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat card-article">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="stat-label">Mouvements</h6>
                                <h3 class="stat-value"><?= $statsGlobales['total_mouvements'] ?></h3>
                            </div>
                            <i class="fas fa-exchange-alt stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rapports détaillés -->
        <ul class="nav nav-tabs mt-4" id="rapportsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="global-tab" data-bs-toggle="tab" data-bs-target="#global"
                    type="button" role="tab">Synthèse</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="entrees-tab" data-bs-toggle="tab" data-bs-target="#entrees" type="button"
                    role="tab">Entrées</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sorties-tab" data-bs-toggle="tab" data-bs-target="#sorties" type="button"
                    role="tab">Sorties</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="articles-tab" data-bs-toggle="tab" data-bs-target="#articles" type="button"
                    role="tab">Par Article</button>
            </li>
        </ul>

        <div class="tab-content" id="rapportsTabContent">
            <!-- Onglet Synthèse -->
            <div class="tab-pane fade show active" id="global" role="tabpanel">
                <div class="table-container mt-3">
                    <h5 class="section-title"><i class="fas fa-chart-pie me-2"></i>Synthèse des mouvements</h5>
                    <table id="synthèseTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Référence</th>
                                <th>Stock Initial</th>
                                <th>Entrées</th>
                                <th>Sorties</th>
                                <th>Stock Actuel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($articles as $article): 
                                $statsArticle = getStatsArticle($connexion, $article['id']);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($article['nom']) ?></td>
                                <td><?= htmlspecialchars($article['references']) ?></td>
                                <td><?= $statsArticle['stock_initial'] ?></td>
                                <td><span class="badge badge-entree">+<?= $statsArticle['total_entrees'] ?></span></td>
                                <td><span class="badge badge-sortie">-<?= $statsArticle['total_sorties'] ?></span></td>
                                <td><strong><?= $statsArticle['stock_actuel'] ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Entrées -->
            <div class="tab-pane fade" id="entrees" role="tabpanel">
                <div class="table-container mt-3">
                    <h5 class="section-title"><i class="fas fa-boxes me-2"></i>Détail des entrées</h5>
                    <table id="entreesTable" class="table table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date dernière entrée</th>
                                <th>Référence</th>
                                <th>Article</th>
                                <th>Quantité totale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach($entrees as $entree): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= date('d/m/Y', strtotime($entree['derniere_entree'])) ?></td>
                                <td><?= htmlspecialchars($entree['references']) ?></td>
                                <td><?= htmlspecialchars($entree['article']) ?></td>
                                <td><?= htmlspecialchars($entree['total_quantite']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Sorties -->
            <div class="tab-pane fade" id="sorties" role="tabpanel">
                <div class="table-container mt-3">
                    <h5 class="section-title"><i class="fas fa-truck-loading me-2"></i>Détail des sorties</h5>
                    <table id="sortiesTable" class="table table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date dernière sortie</th>
                                <th>Référence</th>
                                <th>Article</th>
                                <th>Quantité totale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach($sorties as $sortie): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= date('d/m/Y', strtotime($sortie['derniere_sortie'])) ?></td>
                                <td><?= htmlspecialchars($sortie['references']) ?></td>
                                <td><?= htmlspecialchars($sortie['article']) ?></td>
                                <td><?= htmlspecialchars($sortie['total_quantite']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Articles -->
            <div class="tab-pane fade" id="articles" role="tabpanel">
                <div class="table-container mt-3">
                    <h5 class="section-title"><i class="fas fa-box-open me-2"></i>Mouvements par article</h5>
                    <div class="mb-4">
                        <select id="selectArticle" class="form-select" style="width: 300px;">
                            <option value="">Tous les articles</option>
                            <?php foreach($articles as $article): ?>
                            <option value="<?= $article['id'] ?>"><?= htmlspecialchars($article['references']) ?> -
                                <?= htmlspecialchars($article['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <table id="articlesTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Article</th>
                                <th>Référence</th>
                                <th>Quantité</th>
                                <th style="display: none;">ArticleID</th> <!-- colonne cachée -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Combiner entrées et sorties pour l'affichage
                            foreach($entrees as $entree): ?>
                            <tr data-article="<?= $entree['article_id'] ?>">
                                <td><?= date('d/m/Y', strtotime($entree['derniere_entree'])) ?></td>
                                <td><span class="badge badge-entree">Entrée</span></td>
                                <td><?= htmlspecialchars($entree['article']) ?></td>
                                <td><?= htmlspecialchars($entree['references']) ?></td>
                                <td>+<?= $entree['total_quantite'] ?></td>
                                <td style="display:none;"><?= $entree['article_id'] ?></td>
                            </tr>
                            <?php endforeach; 
                            
                            foreach($sorties as $sortie): ?>
                            <tr data-article="<?= $sortie['article_id'] ?>">
                                <td><?= date('d/m/Y', strtotime($sortie['derniere_sortie'])) ?></td>
                                <td><span class="badge badge-sortie">Sortie</span></td>
                                <td><?= htmlspecialchars($sortie['article']) ?></td>
                                <td><?= htmlspecialchars($sortie['references']) ?></td>
                                <td>-<?= $sortie['total_quantite'] ?></td>
                                <td style="display:none;"><?= $sortie['article_id'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"></script>

    <script>
    $(document).ready(function() {
        // Initialiser les DataTables
        $('#synthèseTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf']
        });

        $('#entreesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [
                [1, 'desc']
            ],
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf']
        });

        $('#sortiesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [
                [1, 'desc']
            ],
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf']
        });

        var articlesTable = $('#articlesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf'],
            columnDefs: [{
                targets: 5, // 7e colonne (index 6)
                visible: false,
                searchable: true
            }]
        });



        $('#selectArticle').on('change', function() {
            var articleId = $(this).val();
            articlesTable.column(6).search(articleId).draw();
        });



    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>