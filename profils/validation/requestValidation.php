<?php
// Verifier la session si elle est actif, sinon on redirige vers la racine
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
// Verifier si la session stock toujours la valeur du niveau de la classe, sinon on l'initialise
if (isset($_SESSION['classe'])) {
    $classe = $_SESSION['classe'];
} else {
    $classe = "";
}
include('../../traitement/fonction.php');

if (isset($_POST['numEtudiant'])) {
    $num_etu = $_POST['numEtudiant'];
    //Appel de la fonction de verification si l'etudiant a deja choisi un lit
    $data = getOneByAffectation($num_etu);
    if (mysqli_num_rows($data) > 0) {
        while ($row = mysqli_fetch_array($data)) {
            $array = $row;
        }
        if ($array['migration_status'] == 'Non migré') {
            $queryString = http_build_query(['data' => $array]);
            header("location: validation.php?" . $queryString);
            exit();
        } else {
            header('Location: validation.php?erreurValider=Etudiant déja valider !!!');
        }
    } else {
        header("location: validation.php?erreurNonTrouver=Aucun résultat trouvé !!!");
    }
    // Libérer la mémoire du résultat
    mysqli_free_result($data);
}

if (isset($_POST['valide'])) {
    try {
        $id_aff = $_POST['valide'];
        $user = $_SESSION['username'];
        // Appel de la fonction d'enregistrement de la validation du lit
        $requete = setValidation($id_aff, $user);
        if ($requete == 1) {
            header('Location: validation.php?successValider=Etudiant valider avec success !!!');
        }
    } catch (mysqli_sql_exception $e) {
        header('Location: validation.php?erreurValider=Etudiant déja valider !!!');
    }
}
