<?php
session_start();
include('../traitement/fonction.php');
include('../traitement/requete.php');
include('../activite.php');

$userId = $_SESSION['id_user'];
$userRole = $_SESSION['profil'];
$idPanne = isset($_GET['idPanne']) ? (int)$_GET['idPanne'] : null;

// Afficher les détails d'une panne spécifique
$pannes = obtenirPanneParId($connexion, $idPanne);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #3777B0;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .section-title {
            color: var(--secondary-color);
            font-weight: 600;
            margin: 25px 0 15px;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 8px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--secondary-color);
            min-width: 200px;
        }
        
        .badge-urgence {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
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
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
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
            min-width: 100px;
            margin: 0 5px;
            font-weight: 500;
        }
        
        .description-box {
            background-color: var(--light-color);
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid var(--primary-color);
        }
        
        .back-btn {
            width: 150px;
            margin-top: 20px;
        }
        
        .detail-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>

<body>
    <?php include('../head.php'); ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold"><i class="fas fa-tools me-2"></i>Détails de la Panne #<?= htmlspecialchars($idPanne) ?></h2>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Informations de base</h4>
            </div>
            <div class="card-body">
                <?php if ($pannes): ?>
                <?php foreach ($pannes as $panne): ?>
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">N° Panne</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['panne_id']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Type de Panne</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['type_panne']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Localisation Exacte</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['localisation']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Niveau d'Urgence</div>
                    <div class="col-md-9">
                        <?php if ($panne['niveau_urgence'] == 'Faible'): ?>
                        <span class="badge-urgence badge-faible">Faible</span>
                        <?php elseif($panne['niveau_urgence'] == 'Moyenne'): ?>
                        <span class="badge-urgence badge-moyenne">Moyenne</span>
                        <?php elseif($panne['niveau_urgence'] == 'Élevée'): ?>
                        <span class="badge-urgence badge-elevee">Élevée</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Date de Déclaration</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['date_enregistrement']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Description</div>
                    <div class="col-md-9 description-box">
                        <?= nl2br(htmlspecialchars($panne['panne_description'])) ?>
                    </div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">État</div>
                    <div class="col-md-9">
                        <?php if ($panne['resultat'] == 'depanner'): ?>
                        <span class="badge-etat badge-resolu">Résolu</span>
                        <?php elseif ($panne['resultat'] == 'en cours'): ?>
                        <span class="badge-etat badge-encours">En cours</span>
                        <?php else: ?>
                        <span class="badge-etat badge-nonresolu">Non résolu</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="alert alert-warning">Aucune information trouvée pour cette panne.</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Section Instructions Chef S.E.M -->
        <?php if ($_SESSION['profil'] != 'residence' && $_SESSION['profil'] != 'service' && !empty($panne['date_imputation'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Instructions du Chef S.E.M</h4>
            </div>
            <div class="card-body">
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Date d'Imputation</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['date_imputation']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Instructions</div>
                    <div class="col-md-9 description-box">
                        <?= nl2br(htmlspecialchars($panne['instruction'])) ?>
                    </div>
                </div>
                
                <?php if ($_SESSION['profil'] == 'dst'): ?>
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <button class="btn btn-primary action-btn" 
                                onclick="checkPanneStatus('<?= $panne['resultat'] ?>', 'Modifier','<?= $panne['imputation_id'] ?>')">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </button>
                        <button class="btn btn-danger action-btn" 
                                onclick="checkPanneStatus('<?= $panne['resultat'] ?>', 'Supprimer','<?= $panne['imputation_id'] ?>')">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Intervention Atelier -->
        <?php if (!empty($panne['date_intervention'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Intervention Atelier</h4>
            </div>
            <div class="card-body">
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Date d'Intervention</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['date_intervention']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Description des Actions</div>
                    <div class="col-md-9 description-box">
                        <?= nl2br(htmlspecialchars($panne['description_action'])) ?>
                    </div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Responsable</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['personne_agent']) ?></div>
                </div>
                
                <?php if ($_SESSION['profil'] == 'atelier'): ?>
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <button class="btn btn-primary action-btn" 
                                onclick="checkPanneStatus('<?= $panne['evaluation_qualite'] ?>', 'Modifier','<?= $panne['intervention_id'] ?>')">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </button>
                        <button class="btn btn-danger action-btn" 
                                onclick="checkPanneStatus('<?= $panne['evaluation_qualite'] ?>', 'Supprimer','<?= $panne['intervention_id'] ?>')">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Observations Chef Résidence -->
        <?php if (!empty($panne['evaluation_qualite'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Observations Chef Résidence</h4>
            </div>
            <div class="card-body">
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Évaluation</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['evaluation_qualite']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Date d'Observation</div>
                    <div class="col-md-9"><?= htmlspecialchars($panne['date_observation']) ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Commentaires</div>
                    <div class="col-md-9 description-box">
                        <?= nl2br(htmlspecialchars($panne['commentaire_suggestion'])) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="javascript:history.back()" class="btn btn-success back-btn">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="exampleModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer cette Imputation ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="imputForm" method="GET" action="../traitement/traitement.php">
                        <input type="hidden" name="imputation_id" id="ImputationIdInput" value="">
                        <button type="submit" class="btn btn-danger">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="interventionModal" tabindex="-1" aria-labelledby="interventionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="interventionModalLabel">Attention</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Intervention en cours</h5>
                    <p>Cette panne fait déjà l'objet d'une intervention.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chefAtelierModal" tabindex="-1" aria-labelledby="chefAtelierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="chefAtelierModalLabel">Attention</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Dépannage en cours</h5>
                    <p>Cette panne fait déjà l'objet d'une Observation Chef Residence.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chefAtelierDeleteModal" tabindex="-1" aria-labelledby="chefAtelierDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="chefAtelierDeleteModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer cette Intervention ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="imputForm" method="GET" action="../traitement/traitement.php">
                        <input type="hidden" name="intervention_id" id="InterventionIdInput" value="">
                        <button type="submit" class="btn btn-danger">Confirmer</button>
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
    // Exemple de variable pour simuler le rôle de l'utilisateur (à remplacer par votre méthode d'authentification)
    const userRoles = '<?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?>';

    function checkPanneStatus(resultat, action, Id) {
        if (userRoles === 'atelier') {
            // Comportement pour le chef d'atelier
            if (resultat == 'Fait' || resultat == 'Inachevee') {
                showChefAtelierModal(); // Modal spécifique pour le chef d'atelier
            } else {
                // Comportement pour le chef d'atelier pour d'autres actions
                if (action === 'Supprimer') {
                    showChefAtelierDeleteModal(Id); // Modal spécifique pour suppression
                } else if (action === 'Modifier') {
                    window.location.href = 'dst/intervention.php?intervention_id=' + Id; // Redirection spécifique
                }
            }
        }
        if (userRoles === 'dst') {
            // Comportement pour les autres rôles
            if (resultat == 'en cours' || resultat == 'depanner') {
                showInterventionModal();
            } else {
                if (action === 'Supprimer') {
                    showDeleteModal(Id);
                } else if (action === 'Modifier') {
                    window.location.href = 'dst/imputation.php?imputation_id=' + Id; // Redirection spécifique
                }
            }
        }
    }

    function showInterventionModal() {
        const interventionModal = new bootstrap.Modal(document.getElementById('interventionModal'));
        interventionModal.show();
    }

    function showDeleteModal(imputationId) {
        const deleteModal = new bootstrap.Modal(document.getElementById('exampleModal'));
        document.getElementById('ImputationIdInput').value = imputationId;
        deleteModal.show();
    }

    function showChefAtelierModal() {
        const chefAtelierModal = new bootstrap.Modal(document.getElementById('chefAtelierModal'));
        chefAtelierModal.show();
    }

    function showChefAtelierDeleteModal(imputationId) {
        const chefAtelierDeleteModal = new bootstrap.Modal(document.getElementById('chefAtelierDeleteModal'));
        document.getElementById('InterventionIdInput').value = imputationId;
        chefAtelierDeleteModal.show();
    }
    </script>

    <?php include('../footer.php'); ?>
</body>
</html>