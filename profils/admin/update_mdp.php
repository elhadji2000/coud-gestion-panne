<?php
session_start();

include('../../traitement/fonction.php');
//include('../../traitement/requete.php');

$error = '';
$success = '';

// Traitement du formulaire de modification de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Vérification que les mots de passe correspondent
    if ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas";
    } 
    // Vérification de l'ancien mot de passe
    elseif (!verifyCurrentPassword($_SESSION['username'], $current_password, $connexion)) {
        $error = "L'ancien mot de passe est incorrect";
    } 
    // Modification du mot de passe
    else {
        if (updatePassword($_SESSION['username'], $new_password, $connexion)) {
            $success = "Mot de passe modifié avec succès";
            // Mettre à jour le mot de passe en session
            $_SESSION['mdp'] = $new_password;
            $_SESSION['type_mdp'] = "updated";
        } else {
            $error = "Une erreur est survenue lors de la modification";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le mot de passe | GESCOUD</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    :root {
        --primary: #4361ee;
        --primary-light: #e6f0ff;
        --success: #28a745;
        --danger: #dc3545;
        --light: #f8f9fa;
        --dark: #212529;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fb;
    }

    .password-container {
        max-width: 500px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .password-title {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 1.5rem;
        text-align: center;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-light);
    }

    .form-label {
        font-weight: 500;
        color: var(--dark);
    }

    .form-control {
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
    }

    .password-toggle {
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dark);
    }

    .password-input-group {
        position: relative;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        padding: 0.5rem 1.5rem;
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: #3a56d4;
        border-color: #3a56d4;
    }

    .alert {
        border-radius: 6px;
    }

    @media (max-width: 576px) {
        .password-container {
            padding: 1.5rem;
            margin: 1rem;
        }
    }
    </style>
</head>

<body>
    <header
        style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #f8f9fa; border-bottom: 1px solid #ddd;">
        <div style="display: flex; align-items: center;">
            <img src="http://localhost/COUD/panne/assets/images/logo.png" alt="Logo" style="height: 40px;">
            <span style="margin-left: 10px; font-weight: bold; font-size: 18px;">GESCOUD</span>
        </div>
    </header>
    <div class="container">
        <div class="password-container">
            <h2 class="password-title">
                <i class="fas fa-key me-2"></i>Modifier le mot de passe
            </h2>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Ancien mot de passe -->
                <div class="mb-3">
                    <label for="current_password" class="form-label">Ancien mot de passe</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control" id="current_password" name="current_password"
                            required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('current_password', this)"></i>
                    </div>
                </div>

                <!-- Nouveau mot de passe -->
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password', this)"></i>
                    </div>
                    <small class="text-muted">Minimum 8 caractères</small>
                </div>

                <!-- Confirmation nouveau mot de passe -->
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($success): ?>
    <!-- Modal Bootstrap -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle me-2"></i>Succès
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>
                <div class="modal-body text-center">
                    <?= htmlspecialchars($success) ?>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script JS -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const modalElement = document.getElementById('successModal');
        const modalInstance = new bootstrap.Modal(modalElement);

        // Afficher le modal
        modalInstance.show();

        // Lorsqu'il est fermé, rediriger vers index.php
        modalElement.addEventListener('hidden.bs.modal', function() {
            window.location.href = 'http://localhost/COUD/panne/index.php'; // modifie si besoin
        });
    });
    </script>
    <?php endif; ?>



    <script>
    // Basculer la visibilité du mot de passe
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Validation côté client
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword.length < 8) {
            alert('Le mot de passe doit contenir au moins 8 caractères');
            e.preventDefault();
            return false;
        }

        if (newPassword !== confirmPassword) {
            alert('Les mots de passe ne correspondent pas');
            e.preventDefault();
            return false;
        }
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>