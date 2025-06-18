<?php
session_start();
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');


$entree_data = null;
$mode_edition = null;

if (isset($_GET['id'])) {
    $id_entree = $_GET['id'];
    $mode_edition = getEntreeById($connexion, $id_entree);
    $entree_data = getEntreeById($connexion, $id_entree);
    
    if (!$mode_edition) {
        header('Location: sortie_stock.php?error=sortie_not_found');
        exit();
    }
}

//########################### pour Enregistrer/Modifier une Entrée #######################################
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['article_id']) && isset($_POST['quantite']) && 
        isset($_POST['date_entree']) && isset($_POST['remarque'])) {

        $article_id = $_POST['article_id'];
        $quantite = $_POST['quantite'];
        $date_entree = $_POST['date_entree'];
        $remarque = $_POST['remarque'];

        if (isset($_POST['id_entree'])) {
            // Mode modification
            $id_entree = $_POST['id_entree'];
            if (modifierEntree($connexion, $id_entree, $article_id, $quantite, $date_entree, $remarque)) {
                header('Location: /COUD/panne/profils/stock/nouvelle_entree?success=3&id='.$id_entree);
                exit();
            } else {
                header('Location: /COUD/panne/profils/stock/nouvelle_entree?error=2&id='.$id_entree);
                exit();
            }
        } else {
            // Mode création
            if (enregistrerEntree($connexion, $article_id, $quantite, $date_entree, $remarque)) {
                header('Location: /COUD/panne/profils/stock/nouvelle_entree?success=2');
                exit();
            } else {
                header('Location: /COUD/panne/profils/stock/nouvelle_entree?error=1');
                exit();
            }
        }
    }
}
//########################### FIN Enregistrer/Modifier une Entrée #######################################

// Récupérer la liste des articles
$articles = listeArticles($connexion);
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

    .edit-badge {
        background-color: var(--secondary);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container py-4">
        <div class="form-container">
            <h3 class="form-title">
                <i class="fas fa-box me-2"></i>
                <?= isset($mode_edition) ? 'Modifier Entrée en Stock' : 'Nouvelle Entrée en Stock' ?>
                <?php if (isset($mode_edition)): ?>
                <span class="edit-badge">Mode édition</span>
                <?php endif; ?>
            </h3>

            <form id="entreeForm" method="POST" action="nouvelle_entree">
                <?php if (isset($mode_edition)): ?>
                <input type="hidden" name="id_entree" value="<?= $entree_data['id'] ?>">
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
                            <?= ($mode_edition && $entree_data['article_id'] == $article['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($article['references']) ?> - <?= htmlspecialchars($article['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nom_article" class="form-label">Nom de l'article</label>
                        <input type="text" class="form-control readonly-field" id="nom_article" readonly
                            value="<?= $mode_edition ? htmlspecialchars($entree_data['nom_article']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="reference_article" class="form-label">Référence</label>
                        <input type="text" class="form-control readonly-field" id="reference_article" readonly
                            value="<?= $mode_edition ? htmlspecialchars($entree_data['reference_article']) : '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description_article" class="form-label">Description</label>
                    <textarea class="form-control readonly-field" id="description_article" rows="2"
                        readonly><?= $mode_edition ? htmlspecialchars($entree_data['description_article']) : '' ?></textarea>
                </div>

                <hr class="section-divider">

                <!-- Section Entrée -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="quantite" class="form-label required-field">Quantité</label>
                        <input type="number" class="form-control" id="quantite" name="quantite" min="1" required
                            value="<?= $mode_edition ? $entree_data['quantite'] : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="date_entree" class="form-label required-field">Date d'entrée</label>
                        <input type="date" class="form-control" id="date_entree" name="date_entree" required
                            value="<?= $mode_edition ? $entree_data['date_entree'] : date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="remarque" class="form-label">Remarques</label>
                        <input type="text" class="form-control" id="remarque" name="remarque" placeholder="Facultatif"
                            value="<?= $mode_edition ? htmlspecialchars($entree_data['remarque']) : '' ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="entree_stock.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Êtes-vous sûr de vouloir continuer ?')">
                        <i class="fas fa-save me-1"></i> <?= $mode_edition ? 'Mettre à jour' : 'Enregistrer' ?>
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
        // Initialiser Select2
        $('.select2-article').select2({
            placeholder: "Rechercher un article...",
            allowClear: true
        });

        // Gérer le changement d'article
        $('#article_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            $('#nom_article').val(selectedOption.data('nom') || '');
            $('#reference_article').val(selectedOption.data('reference') || '');
            $('#description_article').val(selectedOption.data('description') || '');
        });

        // Si en mode édition, déclencher le changement pour remplir les champs
        <?php if ($mode_edition): ?>
        $('#article_id').trigger('change');
        <?php endif; ?>
    });
    </script>

    <?php if (isset($_GET['success'])): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($_GET['success'] == 2): ?>
                    Entrée enregistrée avec succès !
                    <?php elseif ($_GET['success'] == 3): ?>
                    Entrée modifiée avec succès !
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();

    // Redirection après fermeture du modal
    document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
        window.location.href = 'entree_stock.php';
    });
    </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Erreur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($_GET['error'] == 1): ?>
                    Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.
                    <?php elseif ($_GET['error'] == 2): ?>
                    Une erreur est survenue lors de la modification. Veuillez réessayer.
                    <?php endif; ?>
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