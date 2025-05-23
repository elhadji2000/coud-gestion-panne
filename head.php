<?php
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
  header('Location: /COUD/codif/');
  exit();
}
require_once(__DIR__ . '/traitement/fonction.php');
if ($_SESSION['profil'] == 'user') {
  $inforequeteAffectEtu = getStudentChoiseLit($_SESSION['id_etu']);

  $affecter = 0;
  while ($row = $inforequeteAffectEtu->fetch_assoc()) {
    $affecter++;
  }
  $resultatReqLitEtu = getOneLitByStudent($_SESSION['id_etu']);
}
?>

<head>
  <!--- basic page needs================================================== -->
  <meta charset="utf-8" />
  <title>GESCOUD</title>
  <meta name="description" content="" />
  <meta name="author" content="" />

  <!-- mobile specific metas================================================== -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS================================================== -->
  <link rel="stylesheet" href="http://localhost/COUD/panne/assets/css/base.css" />
  <link rel="stylesheet" href="http://localhost/COUD/panne/assets/css/vendor.css" />
  <link rel="stylesheet" href="http://localhost/COUD/panne/assets/css/main.css" />

  <!-- script================================================== -->
  <script src="http://localhost/COUD/panne/assets/js/modernizr.js"></script>
  <script src="http://localhost/COUD/panne/assets/js/pace.min.js"></script>

  <!-- favicons================================================== -->
  <link rel="shortcut icon" href="log.gif" type="image/x-icon" />
  <link rel="icon" href="log.gif" type="image/x-icon" />
</head>

<body id="top">
  <!-- header================================================== -->
  <header class="s-header">
    <div class="header-logo">
      <a class="site-logo" href="#"><img src="http://localhost/COUD/panne/assets/images/logo.png" alt="Homepage" /></a>
      GESCOUD
    </div>
    <nav class="header-nav-wrap">
      <ul class="header-nav">
        <?php if (($_SESSION['profil'] == 'paiement') && isset($_SESSION['classe'])) { ?>
          <li class="nav-item">
            <a class="nav-link" href="paiement.php" title="Paiement de caution">Paiement</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../personnels/niveau.php" title="Changer de niveau de formation ">Changer-Classe</a>
          </li>
        <?php } ?>
        <?php if (($_SESSION['profil'] == 'validation') && isset($_SESSION['classe'])) { ?>
          <li class="nav-item">
            <a class="nav-link" href="validation.php" title="Paiement de caution">Validation</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../personnels/niveau.php" title="Changer de niveau de formation ">Changer-Classe</a>
          </li>
        <?php } ?>
        <?php if (($_SESSION['profil'] == 'quota') && isset($_SESSION['classe'])) { ?>
          <li class="nav-item active">
            <a class="nav-link" href="listeLits.php" title="Revenir à la page d'accueil">Accueil <span></span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="detailsLits.php" title="Détail des lits affecté à cette classe">Détail</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="niveau.php" title="Changer de niveau de formation ">Changer-Classe</a>
          </li>
        <?php } ?>
        <?php if ($_SESSION['profil'] == 'user') { ?>
          <li class="nav-item active">
            <a class="nav-link" href="../etudiants/resultat.php" title="Revenir à la page d'accueil">Accueil</a>
          </li>
          <?php
          if ($affecter == 0) {
            $_SESSION['lit_choisi'] = ''; ?>
            <li class="nav-item active">
              <a class="nav-link" href="../etudiants/codifier.php" title="Aller à la page des codifications">Codifier</a>
            </li>
          <?php } else {
            while ($rows = $resultatReqLitEtu->fetch_assoc()) {
              $_SESSION['lit_choisi'] = $rows['lit'];
              $_SESSION['id_lit'] = $rows['id_lit'];
            }
          }
          ?>
        <?php } ?>
        <li class="nav-item">
          <a class="nav-link" href="/COUD/panne/" title="Déconnexion"><i class="fa fa-sign-out" aria-hidden="true"></i> Déconnexion</a>
        </li>
      </ul>
    </nav>

    <a class="header-menu-toggle" href="#0"><span>Menu</span></a>
  </header>
  <!-- end s-header -->
</body>
<section id="homedesigne" class="s-homedesigne">
  <?php if (($_SESSION['profil'] == 'quota') || ($_SESSION['profil'] == 'paiement') || ($_SESSION['profil'] == 'validation') || ($_SESSION['profil'] == 'chef_pavillon') || ($_SESSION['profil'] == 'residence')|| ($_SESSION['profil'] == 'dst') || ($_SESSION['profil'] == 'residence')|| ($_SESSION['profil'] == 'admin') || ($_SESSION['profil'] == 'residence') || ($_SESSION['profil'] == 'section') || ($_SESSION['profil'] == 'atelier')) { ?>
    <p class="lead">Espace Administration: Bienvenue! <br> <span>
        (<?= $_SESSION['prenom'] . "  " . $_SESSION['nom'] . " | " . $_SESSION['profil2'] ?>)
      </span></p>
  <?php } elseif ($_SESSION['profil'] == 'user') { ?>
    <p class="lead">Espace etudiant: Bienvenue! <br> <br> <span>
        (<?= $_SESSION['prenom'] . "  " . $_SESSION['nom'] ?>)
      </span><br><span><?= $_SESSION['classe']; ?></span></p>
  <?php } ?>
</section>