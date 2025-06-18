<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

// Récupérer la sortie à modifier
$sortie = null;
if (isset($_GET['id'])) {
    $sortie_id = $_GET['id'];
    $sortie = getSortieById($connexion, $sortie_id);
    
    if (!$sortie) {
        header('Location: sortie_stock.php?error=sortie_not_found');
        exit();
    }
}

//########################### pour Enregistrer ou Modifier une Sortie #######################################
if ($_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($_GET['article_id']) && isset($_GET['intervention_id']) &&
    isset($_GET['quantite']) && isset($_GET['date_sortie']) && isset($_GET['remarque'])) {

    $article_id = $_GET['article_id'];
    $intervention_id = $_GET['intervention_id'];
    $quantite = $_GET['quantite'];
    $date_sortie = $_GET['date_sortie'];
    $remarque = $_GET['remarque'];

    if (isset($_GET['id_delete'])) {
        // Modification d'une sortie existante
        $id = $_GET['id_delete'];
        if (modifierSortie($connexion, $id, $article_id, $intervention_id, $quantite, $date_sortie, $remarque)) {
            header('Location: sortie_stock.php?success=1');
            exit();
        } else {
            header('Location: nouvelle_sortie.php?id='.$id.'&error=1');
            exit();
        }
    } else {
        // Nouvelle sortie
        if (enregistrerSortie($connexion, $article_id, $intervention_id, $quantite, $date_sortie, $remarque)) {
            header('Location: sortie_stock.php?success=2');
            exit();
        } else {
            header('Location: nouvelle_sortie.php?error=1');
            exit();
        }
    }
}
//########################### FIN Enregistrer ou Modifier une Sortie #######################################

