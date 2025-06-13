<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /COUD/codif/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
//########################### pour Enregistrer article #######################################
if ($_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($_GET['nom']) && isset($_GET['reference']) &&
    isset($_GET['description']) && isset($_GET['categorie'])) {

    $nom = $_GET['nom'];
    $reference = $_GET['reference'];
    $description = $_GET['description'];
    $categorie = $_GET['categorie'];

    if (enregistrerArticles($connexion, $nom, $categorie, $description, $reference)) {
        header('Location: /COUD/panne/profils/stock/nouvel_article?success=1');
        exit();
    } else {
        header('Location: /COUD/panne/profils/stock/nouvel_article');
        exit();
    }
}
//########################### FIN Enregistrer article #######################################
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel Article | Stock</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    :root {
        --primary: #4361ee;
        --primary-light: #e6f0ff;
        --secondary: #3a0ca3;
        --light: #f8f9fa;
        --dark: #212529;
        --border-radius: 6px;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: #f8fafc;
    }

    .form-container {
        max-width: 700px;
        margin: 2rem auto;
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

    .form-control {
        height: 42px;
        border-radius: var(--border-radius);
    }

    .form-select {
        height: 42px;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        padding: 0.5rem 1.5rem;
        border-radius: var(--border-radius);
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
            <h3 class="form-title"><i class="fas fa-plus-circle me-2"></i>Nouvel Article</h3>

            <form id="articleForm" method="GET" action="nouvel_article.php">
                <div class="mb-4">
                    <label for="nom" class="form-label required-field">Nom de l'article</label>
                    <input type="text" class="form-control" id="nom" name="nom" required
                        placeholder="Entrez le nom de l'article">
                </div>

                <div class="mb-4">
                    <label for="reference" class="form-label required-field">Référence</label>
                    <input type="text" class="form-control" id="reference" name="reference" required
                        placeholder="Entrez la référence de l'article">
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                        placeholder="Décrivez l'article (facultatif)"></textarea>
                </div>

                <div class="mb-4">
                    <label for="categorie" class="form-label required-field">Catégorie</label>
                    <select class="form-select" id="categorie" name="categorie" required>
                        <option value="" disabled selected>Sélectionnez une catégorie</option>
                        <option value="Plomberie">Plomberie</option>
                        <option value="Maçonnerie">Maçonnerie</option>
                        <option value="Électricité">Électricité</option>
                        <option value="Menuiserie bois">Menuiserie bois</option>
                        <option value="Menuiserie aluminium">Menuiserie aluminium</option>
                        <option value="Menuiserie métallique">Menuiserie métallique</option>
                        <option value="Froid">Froid</option>
                        <option value="Peinture">Peinture</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="articles.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
        <br>
        <div class="text-center">
            <a href="articles.php" class="btn btn-success back-btn">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // Validation basique du formulaire
        $('#articleForm').submit(function(e) {
            // Vérification des champs obligatoires
            if ($('#nom').val() === '' || $('#reference').val() === '' || $('#categorie').val() ===
                null) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });
    });
    </script>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Article enregistrée avec succès !
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
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include('../../footer.php'); ?>
</body>

</html>