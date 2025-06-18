<?php
session_start();
include('../../traitement/fonction.php');
include('../../traitement/requete.php');

// Vérifier si l'ID est présent dans l'URL
if (isset($_GET['id'])) {
    $id_entree = $_GET['id'];
    
    // Appeler la fonction de suppression
    if (supprimerEntree($connexion, $id_entree)) {
        // Journaliser l'activité
        //journaliser($_SESSION['username'], "Suppression de l'article ID: $id_article");
        
        // Redirection avec message de succès
        header('Location: entree_stock.php?success=suppression');
    } else {
        // Redirection avec message d'erreur
        header('Location: entree_stock.php?error=suppression');
    }
} else {
    // Si aucun ID n'est spécifié
    header('Location: entree_stock.php?error=no_id');
}
exit();
?>