// Récupérer la liste des articles et interventions
$articles = listeArticles($connexion);
$interventions = getInterventions($connexion);
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
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
    :root {
        --primary: #4361ee;
        --primary-light: #e6f0ff;
        --secondary: #3a0ca3;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --light-gray: #f1f3f5;
        --border-radius: 6px;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: #f8fafc;
        color: var(--dark);
        line-height: 1.6;
    }

    .form-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
    }

    .form-title {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--primary-light);
    }

    .form-label {
        font-weight: 500;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .form-control,
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
        padding: 0.5rem 1rem;
    }

    .form-control:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .btn {
        padding: 0.5rem 1.25rem;
        border-radius: var(--border-radius);
        font-weight: 500;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-outline-secondary {
        border-color: #e0e0e0;
    }

    .readonly-field {
        background-color: var(--light-gray);
        cursor: not-allowed;
    }

    .section-divider {
        margin: 1.5rem 0;
        border: 0;
        border-top: 1px solid var(--primary-light);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }

    .required-field::after {
        content: " *";
        color: #dc3545;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container py-4">
        <div class="form-container">
            <h3 class="form-title">
                <i class="fas fa-<?= isset($sortie) ? 'edit' : 'box-open' ?> me-2"></i>
                <?= isset($sortie) ? 'Modifier' : 'Nouvelle' ?> Sortie
            </h3>

            <form id="sortieForm" method="GET" action="nouvelle_sortie.php">
                <?php if (isset($sortie)): ?>
                <input type="hidden" name="id_delete" value="<?= htmlspecialchars($sortie['id']) ?>">
                <?php endif; ?>

                <!-- Section Article -->
                <div class="mb-4">
                    <label for="article_id" class="form-label required-field">Article</label>
                    <select class="form-select select2-article" id="article_id" name="article_id" required>
                        <option value="">Sélectionner un article</option>
                        <?php foreach ($articles as $article): ?>
                        <option value="<?= $article['id'] ?>" data-nom="<?= htmlspecialchars($article['nom']) ?>"
                            data-reference="<?= htmlspecialchars($article['references']) ?>"
                            data-description="<?= htmlspecialchars($article['description']) ?>"
                            <?= (isset($sortie) && $sortie['article_id'] == $article['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($article['references']) ?> - <?= htmlspecialchars($article['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nom_article" class="form-label">Nom de l'article</label>
                        <input type="text" class="form-control readonly-field" id="nom_article" readonly
                            value="<?= isset($sortie) ? htmlspecialchars($sortie['nom_article']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="reference_article" class="form-label">Référence</label>
                        <input type="text" class="form-control readonly-field" id="reference_article" readonly
                            value="<?= isset($sortie) ? htmlspecialchars($sortie['reference_article']) : '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description_article" class="form-label">Description</label>
                    <textarea class="form-control readonly-field" id="description_article" rows="2"
                        readonly><?= isset($sortie) ? htmlspecialchars($sortie['description_article']) : '' ?></textarea>
                </div>

                <hr class="section-divider">

                <!-- Section Intervention -->
                <div class="mb-4">
                    <label for="intervention_id" class="form-label required-field">Intervention</label>
                    <select class="form-select select2-intervention" id="intervention_id" name="intervention_id"
                        required>
                        <option value="">Sélectionner une intervention</option>
                        <?php foreach ($interventions as $intervention): ?>
                        <option value="<?= $intervention['id'] ?>"
                            data-description="<?= htmlspecialchars($intervention['description_action']) ?>"
                            data-resultat="<?= htmlspecialchars($intervention['resultat']) ?>"
                            <?= (isset($sortie) && $sortie['intervention_id'] == $intervention['id']) ? 'selected' : '' ?>>
                            Intervention #<?= $intervention['id'] ?> -
                            <?= htmlspecialchars(substr($intervention['description_action'], 0, 30)) ?>...
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="description_intervention" class="form-label">Description</label>
                        <textarea class="form-control readonly-field" id="description_intervention" rows="3"
                            readonly><?= isset($sortie) ? htmlspecialchars($sortie['description_intervention']) : '' ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="resultat_intervention" class="form-label">Résultat attendu</label>
                        <textarea class="form-control readonly-field" id="resultat_intervention" rows="3"
                            readonly><?= isset($sortie) ? htmlspecialchars($sortie['resultat_intervention']) : '' ?></textarea>
                    </div>
                </div>

                <hr class="section-divider">

                <!-- Section Sortie -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="quantite" class="form-label required-field">Quantité</label>
                        <input type="number" class="form-control" id="quantite" name="quantite" min="1" required
                            value="<?= isset($sortie) ? htmlspecialchars($sortie['quantite']) : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="date_sortie" class="form-label required-field">Date de sortie</label>
                        <input type="date" class="form-control" id="date_sortie" name="date_sortie" required
                            value="<?= isset($sortie) ? date('Y-m-d', strtotime($sortie['date_sortie'])) : date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="remarque" class="form-label">Remarques</label>
                        <input type="text" class="form-control" id="remarque" name="remarque" placeholder="Facultatif"
                            value="<?= isset($sortie) ? htmlspecialchars($sortie['remarque']) : '' ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="sortie_stock.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Êtes-vous sûr de vouloir continuer ?')">
                        <i class="fas fa-save me-1"></i> <?= isset($sortie) ? 'Mettre à jour' : 'Enregistrer' ?>
                    </button>
                </div>
            </form>
        </div>
        <br>
        <div class="text-center">
            <a href="sortie_stock.php" class="btn btn-success back-btn">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialiser Select2
        $('.select2-article').select2({
            placeholder: "Rechercher un article...",
            allowClear: true
        });

        $('.select2-intervention').select2({
            placeholder: "Rechercher une intervention...",
            allowClear: true
        });

        // Gérer le changement d'article
        $('#article_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            $('#nom_article').val(selectedOption.data('nom') || '');
            $('#reference_article').val(selectedOption.data('reference') || '');
            $('#description_article').val(selectedOption.data('description') || '');
        });

        // Gérer le changement d'intervention
        $('#intervention_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            $('#description_intervention').val(selectedOption.data('description') || '');
            $('#resultat_intervention').val(selectedOption.data('resultat') || '');
        });

        // Si on est en mode modification, déclencher les événements change pour remplir les champs
        <?php if (isset($sortie)): ?>
        $('#article_id').trigger('change');
        $('#intervention_id').trigger('change');
        <?php endif; ?>
    });
    </script>

    <?php if (isset($_GET['error'])): ?>
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Erreur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Une erreur est survenue lors de <?= isset($sortie) ? 'la modification' : "l'enregistrement" ?> de la
                    sortie. Veuillez réessayer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
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