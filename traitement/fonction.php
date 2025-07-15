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

    $sql = "
        SELECT 
            p.id, 
            p.type_panne, 
            p.date_enregistrement, 
            p.description, 
            p.localisation, 
            p.niveau_urgence,
            u.nom, 
            u.prenom,
            u.profil1, 
            u.profil2,

            -- Dernière intervention
            (
                SELECT resultat 
                FROM Intervention 
                WHERE id_panne = p.id 
                ORDER BY date_intervention DESC 
                LIMIT 1
            ) AS dernier_resultat,
            (
                SELECT id 
                FROM Intervention 
                WHERE id_panne = p.id 
                ORDER BY date_intervention DESC 
                LIMIT 1
            ) AS idIntervention,

            -- Dernière observation
            (
                SELECT id 
                FROM Observation 
                WHERE id_panne = p.id 
                ORDER BY date_observation DESC 
                LIMIT 1
            ) AS idObservation,

            -- Dernière imputation
            (
                SELECT instruction 
                FROM Imputation 
                WHERE id_panne = p.id 
                ORDER BY date_imputation DESC 
                LIMIT 1
            ) AS instruction

        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        WHERE p.id_chef_residence = ?

        ORDER BY 
            -- Priorité d'affichage
            CASE 
                WHEN (
                    SELECT COUNT(*) FROM Intervention WHERE id_panne = p.id
                ) = 0 THEN 1
                WHEN (
                    SELECT resultat 
                    FROM Intervention 
                    WHERE id_panne = p.id 
                    ORDER BY date_intervention DESC 
                    LIMIT 1
                ) = 'en cours' THEN 2
                ELSE 3
            END ASC,

            -- Urgence
            CASE 
                WHEN p.niveau_urgence = 'Élevée' THEN 1
                WHEN p.niveau_urgence = 'Moyenne' THEN 2
                WHEN p.niveau_urgence = 'Faible' THEN 3
                ELSE 4
            END ASC,

            -- Date récente
            p.date_enregistrement DESC

        LIMIT ? OFFSET ?
    ";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('iii', $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Compte total
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

    return [
        'pannes' => $pannes,
        'total_count' => $totalCount
    ];
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
            -- Informations panne
            p.id AS panne_id, 
            p.type_panne, 
            p.date_enregistrement, 
            p.description AS panne_description, 
            p.localisation, 
            p.niveau_urgence,

            -- Chef de résidence
            u.nom AS declarant, 
            u.profil1 AS chef_role,

            -- Intervention
            i.id AS intervention_id, 
            i.date_intervention, 
            i.description_action, 
            i.resultat, 

            -- Liste des agents affectés à l’intervention
            GROUP_CONCAT(DISTINCT CONCAT(a.prenom, ' ', a.nom) SEPARATOR ', ') AS agents_intervention,

            -- Observation (liée à l’intervention)
            o.id AS observation_id, 
            o.evaluation_qualite,
            o.date_observation, 
            o.commentaire_suggestion,

            -- Imputation
            m.id AS imputation_id,
            m.id_chef_dst,
            m.instruction,
            m.date_imputation

        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON i.id = o.id_intervention
        LEFT JOIN Imputation m ON p.id = m.id_panne
        LEFT JOIN intervention_agent ia ON i.id = ia.intervention_id
        LEFT JOIN Agent a ON ia.agent_id = a.id

        WHERE p.id = ?
        GROUP BY 
            p.id, i.id, o.id, m.id, u.id
        ORDER BY i.date_intervention ASC, o.date_observation ASC
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
function allPannes($connexion, $page = 1, $limit = 200, $profil2 = null, $isChefDst=false) {
    $offset = ($page - 1) * $limit;

    // Clause WHERE
    $whereClauses = [];
    $params = [];
    $types = '';

    if ($profil2 !== null) {
        $whereClauses[] = "p.type_panne = ?";
        $params[] = $profil2;
        $types .= 's';
    }

    // JOIN conditionnel sur imputation
    $joinImputation = $isChefDst
        ? "LEFT JOIN Imputation m ON p.id = m.id_panne"
        : "INNER JOIN Imputation m ON p.id = m.id_panne";

    $whereClause = '';
    if (!empty($whereClauses)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Requête principale (une ligne par panne)
    $sql = "
        SELECT 
            p.id,
            p.type_panne,
            p.date_enregistrement,
            p.description,
            p.localisation,
            p.niveau_urgence,
            u.nom,
            u.prenom,
            u.profil1,
            u.profil2,

            -- Dernière intervention
            (
                SELECT resultat 
                FROM Intervention 
                WHERE id_panne = p.id 
                ORDER BY date_intervention DESC 
                LIMIT 1
            ) AS resultat,
            (
                SELECT id 
                FROM Intervention 
                WHERE id_panne = p.id 
                ORDER BY date_intervention DESC 
                LIMIT 1
            ) AS idIntervention,

            -- Dernière observation
            (
                SELECT id 
                FROM Observation 
                WHERE id_panne = p.id 
                ORDER BY date_observation DESC 
                LIMIT 1
            ) AS idObservation,

            -- Imputation (si existe)
            m.instruction,
            m.id_chef_dst,
            m.resultat AS resultat_imp,
            m.date_imputation

        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        $joinImputation
        $whereClause
        ORDER BY 
            -- Intervention status (null = non traité en premier)
            CASE 
                WHEN (
                    SELECT resultat 
                    FROM Intervention 
                    WHERE id_panne = p.id 
                    ORDER BY date_intervention DESC 
                    LIMIT 1
                ) IS NULL THEN 1
                WHEN (
                    SELECT resultat 
                    FROM Intervention 
                    WHERE id_panne = p.id 
                    ORDER BY date_intervention DESC 
                    LIMIT 1
                ) = 'en cours' THEN 2
                ELSE 3
            END ASC,

            -- Urgence
            CASE 
                WHEN p.niveau_urgence = 'Élevée' THEN 1
                WHEN p.niveau_urgence = 'Moyenne' THEN 2
                WHEN p.niveau_urgence = 'Faible' THEN 3
                ELSE 4
            END ASC,

            -- Date récente
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";

    // Lier les paramètres
    $stmt = $connexion->prepare($sql);
    if (!empty($params)) {
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Total count (sans pagination)
    $sqlCount = "SELECT COUNT(*) as total_count FROM Panne p $joinImputation $whereClause";
    $stmtCount = $connexion->prepare($sqlCount);
    if (!empty($params)) {
        $countParams = array_slice($params, 0, count($params) - 2); // remove limit, offset
        $countTypes = substr($types, 0, -2);
        $stmtCount->bind_param($countTypes, ...$countParams);
    }
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];
    $totalPages = ceil($totalCount / $limit);

    return [
        'pannes' => $pannes,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'current_page' => $page
    ];
}

// ###############       FIN DE LA FONCTION      ####################

//######################### DEBUT la fonction pour enregistrer des interventions ####################################
function enregistrerIntervention($connexion, $date_intervention, $description_action, $personne_agent, $date_sys, $resultat, $id_chef_atelier, $id_panne) {
    // Requête d'insertion pour ajouter une nouvelle intervention
    $sql = "
        INSERT INTO Intervention (date_intervention, date_sys, description_action, resultat, personne_agent, id_chef_atelier, id_panne)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    if (!$stmt) {
        error_log("Erreur de préparation : " . $connexion->error);
        return false;
    }

    // Lier les paramètres
    $stmt->bind_param('sssssii', $date_intervention, $date_sys, $description_action, $resultat, $personne_agent, $id_chef_atelier, $id_panne);

    // Exécuter la requête
    if ($stmt->execute()) {
        return $stmt->insert_id; // Retourne l'ID de l'intervention créée
    } else {
        error_log("Erreur d'exécution : " . $stmt->error);
        return false;
    }
}


function updateIntervention($connexion, $date_intervention, $description_action, $personne_agent, $date_sys, $resultat, $id_chef_atelier, $id_panne, $intervention_id) {
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
    // Vérification préalable des types attendus
    if (!is_int($idPanne) || !is_int($idChefDst)) {
        throw new InvalidArgumentException("idPanne et idChefDst doivent être des entiers.");
    }

    if (!is_string($instruction) || !is_string($resultat) || !is_string($dateImputation)) {
        throw new InvalidArgumentException("instruction, resultat et dateImputation doivent être des chaînes de caractères.");
    }

    // Si un ID d'imputation est fourni et valide (entier > 0), on met à jour uniquement l'instruction
    if (!empty($imputationId) && is_numeric($imputationId) && $imputationId > 0) {
        $imputationId = (int)$imputationId;

        $sql = "UPDATE Imputation SET instruction = ? WHERE id = ?";

        $stmt = $connexion->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
        }

        $stmt->bind_param('si', $instruction, $imputationId);

        if ($stmt->execute() === false) {
            throw new Exception('Échec de l\'exécution de la requête UPDATE : ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    // Sinon, on procède à une insertion
    $sql = "INSERT INTO Imputation (id_panne, id_chef_dst, instruction, resultat, date_imputation) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $connexion->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête INSERT : ' . $connexion->error);
    }

    $stmt->bind_param('iisss', $idPanne, $idChefDst, $instruction, $resultat, $dateImputation);

    if ($stmt->execute() === false) {
        throw new Exception('Échec de l\'exécution de la requête INSERT : ' . $stmt->error);
    }

    $stmt->close();
    return true;
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
        SELECT *
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
function allServices($connexion) {
    $sql = "SELECT DISTINCT nom, libelle FROM departement ORDER BY nom ASC;";
    $stmt = $connexion->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $services = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $services;
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
function updateUtilisateur($connexion, $id, $username, $nom, $prenom, $email, $telephone, $profil1, $profil2, $motDePasse = null) {
    // Vérifier si l'email existe déjà pour un autre utilisateur
    $sqlCheck = "SELECT COUNT(*) AS count FROM Utilisateur WHERE email = ? AND id != ?";
    $stmtCheck = $connexion->prepare($sqlCheck);

    if ($stmtCheck === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    $stmtCheck->bind_param('si', $email, $id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if ($rowCheck['count'] > 0) {
        throw new Exception("Un autre utilisateur avec cet email existe déjà.");
    }

    $stmtCheck->close();

    // S'assurer que profil1 est bien renseigné
    if (empty($profil1)) {
        throw new Exception("Le champ profil1 ne peut pas être vide.");
    }

    // Préparer la requête de mise à jour
    if (!empty($motDePasse)) {
        $motDePasseHashe = sha1($motDePasse);
        $sql = "UPDATE Utilisateur 
                SET username = ?, nom = ?, prenom = ?, email = ?, telephone = ?, password = ?, profil1 = ?, profil2 = ?
                WHERE id = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('ssssssssi', $username, $nom, $prenom, $email, $telephone, $motDePasseHashe, $profil1, $profil2, $id);
    } else {
        $sql = "UPDATE Utilisateur 
                SET username = ?, nom = ?, prenom = ?, email = ?, telephone = ?, profil1 = ?, profil2 = ?
                WHERE id = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('sssssssi', $username, $nom, $prenom, $email, $telephone, $profil1, $profil2, $id);
    }

    if ($stmt === false) {
        throw new Exception('Erreur préparation requête : ' . $connexion->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Erreur exécution requête : ' . $stmt->error);
    }

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

// 1. Compter le nombre total de pannes (avec filtre si pas admin ou dst)
function countTotalPannes($connexion, $id_user, $profil) {
    if ($profil === 'admin' || $profil === 'dst' || $profil === 'atelier') {
        $query = "SELECT COUNT(*) as total FROM panne";
        $stmt = $connexion->prepare($query);
    }
    elseif($profil === 'section') {
        $query = "SELECT COUNT(*) as total FROM panne WHERE type_panne = ?";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("s", $_SESSION['profil2']);
    }
    else {
        $query = "SELECT COUNT(*) as total FROM panne WHERE id_chef_residence = ?";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_user);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

// 2. Compter les pannes en cours via jointure intervention → panne → utilisateur
function countPannesEnCours($connexion, $id_user, $profil) {
    if ($profil === 'admin' || $profil === 'dst' || $profil === 'atelier') {
        $query = "SELECT COUNT(*) as total FROM intervention WHERE resultat = 'en cours'";
        $stmt = $connexion->prepare($query);
    }
    elseif($profil === 'section') {
        $query = "
            SELECT COUNT(*) as total 
            FROM intervention i
            JOIN panne p ON i.id_panne = p.id
            WHERE i.resultat = 'en cours' AND p.type_panne = ?
        ";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("s", $_SESSION['profil2']);
    }
    else {
        $query = "
            SELECT COUNT(*) as total 
            FROM intervention i
            JOIN panne p ON i.id_panne = p.id
            WHERE i.resultat = 'en cours' AND p.id_chef_residence = ?
        ";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_user);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

// 3. Compter les pannes résolues avec même logique
function countPannesResolues($connexion, $id_user, $profil) {
    if ($profil === 'admin' || $profil === 'dst' || $profil === 'atelier') {
        $query = "SELECT COUNT(*) as total FROM intervention WHERE resultat = 'depanner'";
        $stmt = $connexion->prepare($query);
    }
    elseif($profil === 'section') {
        $query = "
            SELECT COUNT(*) as total 
            FROM intervention i
            JOIN panne p ON i.id_panne = p.id
            WHERE i.resultat = 'depanner' AND p.type_panne = ?
        ";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("s", $_SESSION['profil2']);
    }
    else {
        $query = "
            SELECT COUNT(*) as total 
            FROM intervention i
            JOIN panne p ON i.id_panne = p.id
            WHERE i.resultat = 'depanner' AND p.id_chef_residence = ?
        ";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_user);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}
function getTypesPannesAvecCounts($connexion, $id_user, $profil) {
    if ($profil === 'admin' || $profil === 'dst' || $profil === 'atelier') {
        $query = "SELECT type_panne, COUNT(*) as count FROM panne GROUP BY type_panne";
        $stmt = $connexion->prepare($query);
    }
    elseif($profil === 'section') {
        $query = "SELECT type_panne, COUNT(*) as count FROM panne WHERE type_panne = ? GROUP BY type_panne";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("s", $_SESSION['profil2']);
    }
    else {
        $query = "SELECT type_panne, COUNT(*) as count FROM panne WHERE id_chef_residence = ? GROUP BY type_panne";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_user);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $typesPannes = [];
    $countsPannes = [];

    while ($row = $result->fetch_assoc()) {
        $typesPannes[] = $row['type_panne'];
        $countsPannes[] = $row['count'];
    }

    return [
        'types' => $typesPannes,
        'counts' => $countsPannes
    ];
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

function enregistrerSortie($connexion, $article_id, $quantite, $date_sortie, $remarque) {
    $sql = "INSERT INTO sortie_stock (article_id, quantite, date_sortie, remarque) 
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($connexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiss", $article_id, $quantite, $date_sortie, $remarque);
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
        a.references,
        a.id AS article_id,
        a.nom AS article,
        SUM(s.quantite) AS total_quantite,
        MAX(s.date_sortie) AS derniere_sortie,
        GROUP_CONCAT(s.remarque SEPARATOR ' | ') AS remarques
    FROM sortie_stock s
    JOIN articles a ON s.article_id = a.id
    GROUP BY a.references, a.nom
    ORDER BY derniere_sortie DESC;
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
        a.references,
        a.nom AS article,
        a.id AS article_id,
        SUM(s.quantite) AS total_quantite,
        MAX(s.date_entree) AS derniere_entree,
        GROUP_CONCAT(s.remarque SEPARATOR ' | ') AS remarques
    FROM entree_stock s
    JOIN articles a ON s.article_id = a.id
    GROUP BY a.references, a.nom
    ORDER BY derniere_entree DESC;
    ";

    $result = $connexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}
function getEntreesParReference($connexion, $reference) {
    $sql = "SELECT 
                s.id,
                s.date_entree,
                s.quantite,
                s.remarque,
                a.nom AS article
            FROM entree_stock s
            JOIN articles a ON s.article_id = a.id
            WHERE a.references = ?
            ORDER BY s.date_entree DESC";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function getSortiesParReference($connexion, $reference) {
    $sql = "SELECT 
                s.id,
                s.date_sortie,
                s.quantite,
                s.remarque,
                a.nom AS article,
                a.references,
                i.id AS intervention_id,
                i.date_intervention,
                i.type_intervention,
                i.description_action
            FROM sortie_stock s
            JOIN articles a ON s.article_id = a.id
            LEFT JOIN intervention i ON s.intervention_id = i.id
            WHERE a.references = ?
            ORDER BY s.date_sortie DESC";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

// Fonction pour récupérer un article par son ID
function getArticleById($connexion, $id) {
    $query = "SELECT * FROM articles WHERE id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fonction pour modifier un article
function modifierArticle($connexion, $id, $nom, $categorie, $description, $reference) {
    $query = "UPDATE articles SET nom = ?, categorie = ?, description = ?, `references` = ? WHERE id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("ssssi", $nom, $categorie, $description, $reference, $id);
    return $stmt->execute();
}
function supprimerArticle($connexion, $id) {
    // Vérifier d'abord si l'article existe
    $check = "SELECT id FROM articles WHERE id = ?";
    $stmt = $connexion->prepare($check);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        return false; // Article non trouvé
    }
    
    // Si l'article existe, procéder à la suppression
    $query = "DELETE FROM articles WHERE id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
function supprimerSortie($connexion, $id) {
    // Vérifier d'abord si l'article existe
    $check = "SELECT id FROM sortie_stock WHERE id = ?";
    $stmt = $connexion->prepare($check);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        return false; // Article non trouvé
    }
    
    // Si l'article existe, procéder à la suppression
    $query = "DELETE FROM sortie_stock WHERE id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
function supprimerEntree($connexion, $id) {
    // Vérifier d'abord si l'article existe
    $check = "SELECT id FROM entree_stock WHERE id = ?";
    $stmt = $connexion->prepare($check);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        return false; // Article non trouvé
    }
    
    // Si l'article existe, procéder à la suppression
    $query = "DELETE FROM entree_stock WHERE id = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
// Fonction pour récupérer une sortie par son ID
function getSortieById($connexion, $id) {
    $sql = "SELECT s.*, a.nom AS nom_article, a.references AS reference_article, a.description AS description_article,
                   i.description_action AS description_intervention, i.resultat AS resultat_intervention
            FROM sortie_stock s
            JOIN articles a ON s.article_id = a.id
            JOIN intervention i ON s.intervention_id = i.id
            WHERE s.id = ?";

    $stmt = mysqli_prepare($connexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id); // 'i' pour entier
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            return mysqli_fetch_assoc($result);
        }
    }
    return null; // ou false si tu veux gérer une erreur
}
// Fonction pour modifier une sortie
function modifierSortie($connexion, $id, $article_id, $quantite, $date_sortie, $remarque) {
    $sql = "UPDATE sortie_stock SET 
                article_id = ?, 
                quantite = ?, 
                date_sortie = ?, 
                remarque = ?,
                updated_at = NOW()
            WHERE id = ?";

    $stmt = mysqli_prepare($connexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'iissi', $article_id, $quantite, $date_sortie, $remarque, $id);
        return mysqli_stmt_execute($stmt);
    } else {
        error_log("Erreur de préparation de la requête : " . mysqli_error($connexion));
        return false;
    }
}
function getEntreeById($connexion, $id) {
    $query = "SELECT e.*, a.nom as nom_article, a.references as reference_article, a.description as description_article 
              FROM entree_stock e 
              JOIN articles a ON e.article_id = a.id 
              WHERE e.id = ?";
    $stmt = mysqli_prepare($connexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function modifierEntree($connexion, $id, $article_id, $quantite, $date_entree, $remarque) {
    $query = "UPDATE entree_stock 
              SET article_id = ?, quantite = ?, date_entree = ?, remarque = ?
              WHERE id = ?";
    $stmt = mysqli_prepare($connexion, $query);
    mysqli_stmt_bind_param($stmt, "iissi", $article_id, $quantite, $date_entree, $remarque, $id);
    return mysqli_stmt_execute($stmt);
}

function verifyCurrentPassword($username, $password, $connexion) {
    $query = "SELECT password FROM utilisateur WHERE username = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        return sha1($password) === $user['password']; // Utilisé si hashé avec sha1()
    }

    return false;
}
function updatePassword($username, $new_password, $connexion) {
    // Hashage avec SHA-1 (⚠️ non recommandé pour production)
    $hashed_password = sha1($new_password);

    // Mise à jour du mot de passe et du type_mdp
    $query = "UPDATE utilisateur SET password = ?, type_mdp = ? WHERE username = ?";
    $stmt = $connexion->prepare($query);
    $type_mdp = 'updated';

    $stmt->bind_param('sss', $hashed_password, $type_mdp, $username);

    return $stmt->execute();
}

function notifierUrgence($connexion, $type_panne, $description, $localisation) {
    // Récupérer les utilisateurs à notifier (ex. responsables techniques, sécurité, etc.)
    $query = "SELECT nom, prenom, email, telephone, canal_notif FROM utilisateur WHERE recevoir_alerte = 1";
    $stmt = $connexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $message = "🚨 ALERTE URGENCE COUD 🚨\n"
        . "Type : $type_panne\n"
        . "Localisation : $localisation\n"
        . "Description : $description";

    while ($user = $result->fetch_assoc()) {
        $canal = strtolower($user['canal_notif']); // 'email', 'sms', 'whatsapp'
        $contact = $user['telephone'];
        $email = $user['email'];

        switch ($canal) {
            case 'email':
                envoyerEmail($email, 'Alerte Urgence COUD', $message);
                break;
            case 'sms':
                envoyerSMS($contact, $message); // nécessite une API
                break;
            case 'whatsapp':
                envoyerWhatsapp($contact, $message); // nécessite une API ou WhatsApp Business
                break;
        }
    }
}

function envoyerEmail($to, $subject, $message) {
    mail($to, $subject, $message); // ou mieux, PHPMailer
}
function listeAgents($connexion, $section = null) {
    $agents = [];

    if ($section) {
        $sql = "SELECT * FROM agent WHERE section = ?";
        $stmt = mysqli_prepare($connexion, $sql);
        mysqli_stmt_bind_param($stmt, "s", $section);
    } else {
        $sql = "SELECT * FROM agent ORDER BY section, nom";
        $stmt = mysqli_prepare($connexion, $sql);
    }

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $agents[] = $row;
        }

        mysqli_stmt_close($stmt);
    }

    return $agents;
}
function getAgentsParIntervention($connexion, $intervention_id) {
    $agents = [];

    $sql = "SELECT a.nom, a.prenom 
            FROM agent a
            INNER JOIN intervention_agent ia ON a.id = ia.agent_id
            WHERE ia.intervention_id = ?";
    
    $stmt = mysqli_prepare($connexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $intervention_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $agents[] = $row['prenom'] . ' ' . $row['nom'];
    }

    mysqli_stmt_close($stmt);
    return $agents;
}

