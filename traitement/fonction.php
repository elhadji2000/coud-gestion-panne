<?php
// Connectez-vous à votre base de données MySQL
function connexionBD()
{
    $connexion = mysqli_connect("localhost", "root", "", "supercoud_panne");

    // Vérifiez la connexion
    if ($connexion === false) {
        die("Erreur : Impossible de se connecter. " . mysqli_connect_error());
    }

    // Fix UTF-8 pour les caractères spéciaux
    mysqli_set_charset($connexion, "utf8mb4");

    return $connexion;
}

$connexion = connexionBD();
//####################### Fonction pour obtenir les pannes enregistrées par l'utilisateur connecté ###########################
function allPannesByUser($connexion, $user_id, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;

    // Requête pour récupérer les pannes paginées
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat,i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.profil1,u.profil2,u.prenom, 
               o.evaluation_qualite,o.id AS idObservation,o.date_observation, o.commentaire_suggestion,m.instruction
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Observation o ON p.id = o.id_panne
        LEFT JOIN Imputation m ON p.id = m.id_panne
        WHERE p.id_chef_residence = ?
        ORDER BY 
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('iii', $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Requête pour compter le nombre total de pannes
    $sqlCount = "
        SELECT COUNT(*) as total_count
        FROM Panne
        WHERE id_chef_residence = ?
    ";
    $stmtCount = $connexion->prepare($sqlCount);
    $stmtCount->bind_param('i', $user_id);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];

    return ['pannes' => $pannes, 'total_count' => $totalCount];
}
// ###############       FIN DE LA FONCTION      ####################

//############## Fonction de connexion dans l'espace utilisateur ############################
function login($username, $password)
{
    global $connexion;

    // Hacher le mot de passe avec SHA-1
    $hashed_password = sha1($password);

    // Requête SQL modifiée pour vérifier si l'utilisateur est actif
    $query = "SELECT * FROM `utilisateur` WHERE `username` = ? AND `password` = ? AND `statut` = 1";
    
    // Préparer la requête pour éviter les injections SQL
    $stmt = $connexion->prepare($query);
    $stmt->bind_param('ss', $username, $hashed_password);
    $stmt->execute();
    
    // Récupérer les résultats
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Fermer la requête
    $stmt->close();
    
    return $user; // Retourne les informations si l'utilisateur est trouvé et actif, sinon retourne null
}

// ###############       FIN DE LA FONCTION login     ####################


