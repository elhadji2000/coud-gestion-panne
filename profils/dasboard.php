<?php
 session_start();

include('../traitement/fonction.php');
include('../traitement/requete.php');

$userId = $_SESSION['id_user'];
$userRole = $_SESSION['profil'];

// Récupération des données pour les statistiques
$totalPannes = countTotalPannes($connexion, $userId, $userRole);
$pannesResolues = countPannesResolues($connexion, $userId, $userRole);
$pannesEnCours = countPannesEnCours($connexion, $userId, $userRole);
$pannesNonResolues = $totalPannes - $pannesResolues;

// Récupération des pannes par type
$typesPannes = [];
$countsPannes = [];
$dataPannes = getTypesPannesAvecCounts($connexion, $userId, $userRole);
$typesPannes = $dataPannes['types'];
$countsPannes = $dataPannes['counts'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GESCOUD - Tableau de Bord</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --info: #36b9cc;
            --dark: #5a5c69;
            --light: #f8f9fc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a4a4a;
        }
        
        .dashboard-container {
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
        }
        
        .dashboard-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .dashboard-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2e3a4d;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-subtitle {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 400;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .stat-card.total {
            border-left-color: var(--primary);
        }
        
        .stat-card.resolved {
            border-left-color: var(--success);
        }
        
        .stat-card.pending {
            border-left-color: var(--warning);
        }
        
        .stat-card.unresolved {
            border-left-color: var(--danger);
        }
        
        .stat-card.type {
            border-left-color: var(--info);
        }
        
        .stat-icon {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            opacity: 0.8;
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            color: rgba(0,0,0,0.1);
        }
        
        .stat-card.total .stat-icon {
            color: rgba(78, 115, 223, 0.2);
        }
        
        .stat-card.resolved .stat-icon {
            color: rgba(28, 200, 138, 0.2);
        }
        
        .stat-card.pending .stat-icon {
            color: rgba(246, 194, 62, 0.2);
        }
        
        .stat-card.unresolved .stat-icon {
            color: rgba(231, 74, 59, 0.2);
        }
        
        .stat-card.type .stat-icon {
            color: rgba(54, 185, 204, 0.2);
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
        
        .progress {
            height: 6px;
            border-radius: 3px;
            margin-top: 10px;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            border-radius: 3px;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
        }
        
        .chart-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2e3a4d;
            margin-bottom: 0;
        }
        
        .chart-wrapper {
            position: relative;
            height: 350px;
            width: 100%;
        }
        
        .type-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-flex;
            align-items: center;
        }
        
        .type-badge i {
            margin-right: 0.3rem;
            font-size: 0.7rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include('../head.php'); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Tableau de Bord des Pannes</h1>
            <p class="dashboard-subtitle">Statistiques et analyses des pannes enregistrées</p>
        </div>

        <!-- Section des statistiques principales -->
        <div class="stats-grid">
            <!-- Carte Pannes Totales -->
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-title">Total des Pannes</div>
                <div class="stat-value"><?php echo $totalPannes; ?></div>
                <div class="stat-diff">Toutes pannes enregistrées</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>

            <!-- Carte Pannes Résolues -->
            <div class="stat-card resolved">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-title">Pannes Résolues</div>
                <div class="stat-value"><?php echo $pannesResolues; ?></div>
                <div class="stat-diff"><?php echo round(($pannesResolues/$totalPannes)*100, 2); ?>% du total</div>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: <?php echo ($totalPannes > 0) ? ($pannesResolues/$totalPannes)*100 : 0; ?>%"></div>
                </div>
            </div>

            <!-- Carte Pannes en Cours -->
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-title">Pannes en Cours</div>
                <div class="stat-value"><?php echo $pannesEnCours; ?></div>
                <div class="stat-diff"><?php echo round(($pannesEnCours/$totalPannes)*100, 2); ?>% du total</div>
                <div class="progress">
                    <div class="progress-bar bg-warning" style="width: <?php echo ($totalPannes > 0) ? ($pannesEnCours/$totalPannes)*100 : 0; ?>%"></div>
                </div>
            </div>

            <!-- Carte Pannes Non Résolues -->
            <div class="stat-card unresolved">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-title">Pannes Non Résolues</div>
                <div class="stat-value"><?php echo $pannesNonResolues; ?></div>
                <div class="stat-diff"><?php echo round(($pannesNonResolues/$totalPannes)*100, 2); ?>% du total</div>
                <div class="progress">
                    <div class="progress-bar bg-danger" style="width: <?php echo ($totalPannes > 0) ? ($pannesNonResolues/$totalPannes)*100 : 0; ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Deuxième ligne de cartes pour les types de pannes -->
        <div class="stats-grid">
            <?php 
            // Afficher une carte pour chaque type de panne
            foreach ($typesPannes as $index => $type) {
                $count = $countsPannes[$index];
                $percentage = round(($count/$totalPannes)*100, 2);
                $color = [
                    'rgba(78, 115, 223, 0.9)',
                    'rgba(28, 200, 138, 0.9)',
                    'rgba(246, 194, 62, 0.9)',
                    'rgba(231, 74, 59, 0.9)',
                    'rgba(54, 185, 204, 0.9)',
                    'rgba(155, 89, 182, 0.9)'
                ][$index % 6];
                
                $icon = [
                    'fas fa-laptop',
                    'fas fa-network-wired',
                    'fas fa-print',
                    'fas fa-server',
                    'fas fa-database',
                    'fas fa-mobile-alt'
                ][$index % 6];
            ?>
            <div class="stat-card type">
                <div class="stat-icon">
                    <i class="<?php echo $icon; ?>"></i>
                </div>
                <div class="stat-title">Pannes <?php echo htmlspecialchars($type); ?></div>
                <div class="stat-value"><?php echo $count; ?></div>
                <div class="stat-diff"><?php echo $percentage; ?>% du total</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $color; ?>"></div>
                </div>
            </div>
            <?php } ?>
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
    </div>

    <?php include('../footer.php'); ?>

    <!-- Bootstrap JS et dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Fonction pour générer des couleurs
        function getColor(index) {
            const colors = [
                'rgba(78, 115, 223, 0.9)',
                'rgba(28, 200, 138, 0.9)',
                'rgba(246, 194, 62, 0.9)',
                'rgba(231, 74, 59, 0.9)',
                'rgba(54, 185, 204, 0.9)',
                'rgba(155, 89, 182, 0.9)'
            ];
            return colors[index % colors.length];
        }

        // Données pour les graphiques
        const typePanneLabels = <?php echo json_encode($typesPannes); ?>;
        const typePanneData = <?php echo json_encode($countsPannes); ?>;
        
        // Initialisation des graphiques après le chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des types de pannes (camembert)
            const typePanneCtx = document.getElementById('typePanneChart').getContext('2d');
            const typePanneChart = new Chart(typePanneCtx, {
                type: 'doughnut',
                data: {
                    labels: typePanneLabels,
                    datasets: [{
                        data: typePanneData,
                        backgroundColor: typePanneLabels.map((_, index) => getColor(index)),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
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
                    },
                    cutout: '70%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>
