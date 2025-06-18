<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');

// Vérifier si l'ID est présent dans l'URL
if (isset($_GET['id'])) {
    $id_article = $_GET['id'];
    
    // Appeler la fonction de suppression
    if (supprimerSortie($connexion, $id_article)) {
        // Journaliser l'activité
        //journaliser($_SESSION['username'], "Suppression de l'article ID: $id_article");
        
        // Redirection avec message de succès
        header('Location: sortie_stock.php?success=suppression');
    } else {
        // Redirection avec message d'erreur
        header('Location: sortie_stock.php?error=suppression');
    }
} else {
    // Si aucun ID n'est spécifié
    header('Location: sortie_stock.php?error=no_id');
}
exit();
?>