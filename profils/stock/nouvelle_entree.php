<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /COUD/codif/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

//########################### pour Enregistrer une Entrée #######################################
if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['article_id']) && isset($_POST['quantite']) && 
    isset($_POST['date_entree']) && isset($_POST['remarque'])) {

    $article_id = $_POST['article_id'];
    $quantite = $_POST['quantite'];
    $date_entree = $_POST['date_entree'];
    $remarque = $_POST['remarque'];

    if (enregistrerEntree($connexion, $article_id, $quantite, $date_entree, $remarque)) {
        header('Location: /COUD/panne/profils/stock/nouvelle_entree?success=2');
        exit();
    } else {
        header('Location: /COUD/panne/profils/stock/nouvelle_entree');
        exit();
    }
}
//########################### FIN Enregistrer une Entrée #######################################

// Récupérer la liste des articles
$articles = listeArticles($connexion);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Entrées | Stock</title>

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

    .form-control, .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
        padding: 0.5rem 1rem;
    }

    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
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
            <h3 class="form-title"><i class="fas fa-box me-2"></i>Entrée en Stock</h3>

            <form id="entreeForm" method="POST" action="nouvelle_entree">
                <!-- Section Article -->
                <div class="mb-4">
                    <label for="article_id" class="form-label required-field">Article</label>
                    <select class="form-select select2-article" id="article_id" name="article_id" required>
                        <option value="">Sélectionner un article</option>
                        <?php foreach ($articles as $article): ?>
                        <option value="<?= $article['id'] ?>"
                            data-nom="<?= htmlspecialchars($article['nom']) ?>"
                            data-reference="<?= htmlspecialchars($article['references']) ?>"
                            data-description="<?= htmlspecialchars($article['description']) ?>">
                            <?= htmlspecialchars($article['references']) ?> - <?= htmlspecialchars($article['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nom_article" class="form-label">Nom de l'article</label>
                        <input type="text" class="form-control readonly-field" id="nom_article" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="reference_article" class="form-label">Référence</label>
                        <input type="text" class="form-control readonly-field" id="reference_article" readonly>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description_article" class="form-label">Description</label>
                    <textarea class="form-control readonly-field" id="description_article" rows="2" readonly></textarea>
                </div>

                <hr class="section-divider">

                <!-- Section Entrée -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="quantite" class="form-label required-field">Quantité</label>
                        <input type="number" class="form-control" id="quantite" name="quantite" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label for="date_entree" class="form-label required-field">Date d'entrée</label>
                        <input type="date" class="form-control" id="date_entree" name="date_entree" required>
                    </div>
                    <div class="col-md-4">
                        <label for="remarque" class="form-label">Remarques</label>
                        <input type="text" class="form-control" id="remarque" name="remarque" placeholder="Facultatif">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-undo me-1"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
        <br>
        <div class="text-center">
            <a href="entree_stock.php" class="btn btn-success back-btn">
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

        // Définir la date du jour par défaut
        $('#date_entree').val(new Date().toISOString().substr(0, 10));

        // Gérer le changement d'article
        $('#article_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            $('#nom_article').val(selectedOption.data('nom') || '');
            $('#reference_article').val(selectedOption.data('reference') || '');
            $('#description_article').val(selectedOption.data('description') || '');
        });
    });
    </script>

    <?php if (isset($_GET['success']) && $_GET['success'] == 2): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Entrèe enregistrée avec succès !
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