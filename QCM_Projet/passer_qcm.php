<?php
// Inclure le header
include 'header_simple.php';

// Récupérer filière et niveau de l'étudiant
$stmt = $lien->prepare("
  SELECT Fil_Etud, Niv_Etud 
    FROM etudiants 
   WHERE ID_Etud = ?");
$stmt->execute([$_SESSION['ID_Etud']]);
list($fil, $niv) = $stmt->fetch(PDO::FETCH_NUM);

// Requête : QCMs correspondant + si déjà passé
$sql = "
  SELECT q.qcm_id, q.date_creation, q.titre_qcm, r.note,
         CASE WHEN r.resultat_id IS NOT NULL THEN 1 ELSE 0 END AS deja_passe
    FROM qcm q
    LEFT JOIN resultat_qcm r
      ON q.qcm_id = r.id_qcm
     AND r.id_etudiant = :etud
   WHERE q.id_filiere = :fil
     AND q.niveau_qcm = :niv
   GROUP BY q.qcm_id";
$stmt = $lien->prepare($sql);
$stmt->execute([
    'etud' => $_SESSION['ID_Etud'],
    'fil'  => $fil,
    'niv'  => $niv
]);
$qcms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <title><?php echo LIB_PasserQCM; ?></title>
    <style>
        .qcm-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .qcm-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .qcm-title {
            font-weight: 600;
            color: #2c3e50;
        }
        .qcm-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .qcm-status-completed {
            background-color: #ecf0f1;
        }
        .page-header {
            background-color: #3498db;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .btn-passer {
            background-color: #2ecc71;
            border-color: #27ae60;
        }
        .btn-passer:hover {
            background-color: #27ae60;
            border-color: #219d54;
        }
        .badge-completed {
            background-color: #95a5a6 !important;
        }
        .success-alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="p-4">
  <div class="container">
    <div class="page-header text-center">
        <h1><i class="fas fa-clipboard-list me-2"></i><?= LIB_PasserQCM ?></h1>
        <p class="lead"><?= LIB_QCMDisponibles ?></p>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success success-alert alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
    <?php foreach($qcms as $q): ?>
        <div class="col-md-6">
            <div class="card qcm-card <?= $q['deja_passe'] ? 'qcm-status-completed' : '' ?>">
                <div class="card-body">
                    <h3 class="qcm-title"><?= htmlspecialchars($q['titre_qcm']) ?></h3>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="qcm-date">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?= date('d-m-Y', strtotime($q['date_creation'])) ?>
                        </span>
                    </div>
                    
                    <?php if($q['deja_passe']): ?>
                      <span class="text-muted">Note: <?= $q['note'] ?></span>
                        <div class="text-center">
                            <span class="badge bg-secondary badge-completed">
                                <i class="fas fa-check me-1"></i><?= LIB_Dejapasse ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <a href="faire_qcm.php?id=<?= $q['qcm_id'] ?>" class="btn btn-passer btn-lg">
                                <i class="fas fa-play-circle me-1"></i><?= LIB_PasserQCM ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    
    <?php if(count($qcms) == 0): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i><?= LIB_AucunQCM ?>
    </div>
    <?php endif; ?>
  </div>
  
  <footer class="text-center mt-5 py-3 text-muted border-top">
    <p>&copy; 2025 <?php echo LIB_NomProjet; ?></p>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>