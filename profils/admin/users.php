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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .search-container {
        max-width: 600px;
        margin: 20px auto;
    }

    .search-box {
        position: relative;
    }

    #resetSearch {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #6c757d;
        cursor: pointer;
        display: none;
    }

    #resetSearch:hover {
        color: #495057;
    }

    .table {
        width: 100%;
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th,
    .table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        text-align: center;
        border-top: 1px solid #dee2e6;
        font-size: 1.5rem;
    }

    .table thead th {
        background-color: #3777B0;
        color: #ffffff;
        font-weight: 500;
        border-bottom: 2px solid #2c3e50;
    }

    .status-checkbox {
        transform: scale(1.5);
        cursor: pointer;
    }

    .action-btn {
        padding: 5px 10px;
        margin: 0 2px;
    }

    .container-fluid {
        width: 100%;
        padding: 2rem;
    }

    .search-container {
        max-width: 500px;
        margin: 15px auto;
    }

    .input-group-text,
    .form-control {
        padding: 1rem 1rem;
        font-size: 1.5rem;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    <br>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1><i class="fas fa-users me-2"></i>Gestion des Utilisateurs</h1>
            </div>
        </div>

        <!-- Remplacez la partie recherche dans le body par ce code -->
        <div class="row mb-4">
            <div class="col-8">
                <div class="search-container">
                    <div class="search-box mx-auto">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Rechercher un utilisateur...">
                            <button id="resetSearch" type="button" class="btn-close" aria-label="Reset"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end search-container">
            <a href="addUser" class="btn btn-success form-control">
                <i class="fas fa-plus me-2"></i>Ajouter Utilisateur
            </a>
        </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Profil 1</th>
                        <th>Profil 2</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    <?php foreach ($allUsers as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['telephone']) ?></td>
                        <td><?= htmlspecialchars(strtoupper($user['nom'])) ?></td>
                        <td><?= htmlspecialchars($user['prenom']) ?></td>
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
                                class="btn btn-primary btn-action" title="Modifier">
                                <i class="fas fa-pencil-alt"></i>
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