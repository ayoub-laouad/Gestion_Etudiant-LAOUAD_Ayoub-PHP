<?php
// Inclure le header
include 'header_simple.php';

// Fonction pour compter les étudiants par filière
function getEtudiantsParFiliere($lien) {
    $sql = "SELECT f.Abr_Fil, COUNT(e.ID_Etud) as nombre 
            FROM filiere f 
            LEFT JOIN etudiants e ON f.Id_Fil = e.Fil_Etud 
            GROUP BY f.Id_Fil";
    $stmt = $lien->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour compter les étudiants par niveau
function getEtudiantsParNiveau($lien) {
    $sql = "SELECT n.Lib_Niv, COUNT(e.ID_Etud) as nombre 
            FROM niveaux n 
            LEFT JOIN etudiants e ON n.Id_Niv = e.Niv_Etud 
            GROUP BY n.Id_Niv";
    $stmt = $lien->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir les statistiques des QCM
function getStatsQCM($lien) {
    $sql = "SELECT q.qcm_id, q.titre_qcm, f.Lib_Fil, n.Lib_Niv, 
            COUNT(r.resultat_id) as passages,
            IFNULL(AVG(r.note), 0) as moyenne 
            FROM qcm q 
            JOIN filiere f ON q.id_filiere = f.Id_Fil 
            JOIN niveaux n ON q.niveau_qcm = n.Id_Niv 
            LEFT JOIN resultat_qcm r ON q.qcm_id = r.id_qcm 
            GROUP BY q.qcm_id";
    $stmt = $lien->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir les derniers résultats QCM
function getDerniersResultats($lien, $limit = 5) {
    $sql = "SELECT r.resultat_id, e.Nom_Etud, e.Prenom_Etud, q.titre_qcm, r.note, r.date_passage 
            FROM resultat_qcm r 
            JOIN etudiants e ON r.id_etudiant = e.ID_Etud 
            JOIN qcm q ON r.id_qcm = q.qcm_id 
            ORDER BY r.date_passage DESC 
            LIMIT :limit";
    $stmt = $lien->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir les QCM disponibles pour un étudiant
function getQCMPourEtudiant($lien, $filiere, $niveau) {
    $sql = "SELECT q.qcm_id, q.titre_qcm, f.Lib_Fil, n.Lib_Niv, q.date_creation 
            FROM qcm q 
            JOIN filiere f ON q.id_filiere = f.Id_Fil 
            JOIN niveaux n ON q.niveau_qcm = n.Id_Niv 
            WHERE q.id_filiere = :filiere AND q.niveau_qcm = :niveau";
    $stmt = $lien->prepare($sql);
    $stmt->bindParam(':filiere', $filiere, PDO::PARAM_INT);
    $stmt->bindParam(':niveau', $niveau, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir les résultats QCM d'un étudiant
function getResultatsEtudiant($lien, $etudiantId) {
    $sql = "SELECT r.resultat_id, q.titre_qcm, r.note, r.date_passage, r.duree 
            FROM resultat_qcm r 
            JOIN qcm q ON r.id_qcm = q.qcm_id 
            WHERE r.id_etudiant = :etudiantId 
            ORDER BY r.date_passage DESC";
    $stmt = $lien->prepare($sql);
    $stmt->bindParam(':etudiantId', $etudiantId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?php echo LANG_CODE; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LIB_ACCUEIL; ?></title>
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            min-width: 300px;
        }
        
        .card-header {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .stat-box {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            flex: 1;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4a6fdc;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f5f5f5;
        }
        
        .student-welcome {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            margin-top: 20px;
            text-align: center;
        }
        
        .welcome-header {
            font-size: 24px;
            color: #4a6fdc;
            margin-bottom: 15px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .action-button {
            background-color: #4a6fdc;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
        }
        
        .action-button:hover {
            background-color: #3a5dbc;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($etudiant['Role_Etud']) && $etudiant['Role_Etud'] == 'admin'): ?>
            <!-- Tableau de bord administrateur -->
            <h2><?php echo LIB_BIENVENUE; ?>, <?php echo htmlspecialchars($etudiant['Nom_Etud']); ?> - <span style="color: #4a6fdc;"><?php echo LIB_MODE_ADMIN; ?></span></h2>
            
            <div class="dashboard-container">
                <!-- Statistiques des étudiants -->
                <div class="card">
                    <div class="card-header"><?php echo LIB_STATS_ETUDIANTS; ?></div>
                    
                    <?php
                    // Compter le nombre total d'étudiants
                    $stmt = $lien->query("SELECT COUNT(*) as total FROM etudiants");
                    $totalEtudiants = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    // Compter par sexe
                    $stmt = $lien->query("SELECT Sexe_Etud, COUNT(*) as nombre FROM etudiants GROUP BY Sexe_Etud");
                    $resultatSexe = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $hommes = 0;
                    $femmes = 0;
                    
                    foreach($resultatSexe as $row) {
                        if($row['Sexe_Etud'] == 'M') {
                            $hommes = $row['nombre'];
                        } else if($row['Sexe_Etud'] == 'F') {
                            $femmes = $row['nombre'];
                        }
                    }
                    ?>
                    
                    <div class="stats-container">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalEtudiants; ?></div>
                            <div class="stat-label"><?php echo LIB_TOTAL_ETUDIANTS; ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $hommes; ?></div>
                            <div class="stat-label"><?php echo LIB_HOMMES; ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $femmes; ?></div>
                            <div class="stat-label"><?php echo LIB_FEMMES; ?></div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="etudiantsChart"></canvas>
                    </div>
                </div>
                
                <!-- Statistiques des QCM -->
                <div class="card">
                    <div class="card-header"><?php echo LIB_STATS_QCM; ?></div>
                    
                    <?php
                    // Compter le nombre total de QCM
                    $stmt = $lien->query("SELECT COUNT(*) as total FROM qcm");
                    $totalQCM = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    // Compter le nombre total de questions
                    $stmt = $lien->query("SELECT COUNT(*) as total FROM questions");
                    $totalQuestions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    // Compter le nombre total de passages de QCM
                    $stmt = $lien->query("SELECT COUNT(*) as total FROM resultat_qcm");
                    $totalPassages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    ?>
                    
                    <div class="stats-container">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalQCM; ?></div>
                            <div class="stat-label"><?php echo LIB_QCM_CREES; ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalQuestions; ?></div>
                            <div class="stat-label"><?php echo LIB_QUESTIONS; ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalPassages; ?></div>
                            <div class="stat-label"><?php echo LIB_PASSAGES; ?></div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="qcmChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-container">
                <!-- Détails des QCM -->
                <div class="card">
                    <div class="card-header"><?php echo LIB_LISTE_QCM; ?></div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo LIB_TITRE; ?></th>
                                <th><?php echo LIB_FILIERE; ?></th>
                                <th><?php echo LIB_NIVEAU; ?></th>
                                <th><?php echo LIB_PASSAGES; ?></th>
                                <th><?php echo LIB_NOTE_MOYENNE; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statsQCM = getStatsQCM($lien);
                            foreach($statsQCM as $qcm):
                            ?>
                            <tr>
                                <td><?php echo $qcm['qcm_id']; ?></td>
                                <td><?php echo htmlspecialchars($qcm['titre_qcm']); ?></td>
                                <td><?php echo htmlspecialchars($qcm['Lib_Fil']); ?></td>
                                <td><?php echo htmlspecialchars($qcm['Lib_Niv']); ?></td>
                                <td><?php echo $qcm['passages']; ?></td>
                                <td><?php echo number_format($qcm['moyenne'], 2); ?>/20</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Derniers résultats -->
                <div class="card">
                    <div class="card-header"><?php echo LIB_DERNIERS_RESULTATS; ?></div>
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo LIB_ETUDIANT; ?></th>
                                <th>QCM</th>
                                <th><?php echo LIB_NOTE; ?></th>
                                <th><?php echo LIB_DATE; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $derniersResultats = getDerniersResultats($lien);
                            foreach($derniersResultats as $resultat):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resultat['Nom_Etud'] . ' ' . $resultat['Prenom_Etud']); ?></td>
                                <td><?php echo htmlspecialchars($resultat['titre_qcm']); ?></td>
                                <td><?php echo $resultat['note']; ?>/20</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($resultat['date_passage'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <script>
                // Graphique des étudiants par filière
                document.addEventListener('DOMContentLoaded', function() {
                    <?php
                    $etudiantsParFiliere = getEtudiantsParFiliere($lien);
                    $labelsFiliere = [];
                    $dataFiliere = [];
                    
                    foreach($etudiantsParFiliere as $item) {
                        $labelsFiliere[] = $item['Abr_Fil'];
                        $dataFiliere[] = $item['nombre'];
                    }
                    
                    $etudiantsParNiveau = getEtudiantsParNiveau($lien);
                    $labelsNiveau = [];
                    $dataNiveau = [];
                    
                    foreach($etudiantsParNiveau as $item) {
                        $labelsNiveau[] = $item['Lib_Niv'];
                        $dataNiveau[] = $item['nombre'];
                    }
                    ?>
                    
                    // Chart pour étudiants
                    const etudiantsCtx = document.getElementById('etudiantsChart').getContext('2d');
                    new Chart(etudiantsCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($labelsFiliere); ?>,
                            datasets: [{
                                label: '<?php echo LIB_ETUDIANTS_PAR_FILIERE; ?>',
                                data: <?php echo json_encode($dataFiliere); ?>,
                                backgroundColor: 'rgba(74, 111, 220, 0.7)',
                                borderColor: 'rgba(74, 111, 220, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                    
                    // Chart pour QCM
                    const qcmCtx = document.getElementById('qcmChart').getContext('2d');
                    new Chart(qcmCtx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode($labelsNiveau); ?>,
                            datasets: [{
                                label: '<?php echo LIB_ETUDIANTS_PAR_NIVEAU; ?>',
                                data: <?php echo json_encode($dataNiveau); ?>,
                                backgroundColor: [
                                    'rgba(74, 111, 220, 0.7)',
                                    'rgba(54, 162, 235, 0.7)',
                                    'rgba(153, 102, 255, 0.7)'
                                ],
                                borderColor: [
                                    'rgba(74, 111, 220, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(153, 102, 255, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                });
            </script>
            
        <?php else: ?>
            <!-- Accueil étudiant -->
            <div class="student-welcome">
                <h2 class="welcome-header"><?php echo LIB_BIENVENUE; ?>, <?php echo htmlspecialchars($etudiant['Prenom_Etud'] . ' ' . $etudiant['Nom_Etud']); ?>!</h2>
                <p><?php echo LIB_MESSAGE_BIENVENUE; ?></p>
                
                <?php
                // Récupérer les informations de l'étudiant
                $etudiantId = $etudiant['ID_Etud'];
                $filiereId = $etudiant['Fil_Etud'];
                $niveauId = $etudiant['Niv_Etud'];
                
                // Récupérer le nom de la filière et du niveau
                $stmt = $lien->prepare("SELECT f.Lib_Fil, n.Lib_Niv 
                        FROM etudiants e 
                        JOIN filiere f ON e.Fil_Etud = f.Id_Fil 
                        JOIN niveaux n ON e.Niv_Etud = n.Id_Niv 
                        WHERE e.ID_Etud = :etudiantId");
                $stmt->bindParam(':etudiantId', $etudiantId, PDO::PARAM_INT);
                $stmt->execute();
                $infoEtudiant = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Compter les QCM disponibles
                $qcmDisponibles = getQCMPourEtudiant($lien, $filiereId, $niveauId);
                $nombreQCM = count($qcmDisponibles);
                
                // Récupérer les résultats de l'étudiant
                $resultatsEtudiant = getResultatsEtudiant($lien, $etudiantId);
                $nombreResultats = count($resultatsEtudiant);
                
                // Calculer la note moyenne
                $noteTotale = 0;
                foreach($resultatsEtudiant as $resultat) {
                    $noteTotale += $resultat['note'];
                }
                $noteMoyenne = $nombreResultats > 0 ? $noteTotale / $nombreResultats : 0;
                ?>
                
                <div style="margin-top: 20px; text-align: left; background-color: white; padding: 20px; border-radius: 6px;">
                    <p><strong><?php echo LIB_FILIERE; ?>:</strong> <?php echo htmlspecialchars($infoEtudiant['Lib_Fil']); ?></p>
                    <p><strong><?php echo LIB_NIVEAU; ?>:</strong> <?php echo htmlspecialchars($infoEtudiant['Lib_Niv']); ?></p>
                    <p><strong><?php echo LIB_QCM_DISPONIBLES; ?>:</strong> <?php echo $nombreQCM; ?></p>
                    <p><strong><?php echo LIB_QCM_COMPLETES; ?>:</strong> <?php echo $nombreResultats; ?></p>
                    <p><strong><?php echo LIB_NOTE_MOYENNE; ?>:</strong> <?php echo number_format($noteMoyenne, 2); ?>/20</p>
                </div>
            </div>
            
            <?php if($nombreResultats > 0): ?>
            <div class="card" style="margin-top: 20px;">
                <div class="card-header"><?php echo LIB_DERNIERS_RESULTATS; ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>QCM</th>
                            <th><?php echo LIB_NOTE; ?></th>
                            <th><?php echo LIB_DATE; ?></th>
                            <th><?php echo LIB_DUREE; ?> (s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($resultatsEtudiant as $resultat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resultat['titre_qcm']); ?></td>
                            <td><?php echo $resultat['note']; ?>/20</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($resultat['date_passage'])); ?></td>
                            <td><?php echo $resultat['duree']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
    
    <footer class="text-center mt-4">
        <p>&copy; 2025 <?php echo LIB_NOM_PROJET; ?></p>
    </footer>
</body>
</html>