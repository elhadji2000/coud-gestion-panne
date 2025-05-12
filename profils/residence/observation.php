<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}

// Supprimer une variable de session spécifique
unset($_SESSION['classe']);

// Inclusion des fichiers nécessaires
require_once('../../traitement/fonction.php');
require_once('../../traitement/requete.php');
require_once('../../activite.php');

// Récupération des paramètres avec validation
$idp = isset($_GET['idp']) ? (int)$_GET['idp'] : null;
$idint = isset($_GET['idInt']) ? (int)$_GET['idInt'] : null;
$idObservation = isset($_GET['idObservation']) ? (int)$_GET['idObservation'] : null;

$evaluation = '';
$commentaire = '';

// Si en mode modification, charger les données de l'observation
if ($idObservation) {
    try {
        $sql = "SELECT evaluation_qualite, commentaire_suggestion FROM observation WHERE id = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('i', $idObservation);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($observation = $result->fetch_assoc()) {
            $evaluation = htmlspecialchars($observation['evaluation_qualite'], ENT_QUOTES, 'UTF-8');
            $commentaire = htmlspecialchars($observation['commentaire_suggestion'], ENT_QUOTES, 'UTF-8');
        }
    } catch (Exception $e) {
        // Loguer l'erreur
        error_log("Erreur lors de la récupération de l'observation: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GESCOUD</title>
    
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3273dc;
            --hover-color: #2765c1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .observation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 1rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(50, 115, 220, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: var(--hover-color);
            text-decoration: underline;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    
    <div class="observation-container">
        <h2 class="form-title">
            <i class="fas fa-clipboard-check me-2"></i>FORMULAIRE D'OBSERVATION
        </h2>
        
        <form method="POST" action="../../traitement/traitement" class="needs-validation" novalidate>
            <div class="mb-4">
                <label for="evaluation" class="form-label required-field">
                    <strong><i class="fas fa-star me-2"></i>Évaluation</strong>
                </label>
                <select name="evaluation" id="evaluation" class="form-select" required>
                    <option value="" disabled selected>Sélectionnez une option</option>
                    <option value="Fait" <?= ($evaluation == 'Fait') ? 'selected' : ''; ?>>Fait</option>
                    <option value="Inachevee" <?= ($evaluation == 'Inachevee') ? 'selected' : ''; ?>>Inachevée</option>
                </select>
                <div class="invalid-feedback">
                    Veuillez sélectionner une évaluation.
                </div>
            </div>
            
            <div class="mb-4">
                <label for="commentaire" class="form-label required-field">
                    <strong><i class="fas fa-comment me-2"></i>Commentaire ou Suggestion</strong>
                </label>
                <textarea name="commentaire" id="commentaire" class="form-control" required><?= $commentaire ?></textarea>
                <div class="invalid-feedback">
                    Veuillez saisir un commentaire ou une suggestion.
                </div>
            </div>
            
            <input type="hidden" name="idIntervention" value="<?= htmlspecialchars($idint, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="idPanne" value="<?= htmlspecialchars($idp, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="idObservation" value="<?= htmlspecialchars($idObservation, ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="javascript:history.back()" class="btn btn-outline-secondary me-md-2">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>ENREGISTRER
                </button>
            </div>
        </form>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <!-- Form Validation -->
    <script>
        (function() {
            'use strict';
            
            // Fetch all forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        
                        form.classList.add('was-validated');
                    }, false);
                });
        })();
    </script>

    <?php include('../../footer.php'); ?>
</body>
</html>