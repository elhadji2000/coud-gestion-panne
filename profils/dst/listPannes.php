<?php
session_start();

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
$userId = $_SESSION['id_user'];
$profil2 = $_SESSION['profil2'];
$profil1 = $_SESSION['profil'];

// Vérifiez le profil et définissez les variables appropriées
$isSEM = ($profil2 === 'S.E.M');
$isDst = ($profil2 === 'chef dst');
$dst = ($profil1 === 'dst');

// Récupérer toutes les pannes
$result = allPannes($connexion, 1, 200, null, $dst);
$allPannes = $result['pannes'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #f8fafc;
    }

    .page-header {
        background-color: white;
        padding: 1rem 0;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.5rem;
    }

    .table-container {
        background: white;
        border-radius: 8px;
        padding: 1rem;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 0.75rem;
    }

    .table td {
        padding: 0.75rem;
        vertical-align: middle;
    }

    .badge {
        padding: 0.35rem 0.5rem;
        font-weight: 600;
    }

    .filter-section {
        background-color: white;
        border-radius: 8px;
        padding: 0.6rem;
    }

    .filter-title {
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .filter-section .form-select,
    .filter-section .form-control {
        min-width: 100%;
        box-sizing: border-box;
        height: 38px;
        /* pour uniformiser la hauteur */
        font-size: 0.875rem;
        /* pour être cohérent avec form-select-sm */
    }

    .filter-section label {
        font-weight: 500;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        display: block;
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="page-header">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-tools me-2"></i>Gestion des Pannes
            </h1>
        </div>
    </div>

    <div class="container-fluid mb-4">
        <!-- Filtres -->
        <div class="filter-section">
            <div class="row g-3">
                <div class="col-md-2 col-sm-6">
                    <label>Type</label>
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <?php 
                        $types = array_unique(array_column($allPannes, 'type_panne'));
                        foreach($types as $type): 
                        ?>
                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label>Urgence</label>
                    <select id="filterUrgence" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="Faible">Faible</option>
                        <option value="Moyenne">Moyenne</option>
                        <option value="Élevée">Élevée</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label>État</label>
                    <select id="filterEtat" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="depanner">Résolu</option>
                        <option value="en cours">En cours</option>
                        <option value="Non Traité">Non Traité</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label>Date</label>
                    <input type="date" id="filterDate" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo me-1"></i>Réinit.
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Type</th>
                        <th>Localisation</th>
                        <th>Urgence</th>
                        <th>Date</th>
                        <th>État</th>
                        <th>Details</th>
                        <?php if ($_SESSION['profil'] == 'sem' || $_SESSION['profil'] == 'dst') : ?>
                        <th>Imputation</th>
                        <?php endif; ?>
                        <?php if ($_SESSION['profil'] == 'atelier') : ?>
                        <th>Intervention</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPannes as $panne): 
                        $dateParts = explode('-', $panne['date_enregistrement']);
                        $formattedDate = count($dateParts) === 3 ? $dateParts[2].'/'.$dateParts[1].'/'.$dateParts[0] : $panne['date_enregistrement'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($panne['id']) ?></td>
                        <td><?= htmlspecialchars($panne['type_panne']) ?></td>
                        <td><?= htmlspecialchars(ucfirst(strtolower($panne['localisation']))) ?></td>
                        <td>
                            <?php if ($panne['niveau_urgence'] == 'Faible'): ?>
                            <span class="badge bg-success">Faible</span>
                            <?php elseif($panne['niveau_urgence'] == 'Moyenne'): ?>
                            <span class="badge bg-warning text-dark">Moyenne</span>
                            <?php elseif($panne['niveau_urgence'] == 'Élevée'): ?>
                            <span class="badge bg-danger">Élevée</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($formattedDate) ?></td>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <span class="badge bg-success">Résolu</span>
                            <?php elseif ($panne['resultat'] == 'en cours'): ?>
                            <span class="badge bg-warning text-dark">En cours</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non Traité</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="../vuePanne.php?idPanne=<?= $panne['id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-list"></i> Détails
                            </a>
                        </td>

                        <?php if ($_SESSION['profil'] == 'sem' || $_SESSION['profil'] == 'dst') : ?>
                        <td>
                            <?php if ($isSEM): ?>
                            <?php if ($panne['resultat_imp'] === 'imputer'): ?>
                            <button class="btn btn-success btn-sm btn-action" disabled title="Déjà imputé">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php else: ?>
                            <a href="imputation.php?idPanne=<?= htmlspecialchars($panne['id']) ?>&type=<?= htmlspecialchars($panne['type_panne']) ?>"
                                class="btn btn-warning btn-sm btn-action" title="Imputer">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($isDst): ?>
                            <?php if ($panne['resultat_imp'] === 'imputer'): ?>
                            <span class="badge bg-success">OUI</span>
                            <?php else: ?>
                            <span class="badge bg-danger">NON</span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>

                        <?php if ($_SESSION['profil'] == 'atelier') : ?>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <button class="btn btn-success btn-sm btn-action" disabled title="Déjà dépanné">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php else: ?>
                            <a href="intervention?idp=<?= $panne['id'] ?>&type=<?= $panne['type_panne'] ?>"
                                class="btn btn-primary btn-sm btn-action" title="Intervenir">
                                <i class="fas fa-wrench"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function() {
        // Filtre par type
        $('#filterType').on('change', function() {
            filterTable();
        });

        // Filtre par niveau d'urgence
        $('#filterUrgence').on('change', function() {
            filterTable();
        });

        // Filtre par état
        $('#filterEtat').on('change', function() {
            filterTable();
        });

        // Filtre par date
        $('#filterDate').on('change', function() {
            filterTable();
        });

        // Réinitialisation des filtres
        $('#resetFilters').on('click', function() {
            $('#filterType, #filterUrgence, #filterEtat').val('');
            $('#filterDate').val('');
            $('tbody tr').show();
        });

        function filterTable() {
            const type = $('#filterType').val().toLowerCase();
            const urgence = $('#filterUrgence').val().toLowerCase();
            const etat = $('#filterEtat').val();
            const date = $('#filterDate').val();

            $('tbody tr').each(function() {
                const rowType = $(this).find('td:eq(1)').text().toLowerCase();
                const rowUrgence = $(this).find('td:eq(3) span').text().toLowerCase();
                const rowEtat = $(this).find('td:eq(5) span').text();
                const rowDate = $(this).find('td:eq(4)').text();

                const dateParts = rowDate.split('/');
                const formattedRowDate = dateParts.length === 3 ?
                    `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}` : '';

                const typeMatch = !type || rowType.includes(type);
                const urgenceMatch = !urgence || rowUrgence.includes(urgence);
                let etatMatch = true;

                if (etat === 'depanner') etatMatch = rowEtat === 'Résolu';
                else if (etat === 'en cours') etatMatch = rowEtat === 'En cours';
                else if (etat === 'Non Traité') etatMatch = rowEtat === 'Non Traité';

                const dateMatch = !date || formattedRowDate === date;

                $(this).toggle(typeMatch && urgenceMatch && etatMatch && dateMatch);
            });
        }
    });
    </script>

    <?php include('../../footer.php'); ?>
</body>

</html>