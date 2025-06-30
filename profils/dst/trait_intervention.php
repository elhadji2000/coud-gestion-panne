<?php
session_start();
require_once '../../traitement/fonction.php'; // fichier qui contient la connexion `$connexion`

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Sécurisation des données
    $idPanne = mysqli_real_escape_string($connexion, $_GET['idPanne']);
    //$intervention_id = mysqli_real_escape_string($connexion, $_GET['intervention_id']);
    $type_intervention = mysqli_real_escape_string($connexion, $_GET['type_intervention']);
    $description_action = mysqli_real_escape_string($connexion, $_GET['description_action']);
    $date_intervention = mysqli_real_escape_string($connexion, $_GET['date_intervention']);
    $agents = $_GET['agents'] ?? [];
    $articles = $_GET['articles'] ?? [];
    $resultat="en cours";

    /* if (!empty($intervention_id)) {
    // ➤ Mise à jour de l’intervention existante
    $sql = "UPDATE intervention SET date_intervention=?, type_intervention=?, description_action=? WHERE id=?";
    $stmt = mysqli_prepare($connexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $date_intervention, $type_intervention, $description_action, $intervention_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    } else { */
    // ➤ Ajout d’une nouvelle intervention
    $sql = "INSERT INTO intervention (date_intervention, type_intervention,resultat, description_action, id_panne,id_chef_atelier) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $date_intervention, $type_intervention, $resultat, $description_action, $idPanne,$_SESSION['id_user']);
    mysqli_stmt_execute($stmt);
    $intervention_id = mysqli_insert_id($connexion); // Récupération de l'ID
    mysqli_stmt_close($stmt);
    //}


    // === 2. Vider les anciens agents liés à cette intervention
    $sql = "DELETE FROM intervention_agent WHERE intervention_id=?";
    $stmt = mysqli_prepare($connexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $intervention_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // === 3. Insérer les nouveaux agents
    $sql = "INSERT INTO intervention_agent (intervention_id, agent_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($connexion, $sql);
    foreach ($agents as $agent_id) {
        mysqli_stmt_bind_param($stmt, "ii", $intervention_id, $agent_id);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    // === 4. Traiter la sortie de stock
    foreach ($articles as $article) {
        $article_id = mysqli_real_escape_string($connexion, $article['article_id']);
        $quantite = mysqli_real_escape_string($connexion, $article['quantite']);

        // Enregistrer la sortie de stock (exemple dans une table `sortie_stock`)
        $sql = "INSERT INTO sortie_stock (intervention_id, article_id, quantite, date_sortie) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connexion, $sql);
        mysqli_stmt_bind_param($stmt, "iiis", $intervention_id, $article_id, $quantite, $date_intervention);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // === 5. Redirection avec message
    $_SESSION['success'] = "Intervention et sortie de stock enregistrées avec succès.";
    header('Location: ./listePannes.php');
    exit();
} else {
    echo "Méthode non autorisée.";
}
?>