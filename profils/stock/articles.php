<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

// Afficher les messages de succès/erreur
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

// Récupérer la liste des articles
$articles = listeArticles($connexion);
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
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #f8fafc;
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
        border: none;
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
        margin-bottom: 2rem;
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

    .badge-urgence {
        padding: 0.30rem 0.30rem;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-faible {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-moyenne {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-elevee {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-etat {
        padding: 0.30rem 0.30rem;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-resolu {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-encours {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-nonresolu {
        background-color: #f8d7da;
        color: #721c24;
    }

    .action-btns {
        white-space: nowrap;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
    }

    .action-link {
        border-radius: 4px;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .action-link:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .link-edit {
        color: var(--primary);
    }

    .link-edit:hover {
        color: #1a6fc9;
    }

    .link-delete {
        color: var(--danger);
    }

    .link-delete:hover {
        color: #c82333;
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
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
                    <p class="text-muted mb-0">Liste complète des articles en stock</p>
                </div>
                <?php if (isset($_SESSION['profil2']) && $_SESSION['profil2'] === 'chef d\'atelier'): ?>
                <div>
                    <a href="nouvel_article.php" class="btn btn-add">
                        <i class="fas fa-plus me-2"></i>Nouvel Article
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container mb-5">
        <div class="table-container">
            <div class="table-responsive">
                <table id="articlesTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <?php if (isset($_SESSION['profil2']) && $_SESSION['profil2'] === 'chef d\'atelier'): ?>
                            <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n=0; ?>
                        <?php foreach($articles as $article): ?>
                        <tr>
                            <?php $n++; ?>
                            <td><?= htmlspecialchars($n) ?></td>
                            <td><?= htmlspecialchars($article['nom']) ?></td>
                            <td><?= htmlspecialchars($article['categorie']) ?></td>
                            <?php if (isset($_SESSION['profil2']) && $_SESSION['profil2'] === 'chef d\'atelier'): ?>
                            <td>
                                <a href="nouvel_article.php?id=<?= $article['id'] ?>" class="action-link link-edit me-2"
                                    title="Modifier">
                                    <i class="fas fa-edit me-1"></i>Modifier
                                </a>
                                <a href="#" class="action-link link-delete delete-btn" title="Supprimer"
                                    data-id="<?= $article['id'] ?>">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a id="confirmDelete" href="#" class="btn btn-danger">Confirmer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialisation de DataTable
        $('#articlesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            responsive: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ]
        });

        // Gestion de la suppression
        $('.delete-btn').click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#confirmDelete').attr('href', 'supprimer_article.php?id=' + id);
            $('#deleteModal').modal('show');
        });
    });
    </script>

    <?php if (isset($success) && $success === 'suppression'): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    L'article a été supprimé avec succès.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    </script>
    <?php endif; ?>
    <?php if (isset($success) && $success === 'modifier'): ?>
    <div class="modal fade" id="successModal2" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    L'article a été modifiè avec succès.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal2'));
    modal.show();
    </script>
    <?php endif; ?>
    <?php if (isset($success) && $success === 'enregistrer'): ?>
    <div class="modal fade" id="successModal3" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    L'article a été enregistrer avec succès.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal3'));
    modal.show();
    </script>
    <?php endif; ?>

    <?php if (isset($error) && $error === 'suppression'): ?>
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Erreur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Une erreur est survenue lors de la suppression de l'article.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
    </script>
    <?php endif; ?>

    <?php include('../../footer.php'); ?>
</body>

</html>