<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Initialisation des variables
$user = [
    'username' => '',
    'email' => '',
    'telephone' => '',
    'nom' => '',
    'prenom' => '',
    'profil1' => '',
    'profil2' => '',
    'password' => ''
];

if ($user_id) {
    $sql = "SELECT id, username, statut, email, telephone, nom, prenom, profil1, profil2 FROM Utilisateur WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user['password'] = $_SESSION['mdp'];
}
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

    <style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --light-bg: rgba(50, 115, 220, 0.1);
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }

    .form-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-title {
        color: var(--primary-color);
        text-align: center;
        margin-bottom: 2rem;
        font-weight: 600;
        border-bottom: 2px solid var(--light-bg);
        padding-bottom: 1rem;
    }

    .form-label {
        font-weight: 500;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        padding: 0.75rem;
        border-radius: 6px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
        font-size: 1.2rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }

    .form-select {
        background-color: var(--light-bg);
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
        color: white;
        font-size: 14px;
    }

    .btn-primary-custom:hover {
        background-color: #1a252f;
        transform: translateY(-2px);
        color: white;
        font-size: 14px;
    }

    .back-link {
        color: var(--primary-color);
        text-decoration: none;
        transition: all 0.3s;
    }

    .back-link:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="form-container">
        <h2 class="form-title">
            <i class="fas fa-user-edit me-2"></i>
            <?= $user_id ? 'MODIFICATION UTILISATEUR' : 'NOUVEAU UTILISATEUR' ?>
        </h2>

        <form method="POST" action="./../../traitement/traitement" class="needs-validation" novalidate>
            <!-- Champ caché pour l'ID de l'utilisateur lors de la modification -->
            <?php if (isset($user['id'])): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            <?php endif; ?>
            <?php if (isset($_GET['erreur'])): ?>
            <?php if ($_GET['erreur'] === 'exist'): ?>
            <div class="alert alert-danger">Nom d'utilisateur ou email déjà utilisé.</div>
            <?php elseif ($_GET['erreur'] === 'save'): ?>
            <div class="alert alert-danger">Une erreur est survenue lors de l'enregistrement.</div>
            <?php endif; ?>
            <?php endif; ?>


            <div class="row g-3">
                <!-- Username -->
                <div class="col-md-6 form-section">
                    <label for="username" class="form-label">Matricule</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?= htmlspecialchars($user['username']) ?>" placeholder="Entrez le nom d'utilisateur"
                        required>
                    <div class="invalid-feedback">
                        Veuillez entrer un nom d'utilisateur.
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6 form-section">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= htmlspecialchars($user['email']) ?>" placeholder="exemple@domaine.com" required>
                    <div class="invalid-feedback">
                        Veuillez entrer une adresse email valide.
                    </div>
                </div>

                <!-- Nom -->
                <div class="col-md-6 form-section">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom"
                        value="<?= htmlspecialchars($user['nom']) ?>" required>
                    <div class="invalid-feedback">
                        Veuillez entrer le nom.
                    </div>
                </div>

                <!-- Prénom -->
                <div class="col-md-6 form-section">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom"
                        value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    <div class="invalid-feedback">
                        Veuillez entrer le prénom.
                    </div>
                </div>

                <!-- Téléphone -->
                <div class="col-md-6 form-section">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone"
                        value="<?= htmlspecialchars($user['telephone']) ?>" placeholder="00 000 00 00" required>
                    <div class="invalid-feedback">
                        Veuillez entrer un numéro de téléphone valide.
                    </div>
                </div>

                <!-- Mot de passe (seulement pour nouvel utilisateur) -->
                <?php if (!$user_id): ?>
                <div class="col-md-6 form-section">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" class="form-control" value="coud2025" id="password" name="password"
                            required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        Veuillez entrer un mot de passe.
                    </div>
                </div>
                <?php endif; ?>

                <!-- Profil 1 -->
                <div class="col-md-6 form-section">
                    <label for="profil1" class="form-label">Profil 1</label>
                    <select class="form-select" id="profil1" name="profil1" required onchange="updateProfile2()">
                        <option value="" disabled <?= empty($user['profil1']) ? 'selected' : '' ?>>Choisir...</option>
                        <option value="residence" <?= $user['profil1'] === 'residence' ? 'selected' : '' ?>>Résidence
                        </option>
                        <option value="section" <?= $user['profil1'] === 'section' ? 'selected' : '' ?>>Section</option>
                        <option value="atelier" <?= $user['profil1'] === 'atelier' ? 'selected' : '' ?>>Atelier</option>
                        <option value="dst" <?= $user['profil1'] === 'dst' ? 'selected' : '' ?>>DST</option>
                        <option value="service" <?= $user['profil1'] === 'service' ? 'selected' : '' ?>>Service</option>
                        <option value="admin" <?= $user['profil1'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <div class="invalid-feedback">
                        Veuillez sélectionner un profil.
                    </div>
                </div>

                <!-- Profil 2 -->
                <div class="col-md-6 form-section">
                    <label for="profil2" class="form-label">Profil 2</label>
                    <select class="form-select" id="profil2" name="profil2" required>
                        <option value="<?= htmlspecialchars($user['profil2']) ?>" selected>
                            <?= htmlspecialchars($user['profil2']) ?>
                        </option>
                    </select>
                    <div class="invalid-feedback">
                        Veuillez sélectionner un sous-profil.
                    </div>
                </div>
            </div>

            <!-- Boutons de soumission -->
            <div class="d-flex justify-content-between mt-4">
                <a href="users.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <button type="submit" class="btn btn-primary-custom"
                    onclick="return confirm('Êtes-vous sûr de vouloir continuer ?')">
                    <i class="fas fa-save me-2"></i>ENREGISTRER
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php
$pavillons = allPavillons($connexion);
$services = allServices($connexion);
?>
    <script>
    // Fonction pour mettre à jour le Profil 2 en fonction du Profil 1 sélectionné
    function updateProfile2() {
        const profile1 = document.getElementById('profil1').value;
        const profile2Select = document.getElementById('profil2');

        // Options par défaut
        profile2Select.innerHTML = '<option value="" disabled selected>Choisir...</option>';

        // Définir les options en fonction du Profil 1
        const options = {
            'dst': ['DST', 'S.E.M'],
            'atelier': ['chef d\'atelier'],
            'residence': [
                <?php 
        foreach ($pavillons as $pav) {
            echo "'" . htmlspecialchars($pav['campus'] . " | " . $pav['pavillon'], ENT_QUOTES) . "',";
        }
        ?>
            ],
            'service': [
                <?php 
        foreach ($services as $ser) {
            echo "'" . htmlspecialchars($ser['nom'], ENT_QUOTES) . "',";
        }
        ?>
            ],
            'admin': ['Admin'],
            'section': ['Plomberie', 'Maçonnerie', 'Électricité', 'Menuserie_bois', 'Menuserie_allu',
                'Menuserie_metallique', 'Froid', 'Peinture'
            ]
        };
        // Ajouter les options appropriées
        if (options[profile1]) {
            options[profile1].forEach(option => {
                const newOption = document.createElement('option');
                newOption.value = option;
                newOption.textContent = option;
                profile2Select.appendChild(newOption);
            });
        }
    }

    // Basculer la visibilité du mot de passe
    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Validation du formulaire
    (function() {
        'use strict';

        const forms = document.querySelectorAll('.needs-validation');

        Array.from(forms).forEach(form => {
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