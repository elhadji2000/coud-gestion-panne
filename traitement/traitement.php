<?php
session_start();
include 'fonction.php';

// Vérifier si les données du formulaire sont définies
//#################################### DEBUT Enregister une Panne #####################################################
if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['type_panne']) && isset($_POST['localisation']) &&
    isset($_POST['description']) && isset($_POST['niveau_urgence']) &&
    isset($_SESSION['id_user'])) {

    $type_panne = $_POST['type_panne'];
    $localisation = $_POST['localisation'];
    $description = $_POST['description'];
    $niveau_urgence = $_POST['niveau_urgence'];
    $date_enregistrement = date('d/m/Y') ;// La date actuelle

    $id_chef_residence = $_SESSION['id_user'];
    $profil1 = $_SESSION['profil2'];

    // Ajouter la valeur du profil1 à la localisation
    $localisation = $profil1 . " | " . $localisation;

    if (insertPanne($connexion, $type_panne, $date_enregistrement, $description, $localisation, $niveau_urgence, $id_chef_residence)) {
        header('Location: /COUD/panne/profils/residence/ajoutPanne?success=1');
        exit();
    } else {
        header('Location: /COUD/panne/profils/residence/ajoutPanne');
        exit();
    }
} 
//#################################### FIN Enregister une Panne #####################################################

//#################################### DEBUT Enregister une Observation #####################################################
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['evaluation']) && isset($_POST['commentaire']) &&
    isset($_POST['idPanne']) && isset($_POST['idIntervention']) &&
    isset($_POST['idObservation'])) {

    $evaluationQualite = $_POST['evaluation'];
    $commentaireSuggestion = $_POST['commentaire'];
    $idUtilisateur = $_SESSION['id_user'];
    $idPanne = $_POST['idPanne'];
    $idIntervention = $_POST['idIntervention'];
    $idObservation = isset($_POST['idObservation']) ? $_POST['idObservation'] : null;
    $date_observation = date('d/m/Y'); // La date actuelle

    if (enregistrerObservation($connexion, $idPanne, $idUtilisateur, $idIntervention, $evaluationQualite, $date_observation, $commentaireSuggestion, $idObservation)) {
        header('Location: /COUD/panne/profils/residence/listPannes?obs=1');
        exit();
    } else {
        header('Location: /COUD/panne/profils/residence/observation');
        exit();
    }
}
//#################################### FIN Enregister une Observation #####################################################

//#################################### DEBUT Enregister une Intervention #####################################################
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['agent']) && isset($_POST['details']) &&
    isset($_POST['idPanne']) && isset($_POST['date_intervention']) && isset($_POST['intervention_id']) ) {

    $personne_agent = $_POST['agent'];
    $description_action = $_POST['details'];
    $id_chef_atelier = $_SESSION['id_user'];
    $id_panne = $_POST['idPanne'];
    $date_intervention = $_POST['date_intervention'];
    // Convertir la date au format français
    $date_intervention = date('d/m/Y', strtotime($date_intervention));

    $intervention_id = isset($_POST['intervention_id']) ? $_POST['intervention_id'] : null;
    $date_sys = date('d/m/Y');


    $resultat = "en cours";

    if ( enregistrerIntervention($connexion, $date_intervention, $description_action, $personne_agent, $date_sys, $resultat, $id_chef_atelier, $id_panne, $intervention_id)) {
        header('Location: /COUD/panne/profils/dst/listPannes');
        exit();
    } else {
        header('Location: /COUD/panne/profils/dst/intervention');
        exit();
    }
}
//#################################### FIN Enregister une Intervention #####################################################

//########################## pour supprimer Intervention ######################################################

elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['intervention_id']) ) {
    $intervention_id = $_GET['intervention_id'];
    // Supprimer la panne de la base de données
    $sql = "DELETE FROM Intervention WHERE id = ?";
    if ($stmt = $connexion->prepare($sql)) {
        $stmt->bind_param("i", $intervention_id);
        $stmt->execute();
        $stmt->close();
        header('Location: /COUD/panne/profils/dst/listPannes');
    exit();
    }

    header('Location: /COUD/panne/profils/dst/listPannes?echec=');
    exit();
} 
//########################### Fin pour supprimer Intervention #####################################################

