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
    <link rel="icon" href="log.gif" type="image/x-icon">
</head>

<body>
    <header
        style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #f8f9fa; border-bottom: 1px solid #ddd;">
        <div style="display: flex; align-items: center;">
            <img src="http://localhost/COUD/panne/assets/images/logo.png" alt="Logo" style="height: 40px;">
            <span style="margin-left: 10px; font-weight: bold; font-size: 18px;">GESCOUD</span>
        </div>

        <nav>
            <ul style="display: flex; list-style: none; margin: 0; padding: 0;">
                <?php if ($_SESSION['profil'] == 'admin') { ?>
                <li style="margin: 0 10px;">
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li style="margin: 0 10px;"><a href="http://localhost/COUD/panne/profils/stock/gestion.php"><i
                            class="fas fa-warehouse" aria-hidden="true"></i> Stock</a></a>
                </li>
                <li style="margin: 0 10px;"><a href="http://localhost/COUD/panne/profils/admin/users.php"><i
                            class="fa fa-users" aria-hidden="true"></i>Utilisateurs</a></li>
                <?php } elseif ($_SESSION['profil'] == 'residence') { ?>
                <li style="margin: 0 10px;"><a href="http://localhost/COUD/panne/profils/dasboard.php"><i
                            class="fa fa-home" aria-hidden="true"></i>Accueil</a></li>
                <li style="margin: 0 10px;"><a href="http://localhost/COUD/panne/profils/residence/listPannes.php"> <i
                            class="fa fa-wrench" aria-hidden="true"></i> Gestion Pannes</a></li>
                <?php } elseif (($_SESSION['profil'] == 'dst') || ($_SESSION['profil'] == 'atelier')) { ?>
                <li style="margin: 0 10px;">
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li style="margin: 0 10px;"><a href="http://localhost/COUD/panne/profils/dst/listPannes.php"> <i
                            class="fa fa-wrench" aria-hidden="true"></i> Gestion Pannes</a></li>
                <li style="margin: 0 10px;"><a href="http://localhost/COUD/panne/profils/stock/gestion.php"><i
                            class="fas fa-warehouse" aria-hidden="true"></i>Stock</a>
                </li>
                <?php } elseif ($_SESSION['profil'] == 'section') { ?>
                <li style="margin: 0 10px;">
                    <a href="http://localhost/COUD/panne/profils/dasboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>Accueil
                    </a>
                </li>
                <li style="margin: 0 10px;">
                    <a href="http://localhost/COUD/panne/profils/section/listPannes.php">
                        <i class="fa fa-wrench" aria-hidden="true"></i>Gestion Pannes
                    </a>
                </li>
                <?php } ?>
                <li style="margin: 0 10px;">
                    <a href="/COUD/panne/profils/admin/update_mdp.php">
                        <i class="fa fa-lock" aria-hidden="true"></i>Mot de passe
                    </a>
                </li>
                <li style="margin: 0 10px;">
                    <a href="/COUD/panne/logout.php"><i class="fa fa-sign-out"></i>Déconnexion</a>
                </li>

            </ul>
        </nav>
    </header>

    <div style="text-align: center; padding: 10px; background: #3777B0;color:white;">
        <?php if (in_array($_SESSION['profil'], ['residence', 'dst', 'admin', 'section', 'atelier'])) { ?>
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