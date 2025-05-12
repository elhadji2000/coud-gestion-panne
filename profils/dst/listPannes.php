<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/panne/');
    exit();
}
unset($_SESSION['classe']);

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
$userId = $_SESSION['id_user'];
$profil2 = $_SESSION['profil2'];
$profil1 = $_SESSION['profil'];
// Vérifiez le profil et définissez les variables appropriées
$isSEM = ($profil2 === 'S.E.M');
$isDst = ($profil2 === 'chef dst');
$dst = ($profil1 === 'dst');

// Récupérer toutes les pannes pour le filtrage côté client
$result = allPannes($connexion, 1, 200, null, '', $dst);
$allPannes = $result['pannes'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        font-size: 1.05rem;
        padding: 0;
        margin: 0;
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

    .table-container {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
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

    .badge-urgence {
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
        font-size: 0.95rem;
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
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
        font-size: 0.95rem;
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

    .action-btn {
        margin: 0 3px;
        font-size: 1.2rem;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    <br>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1><i class="fas fa-tools me-2"></i>Liste des Pannes</h1>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="search-container mx-auto">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Rechercher par type, localisation ou état...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Type</th>
                        <th>Localisation</th>
                        <th>Urgence</th>
                        <th>Date</th>
                        <th>État</th>
                        <th>Actions</th>
                        <?php if ($_SESSION['profil'] == 'sem' || $_SESSION['profil'] == 'dst') : ?>
                        <th>Imputation</th>
                        <?php endif; ?>
                        <?php if ($_SESSION['profil'] == 'atelier') : ?>
                        <th>Intervention</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="pannesTable">
                    <?php foreach ($allPannes as $panne): ?>
                    <tr>
                        <td><?= htmlspecialchars($panne['id']) ?></td>
                        <td><?= htmlspecialchars($panne['type_panne']) ?></td>
                        <td><?= htmlspecialchars(strtolower($panne['localisation'])) ?></td>
                        <td>
                            <?php if ($panne['niveau_urgence'] == 'Faible'): ?>
                            <span class="badge-urgence badge-faible">Faible</span>
                            <?php elseif($panne['niveau_urgence'] == 'Moyenne'): ?>
                            <span class="badge-urgence badge-moyenne">Moyenne</span>
                            <?php elseif($panne['niveau_urgence'] == 'Élevée'): ?>
                            <span class="badge-urgence badge-elevee">Élevée</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($panne['date_enregistrement']) ?></td>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <span class="badge-etat badge-resolu">Résolu</span>
                            <?php elseif ($panne['resultat'] == 'en cours'): ?>
                            <span class="badge-etat badge-encours">En cours</span>
                            <?php else: ?>
                            <span class="badge-etat badge-nonresolu">Non résolu</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Bouton Voir -->
                            <a href="../vuePanne.php?idPanne=<?= htmlspecialchars($panne['id']) ?>"
                                class="btn btn-primary btn-sm action-btn" title="Voir détails">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>

                        <?php if ($_SESSION['profil'] == 'sem' || $_SESSION['profil'] == 'dst') : ?>
                        <td>
                            <?php if ($isSEM): ?>
                            <?php if ($panne['resultat_imp'] === 'imputer'): ?>
                            <button class="btn btn-success btn-sm action-btn" disabled title="Déjà imputé">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php else: ?>
                            <a href="imputation.php?idPanne=<?= htmlspecialchars($panne['id']) ?>&type=<?= htmlspecialchars($panne['type_panne']) ?>"
                                class="btn btn-warning btn-sm action-btn" title="Imputer">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($isDst): ?>
                            <?php if ($panne['resultat_imp'] === 'imputer'): ?>
                            <span class="badge bg-success">OUI</span>
                            <?php else: ?>
                            <span class="badge bg-danger">NON</span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>

                        <?php if ($_SESSION['profil'] == 'atelier') : ?>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <button class="btn btn-success btn-sm action-btn" disabled title="Déjà dépanné">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php elseif ($panne['resultat'] == 'en cours'): ?>
                            <button class="btn btn-warning btn-sm action-btn" disabled title="En cours">
                                <i class="fas fa-spinner"></i>
                            </button>
                            <?php else: ?>
                            <a href="intervention?idp=<?= $panne['id'] ?>" class="btn btn-danger btn-sm action-btn"
                                title="Intervenir">
                                <i class="fas fa-wrench"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette panne ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="post" action="../../traitement/traitement">
                        <input type="hidden" name="panneDelete" id="deletePanneIdInput" value="">
                        <input type="hidden" name="action" value="deletePanne">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Success Modal -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 2): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    vous avez affectè la tache à la section <?= htmlspecialchars($_GET['type_panne']) ?> !
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal"
                        onclick="closeModalAndClearParams()">Fermer</button>

                    <script>
                    function closeModalAndClearParams() {
                        const url = new URL(window.location);
                        url.search = ''; // Supprime tous les paramètres
                        window.history.replaceState({}, '', url);
                    }
                    </script>

                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    </script>
    <?php endif; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Recherche en temps réel
        $('#searchInput').on('keyup', function() {
            const searchText = $(this).val().toLowerCase();

            $('#pannesTable tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(searchText));
            });
        });

        // Gestion des boutons Supprimer
        $('.delete-btn').click(function() {
            const panneId = $(this).data('panne-id');
            $('#deletePanneIdInput').val(panneId);
            $('#deleteModal').modal('show');
        });
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>