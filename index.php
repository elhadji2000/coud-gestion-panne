<?php
session_start();
if (!empty($_SESSION['username']) && !empty($_SESSION['mdp'])) {
  session_destroy();
}
include('activite.php');
include('traitement/connect.php');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <title>GESCOUD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/COUD/panne/assets/css/base.css" />
  <link rel="stylesheet" href="assets/css/vendor.css" />
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/login.css" />
  <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
  <!-- script================================================== -->
  <script src="../assets/js/modernizr.js"></script>
  <script src="assets/js/pace.min.js"></script>
</head>

<body>
  <header class="s-header">
    <div class="header-logo">
      <a class="site-logo" href="#"><img src="/COUD/panne/assets/images/logo.png" alt="Homepage" /></a>
      Centre des Oeuvres universitaires de Dakar
    </div>
    <nav class="header-nav-wrap">
      <ul class="header-nav">

      </ul>
    </nav>
  </header>
  <section id="homedesigne" class="s-homedesigne">
    <p class="lead">Bienvenue dans l'espace de connexion !</p>
  </section>
  <div class="container">
    <div class="row add-bottom">
      <div class="row contact__main">
        <div class="col-eight tab-full contact__form1">
          <form id="loginForm" action="/COUD/panne/traitement/connect.php">
            <center>
              <strong>VEUILLEZ RENSEIGNER LES CHAMPS</strong>
            </center>
            <span class="login-error">
              <?php
              if (isset($_GET['error'])) {
                echo $_GET['error'];
              }
              ?>
            </span>
            <fieldset>
              <div class="form-field">
                <input onkeydown="upperCaseF(this)" name="username_user" id="username" required type="text" placeholder="Numero de carte (ou du certificat d'inscription)" value="" class="full-width">
              </div>
              <div class="form-field">
                <input name="password_user" type="password" required id="password" placeholder="Mot de passe" value="" class="full-width">
              </div>
              <?php if (isset($error_message)) { ?>
                <div id="error-message" class="error-message"><?= $error_message ?></div>
              <?php } ?>
              <div class="form-field">
                <button type="submit" class="full-width btn--primary">Se connecter</button>
                <br><br>
                <a href='mpo1'>Mot de passe GESCOUD oublié ?</a> <br>
                <a href='rc'>Faire une reclamation?</a> <br>
                <center> <a href='index'>Retour</a> </center>
                <div class="submit-loader">
                  <div class="text-loader">Connexion en cours...</div>
                  <div class="s-loader">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                  </div>
                </div>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- <div id="preloader">
    <div id="loader"></div>
  </div> -->
  <!-- Java Script================================================== -->
  <script src="assets/js/script.js"></script>
  <script src="assets/js/jquery-3.2.1.min.js"></script>
  <script src="assets/js/plugins.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>