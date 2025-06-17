<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /COUD/codif/');
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');

// Vérifier si l'ID est présent dans l'URL
if (isset($_GET['id'])) {
    $id_article = $_GET['id'];
    
    // Appeler la fonction de suppression
    if (supprimerArticle($connexion, $id_article)) {
        // Journaliser l'activité
        //journaliser($_SESSION['username'], "Suppression de l'article ID: $id_article");
        
        // Redirection avec message de succès
        header('Location: articles.php?success=suppression');
    } else {
        // Redirection avec message d'erreur
        header('Location: articles.php?error=suppression');
    }
} else {
    // Si aucun ID n'est spécifié
    header('Location: articles.php?error=no_id');
}
exit();
?>