// ###############       DEBUT DE LA FONCTION POUR RECHERCHER LES PANNES UTLISATEUR PAR MOTS CLE     ####################
function rechercherPannesParMotCle($connexion, $userId, $searchTerm, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;
    $likeTerm = '%' . $searchTerm . '%';

    // Requête pour récupérer les pannes paginées
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat,i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.profil1,u.profil2,u.prenom, 
               o.evaluation_qualite,o.id AS idObservation,o.date_observation, o.commentaire_suggestion
        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON p.id = o.id_panne
        WHERE p.id_chef_residence = ? AND (
            p.type_panne LIKE ? OR
            p.localisation LIKE ? OR
            p.description LIKE ? OR
            p.niveau_urgence LIKE ? OR
            p.date_enregistrement LIKE ? OR
            i.resultat LIKE ? OR
            i.description_action LIKE ? OR
            i.personne_agent LIKE ? OR
            o.evaluation_qualite LIKE ? OR
            o.commentaire_suggestion LIKE ?
        )
        ORDER BY 
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('issssssssssii', $userId, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Requête pour compter le nombre total de pannes correspondant aux critères de recherche
    $sqlCount = "
        SELECT COUNT(*) as total_count
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON p.id = o.id_panne
        WHERE p.id_chef_residence = ? AND (
            p.type_panne LIKE ? OR
            p.localisation LIKE ? OR
            p.description LIKE ? OR
            p.niveau_urgence LIKE ? OR
            p.date_enregistrement LIKE ? OR
            i.resultat LIKE ? OR
            i.description_action LIKE ? OR
            i.personne_agent LIKE ? OR
            o.evaluation_qualite LIKE ? OR
            o.commentaire_suggestion LIKE ?
        )
    ";

    $stmtCount = $connexion->prepare($sqlCount);
    $stmtCount->bind_param('issssssssss', $userId, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];

    // Calcul du nombre total de pages
    $totalPages = ceil($totalCount / $limit);

    return ['pannes' => $pannes, 'total_count' => $totalCount, 'total_pages' => $totalPages, 'current_page' => $page];
}
//  #################  FIN DE LA  FONCTION     ##########################


//  #################   FONCTION POUR RECUPERER LES DETAILS DE LA PANNE    ##########################
function obtenirPanneParId($connexion, $panneId) {
    $sql = "
        SELECT 
            p.id AS panne_id, 
            p.type_panne, 
            p.date_enregistrement, 
            p.description AS panne_description, 
            p.localisation, 
            p.niveau_urgence,
            u.nom AS chef_nom, 
            u.profil1 AS chef_role,
            i.id AS intervention_id, 
            i.date_intervention, 
            i.description_action, 
            i.resultat, 
            i.personne_agent,
            o.id AS observation_id, 
            o.evaluation_qualite,
            o.date_observation, 
            o.commentaire_suggestion,
            m.id AS imputation_id,
            m.id_chef_dst,
            m.instruction,
            m.date_imputation
        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON p.id = o.id_panne
        LEFT JOIN Imputation m ON p.id = m.id_panne
        WHERE p.id = ?
    ";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $panneId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
//  ################# FIN DE LA  FONCTION  ##########################


// ###############       debut DE LA FONCTION insererPanne     ####################
function insertPanne($connexion, $type_panne, $date_enregistrement, $description, $localisation, $niveau_urgence, $id_chef_residence) {
    $sql = "
        INSERT INTO Panne (type_panne, date_enregistrement, description, localisation, niveau_urgence, id_chef_residence)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('sssssi', $type_panne, $date_enregistrement, $description, $localisation, $niveau_urgence, $id_chef_residence);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    } else {
        return false;
    }
}
// ###############       FIN DE LA FONCTION insererPanne     ####################

// ###############   DEBUT DE LA FONCTION enregistrerObservation   ###########################
function enregistrerObservation($connexion, $idPanne, $idUtilisateur, $idIntervention, $evaluationQualite, $date_observation, $commentaireSuggestion, $idObservation = null) {
    if ($idObservation) {
        // Mise à jour de l'observation existante
        $sql = "
            UPDATE observation
            SET evaluation_qualite = ?,date_observation = ?, commentaire_suggestion = ?
            WHERE id = ?
        ";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('sssi', $evaluationQualite, $date_observation, $commentaireSuggestion, $idObservation);
    } else {
        // Insertion d'une nouvelle observation
        $sql = "
            INSERT INTO observation (id_panne, id_chef_residence, id_intervention, evaluation_qualite, date_observation, commentaire_suggestion)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('iiisss', $idPanne, $idUtilisateur, $idIntervention, $evaluationQualite, $date_observation, $commentaireSuggestion);
    }

    if ($stmt->execute()) {
        if ($evaluationQualite === 'Fait') {
            $sqlUpdateIntervention = "
                UPDATE intervention
                SET resultat = 'depanner'
                WHERE id = ?
            ";
        } elseif ($evaluationQualite === 'Inachevee') {
            $sqlUpdateIntervention = "
                UPDATE intervention
                SET resultat = 'en cours'
                WHERE id = ?
            ";
        } else {
            $sqlUpdateIntervention = null;
        }

        $stmtUpdate = $connexion->prepare($sqlUpdateIntervention);
        $stmtUpdate->bind_param('i', $idIntervention);
        $stmtUpdate->execute();
        return true;
    } else {
        return false;
    }
}
// ###############       FIN DE LA FONCTION enregistrerObservation    ####################

// Fonction pour obtenir les pannes enregistrées par l'utilisateur connecté
function allPannes1($connexion, $page = 1, $limit = 10, $profil2 = null) {
    $offset = ($page - 1) * $limit;

    // Initialiser la clause WHERE
    $whereClause = '';
    $params = [];
    $types = '';

    // Ajouter la condition pour filtrer par profil2 et type_panne
    if ($profil2 !== null) {
        $whereClause = " WHERE p.type_panne = ?";
        $params[] = $profil2;
        $types .= 's'; // 's' pour string
    }

    // Requête pour récupérer les pannes paginées
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat, i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.prenom, u.profil1, u.profil2,
               o.evaluation_qualite, o.id AS idObservation,o.date_observation, o.commentaire_suggestion
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Observation o ON p.id = o.id_panne
        $whereClause
        ORDER BY 
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Ajouter les paramètres pour la requête préparée
    if ($profil2 !== null) {
        // Pour les requêtes qui utilisent des paramètres, assurez-vous de les ajouter à la liste des paramètres
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii'; // 'i' pour integer

        // Lier les paramètres
        $stmt->bind_param($types, ...$params);
    } else {
        // Pas de filtre, seulement les paramètres de LIMIT et OFFSET
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii'; // 'i' pour integer

        // Lier les paramètres
        $stmt->bind_param($types, ...$params);
    }
    
    // Exécuter la requête
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Requête pour compter le nombre total de pannes
    $sqlCount = "
        SELECT COUNT(*) as total_count
        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        $whereClause
    ";
    
    // Préparer la requête de comptage
    $stmtCount = $connexion->prepare($sqlCount);
    
    // Ajouter les paramètres pour la requête préparée
    if ($profil2 !== null) {
        $stmtCount->bind_param('s', $profil2);
    }
    
    // Exécuter la requête
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];

    // Calcul du nombre total de pages
    $totalPages = ceil($totalCount / $limit);

    return ['pannes' => $pannes, 'total_count' => $totalCount, 'total_pages' => $totalPages, 'current_page' => $page];
}
// ###############       FIN DE LA FONCTION      ####################


