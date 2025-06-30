<?php
session_start();
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
    <!-- Bootstrap CSS seulement -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        font-size: 16px;
    }
    
    .header {
        background-color: #fff;
        padding: 20px 0;
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .page-title {
        color: #333;
        font-weight: bold;
    }
    
    .table-container {
        background: white;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 15px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .badge {
        padding: 5px 8px;
        font-weight: 500;
        font-size: 0.85rem;
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
    
    .badge-resolu {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-encours {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-nontraite {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .action-btn {
        padding: 3px 8px;
        font-size: 0.85rem;
        margin-right: 5px;
    }
    
    .btn-add {
        background-color: #28a745;
        color: white;
        padding: 8px 15px;
        font-weight: 500;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <!-- En-tête simplifié -->
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title mb-0">Gestion des pannes</h1>
                </div>
                <div>
                    <a href="ajoutPanne.php" class="btn btn-add">
                        Nouvelle panne
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container mb-4">
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                                $urgenceClass = match($panne['niveau_urgence']) {
                                    'Faible' => 'badge-faible',
                                    'Moyenne' => 'badge-moyenne',
                                    'Élevée' => 'badge-elevee',
                                    default => '',
                                };
                                ?>
                                <span class="badge <?= $urgenceClass ?>">
                                    <?= htmlspecialchars($panne['niveau_urgence']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($panne['date_enregistrement']) ?></td>
                            <td>
                                <?php 
                                $etat = $panne['dernier_resultat'] ?? null;
                                $etatClass = match($etat) {
                                    'depanner' => 'badge-resolu',
                                    'en cours' => 'badge-encours',
                                    default => 'badge-nontraite',
                                };
                                $etatLabel = match($etat) {
                                    'depanner' => 'Résolu',
                                    'en cours' => 'En cours',
                                    default => 'Non traité',
                                };
                                ?>
                                <span class="badge <?= $etatClass ?>"><?= $etatLabel ?></span>
                            </td>
                            <td>
                                <a href="../vuePanne.php?idPanne=<?= $panne['id'] ?>" class="btn btn-sm btn-info action-btn">
                                    Voir
                                </a>
                                
                                <?php if (in_array($etat, ['en cours', 'depanner'])): ?>
                                <a href="observation?idp=<?= $panne['id'] ?>&idInt=<?= $panne['idIntervention'] ?>" class="btn btn-sm btn-warning action-btn">
                                    Observer
                                </a>
                                <?php endif; ?>
                                
                                <?php if (!in_array($etat, ['depanner', 'en cours']) && empty($panne['instruction'])): ?>
                                <button class="btn btn-sm btn-danger action-btn delete-btn" data-panne-id="<?= $panne['id'] ?>">
                                    Supprimer
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

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <!-- Scripts simplifiés -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // Gestion de la suppression
        $('.delete-btn').click(function() {
            var panneId = $(this).data('panne-id');
            $('#deletePanneId').val(panneId);
            $('#deleteModal').modal('show');
        });
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>
</html>