//############### pour supprimer Panne ##################################
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['panneDelete'])) {
    $panne_id = $_POST['panneDelete'];

    // Supprimer la panne de la base de données
    $sql = "DELETE FROM panne WHERE id = ?";
    if ($stmt = $connexion->prepare($sql)) {
        $stmt->bind_param("i", $panne_id);
        $stmt->execute();
        $stmt->close();
        header('Location: /COUD/panne/profils/residence/listPannes');
    exit();
    }

    header('Location: /COUD/panne/profils/residence/listPannes?echec='.$panne_id);
    exit();
} 

// ##################################################################################################

//########################### pour Enregistrer une Imputation #######################################
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['idPanne']) && isset($_POST['instruction']) &&
    isset($_POST['userId']) && isset($_POST['imputation_id']) && isset($_POST['type_panne'])) {

    $idChefDst = $_POST['userId'];
    $instruction = $_POST['instruction'];
    $idPanne = $_POST['idPanne'];
    $type_panne = $_POST['type_panne'];
    $imputationId = isset($_POST['imputation_id']) ? $_POST['imputation_id'] : null;
    $dateImputation = date('d/m/Y');
    $resultat = "imputer";

    if (enregistrerImputation($connexion, $idPanne, $idChefDst, $instruction, $resultat, $dateImputation, $imputationId)) {
        header('Location: /COUD/panne/profils/dst/listPannes?success=2&type_panne='. $type_panne);
        exit();
    } else {
        header('Location: /COUD/panne/profils/dst/imputation');
        exit();
    }
}
//########################### FIN Enregistrer une Imputation #######################################
//############### pour supprimer imputation ##################################

elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['imputation_id']) ) {
    $imputation_id = $_GET['imputation_id'];
    // Supprimer la panne de la base de données
    $sql = "DELETE FROM imputation WHERE id = ?";
    if ($stmt = $connexion->prepare($sql)) {
        $stmt->bind_param("i", $imputation_id);
        $stmt->execute();
        $stmt->close();
        header('Location: /COUD/panne/profils/dst/listPannes');
    exit();
    }

    header('Location: /COUD/panne/profils/dst/listPannes?echec=');
    exit();
} 
//############### Fin pour supprimer imputation ##################################

//############### Pour Ajouter un Utilisateur ##################################
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['username']) && isset($_POST['nom']) &&
    isset($_POST['prenom']) && isset($_POST['telephone']) &&
    isset($_POST['email']) && isset($_POST['profil1']) &&
    isset($_POST['profil2'])) {

    // Récupérer les données du formulaire
    $username = $_POST['username'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $telephone = $_POST['telephone'];
    $profil1 = $_POST['profil1'];
    $profil2 = $_POST['profil2'];
    $email = $_POST['email'];

    // Vérifier si c'est une mise à jour ou un nouvel utilisateur
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Cas de mise à jour d'un utilisateur existant
        $id = $_POST['id'];

        // Optionnel : Vérifier si un nouveau mot de passe est saisi
        $motDePasse = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;

        // Appeler la fonction de mise à jour
        if (updateUtilisateur($connexion, $id, $username, $nom, $prenom, $email, $telephone, $motDePasse, $profil1, $profil2)) {
            // Redirection en cas de succès
            header('Location: /COUD/panne/profils/admin/users');
            exit();
        } else {
            // Redirection en cas d'échec
            header('Location: /COUD/panne/profils/admin/editUser?id=' . $id);
            exit();
        }

    } else {
        // Cas de création d'un nouvel utilisateur
        $motDePasse = $_POST['password'];

        // Appeler la fonction d'enregistrement
        if (enregistrerUtilisateur($connexion, $username, $nom, $prenom, $email, $telephone, $motDePasse, $profil1, $profil2)) {
            // Redirection en cas de succès
            header('Location: /COUD/panne/profils/admin/users');
            exit();
        } else {
            // Redirection en cas d'échec
            header('Location: /COUD/panne/profils/admin/addUser');
            exit();
        }
    }
}


//############### pour supprimer Utilisateur ##################################
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'changeUserStatus') {
        $userId = $_POST['userStatusChange'];
        $newStatus = $_POST['newStatus'];

        // Mettre à jour le statut de l'utilisateur dans la base de données
        $sql = "UPDATE Utilisateur SET statut = ? WHERE id = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('ii', $newStatus, $userId);
        if ($stmt->execute()) {
            
            header('Location: /COUD/panne/profils/admin/users?message=Statut modifié avec succès');
            exit();
        } else {
            header('Location: /COUD/panne/profils/admin/users?message=error');
            exit();
        }
        exit();
    }
}

    
?>