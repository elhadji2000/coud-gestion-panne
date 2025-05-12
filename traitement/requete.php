<?php
// Verifier la session si elle est actif, sinon on redirige vers la racine
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/panne/');
    exit();
}
// Verifier si la session stock toujours la valeur du niveau de la classe, sinon on l'initialise
if (isset($_SESSION['classe'])) {
    $classe = $_SESSION['classe'];
} else {
    $classe = "";
}
// appelle la page fonction.php
require_once(__DIR__ . '/fonction.php');

// Declaration des variables et tableaux
$tableauDataFaculte = [];
$tableauDataNiveauFormation = [];
$erreurClasse = "";
$messageErreurFaculte = "";
$messageErreurDepartement = "";



