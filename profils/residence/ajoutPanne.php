<?php
session_start();
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        font-size: 1.05rem;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        /* Taille de police globale augmentée */
    }

    .form-container {
        max-width: 700px;
        margin: 30px auto;
        padding: 30px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .form-title {
        text-align: center;
        margin-bottom: 30px;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.8rem;
        /* Titre plus grand */
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 10px;
        /* Espacement accru */
        color: #2c3e50;
        font-size: 1.3rem;
        /* Libellés plus grands */
    }

    /* Styles unifiés pour tous les selects */
    .form-select,
    .select2-container .select2-selection {
        width: 100% !important;
        padding: 12px 15px !important;
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        transition: all 0.3s !important;
        font-size: 1.3rem !important;
        /* Texte plus lisible */
        min-height: 48px !important;
        /* Hauteur uniforme */
    }

    .form-select:focus,
    .select2-container--focus .select2-selection {
        border-color: #3498db !important;
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25) !important;
        font-size: 1.3rem !important;
    }

    /* Styles pour le textarea */
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
        font-size: 1.3rem;
        /* Texte plus lisible */
        padding: 12px 15px !important;
    }

    /* Bouton de soumission */
    .btn-submit {
        background-color: #3498db;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 1.1rem;
        transition: all 0.3s;
        width: 100%;
        margin-top: 10px;
    }

    .btn-submit:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    /* Lien de retour */
    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #3498db;
        text-decoration: none;
        font-size: 1.05rem;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    /* Styles spécifiques pour Select2 */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        font-size: 1.05rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
    }

    /* Options des selects plus lisibles */
    .form-select option,
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 1.05rem !important;
    }

    .required-field::after {
        content: " *";
        color: red;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-tools me-2"></i>Déclaration de Panne</h2>

            <form method="POST" action="./../../traitement/traitement">
                <!-- Localisation -->
                <div class="mb-4">
                    <label class="form-label required-field">Localisation Exacte</label>
                    <select class="form-select select2-localisation" name="localisation" required>
                        <option value="" disabled selected>Choisir une localisation...</option>

                        <?php
        $profil2 = $_SESSION['profil2'] ?? '';
        $profil1 = $_SESSION['profil'] ?? '';

        // Pour les chefs de résidence : format "Campus|Pavillon"
        if ($profil1 === 'residence') {
            echo '<optgroup label="Chambres">';
            try {
                $chambres = getChambresByCampusPavillon($connexion, $profil2);
                if (!empty($chambres)) {
                    foreach ($chambres as $chambre) {
                        $chambre_clean = htmlspecialchars($chambre, ENT_QUOTES);
                        echo "<option value=\"Chambre $chambre_clean\">Chambre $chambre_clean</option>";
                    }
                } else {
                    echo '<option value="" disabled>Aucune chambre trouvée pour ce pavillon</option>';
                }
            } catch (Exception $e) {
                error_log("Erreur récupération chambres: " . $e->getMessage());
                for ($i = 1; $i <= 20; $i++) {
                    echo "<option value=\"Chambre $i\">Chambre $i</option>";
                }
            }
            echo '</optgroup>';
        }

        // Ajout générique pour tous : Couloirs, Toilettes, Autres
        ?>
                        <optgroup label="Couloirs">
                            <option value="Couloir Nord">Couloir Nord</option>
                            <option value="Couloir Sud">Couloir Sud</option>
                            <option value="Couloir Est">Couloir Est</option>
                            <option value="Couloir Ouest">Couloir Ouest</option>
                            <option value="Couloir Central">Couloir Central</option>
                        </optgroup>

                        <optgroup label="Toilettes">
                            <option value="Toilettes RDC">Toilettes RDC</option>
                            <option value="Toilettes 1er étage">Toilettes 1er étage</option>
                            <option value="Toilettes 2ème étage">Toilettes 2ème étage</option>
                            <option value="Toilettes Nord">Toilettes Nord</option>
                            <option value="Toilettes Sud">Toilettes Sud</option>
                        </optgroup>

                        <optgroup label="Bureaux et Autres Espaces">
                            <option value="Cuisine">Cuisine</option>
                            <option value="Salle à manger">Salle à manger</option>
                            <option value="Salle de réunion">Salle de réunion</option>
                            <option value="Bureau">Bureau</option>
                            <option value="Secrétariat">Secrétariat</option>
                            <option value="Accueil">Accueil</option>
                            <option value="Hall d'entrée">Hall d'entrée</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Type de Panne -->
                <div class="mb-4">
                    <label class="form-label required-field">Type de Panne</label>
                    <select name="type_panne" class="form-select select2-type" required>
                        <option value="" disabled selected>Choisir un type...</option>
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

                <!-- Niveau d'Urgence -->
                <div class="mb-4">
                    <label class="form-label required-field">Niveau d'Urgence</label>
                    <select name="niveau_urgence" class="form-select select2-urgence" required>
                        <option value="" disabled selected>Choisir un niveau...</option>
                        <option value="Faible">Faible</option>
                        <option value="Moyenne">Moyenne</option>
                        <option value="Élevée">Élevée</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="form-label required-field">Description</label>
                    <textarea name="description" class="form-control" required
                        placeholder="Décrivez la panne en détail..."></textarea>
                </div>

                <!-- Bouton de soumission -->
                <button type="submit" class="btn btn-submit"
                    onclick="return confirm('Êtes-vous sûr de vouloir continuer ?')">
                    <i class="fas fa-save me-2"></i>ENREGISTRER
                </button>

                <!-- Lien de retour -->
                <a href="listPannes.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialisation de Select2 pour tous les selects
        $('.select2-localisation, .select2-type, .select2-urgence').select2({
            placeholder: "Sélectionner...",
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 3 // Afficher la recherche seulement si + de 3 options
        });

        // Styles supplémentaires pour uniformiser l'apparence
        $('.select2-container').css('font-size', '1.05rem');
    });
    </script>

    <!-- Success Modal -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Panne enregistrée avec succès !
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