// ###############  DEBUT DE LA FONCTION  RECHERCHERPANNES()  #####################################
function rechercherPannes($connexion, $profil2 = null, $search = '', $isChefDst = false) {
    // Initialiser la clause WHERE
    $whereClauses = [];
    $params = [];
    $types = '';

    // Ajouter la condition pour filtrer par profil2 si fourni
    if ($profil2 !== null) {
        $whereClauses[] = "p.type_panne = ?";
        $params[] = $profil2;
        $types .= 's'; // 's' pour string
    }

    // Ajouter la condition pour la recherche par mots-clés seulement si un terme de recherche est spécifié
    if (!empty($search)) {
        $searchConditions = " (p.id LIKE ? OR p.type_panne LIKE ? OR p.date_enregistrement LIKE ? OR 
                               p.description LIKE ? OR p.localisation LIKE ? OR 
                               p.niveau_urgence LIKE ? OR 
                               i.id LIKE ? OR i.resultat LIKE ? OR i.date_intervention LIKE ? OR 
                               i.description_action LIKE ? OR i.personne_agent LIKE ? OR 
                               u.id LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ? OR u.profil1 LIKE ? OR 
                               u.profil2 LIKE ? OR 
                               o.id LIKE ? OR o.evaluation_qualite LIKE ? OR o.commentaire_suggestion LIKE ? OR
                               m.id_chef_dst LIKE ? OR m.date_imputation LIKE ?)";
        $whereClauses[] = $searchConditions;

        // Créez le tableau des paramètres pour correspondre au nombre de ? dans la requête
        $params = array_merge($params, array_fill(0, 21, "%$search%"));
        $types .= str_repeat('s', 21); // 's' pour string répété pour chaque paramètre de recherche
    }

    // Ajouter la jointure obligatoire avec la table Imputation pour les utilisateurs autres que chef DST
    $joinImputation = "";
    if (!$isChefDst) {
        $joinImputation = "INNER JOIN Imputation m ON p.id = m.id_panne";
    } else {
        $joinImputation = "LEFT JOIN Imputation m ON p.id = m.id_panne";
    }

    // Construire la clause WHERE finale
    $whereClause = '';
    if (!empty($whereClauses)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Requête pour récupérer les pannes filtrées par recherche avec les clauses ORDER BY
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat, i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.prenom, u.profil1, u.profil2,
               o.evaluation_qualite, o.id AS idObservation, o.date_observation, o.commentaire_suggestion,
               m.id_chef_dst,m.resultat AS resultat_imp, m.instruction, m.date_imputation
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Observation o ON p.id = o.id_panne
        $joinImputation
        $whereClause
        ORDER BY 
            (CASE 
                WHEN m.id_chef_dst IS NULL THEN 1 
                ELSE 2 
            END) ASC,
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Lier les paramètres si nécessaires
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Exécuter la requête
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
// ##################    FIN DE LA FONCTION      ####################

//######################### DEBUT la fonction pour AllPAnnes() ####################################
function allPannes($connexion, $page = 1, $limit = 10, $profil2 = null, $search = '', $isChefDst = false) {
    $offset = ($page - 1) * $limit;

    // Appeler la fonction de recherche
    $pannesFiltrees = rechercherPannes($connexion, $profil2, $search, $isChefDst);

    // Appliquer la pagination
    $totalCount = count($pannesFiltrees);
    $pannes = array_slice($pannesFiltrees, $offset, $limit);

    // Calcul du nombre total de pages
    $totalPages = ceil($totalCount / $limit);

    return ['pannes' => $pannes, 'total_count' => $totalCount, 'total_pages' => $totalPages, 'current_page' => $page];
}
// ###############       FIN DE LA FONCTION      ####################

//######################### DEBUT la fonction pour enregistrer des interventions ####################################
function enregistrerIntervention($connexion, $date_intervention, $description_action, $personne_agent, $date_sys, $resultat, $id_chef_atelier, $id_panne, $intervention_id = null) {
    if ($intervention_id) {
        // Requête de mise à jour pour modifier uniquement les champs spécifiés
        $sql = "
            UPDATE Intervention
            SET date_intervention = ?, description_action = ?, personne_agent = ?
            WHERE id = ?
        ";

        // Préparer la requête
        $stmt = $connexion->prepare($sql);

        // Lier les paramètres
        $stmt->bind_param('sssi', $date_intervention, $description_action, $personne_agent, $intervention_id);
    } else {
        // Requête d'insertion pour ajouter une nouvelle intervention
        $sql = "
            INSERT INTO Intervention (date_intervention, date_sys, description_action, resultat, personne_agent, id_chef_atelier, id_panne)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        // Préparer la requête
        $stmt = $connexion->prepare($sql);

        // Lier les paramètres
        $stmt->bind_param('sssssii', $date_intervention, $date_sys, $description_action, $resultat, $personne_agent, $id_chef_atelier, $id_panne);
    }

    // Exécuter la requête
    if ($stmt->execute()) {
        // Retourner l'ID de l'intervention insérée ou mise à jour
        return $intervention_id ? $intervention_id : $stmt->insert_id;
    } else {
        // En cas d'erreur, retourner false
        return false;
    }
}
//######################### FIN la fonction pour enregistrer des interventions ####################################

//************************************************************************************************************** */

//######################### DEBUT la fonction pour enregistrer des Imputation ####################################
function enregistrerImputation($connexion, $idPanne, $idChefDst, $instruction, $resultat, $dateImputation, $imputationId = null) {
    if ($imputationId != null) {
        // Préparer la requête de mise à jour pour le champ instruction uniquement
        $sql = "UPDATE Imputation SET instruction = ? WHERE id = ?";

        // Préparer la requête
        $stmt = $connexion->prepare($sql);

        // Vérifier si la préparation de la requête a échoué
        if ($stmt === false) {
            throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
        }

        // Lier les paramètres
        $stmt->bind_param('si', $instruction, $imputationId);

        // Exécuter la requête
        if ($stmt->execute() === false) {
            throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
        }

        // Fermer la requête
        $stmt->close();
        return true;
    } 

    // Préparer la requête d'insertion
    $sql = "INSERT INTO Imputation (id_panne, id_chef_dst, instruction, resultat, date_imputation) VALUES (?, ?, ?, ?, ?)";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Vérifier si la préparation de la requête a échoué
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier les paramètres
    $stmt->bind_param('iisss', $idPanne, $idChefDst, $instruction, $resultat, $dateImputation);

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();

    return true; // Retourner vrai si l'insertion a réussi
}
//######################### FIN la fonction pour enregistrer des Imputation ####################################

// ####################### supprimer imputation ######################################################
function supprimerImputation($connexion, $idImputation) {
    // Préparer la requête de suppression
    $sql = "DELETE FROM Imputation WHERE id_imputation = ?";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Vérifier si la préparation de la requête a échoué
    if ($stmt === false) {
        throw new Exception('Failed to prepare statement: ' . $connexion->error);
    }

    // Lier le paramètre
    $stmt->bind_param('i', $idImputation);

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();

    return true; // Retourner vrai si la suppression a réussi
}
// ####################### Fin supprimer imputation ######################################################

// Fonction pour obtenir tous les utilisateurs avec pagination
function allUtilisateurs($connexion) {
    // Requête pour récupérer tous les utilisateurs
    $sql = "
        SELECT id, username, statut, email, telephone, nom, prenom, profil1, profil2
        FROM Utilisateur ORDER BY id DESC;
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Vérifier si la préparation de la requête a échoué
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Exécuter la requête
    $stmt->execute();
    $result = $stmt->get_result();

    // Récupérer tous les utilisateurs dans un tableau associatif
    $utilisateurs = $result->fetch_all(MYSQLI_ASSOC);

    // Fermer la requête
    $stmt->close();

    // Retourner la liste des utilisateurs
    return $utilisateurs;
}
function allPavillons($connexion) {
    $sql = "SELECT DISTINCT campus, pavillon FROM codif_lit_complet ORDER BY campus, pavillon ASC;";
    $stmt = $connexion->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $pavillons = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $pavillons;
}

function enregistrerUtilisateur($connexion, $username, $nom, $prenom, $email, $telephone, $motDePasse, $profil1, $profil2) {
    // Vérifier si l'utilisateur existe déjà par email
    $sqlCheck = "SELECT COUNT(*) AS count FROM Utilisateur WHERE email = ?";
    $stmtCheck = $connexion->prepare($sqlCheck);

    if ($stmtCheck === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier le paramètre email
    $stmtCheck->bind_param('s', $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    // Si l'utilisateur existe déjà, retourner une erreur
    if ($rowCheck['count'] > 0) {
        throw new Exception("L'utilisateur avec cet email existe déjà.");
    }

    // Fermer la requête de vérification
    $stmtCheck->close();

    // Hacher le mot de passe avec SHA-1
    $motDePasseHashe = sha1($motDePasse);

    // Requête d'insertion pour créer un nouvel utilisateur
    $sql = "INSERT INTO Utilisateur (username, nom, prenom, email, telephone, password, profil1, profil2) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connexion->prepare($sql);

    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier les paramètres
    $stmt->bind_param('ssssssss',$username, $nom, $prenom, $email, $telephone, $motDePasseHashe, $profil1, $profil2);

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();
    
    return true;
}
// Fonction pour modifier un Utilisateur
function updateUtilisateur($connexion, $id, $username, $nom, $prenom, $email, $telephone, $profil1, $profil2) {
    // Vérifier si l'email existe déjà pour un autre utilisateur
    $sqlCheck = "SELECT COUNT(*) AS count FROM Utilisateur WHERE email = ? AND id != ?";
    $stmtCheck = $connexion->prepare($sqlCheck);

    if ($stmtCheck === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier les paramètres email et id
    $stmtCheck->bind_param('si', $email, $id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    // Si l'email existe déjà pour un autre utilisateur, retourner une erreur
    if ($rowCheck['count'] > 0) {
        throw new Exception("Un autre utilisateur avec cet email existe déjà.");
    }

    // Fermer la requête de vérification
    $stmtCheck->close();

    // Préparer la requête de mise à jour
    if ($motDePasse !== null && $motDePasse !== '') {
        // Si un nouveau mot de passe est fourni, le hacher avec SHA-1
        $motDePasseHashe = sha1($motDePasse);
        $sql = "UPDATE Utilisateur SET username = ?, nom = ?, prenom = ?, email = ?, telephone = ?, password = ?, profil1 = ?, profil2 = ? WHERE id = ?";
    } else {
        // Si aucun mot de passe n'est fourni, ne pas mettre à jour le champ password
        $sql = "UPDATE Utilisateur SET username = ?, nom = ?, prenom = ?, email = ?, telephone = ?, profil1 = ?, profil2 = ? WHERE id = ?";
    }

    $stmt = $connexion->prepare($sql);

    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier les paramètres en fonction de la présence du mot de passe
    if ($motDePasse !== null && $motDePasse !== '') {
        $stmt->bind_param('ssssssssi', $username, $nom, $prenom, $email, $telephone, $motDePasseHashe, $profil1, $profil2, $id);
    } else {
        $stmt->bind_param('sssssssi', $username, $nom, $prenom, $email, $telephone, $profil1, $profil2, $id);
    }

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();

    return true;
}

function getChambresByCampusPavillon($connexion, $campusPavillon) {
    // Séparer le campus et le pavillon
    list($campus, $pavillon) = explode(' | ', $campusPavillon);
    
    $sql = "SELECT DISTINCT chambre 
            FROM codif_lit_complet 
            WHERE campus = ? AND pavillon = ? 
            ORDER BY chambre ASC";
    
    $stmt = $connexion->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }
    
    $stmt->bind_param("ss", $campus, $pavillon);
    $stmt->execute();
    $result = $stmt->get_result();
    $chambres = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return array_column($chambres, 'chambre');
}

// Compter le nombre total d'utilisateurs
function countUsers($connexion) {
    $query = "SELECT COUNT(*) as total FROM utilisateur";
    $result = mysqli_query($connexion, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Compter le nombre total de pannes
function countTotalPannes($connexion) {
    $query = "SELECT COUNT(*) as total FROM panne";
    $result = mysqli_query($connexion, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Compter les pannes en cours
function countPannesEnCours($connexion) {
    $query = "SELECT COUNT(*) as total FROM intervention WHERE resultat = 'En cours'";
    $result = mysqli_query($connexion, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Compter les pannes résolues
function countPannesResolues($connexion) {
    $query = "SELECT COUNT(*) as total FROM intervention WHERE resultat = 'depanner'";
    $result = mysqli_query($connexion, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Fonction pour générer une couleur aléatoire
function getRandomColor() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

// Fonction pour récupérer les statistiques mensuelles
function getMonthlyStats($connexion, $months = 6) {
    $stats = [];
    $types = [];
    
    // D'abord récupérer tous les types de pannes existants
    $query = "SELECT DISTINCT type_panne FROM panne";
    $result = mysqli_query($connexion, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $types[] = $row['type_panne'];
    }
    
    // Ensuite récupérer les données pour chaque mois
    for ($i = $months - 1; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthName = date('M Y', strtotime($month));
        
        $query = "SELECT type_panne, COUNT(*) as count 
                  FROM panne 
                  WHERE DATE_FORMAT(date_enregistrement, '%Y-%m') = '$month'
                  GROUP BY type_panne";
        $result = mysqli_query($connexion, $query);
        
        $monthData = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $monthData[$row['type_panne']] = $row['count'];
        }
        
        // On s'assure que tous les types sont présents (avec 0 si nécessaire)
        foreach ($types as $type) {
            if (!isset($monthData[$type])) {
                $monthData[$type] = 0;
            }
        }
        
        $stats[$monthName] = $monthData;
    }
    
    return [
        'months' => array_keys($stats),
        'types' => $types,
        'data' => $stats
    ];
}

/**
 * Retourne le nombre total d'articles différents en stock
 * @param mysqli $connexion Connexion MySQL
 * @return int Nombre d'articles
 */
function nombreArticles($connexion) {
    $sql = "SELECT COUNT(*) as total FROM articles";
    $result = mysqli_query($connexion, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Retourne le nombre d'entrées en stock pour le mois en cours
 * @param mysqli $connexion Connexion MySQL
 * @return int Nombre d'entrées ce mois
 */
function entreesMois($connexion) {
    $sql = "SELECT COUNT(*) as total FROM entree_stock 
            WHERE MONTH(date_entree) = MONTH(CURRENT_DATE()) 
            AND YEAR(date_entree) = YEAR(CURRENT_DATE())";
    $result = mysqli_query($connexion, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Retourne le nombre de sorties de stock pour le mois en cours
 * @param mysqli $connexion Connexion MySQL
 * @return int Nombre de sorties ce mois
 */
function sortiesMois($connexion) {
    $sql = "SELECT COUNT(*) as total FROM sortie_stock 
            WHERE MONTH(date_sortie) = MONTH(CURRENT_DATE()) 
            AND YEAR(date_sortie) = YEAR(CURRENT_DATE())";
    $result = mysqli_query($connexion, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
/**
 * Retourne les articles avec leur stock actuel
 * @param mysqli $connexion Connexion MySQL
 * @return array Liste des articles avec leur stock
 */
function getStockArticles($connexion) {
    $sql = "SELECT a.id, a.nom, a.references as reference, 
                   COALESCE(SUM(e.quantite), 0) - COALESCE(SUM(s.quantite), 0) as quantite
            FROM articles a
            LEFT JOIN entree_stock e ON e.article_id = a.id
            LEFT JOIN sortie_stock s ON s.article_id = a.id
            GROUP BY a.id";
    
    $result = mysqli_query($connexion, $sql);
    $stocks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $stocks[] = $row;
    }
    
    return $stocks;
}
/**
 * Retourne les dernières activités de stock
 * @param mysqli $connexion Connexion MySQL
 * @param int $limit Nombre d'activités à retourner (par défaut 10)
 * @return array Liste des activités
 */
function getActivitesRecent($connexion, $limit = 10) {
    $sql = "(SELECT e.id, e.date_entree as date, 'entree' as type, 
                    e.reference, u.nom as utilisateur
             FROM entree_stock e
             JOIN utilisateur u ON e.utilisateur_id = u.id
             ORDER BY e.date_entree DESC LIMIT $limit)
            
            UNION ALL
            
            (SELECT s.id, s.date_sortie as date, 'sortie' as type,
                    s.reference, u.nom as utilisateur
             FROM sortie_stock s
             JOIN utilisateur u ON s.utilisateur_id = u.id
             ORDER BY s.date_sortie DESC LIMIT $limit)
            
            ORDER BY date DESC LIMIT $limit";
    
    $result = mysqli_query($connexion, $sql);
    $activites = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $activites[] = $row;
    }
    
    return $activites;
}

function listeArticles($connexion) {
    $sql = "SELECT a.id, a.description, a.references, a.nom, a.categorie 
            FROM articles a
            ORDER BY a.id DESC";
    
    $result = mysqli_query($connexion, $sql);
    $stocks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $stocks[] = $row;
    }
    
    return $stocks;
}
function getInterventions($connexion) {
    $sql = "SELECT i.id, i.description_action, i.resultat
            FROM intervention i
            ORDER BY i.id DESC";
    
    $result = mysqli_query($connexion, $sql);
    $interventions = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $interventions[] = $row;
    }

    return $interventions;
}

function enregistrerSortie($connexion, $article_id, $intervention_id, $quantite, $date_sortie, $remarque) {
    $sql = "INSERT INTO sortie_stock (article_id, intervention_id, quantite, date_sortie, remarque) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiiss", $article_id, $intervention_id, $quantite, $date_sortie, $remarque);
        return mysqli_stmt_execute($stmt);
    }

    return false;
}
function enregistrerEntree($connexion, $article_id, $quantite, $date_entree, $remarque){
     $sql = "INSERT INTO entree_stock (article_id, quantite, date_entree, remarque) 
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($connexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiss", $article_id, $quantite, $date_entree, $remarque);
        return mysqli_stmt_execute($stmt);
    }

    return false;
}
function enregistrerArticles($connexion, $nom, $categorie, $description, $references) {
    $sql = "INSERT INTO articles (nom, categorie, description, `references`) 
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($connexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $nom, $categorie, $description, $references);
        return mysqli_stmt_execute($stmt);
    }

    return false;
}


function listeSorties($connexion) {
    $sql = "SELECT 
    s.id AS id,
    a.nom AS article,
    a.id AS article_id,
    i.resultat AS intervention,
    a.references,
    s.quantite,
    s.date_sortie,
    s.remarque,
    s.intervention_id
FROM sortie_stock s
JOIN articles a ON s.article_id = a.id
JOIN intervention i ON s.intervention_id = i.id
ORDER BY s.date_sortie DESC;
";
    
    $result = mysqli_query($connexion, $sql);
    $stocks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $stocks[] = $row;
    }
    
    return $stocks;
}
function listeEntrees($connexion) {
    $sql = "SELECT 
    s.id AS id,
    a.nom AS article,
    a.id AS article_id,
    a.references,
    s.quantite,
    s.date_entree,
    s.remarque
FROM entree_stock s
JOIN articles a ON s.article_id = a.id
ORDER BY s.date_entree DESC;
";
    
    $result = mysqli_query($connexion, $sql);
    $stocks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $stocks[] = $row;
    }
    
    return $stocks;
}

function getStatsGlobales($connexion) {
    $stats = [
        'total_entrees' => 0,
        'total_sorties' => 0,
        'total_articles' => 0,
        'total_mouvements' => 0
    ];

    // Total des entrées
    $query = "SELECT SUM(quantite) as total FROM entree_stock";
    $result = $connexion->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_entrees'] = $row['total'] ?? 0;
    }

    // Total des sorties
    $query = "SELECT SUM(quantite) as total FROM sortie_stock";
    $result = $connexion->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_sorties'] = $row['total'] ?? 0;
    }

    // Total des articles
    $query = "SELECT COUNT(*) as total FROM articles";
    $result = $connexion->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_articles'] = $row['total'] ?? 0;
    }

    // Total des mouvements
    $stats['total_mouvements'] = $stats['total_entrees'] + $stats['total_sorties'];

    return $stats;
}

function getStatsArticle($connexion, $article_id) {
    $stats = [
        'stock_initial' => 0,
        'total_entrees' => 0,
        'total_sorties' => 0,
        'stock_actuel' => 0
    ];

    // Stock initial (vous devrez peut-être adapter cette partie selon votre logique)
    $stats['stock_initial'] = 0; // À remplacer par votre logique de stock initial

    // Total des entrées pour cet article
    $query = "SELECT SUM(quantite) as total FROM entree_stock WHERE article_id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['total_entrees'] = $row['total'] ?? 0;
    }

    // Total des sorties pour cet article
    $query = "SELECT SUM(quantite) as total FROM sortie_stock WHERE article_id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['total_sorties'] = $row['total'] ?? 0;
    }

    // Stock actuel
    $stats['stock_actuel'] = $stats['stock_initial'] + $stats['total_entrees'] - $stats['total_sorties'];

    return $stats;
}