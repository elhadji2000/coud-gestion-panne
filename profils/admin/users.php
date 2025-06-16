<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/panne/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

$userId = $_SESSION['id_user'];
$allUsers = allUtilisateurs($connexion);
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
        font-size: 1.2rem;
    }
    .table td {
        font-size: 1.2rem;
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
                        <i class="fas fa-users me-2"></i>Gestion des Utilisateurs
                    </h1>
                    <p class="text-muted mb-0">Liste complète des Utilisateurs</p>
                </div>
                <div>
                    <a href="addUser.php" class="btn btn-add">
                        <i class="fas fa-plus me-2"></i>Nouvel Utilisateur
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container-fluid mb-5">
        <div class="table-container">
            <table id="articlesTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Username</th>
                        <th>email</th>
                        <th>Telephone</th>
                        <th>Prenom</th>
                        <th>Nom</th>
                        <th>Profile 1</th>
                        <th>Profile 2</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($allUsers as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['telephone']) ?></td>
                        <td><?= htmlspecialchars($user['prenom']) ?></td>
                        <td><?= htmlspecialchars(strtoupper($user['nom'])) ?></td>
                        <td><?= htmlspecialchars($user['profil1']) ?></td>
                        <td><?= htmlspecialchars($user['profil2']) ?></td>
                        <td>
                            <input class="form-check-input status-checkbox change-status" type="checkbox"
                                data-bs-toggle="modal" data-bs-target="#statusModal"
                                data-user-id="<?= htmlspecialchars($user['id']) ?>"
                                <?= $user['statut'] == 1 ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <a href="addUser.php?user_id=<?= htmlspecialchars($user['id']) ?>"
                                class="btn btn-sm btn-outline-danger action-btn" title="modifier"
                                onclick="return confirm('Êtes-vous sûr de vouloir modifier cet article ?')">
                                <i>modifier</i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de confirmation de statut -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="statusModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Voulez-vous vraiment <span id="statusActionText"></span> cet utilisateur ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="statusForm" method="post" action="../../traitement/traitement">
                        <input type="hidden" name="userStatusChange" id="statusUserIdInput" value="">
                        <input type="hidden" name="newStatus" id="newStatusInput" value="">
                        <input type="hidden" name="action" value="changeUserStatus">
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Remplacez le script JavaScript par cette version améliorée -->
    <script>
    $(document).ready(function() {
        const $searchInput = $('#searchInput');
        const $resetSearch = $('#resetSearch');
        const $usersTable = $('#usersTable');
        const $rows = $usersTable.find('tr');

        // Fonction de recherche
        function performSearch() {
            const value = $searchInput.val().toLowerCase().trim();

            if (value.length > 0) {
                $resetSearch.show();
            } else {
                $resetSearch.hide();
            }

            $rows.each(function() {
                const $row = $(this);
                const text = $row.text().toLowerCase();
                $row.toggle(text.includes(value));
            });
        }

        // Recherche en temps réel avec délai
        let searchTimeout;
        $searchInput.on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300);
        });

        // Réinitialisation de la recherche
        $resetSearch.on('click', function() {
            $searchInput.val('').focus();
            $resetSearch.hide();
            $rows.show();
        });

        // Afficher/masquer le bouton de réinitialisation
        $searchInput.on('input', function() {
            if ($(this).val().length > 0) {
                $resetSearch.show();
            } else {
                $resetSearch.hide();
            }
        });

        // Gestion des checkboxes de statut
        $('.change-status').on('change', function() {
            const isChecked = $(this).is(':checked');
            const userId = $(this).data('user-id');

            $('#statusActionText').text(isChecked ? 'activer' : 'désactiver');
            $('#statusUserIdInput').val(userId);
            $('#newStatusInput').val(isChecked ? 1 : 0);
        });
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>