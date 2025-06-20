<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Toujours démarrer la session si elle n'est pas active
}
// 
require_once(__DIR__ . '/traitement/fonction.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>GESCOUD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://localhost/COUD/panne/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <style>
        /* Styles de base */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            flex-wrap: wrap;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo-container img {
            height: 40px;
        }
        
        .logo-container span {
            margin-left: 10px;
            font-weight: bold;
            font-size: 18px;
        }
        
        /* Navigation desktop */
        .desktop-nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .desktop-nav li {
            margin: 0 10px;
        }
        
        .desktop-nav a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .desktop-nav i {
            margin-right: 5px;
        }
        
        /* Menu hamburger */
        .hamburger {
            display: none;
            cursor: pointer;
            font-size: 24px;
        }
        
        /* Navigation mobile */
        .mobile-nav {
            display: none;
            width: 100%;
        }
        
        .mobile-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mobile-nav li {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .mobile-nav a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .mobile-nav i {
            margin-right: 10px;
        }
        
        /* Banner */
        .banner {
            text-align: center;
            padding: 10px;
            background: #3777B0;
            color: white;
        }
        
        /* Media queries pour la responsivité */
        @media (max-width: 768px) {
            .desktop-nav {
                display: none;
            }
            
            .hamburger {
                display: block;
            }
            
            .mobile-nav.active {
                display: block;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="http://localhost/COUD/panne/assets/images/logo.png" alt="Logo">
            <span>GESCOUD</span>
        </div>

        <div class="hamburger" onclick="toggleMenu()">☰</div>

        <nav class="desktop-nav">
            <ul>
                <?php if ($_SESSION['profil'] == 'admin') { ?>
                <li>
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li><a href="http://localhost/COUD/panne/profils/stock/gestion.php">
                        <i class="fas fa-warehouse" aria-hidden="true"></i> Stock</a>
                </li>
                <li><a href="http://localhost/COUD/panne/profils/admin/users.php">
                        <i class="fa fa-users" aria-hidden="true"></i>Utilisateurs</a></li>

                <?php } elseif (($_SESSION['profil'] == 'residence') || ($_SESSION['profil'] == 'service')) { ?>
                <li><a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil</a></li>
                <li><a href="http://localhost/COUD/panne/profils/residence/listPannes.php"> 
                        <i class="fa fa-wrench" aria-hidden="true"></i>Déclarations</a></li>

                <?php } elseif (($_SESSION['profil'] == 'dst') || ($_SESSION['profil'] == 'atelier')) { ?>
                <li>
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li><a href="http://localhost/COUD/panne/profils/dst/listPannes.php"> 
                        <i class="fa fa-wrench" aria-hidden="true"></i>Déclarations</a></li>
                <li><a href="http://localhost/COUD/panne/profils/stock/gestion.php">
                        <i class="fas fa-warehouse" aria-hidden="true"></i>Stock</a>
                </li>

                <?php } elseif ($_SESSION['profil'] == 'section') { ?>
                <li>
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li>
                    <a href="http://localhost/COUD/panne/profils/section/listPannes.php">
                        <i class="fa fa-wrench" aria-hidden="true"></i>Déclarations
                    </a>
                </li>
                <?php } ?>
                <li>
                    <a href="/COUD/panne/profils/admin/update_mdp.php">
                        <i class="fa fa-lock" aria-hidden="true"></i>Mot de passe
                    </a>
                </li>
                <li>
                    <a href="/COUD/panne/logout.php" onclick="return confirm('Êtes-vous sûr de vouloir deconnecter ?')">
                        <i class="fa fa-sign-out"></i>Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <nav class="mobile-nav" id="mobileNav">
            <ul>
                <?php if ($_SESSION['profil'] == 'admin') { ?>
                <li>
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li><a href="http://localhost/COUD/panne/profils/stock/gestion.php">
                        <i class="fas fa-warehouse" aria-hidden="true"></i> Stock</a>
                </li>
                <li><a href="http://localhost/COUD/panne/profils/admin/users.php">
                        <i class="fa fa-users" aria-hidden="true"></i>Utilisateurs</a></li>

                <?php } elseif (($_SESSION['profil'] == 'residence') || ($_SESSION['profil'] == 'service')) { ?>
                <li><a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil</a></li>
                <li><a href="http://localhost/COUD/panne/profils/residence/listPannes.php"> 
                        <i class="fa fa-wrench" aria-hidden="true"></i>Déclarations</a></li>

                <?php } elseif (($_SESSION['profil'] == 'dst') || ($_SESSION['profil'] == 'atelier')) { ?>
                <li>
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li><a href="http://localhost/COUD/panne/profils/dst/listPannes.php"> 
                        <i class="fa fa-wrench" aria-hidden="true"></i>Déclarations</a></li>
                <li><a href="http://localhost/COUD/panne/profils/stock/gestion.php">
                        <i class="fas fa-warehouse" aria-hidden="true"></i>Stock</a>
                </li>

                <?php } elseif ($_SESSION['profil'] == 'section') { ?>
                <li>
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li>
                    <a href="http://localhost/COUD/panne/profils/section/listPannes.php">
                        <i class="fa fa-wrench" aria-hidden="true"></i>Déclarations
                    </a>
                </li>
                <?php } ?>
                <li>
                    <a href="/COUD/panne/profils/admin/update_mdp.php">
                        <i class="fa fa-lock" aria-hidden="true"></i>Mot de passe
                    </a>
                </li>
                <li>
                    <a href="/COUD/panne/logout.php" onclick="return confirm('Êtes-vous sûr de vouloir deconnecter ?')">
                        <i class="fa fa-sign-out"></i>Déconnexion
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="banner">
        <?php if (in_array($_SESSION['profil'], ['residence', 'dst', 'admin', 'section', 'atelier','service'])) { ?>
        <p>Espace Administration: Bienvenue!<br>
            <span>(<?= $_SESSION['prenom'] . " " . $_SESSION['nom'] . " | " . $_SESSION['profil2'] ?>)</span>
        </p>
        <?php } elseif ($_SESSION['profil'] == 'user') { ?>
        <p>Espace étudiant: Bienvenue!<br>
            <span>(<?= $_SESSION['prenom'] . " " . $_SESSION['nom'] ?>)</span><br>
            <span><?= $_SESSION['classe'] ?></span>
        </p>
        <?php } ?>
    </div>

    <script>
        function toggleMenu() {
            const mobileNav = document.getElementById('mobileNav');
            mobileNav.classList.toggle('active');
        }
    </script>
</body>
</html>