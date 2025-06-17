<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /COUD/panne/');
    exit();
}
unset($_SESSION['classe']);

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
$userId = $_SESSION['id_user'];
$profil2 = $_SESSION['profil2'];
$profil1 = $_SESSION['profil'];

$isSEM = ($profil2 === 'S.E.M');
$isDst = ($profil2 === 'chef dst');
$dst = ($profil1 === 'dst');

$result = allPannes($connexion, 1, 200, $profil2, '', $isDst);
$allPannes = $result['pannes'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Pannes | Tableau de bord</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
    :root {
        --primary: #3498db;
        --secondary: #2c3e50;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #f8fafc;
    }

    .page-header {
        background-color: white;
        padding: 1.5rem 0;
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }

    .btn-add {
        background-color: var(--success);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 500;
        border: none;
    }

    .btn-add:hover {
        background-color: #218838;
        color: white;
    }

    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--secondary);
        border-bottom-width: 1px;
        font-size: 1.03rem;
    }

    .badge-urgence {
        padding: 0.30rem 0.30rem;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-faible {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-moyenne {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-elevee {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-etat {
        padding: 0.30rem 0.30rem;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-resolu {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-encours {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-nonresolu {
        background-color: #f8d7da;
        color: #721c24;
    }

    .action-btns {
        white-space: nowrap;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h1 class="page-title">
                        <i class="fas fa-tools me-2"></i>Gestion des Pannes
                    </h1>
                    <p class="text-muted mb-0">Liste complète des pannes enregistrées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container-fluid mb-5">
        <div class="table-container">
            <div class="table-responsive">
                <table id="pannesTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Type</th>
                            <th>Localisation</th>
                            <th>Urgence</th>
                            <th>Date</th>
                            <th>État</th>
                            <th>Voir</th>
                            <?php if ($_SESSION['profil'] == 'sem' || $_SESSION['profil'] == 'dst') : ?>
                            <th>Imputation</th>
                            <?php endif; ?>
                            <?php if ($_SESSION['profil'] == 'atelier') : ?>
                            <th>Intervention</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allPannes as $panne): ?>
                        <tr>
                            <td><?= htmlspecialchars($panne['id']) ?></td>
                            <td><?= htmlspecialchars($panne['type_panne']) ?></td>
                            <td><?= htmlspecialchars($panne['localisation']) ?></td>
                            <td>
                                <?php if ($panne['niveau_urgence'] == 'Faible'): ?>
                                <span class="badge-urgence badge-faible">Faible</span>
                                <?php elseif($panne['niveau_urgence'] == 'Moyenne'): ?>
                                <span class="badge-urgence badge-moyenne">Moyenne</span>
                                <?php elseif($panne['niveau_urgence'] == 'Élevée'): ?>
                                <span class="badge-urgence badge-elevee">Élevée</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($panne['date_enregistrement']) ?></td>
                            <td>
                                <?php if ($panne['resultat'] == 'depanner'): ?>
                                <span class="badge-etat badge-resolu">Résolu</span>
                                <?php elseif ($panne['resultat'] == 'en cours'): ?>
                                <span class="badge-etat badge-encours">En cours</span>
                                <?php else: ?>
                                <span class="badge-etat badge-nonresolu">Non résolu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Bouton Voir -->
                                <a href="../vuePanne.php?idPanne=<?= $panne['id'] ?>" class="text-info action-voir"
                                    title="Voir détails">
                                    <i class="fas fa-eye"></i>Détails
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialisation de DataTable
        $('#pannesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [
                [0, 'desc']
            ],
            responsive: true
        });


    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>