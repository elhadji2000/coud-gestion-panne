<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
// if (empty($_SESSION['classe'])) {
//     header('location: /COUD/codif/profils/personnels/niveau.php');
//     exit();
// }
//connexion à la base de données
include('../../traitement/fonction.php');
connexionBD();
// Sélectionnez les options à partir de la base de données avec une pagination
include('../../traitement/requete.php');

// Comptez le nombre total d'options dans la base de données details lits affecter (quotas)
// $total_pagess = getLitByQuotas($_SESSION['classe'], $_SESSION['sexe']);
$countIn = 0;
if (isset($_GET['erreurValider'])) {
    $_SESSION['erreurValider'] = $_GET['erreurValider'];
} else {
    $_SESSION['erreurValider'] = '';
}
if (isset($_GET['successValider'])) {
    $_SESSION['successValider'] = $_GET['successValider'];
} else {
    $_SESSION['successValider'] = '';
}
if (isset($_GET['erreurNonTrouver'])) {
    $_SESSION['erreurNonTrouver'] = $_GET['erreurNonTrouver'];
} else {
    $_SESSION['erreurNonTrouver'] = '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: CODIFICATION</title>
    <!-- CSS================================================== -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <!-- script================================================== -->
    <script src="../../assets/js/modernizr.js"></script>
    <script src="../../assets/js/pace.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.min.js">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.bundle.min.js">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="row">
        <div class="text-center">
            <h2>Validation des lits</h2>
        </div>
    </div>
    <div class="container">
        <!-- <span style="color: red;"> <?= $_SESSION['erreurValider']; ?> </span> -->
        <div class="row" style="justify-content: center;">
            <?php if ($_SESSION['erreurValider']) { ?>
                <div class="col-md-3">
                    <div class="alert alert-warning" role="alert">
                        <?= $_SESSION['erreurValider']; ?>
                    </div>
                </div>
            <?php } elseif ($_SESSION['successValider']) { ?>
                <div class="col-md-3">
                    <div class="alert alert-success" role="alert">
                        <?= $_SESSION['successValider']; ?>
                    </div>
                </div>
            <?php } elseif ($_SESSION['erreurNonTrouver']) { ?>
                <div class="col-md-3">
                    <div class="alert alert-danger" role="alert">
                        <?= $_SESSION['erreurNonTrouver']; ?>
                    </div>
                </div>
            <?php } ?>
            <form action="requestValidation.php" method="POST" style="display: flex;justify-content: center">
                <div class="row">
                    <div class="col-md-10">
                        <input id="numEtudiant" name="numEtudiant" type="text" class="form-control" placeholder="NUMERO CARTE ETUDIANT" oninput="checkInput()" onblur="validateInput()">
                        <!-- <p id="affichage"></p> -->
                        <script>
                            // Sélectionner l'élément input
                            var inputElement = document.getElementById('numEtudiant');

                            // Ajouter un écouteur d'événement sur l'input pour détecter les changements
                            inputElement.addEventListener('input', function() {
                                // Récupérer la valeur du champ input
                                var texte = inputElement.value;

                                // Convertir le texte en majuscule
                                var texteMajuscule = texte.toUpperCase();

                                // Mettre à jour la valeur du champ input
                                inputElement.value = texteMajuscule;

                                // Récupérer l'élément où afficher le texte
                                var affichageElement = document.getElementById('affichage');

                                // Mettre à jour le texte de l'élément
                                affichageElement.textContent = texteMajuscule;
                            });
                        </script>
                        <!-- <span id="inputMessage" style="color: green; font-size: 12px;"></span> -->
                    </div>
                    <div class="col-md-2">
                        <button id="submitBtn" type="submit" class="btn btn-primary" disabled>Rechercher</button>
                    </div>
                </div>
            </form>
        </div><br><br>
        <div class="row">
            <div class="col-md-12">
                <ul class="options">
                    <?php
                    if (isset($_GET['data'])) {
                        $data = $_GET['data'];
                    ?>
                        <form action="requestValidation.php" method="POST">
                            <div class="row" style="display: flex;justify-content: center;color:black;">
                                <div class="col-md-3 mb-3">
                                    <input type="text" class="form-control" placeholder="<?= $data['prenoms'] ?>" disabled>
                                    <input class="form-control" name="valide" value="<?= $data['id_aff'] ?>" style="visibility: hidden;">
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" value="<?= $data['nom'] ?>" disabled>
                                </div>
                            </div>
                            <div class="row" style="display: flex;justify-content: center;color:black;">
                                <div class="col-md-3 mb-3">
                                    <input class="form-control" placeholder="<?= $data['etablissement'] ?>" disabled>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" placeholder="<?= $data['niveauFormation'] ?>" disabled>
                                </div>
                            </div><br>
                            <div class="row" style="display: flex;justify-content: center;color:black;">
                                <div class="col-md-3 mb-3">
                                    <input class="form-control" placeholder="<?= $data['numIdentite'] ?>" disabled>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" placeholder="<?= $data['campus'] ?>" disabled>
                                </div>
                            </div><br>
                            <div class="row" style="display: flex;justify-content: center;color:black;">
                                <div class="col-md-3 mb-3">
                                    <input class="form-control" placeholder="<?= $data['pavillon'] ?>" disabled>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" placeholder="<?= $data['lit'] ?>" disabled>
                                </div>
                            </div>
                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#confirmationModal">VALIDER</button>
                            <!-- Modal -->
                            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            Êtes-vous sûr de vouloir effectuer cette action ?
                                        </div>
                                        <div class="modal-footer">
                                            <!-- Boutons pour confirmer ou annuler -->
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Confirmer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <script src="../../assets/js/jquery-3.2.1.min.js"></script>
        <script src="../../assets/js/plugins.js"></script>
        <script src="../../assets/js/main.js"></script>

        <!-- JavaScript de Bootstrap (assurez-vous d'ajuster le chemin si nécessaire) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
<script src="../../assets/js/script.js"></script>
</body>

</html>