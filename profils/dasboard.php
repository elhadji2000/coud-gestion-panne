<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
unset($_SESSION['classe']);

include('../traitement/fonction.php');
include('../traitement/requete.php');
//include('../activite.php');

$userId = $_SESSION['id_user'];
$userRole = $_SESSION['profil'];

// Récupération des données pour les graphiques
$typesPannes = [];
$countsPannes = [];
$queryTypes = "SELECT type_panne, COUNT(*) as count FROM panne GROUP BY type_panne";
$resultTypes = mysqli_query($connexion, $queryTypes);
while ($row = mysqli_fetch_assoc($resultTypes)) {
    $typesPannes[] = $row['type_panne'];
    $countsPannes[] = $row['count'];
}

// Données pour le graphique temporel
$months = [];
$monthData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime($month));
    
    $queryMonth = "SELECT type_panne, COUNT(*) as count 
                  FROM panne 
                  WHERE STR_TO_DATE(date_enregistrement, '%d/%m/%Y') LIKE '$month%'
                  GROUP BY type_panne";
    $resultMonth = mysqli_query($connexion, $queryMonth);
    
    $monthData[$month] = [];
    while ($row = mysqli_fetch_assoc($resultMonth)) {
        $monthData[$month][$row['type_panne']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GESCOUD</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --card-spacing: 1.5rem;
            --card-padding: 2rem;
        }
        
        body {
            background-color: #f8f9fc;
        }
        
        .dashboard-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .dashboard-header {
            margin-bottom: 3rem;
            padding-bottom: 1rem;
        }
        
        .dashboard-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2e3a4d;
        }
        
        .dashboard-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--card-spacing);
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: var(--card-padding);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .stat-card.users::before {
            background-color: #4e73df;
        }
        
        .stat-card.pannes::before {
            background-color: #e74a3b;
        }
        
        .stat-card.en-cours::before {
            background-color: #f6c23e;
        }
        
        .stat-card.resolues::before {
            background-color: #1cc88a;
        }
        
        .stat-icon {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        
        .stat-card.users .stat-icon {
            color: #4e73df;
        }
        
        .stat-card.pannes .stat-icon {
            color: #e74a3b;
        }
        
        .stat-card.en-cours .stat-icon {
            color: #f6c23e;
        }
        
        .stat-card.resolues .stat-icon {
            
            color: #1cc88a;
        }
        
        .stat-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2e3a4d;
            margin-bottom: 0.5rem;
        }
        
        .stat-diff {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: var(--card-padding);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 3rem;
        }
        
        .chart-header {
            margin-bottom: 1.5rem;
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2e3a4d;
        }
        
        .chart-wrapper {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include('../head.php'); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Tableau de Bord</h1>
            <p class="dashboard-subtitle">Statistiques globales du système de gestion des pannes</p>
        </div>

        <div class="stats-grid">
            <!-- Carte Utilisateurs -->
            <div class="stat-card users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-title">Utilisateurs Totaux</div>
                <div class="stat-value"><?php echo countUsers($connexion); ?></div>
                <div class="stat-diff">Tous les utilisateurs actifs</div>
            </div>

            <!-- Carte Pannes Totales -->
            <div class="stat-card pannes">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-title">Pannes Totales</div>
                <div class="stat-value"><?php echo countTotalPannes($connexion); ?></div>
                <div class="stat-diff">Depuis le début</div>
            </div>

            <!-- Carte Pannes en Cours -->
            <div class="stat-card en-cours">
                <div class="stat-icon">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-title">Pannes en Cours</div>
                <div class="stat-value"><?php echo countPannesEnCours($connexion); ?></div>
                <div class="stat-diff">À traiter rapidement</div>
            </div>

            <!-- Carte Pannes Résolues -->
            <div class="stat-card resolues">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-title">Pannes Résolues</div>
                <div class="stat-value"><?php echo countPannesResolues($connexion); ?></div>
                <div class="stat-diff">Problèmes corrigés</div>
            </div>
        </div>

        <!-- Graphique des pannes par type -->
        <div class="chart-container">
            <div class="chart-header">
                <h2 class="chart-title">Répartition des pannes par type</h2>
            </div>
            <div class="chart-wrapper">
                <canvas id="typePanneChart"></canvas>
            </div>
        </div>

        <!-- Graphique temporel des pannes déclarées -->
        <div class="chart-container">
            <div class="chart-header">
                <h2 class="chart-title">Évolution des pannes déclarées</h2>
            </div>
            <div class="chart-wrapper">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>
    </div>

    <?php include('../footer.php'); ?>

    <!-- Bootstrap JS et dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Fonction pour générer des couleurs aléatoires
        function getRandomColor() {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            return `rgba(${r}, ${g}, ${b}, 0.7)`;
        }

        // Données pour les graphiques
        const typePanneLabels = <?php echo json_encode($typesPannes); ?>;
        const typePanneData = <?php echo json_encode($countsPannes); ?>;
        
        // Préparation des données pour le graphique temporel
        const timelineLabels = <?php echo json_encode($months); ?>;
        const timelineDatasets = [];
        
        <?php 
        // Générer un dataset pour chaque type de panne
        foreach ($typesPannes as $index => $type) {
            $data = [];
            foreach ($months as $month) {
                $monthKey = date('Y-m', strtotime($month));
                $count = isset($monthData[$monthKey][$type]) ? $monthData[$monthKey][$type] : 0;
                $data[] = $count;
            }
            
            $color = "rgba(".rand(0,255).",".rand(0,255).",".rand(0,255).",0.7)";
            echo "timelineDatasets.push({
                label: '".addslashes($type)."',
                data: ".json_encode($data).",
                backgroundColor: '$color',
                borderColor: '$color',
                borderWidth: 2,
                tension: 0.1,
                fill: false
            });";
        }
        ?>

        // Initialisation des graphiques après le chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des types de pannes (camembert)
            const typePanneCtx = document.getElementById('typePanneChart').getContext('2d');
            const typePanneChart = new Chart(typePanneCtx, {
                type: 'pie',
                data: {
                    labels: typePanneLabels,
                    datasets: [{
                        data: typePanneData,
                        backgroundColor: typePanneLabels.map(() => getRandomColor()),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Graphique temporel (courbe)
            const timelineCtx = document.getElementById('timelineChart').getContext('2d');
            const timelineChart = new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: timelineLabels,
                    datasets: timelineDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de pannes'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Mois'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>