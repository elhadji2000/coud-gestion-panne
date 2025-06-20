<?php
include('fonction.php');
$error = "";

if (!empty($_GET['username_user']) && !empty($_GET['password_user'])) {
    $username = $_GET['username_user'];
    $password = $_GET['password_user'];
    $row = login($username, $password);
    if ($row) {
        session_start();
        $_SESSION['id_user'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['mdp'] = $row['password'];
        $_SESSION['type_mdp'] = $row['type_mdp'];
        $_SESSION['profil'] = $row['profil1'];
        $_SESSION['profil2'] = $row['profil2'];
        $_SESSION['prenom'] = $row['prenom'];
        $_SESSION['nom'] = $row['nom'];
        if ($row['profil1'] == 'quota') {
            header('Location: /COUD/panne/profils/dst/listPannes.php');
            exit();
        } 
        else if ($row['profil1'] == 'residence' || $row['profil1'] == 'service') {
            header('Location: /COUD/panne/profils/dasboard.php');
            exit();
        }else if ($row['profil1'] == 'dst') {
            header('Location: /COUD/panne/profils/dasboard.php');
            exit();
        }else if ($row['profil1'] == 'sem') {
            header('Location: /COUD/panne/profils/dasboard.php');
            exit();
        }else if ($row['profil1'] == 'atelier') {
            header('Location: /COUD/panne/profils/dasboard.php');
            exit();
        }else if ($row['profil1'] == 'admin') {
            header('Location: /COUD/panne/profils/dasboard.php');
            exit();
        }else if ($row['profil1'] == 'section') {
            header('Location: /COUD/panne/profils/dasboard.php');
            exit();
        }  else {
            header('Location: /COUD/panne');
            exit();
            }
        }
     else {
        $error_message = 'Incorrect username or password!';
        $error = "Nom d'utilisateur ou mot de passe Incorrect";
        header('Location: /COUD/panne/?error=' . $error);
        exit();
    }
}