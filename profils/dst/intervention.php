<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
unset($_SESSION['classe']);

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

$idp = isset($_GET['idp']) ? (int)$_GET['idp'] : null;
$intervention_id = isset($_GET['intervention_id']) ? (int)$_GET['intervention_id'] : null;

$date_intervention = '';
$description_action = '';
$personne_agent = '';

if ($intervention_id) {
    $sql = "SELECT date_intervention, description_action, personne_agent FROM intervention WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $intervention_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $intervention = $result->fetch_assoc();
    $date_intervention = $intervention['date_intervention'];
    $date_intervention = DateTime::createFromFormat('d/m/Y', $date_intervention)->format('Y-m-d');
    $description_action = $intervention['description_action'];
    $personne_agent = $intervention['personne_agent'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>CAMPUSCOUD - Formulaire d'Intervention</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }
        
        .intervention-card {
            max-width: 700px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--secondary-color);
        }
        
        .form-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .form-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 3px;
            background: var(--secondary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
            font-size: 1.8rem;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-submit {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .back-link {
            color: var(--light-text);
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .back-link:hover {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .required-field:after {
            content: " *";
            color: var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .intervention-card {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    
    <div class="container py-5">
        <div class="intervention-card">
            <h2 class="form-title">
                <i class="fas fa-tools me-2"></i>FORMULAIRE D'INTERVENTION
            </h2>
            
            <form method="POST" action="../../traitement/traitement">
                <div class="mb-4">
                    <label for="agent" class="form-label required-field">
                        <i class="fas fa-user-shield"></i>Agent intervenant
                    </label>
                    <input type="text" name="agent" id="agent" class="form-control" 
                           value="<?php echo htmlspecialchars($personne_agent, ENT_QUOTES, 'UTF-8'); ?>" required
                           placeholder="Nom complet de l'agent intervenant">
                </div>
                
                <div class="mb-4">
                    <label for="date_intervention" class="form-label required-field">
                        <i class="fas fa-calendar-alt"></i>Date d'intervention
                    </label>
                    <input type="date" name="date_intervention" id="date_intervention" class="form-control" 
                           value="<?php echo htmlspecialchars($date_intervention, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="details" class="form-label required-field">
                        <i class="fas fa-clipboard-list"></i>Détails de l'intervention
                    </label>
                    <textarea name="details" id="details" class="form-control" rows="5" required
                              placeholder="Décrivez en détail les actions réalisées..."><?php echo htmlspecialchars($description_action, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <div class="form-text mt-2">Veuillez être aussi précis que possible dans votre description.</div>
                </div>
                
                <input type="hidden" name="idPanne" value="<?php echo htmlspecialchars($idp, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="intervention_id" value="<?php echo htmlspecialchars($intervention_id, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="javascript:history.back()" class="back-link">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-submit text-white">
                        <i class="fas fa-save me-2"></i>Enregistrer l'intervention
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include('../../footer.php'); ?>
</body>

</html>