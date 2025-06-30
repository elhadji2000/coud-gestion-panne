<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] == 'changeUserStatus') {
        $userId = $_GET['userStatusChange'];
        $newStatus = $_GET['newStatus'];

        // Mettre à jour le statut de l'utilisateur dans la base de données
        $sql = "UPDATE Utilisateur SET statut = ? WHERE id = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('ii', $newStatus, $userId);
        if ($stmt->execute()) {
            
            header('Location: /COUD/panne/profils/admin/users?message=Statut modifié avec succès');
            exit();
        } else {
            header('Location: /COUD/panne/profils/admin/users?message=error');
            exit();
        }
        exit();
    }
}
// Vérifier les paramètres
if (isset($_GET['user_id']) && isset($_GET['new_state'])) {
    $userId = (int)$_GET['user_id'];
    $newState = (int)$_GET['new_state'];
    
    // Requête simple
    $query = "UPDATE utilisateur SET recevoir_alerte = $newState WHERE id = $userId";
    mysqli_query($connexion, $query);
    
    // Redirection vers la page précédente
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit;
}

$userId = $_SESSION['id_user'];
$allUsers = allUtilisateurs($connexion);
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <style>
    :root {
        --primary: #3777B0;
        --secondary: #2c3e50;
        --success: #28a745;
        --danger: #dc3545;
        --warning: #ffc107;
        --light: #f8f9fa;
        --dark: #343a40;
    }

    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #f5f7fa;
    }

    .main-container {
        margin-top: 80px;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        background-color: var(--primary);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }

    .card-title {
        font-weight: 600;
        margin-bottom: 0;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #2c6090;
        border-color: #2c6090;
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

    .status-badge2 {
        width: 80px;
        display: inline-block;
        text-align: center;
        padding: 0.35rem 0.5rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }

    .status-updated {
        background-color: #e6f7ee;
        color: #28a745;
    }

    .status-default {
        background-color: #f0f0f0;
        color: #6c757d;
    }

    .status-active {
        background-color: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }

    .action-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        border-radius: 50%;
    }

    .badge-profile {
        padding: 0.35rem 0.5rem;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.75rem;
        color: white;
    }

    .toggle-pill {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        background: #dc3545;
        color: white;
        font-size: 0.6rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .toggle-pill.active {
        background: #28a745;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header d-flex justify-content-between align-items-center p-3 rounded"
                    style="background-color: var(--primary);">
                    <div>
                        <h2 class="text-white mb-0"><i class="fas fa-users-cog me-2"></i>Gestion des Utilisateurs</h2>
                        <p class="text-white-50 mb-0 small">Liste complète des utilisateurs du système</p>
                    </div>
                    <a href="addUser.php" class="btn btn-light">
                        <i class="fas fa-plus-circle me-2"></i>Nouvel Utilisateur
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom Utilisateur</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Prénom</th>
                                        <th>Nom</th>
                                        <th>Profil Principal</th>
                                        <th>Profil Secondaire</th>
                                        <th>Statut</th>
                                        <th>Alerte</th>
                                        <th>Type</th>
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
                                        <td>
                                            <span class="badge-profile bg-primary">
                                                <?= htmlspecialchars($user['profil1']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-profile bg-secondary">
                                                <?= htmlspecialchars($user['profil2']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge <?= $user['statut'] == 1 ? 'status-active' : 'status-inactive' ?>">
                                                <?= $user['statut'] == 1 ? 'Actif' : 'Inactif' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="toggle-pill <?= $user['recevoir_alerte'] ? 'active' : '' ?>"
                                                onclick="toggleAlertStatus(<?= $user['id'] ?>, <?= $user['recevoir_alerte'] ? 1 : 0 ?>, this)">
                                                <?= $user['recevoir_alerte'] ? 'ON' : 'OFF' ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span
                                                class="status-badge <?= $user['type_mdp'] == 'updated' ? 'status-updated' : 'status-default' ?>">
                                                <?= $user['type_mdp'] == 'updated' ? 'Updated' : 'Default' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="addUser.php?user_id=<?= htmlspecialchars($user['id']) ?>"
                                                class="btn btn-sm btn-primary action-btn" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button
                                                class="btn btn-sm btn-<?= $user['statut'] == 1 ? 'danger' : 'success' ?> action-btn change-status"
                                                data-user-id="<?= htmlspecialchars($user['id']) ?>"
                                                data-current-status="<?= $user['statut'] ?>"
                                                title="<?= $user['statut'] == 1 ? 'Désactiver' : 'Activer' ?>">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de statut -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white" id="modalHeader">
                    <!-- Changé pour être dynamique via JS -->
                    <h5 class="modal-title" id="statusModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment <span id="statusActionText"></span> cet utilisateur ?</p>
                    <p class="small text-muted">Cette action affectera immédiatement les droits d'accès de
                        l'utilisateur.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="statusForm" method="GET" action="users.php">
                        <!-- Changé en POST -->
                        <input type="hidden" name="userStatusChange" id="statusUserIdInput" value="">
                        <input type="hidden" name="newStatus" id="newStatusInput" value="">
                        <input type="hidden" name="action" value="changeUserStatus">
                        <button type="submit" class="btn btn-success" id="confirmStatusBtn">
                            <!-- Couleur de base -->
                            Confirmer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
    $(document).ready(function() {
        // ... (le reste de votre code DataTable)
        // Initialisation de DataTable avec options avancées
        var table = $('#usersTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            columnDefs: [{
                    targets: [0],
                    width: '5%'
                },
                {
                    targets: [3, 8, 9],
                    width: '8%'
                },
                {
                    orderable: false,
                    targets: [9]
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        // Gestion du changement de statut
        $('.change-status').on('click', function() {
            const userId = $(this).data('user-id');
            const currentStatus = $(this).data('current-status');
            const newStatus = currentStatus == 1 ? 0 : 1;
            const actionText = newStatus == 1 ? 'activer' : 'désactiver';

            // Mise à jour du modal
            $('#statusActionText').text(actionText);
            $('#statusUserIdInput').val(userId);
            $('#newStatusInput').val(newStatus);

            // Changement des couleurs selon l'action
            const modalColor = newStatus == 1 ? 'success' : 'danger';
            $('#modalHeader').removeClass('bg-success bg-danger').addClass('bg-' + modalColor);
            $('#confirmStatusBtn').removeClass('btn-success btn-danger').addClass('btn-' + modalColor);

            // Affichage du modal
            $('#statusModal').modal('show');
        });

        // Après confirmation, soumission du formulaire
        $('#statusForm').on('submit', function(e) {
            e.preventDefault();

            // Fermer le modal
            $('#statusModal').modal('hide');

            // Envoyer la requête AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'GET',
                data: $(this).serialize(),
                success: function(response) {
                    // Recharger la page après succès
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // Afficher un message d'erreur si nécessaire
                    alert('Une erreur est survenue: ' + error);
                    console.error(xhr.responseText);
                }
            });
        });
    });

    function toggleAlertStatus(userId, currentState, element) {
        const newState = currentState ? 0 : 1;

        if (confirm(`Voulez-vous ${newState ? 'activer' : 'désactiver'} les alertes ?`)) {
            // Envoyer la requête simple (sans AJAX)
            window.location.href = `users.php?user_id=${userId}&new_state=${newState}`;
        }
    }
    </script>


    <?php include('../../footer.php'); ?>
</body>

</html>