<?php
session_start();
if (!empty($_SESSION['username']) && !empty($_SESSION['mdp'])) {
  session_destroy();
}
include('activite.php');
include('traitement/connect.php');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <title>Connexion | GESCOUD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #eef2ff;
      --secondary: #3f37c9;
      --danger: #f72585;
      --light: #f8f9fa;
      --dark: #212529;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: var(--dark);
      line-height: 1.6;
    }
    
    .login-header {
      background: #3777B0;
      color: white;
      padding: 1.5rem 0;
      text-align: center;
    }
    
    .login-container {
      max-width: 500px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 0rem;
    }
    
    .login-logo img {
      height: 80px;
    }
    
    .login-title {
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: 600;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
      transition: border 0.3s;
    }
    
    .form-control:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px var(--primary-light);
    }
    
    .btn-login {
      width: 100%;
      padding: 0.75rem;
      background: #3777B0;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .btn-login:hover {
      background: var(--secondary);
    }
    
    .login-links {
      margin-top: 1.5rem;
      text-align: center;
    }
    
    .login-links a {
      color: var(--primary);
      text-decoration: none;
      display: block;
      margin-bottom: 0.5rem;
    }
    
    .login-links a:hover {
      text-decoration: underline;
    }
    
    .error-message {
      color: var(--danger);
      background: #ffebee;
      padding: 0.75rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }
    
    @media (max-width: 576px) {
      .login-container {
        margin: 1rem;
        padding: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <div class="login-header">
    <div class="login-logo">
      <img src="/COUD/panne/assets/images/logo.png" alt="COUD Logo">
    </div>
    <h2>Centre des Œuvres Universitaires de Dakar</h2>
  </div>
  <div class="login-container">
    <h3 class="login-title">Connexion à GESCOUD</h3>
    
    <?php if (isset($_GET['error'])): ?>
      <div class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <form id="loginForm" action="/COUD/panne/traitement/connect.php" method="get">
      <div class="form-group">
        <input onkeydown="upperCaseF(this)" name="username_user" id="username" 
               type="text" placeholder="Numéro de carte ou certificat d'inscription" 
               required class="form-control">
      </div>
      
      <div class="form-group">
        <input name="password_user" type="password" id="password" 
               placeholder="Mot de passe" required class="form-control">
      </div>
      
      <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i> Se connecter
      </button>
      
      <div class="login-links">
        <a href="mpo1">Mot de passe oublié ?</a>
        <a href="index">Retour à l'accueil</a>
      </div>
    </form>
  </div>

  <script>
    function upperCaseF(a) {
      setTimeout(function() {
        a.value = a.value.toUpperCase();
      }, 1);
    }
    
    // Animation simple lors du chargement
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');
      form.style.opacity = '0';
      form.style.transform = 'translateY(20px)';
      form.style.transition = 'all 0.4s ease-out';
      
      setTimeout(function() {
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
      }, 100);
    });
  </script>
</body>
</html>