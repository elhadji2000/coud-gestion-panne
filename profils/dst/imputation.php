<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' &&
    isset($_GET['idPanne'], $_GET['instruction'], $_GET['userId'], $_GET['type_panne'], $_GET['imputation_id'])) {

    $idPanne = (int) $_GET['idPanne'];
    $idChefDst = (int) $_GET['userId'];
    $instruction = trim($_GET['instruction']);
    $type_panne = htmlspecialchars($_GET['type_panne']);
    $dateImputation = date('d/m/Y');
    $resultat = "imputer";

    // Vérifie que imputation_id est défini et numérique
    $imputationId = null;
    if (!empty($_GET['imputation_id']) && is_numeric($_GET['imputation_id'])) {
        $imputationId = (int) $_GET['imputation_id'];
    }

    try {
        $success = enregistrerImputation($connexion, $idPanne, $idChefDst, $instruction, $resultat, $dateImputation, $imputationId);

        if ($success) {
            header("Location: /COUD/panne/profils/dst/listPannes?success=2&type_panne=" . urlencode($type_panne));
            exit();
        } else {
            throw new Exception("Échec de l'enregistrement de l'imputation.");
        }
    } catch (Exception $e) {
        // Tu peux enregistrer l’erreur dans un log ici si besoin
        header("Location: /COUD/panne/profils/dst/imputation?error=" . urlencode($e->getMessage()));
        exit();
    }
} 

$idPanne = isset($_GET['idPanne']) ? (int)$_GET['idPanne'] : null;
$type_panne = isset($_GET['type']) ? $_GET['type'] : null;
$userId = $_SESSION['id_user'];
$imputation_id = $_GET['imputation_id'] ??  null;
/* $imputation_id = (isset($_GET['imputation_id']) && is_numeric($_GET['imputation_id']) && $_GET['imputation_id'] > 0)
    ? (int)$_GET['imputation_id']
    : null; */
$instruction = '';

if ($imputation_id) {
    $sql = "SELECT instruction FROM imputation WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $imputation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $imputation = $result->fetch_assoc();
    $instruction = $imputation['instruction'];
}

$clean_instruction = trim($instruction);
$clean_instruction = preg_replace('/\s+/', ' ', $clean_instruction);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>CAMPUSCOUD - Imputation de Panne</title>
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
        
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .form-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--secondary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            font-size: 18px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-outline-secondary {
            color: var(--light-text);
            border-color: var(--light-text);
        }
        
        .btn-outline-secondary:hover {
            color: white;
            background-color: var(--light-text);
        }
        
        .back-link {
            color: var(--light-text);
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    
    <div class="container mt-5 mb-5">
        <div class="form-container">
            <h2 class="form-title">
                <i class="fas fa-tasks me-2"></i>IMPUTATION DE PANNE
            </h2>
            
            <form method="GET" action="imputation.php">
                <div class="mb-4">
                    <label for="instruction" class="form-label">
                        <i class="fas fa-comment-dots me-2"></i>Instructions pour l'atelier
                    </label>
                    <textarea name="instruction" id="instruction" class="form-control" required 
                              placeholder="Veuillez saisir les instructions détaillées pour le chef d'atelier..."><?php echo htmlspecialchars($clean_instruction, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <div class="form-text">Décrivez précisément la nature du problème et les actions attendues.</div>
                </div>
                
                <input type="hidden" name="idPanne" value="<?php echo htmlspecialchars($idPanne, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="type_panne" value="<?php echo htmlspecialchars($type_panne, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="userId" value="<?php echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="imputation_id" value="<?php echo htmlspecialchars($imputation_id, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="javascript:history.back()" class="back-link">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer l'imputation
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