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
$result = allPannesByUser($connexion, $userId, 1, 200);
$allPannes = $result['pannes'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Pannes | Tableau de bord</title>

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
        border-bottom-width: 1px;
    }

    .badge-urgence {
        padding: 0.35rem 0.65rem;
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
        padding: 0.35rem 0.65rem;
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
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h1 class="page-title">
                        <i class="fas fa-tools me-2"></i>Gestion des Pannes
                    </h1>
                    <p class="text-muted mb-0">Liste complète des pannes enregistrées</p>
                </div>
                <div>
                    <a href="ajoutPanne.php" class="btn btn-add">
                        <i class="fas fa-plus me-2"></i>Nouvelle Panne
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container-fluid mb-5">
        <div class="table-container">
            <div class="table-responsive">
                <table id="pannesTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Type</th>
                            <th>Localisation</th>
                            <th>Urgence</th>
                            <th>Date</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allPannes as $panne): ?>
                        <tr>
                            <td><?= htmlspecialchars($panne['id']) ?></td>
                            <td><?= htmlspecialchars($panne['type_panne']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($panne['localisation'])) ?></td>
                            <td>
                                <?php 
                                $urgenceClass = '';
                                switch($panne['niveau_urgence']) {
                                    case 'Faible': $urgenceClass = 'badge-faible'; break;
                                    case 'Moyenne': $urgenceClass = 'badge-moyenne'; break;
                                    case 'Élevée': $urgenceClass = 'badge-elevee'; break;
                                }
                                ?>
                                <span class="badge-urgence <?= $urgenceClass ?>"><?= $panne['niveau_urgence'] ?></span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($panne['date_enregistrement'])) ?></td>
                            <td>
                                <?php 
                                $etatClass = '';
                                switch($panne['resultat']) {
                                    case 'depanner': $etatClass = 'badge-resolu'; break;
                                    case 'en cours': $etatClass = 'badge-encours'; break;
                                    default: $etatClass = 'badge-nonresolu';
                                }
                                ?>
                                <span class="badge-etat <?= $etatClass ?>">
                                    <?= $panne['resultat'] === 'depanner' ? 'Résolu' : ($panne['resultat'] === 'en cours' ? 'En cours' : 'Non résolu') ?>
                                </span>
                            </td>
                            <td class="action-btns">
                                <!-- Bouton Voir -->
                                <a href="../vuePanne.php?idPanne=<?= $panne['id'] ?>" class="text-info action-voir"
                                    title="Voir détails">
                                    <i class="fas fa-eye"></i>voir
                                </a>|

                                <!-- Bouton Observation -->
                                <?php if ($panne['resultat'] === 'en cours' || $panne['resultat'] === 'depanner'): ?>
                                <span class="text-info action-observation" title="Ajouter observation"
                                    style="cursor: pointer;"
                                    onclick="window.location.href='observation?idp=<?= $panne['id'] ?>&idInt=<?= $panne['idIntervention'] ?>&idObservation=<?= $panne['idObservation'] ?>'">
                                    <i class="fas fa-edit"></i> obs
                                </span>
                                <?php else: ?>
                                <span class="text-muted" title="Observation non disponible"
                                    style="cursor: not-allowed; opacity: 0.5;">
                                    <i class="fas fa-edit"></i> obs
                                </span>
                                <?php endif; ?>|


                                <!-- Bouton Supprimer -->
                                <?php if (!($panne['resultat'] === 'depanner' || $panne['resultat'] === 'en cours' || $panne['instruction'] !== null)): ?>
                                <span class="text-danger action-suppr delete-btn" data-panne-id="<?= $panne['id'] ?>"
                                    title="Supprimer" style="cursor: pointer;">
                                    <i class="fas fa-trash"></i> suppr
                                </span>
                                <?php else: ?>
                                <span class="text-muted" title="Suppression non disponible"
                                    style="cursor: not-allowed; opacity: 0.5;">
                                    <i class="fas fa-trash"></i> suppr
                                </span>
                                <?php endif; ?>

                            </td>
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
                    <p>Êtes-vous sûr de vouloir supprimer cette panne ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="post" action="../../traitement/traitement">
                        <input type="hidden" name="panneDelete" id="deletePanneId">
                        <input type="hidden" name="action" value="deletePanne">
                        <button type="submit" class="btn btn-danger">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de succès pour observation -->
    <?php if (isset($_GET['obs']) && $_GET['obs'] == 1): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>L'observation a été enregistrée avec succès !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialisation de DataTable
        $('#pannesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [
                [0, 'desc']
            ],
            responsive: true
        });

        // Gestion de la suppression
        $('.delete-btn').click(function() {
            var panneId = $(this).data('panne-id');
            $('#deletePanneId').val(panneId);
            $('#deleteModal').modal('show');
        });

        // Afficher le modal de succès si nécessaire
        <?php if (isset($_GET['obs']) && $_GET['obs'] == 1): ?>
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        <?php endif; ?>
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>