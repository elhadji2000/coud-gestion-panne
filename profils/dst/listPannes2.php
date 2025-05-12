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
$result = allPannes($connexion, $page, $limit, $profil2, null, $dst); // Récupère toutes les pannes
$allPannes = $result['pannes'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GESCOUD</title>

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
            max-width: 500px; /* Champ de recherche plus large */
            margin: 15px auto;
        }
        
        .input-group-text, .form-control {
            padding: 1rem 1rem; /* Meilleur espacement dans les inputs */
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
        font-size: 1.5rem; /* En-têtes légèrement plus grands */
    }

    .table thead th {
        background-color: #2c3e50;
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
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    <br>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1><i class="fas fa-tools me-2"></i>Gestion des Pannes</h1>
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

        <div class="row mb-3">
            <div class="col-12 text-end">
                <a href="ajoutPanne.php" class="btn btn-success">
                    <strong><i class="fas fa-plus me-2"></i>Déclarer une Panne</strong>
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
                        <th>Type</th>
                        <th>Localisation</th>
                        <th>Urgence</th>
                        <th>Date</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pannesTable">
                    <?php foreach ($allPannes as $panne): ?>
                    <tr>
                        <td><?= htmlspecialchars($panne['id']) ?></td>
                        <td><?= htmlspecialchars($panne['type_panne']) ?></td>
                        <td><?= htmlspecialchars($panne['localisation']) ?></td>
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

                            <!-- Bouton Observation -->
                            <?php if ($panne['resultat'] === 'en cours' || $panne['resultat'] === 'depanner'): ?>
                            <a href="observation?idp=<?= $panne['id'] ?>&idInt=<?= $panne['idIntervention'] ?>&idObservation=<?= $panne['idObservation'] ?>"
                                class="btn btn-info btn-sm action-btn" title="Ajouter observation">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-info btn-sm action-btn disabled" title="Non disponible">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php endif; ?>

                            <!-- Bouton Supprimer -->
                            <?php if (!($panne['resultat'] === 'depanner' || $panne['resultat'] === 'en cours' || $panne['instruction'] !== null)): ?>
                            <button class="btn btn-danger btn-sm action-btn delete-btn"
                                data-panne-id="<?= htmlspecialchars($panne['id']) ?>" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-danger btn-sm action-btn disabled" title="Non disponible">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
    <!-- Success Modal -->
    <?php if (isset($_GET['obs']) && $_GET['obs'] == 1): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Observation enregistrée avec succès !
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    </script>
    <?php endif; ?>

    <?php include('../../footer.php'); ?>
</body>

</html>