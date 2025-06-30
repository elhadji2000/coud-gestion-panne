<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

$idp = isset($_GET['idp']) ? (int)$_GET['idp'] : null;
$intervention_id = (isset($_GET['intervention_id']) && is_numeric($_GET['intervention_id']) && $_GET['intervention_id'] > 0)
    ? (int)$_GET['intervention_id']
    : null;
$section = htmlspecialchars($_GET['type']);
// Récupération des données existantes si modification
$date_intervention = '';
$description_action = '';
$personne_agent = '';

if ($intervention_id) {
    $sql = "SELECT date_intervention, description_action, personne_agent FROM intervention WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $intervention_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $intervention = $result->fetch_assoc();
    $date_intervention = $intervention['date_intervention'];
    $date_intervention = DateTime::createFromFormat('d/m/Y', $date_intervention)->format('Y-m-d');
    $description_action = $intervention['description_action'];
    $personne_agent = $intervention['personne_agent'];
}

// Récupération des articles et agents
$articles = listeArticles($connexion);
$agents = listeAgents($connexion, $section);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>CAMPUSCOUD - Formulaire d'Intervention avec Sortie de Stock</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --light-bg: #f8f9fa;
        --dark-text: #2c3e50;
        --light-text: #7f8c8d;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--light-bg);
        color: var(--dark-text);
    }

    .form-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-top: 4px solid var(--secondary-color);
    }

    .form-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
        text-align: center;
        position: relative;
        padding-bottom: 1rem;
    }

    .form-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 150px;
        height: 3px;
        background: var(--secondary-color);
    }

    .card-header {
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .card-header i {
        margin-right: 10px;
    }

    .form-label {
        font-weight: 500;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .required-field:after {
        content: " *";
        color: var(--accent-color);
    }

    .readonly-field {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .btn-submit {
        background-color: var(--secondary-color);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 500;
    }

    .btn-submit:hover {
        background-color: #2980b9;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        padding: 5px;
        border: 1px solid #ced4da;
    }

    .article-row {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .article-row:last-child {
        border-bottom: none;
    }

    .btn-add-article {
        margin-top: 10px;
    }

    .btn-remove-article {
        margin-top: 32px;
    }

    .select2-container {
        width: 100% !important;
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 1.5rem;
            margin: 1rem;
        }
        
        .btn-remove-article {
            margin-top: 10px;
        }
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container py-4">
        <div class="form-container">
            <h2 class="form-title">
                <i class="fas fa-tools me-2"></i>INTERVENTION AVEC SORTIE DE STOCK
            </h2>

            <form id="interventionForm" method="GET" action="trait_intervention.php">
                <input type="hidden" name="idPanne" value="<?= htmlspecialchars($idp) ?>">
                <input type="hidden" name="intervention_id" value="<?= htmlspecialchars($intervention_id) ?>">

                <!-- Section Intervention -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-tools me-2"></i>Informations sur l'intervention
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="agents" class="form-label required-field">Agents intervenants</label>
                                <select class="form-select select2-agents" id="agents" name="agents[]" multiple="multiple" required>
                                    <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>"
                                        <?= (isset($personne_agent) && strpos($personne_agent, (string)$agent['id']) !== false) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($agent['prenom']) ?> <?= htmlspecialchars($agent['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date_intervention" class="form-label required-field">Date d'intervention</label>
                                <input type="date" name="date_intervention" id="date_intervention" class="form-control" required
                                    value="<?= $date_intervention ? htmlspecialchars($date_intervention) : date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="type_intervention" class="form-label required-field">Type d'intervention</label>
                            <select class="form-select" id="type_intervention" name="type_intervention" required>
                                <option value="">Sélectionner un type...</option>
                                <option value="Maintenance" <?= (isset($description_action) && strpos($description_action, 'Maintenance')) !== false ? 'selected' : '' ?>>Maintenance</option>
                                <option value="Réparation" <?= (isset($description_action) && strpos($description_action, 'Réparation')) !== false ? 'selected' : '' ?>>Réparation</option>
                                <option value="Installation" <?= (isset($description_action) && strpos($description_action, 'Installation')) !== false ? 'selected' : '' ?>>Installation</option>
                                <option value="Contrôle" <?= (isset($description_action) && strpos($description_action, 'Contrôle')) !== false ? 'selected' : '' ?>>Contrôle</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="mt-3">
                            <label for="description_action" class="form-label required-field">Description de l'intervention</label>
                            <textarea name="description_action" id="description_action" class="form-control" rows="4" required
                                placeholder="Décrivez en détail les actions réalisées..."><?= isset($description_action) ? htmlspecialchars($description_action) : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section Sortie de Stock -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-box-open me-2"></i>Sortie de stock associée
                    </div>
                    <div class="card-body" id="articles-container">
                        <!-- Premier article -->
                        <div class="article-row row g-3">
                            <div class="col-md-6">
                                <label class="form-label required-field">Article</label>
                                <select class="form-select" name="articles[0][article_id]" required>
                                    <option value="">Sélectionner un article...</option>
                                    <?php foreach ($articles as $article): ?>
                                    <option value="<?= $article['id'] ?>">
                                        <?= htmlspecialchars($article['references']) ?> - <?= htmlspecialchars($article['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label required-field">Quantité</label>
                                <input type="number" class="form-control" name="articles[0][quantite]" min="1" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-remove-article" style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" id="btn-add-article" class="btn btn-secondary btn-add-article">
                            <i class="fas fa-plus me-2"></i>Ajouter un autre article
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-primary btn-submit" onclick="return confirm('Confirmez-vous cette intervention avec sortie de stock ?')">
                        <i class="fas fa-save me-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialisation des select2
        $('.select2-agents').select2({
            placeholder: "Sélectionnez un ou plusieurs agents",
            allowClear: true
        });

        // Initialiser le premier select2 pour les articles
        $('.select2-article').select2({
            placeholder: "Sélectionner un article...",
            allowClear: true
        });

        // Compteur pour les articles
        let articleCounter = 1;

        // Ajouter un nouvel article
        $('#btn-add-article').click(function() {
            const newArticleRow = `
                <div class="article-row row g-3">
                    <div class="col-md-6">
                        <label class="form-label required-field">Article</label>
                        <select class="form-select" name="articles[${articleCounter}][article_id]" required>
                            <option value="">Sélectionner un article...</option>
                            <?php foreach ($articles as $article): ?>
                            <option value="<?= $article['id'] ?>">
                                <?= htmlspecialchars($article['references']) ?> - <?= htmlspecialchars($article['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label required-field">Quantité</label>
                        <input type="number" class="form-control" name="articles[${articleCounter}][quantite]" min="1" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-remove-article">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#articles-container').append(newArticleRow);
            
            // Initialiser le nouveau select2
            $('#articles-container .select2-article').last().select2({
                placeholder: "Sélectionner un article...",
                allowClear: true
            });
            
            // Afficher les boutons de suppression pour tous les articles sauf le premier
            $('.btn-remove-article').show();
            
            articleCounter++;
        });

        // Supprimer un article
        $(document).on('click', '.btn-remove-article', function() {
            $(this).closest('.article-row').remove();
            
            // Cacher le bouton de suppression s'il ne reste qu'un seul article
            if ($('.article-row').length === 1) {
                $('.btn-remove-article').hide();
            }
            
            // Recalculer les index pour éviter les trous dans le tableau
            $('.article-row').each(function(index) {
                $(this).find('select, input').each(function() {
                    const name = $(this).attr('name').replace(/\[\d+\]/, `[${index}]`);
                    $(this).attr('name', name);
                });
            });
            
            articleCounter = $('.article-row').length;
        });

        // Validation du formulaire
        $('#interventionForm').submit(function() {
            // Vérification des agents sélectionnés
            if ($('#agents').val() === null || $('#agents').val().length === 0) {
                alert('Veuillez sélectionner au moins un agent intervenant');
                return false;
            }

            // Vérification des quantités
            let valid = true;
            $('input[name^="articles["][name$="[quantite]"]').each(function() {
                if (parseInt($(this).val()) <= 0) {
                    alert('La quantité doit être supérieure à zéro pour tous les articles');
                    valid = false;
                    return false; // Sortir de la boucle each
                }
            });
            
            return valid;
        });
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>
</html>