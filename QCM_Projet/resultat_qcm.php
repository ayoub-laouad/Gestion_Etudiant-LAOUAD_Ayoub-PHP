<?php
// Récupération du qcm_id depuis l'URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID de QCM invalide ou non fourni.");
}

// Inclure le header
include 'header_identifier.php';

// Vérifier si un administrateur
if (!$isAdmin) {
    header("Location: accueil.php");
    exit();
}

// Récupération des informations du QCM
try {
    $stmt = $lien->prepare("SELECT q.titre_qcm, f.Lib_Fil, n.Lib_Niv 
                          FROM qcm q 
                          JOIN filiere f ON q.id_filiere = f.Id_Fil 
                          JOIN niveaux n ON q.niveau_qcm = n.Id_Niv 
                          WHERE q.qcm_id = ?");
    $stmt->execute([$id]);
    $qcm_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$qcm_info) {
        die("QCM introuvable.");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des informations du QCM: " . $e->getMessage());
}

// Récupération des résultats des étudiants pour ce QCM
try {
    $stmt = $lien->prepare("SELECT r.resultat_id, r.note, r.date_passage, r.duree, 
                          e.ID_Etud, e.Nom_Etud, e.Prenom_Etud, e.Matricule_Etud, e.Email_Etud
                          FROM resultat_qcm r 
                          JOIN etudiants e ON r.id_etudiant = e.ID_Etud 
                          WHERE r.id_qcm = ? 
                          ORDER BY r.date_passage DESC");
    $stmt->execute([$id]);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des résultats: " . $e->getMessage());
}

// Calcul des statistiques
$nb_etudiants = count($resultats);
$note_moyenne = 0;
$duree_moyenne = 0;
$note_min = INF;
$note_max = -INF;

if ($nb_etudiants > 0) {
    $total_notes = 0;
    $total_duree = 0;
    
    foreach ($resultats as $resultat) {
        $total_notes += $resultat['note'];
        $total_duree += $resultat['duree'];
        $note_min = min($note_min, $resultat['note']);
        $note_max = max($note_max, $resultat['note']);
    }
    
    $note_moyenne = $total_notes / $nb_etudiants;
    $duree_moyenne = $total_duree / $nb_etudiants;
}
?>

<head>
    <title><?php echo LIB_ResultatsQCM; ?> - <?php echo htmlspecialchars($qcm_info['titre_qcm']); ?></title>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo LIB_ResultatsQCM; ?>: <?php echo htmlspecialchars($qcm_info['titre_qcm']); ?></h1>
            <a href="gestion_qcms.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> <?php echo LIB_RetourGestionQCMs; ?>
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4><?php echo LIB_InfosQCM; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong><?php echo LIB_TitreQCM; ?>:</strong> <?php echo htmlspecialchars($qcm_info['titre_qcm']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><?php echo LIB_Filiere; ?>:</strong> <?php echo htmlspecialchars($qcm_info['Lib_Fil']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><?php echo LIB_Niveau; ?>:</strong> <?php echo htmlspecialchars($qcm_info['Lib_Niv']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4><?php echo LIB_Statistiques; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo LIB_NombreEtudiants; ?></h5>
                                <p class="card-text fs-1"><?php echo $nb_etudiants; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo LIB_NoteMoyenne; ?></h5>
                                <p class="card-text fs-1"><?php echo number_format($note_moyenne); ?>/20</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo LIB_NoteMinMax; ?></h5>
                                <p class="card-text fs-1"><?php echo $nb_etudiants > 0 ? number_format($note_min) . " / " . number_format($note_max) : "N/A"; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo LIB_DureeMoyenne; ?></h5>
                                <p class="card-text fs-1"><?php echo number_format($duree_moyenne); ?> s</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><?php echo LIB_ListeResultats; ?></h4>
            </div>
            <div class="card-body">
                <?php if ($nb_etudiants > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo LIB_Numero; ?></th>
                                    <th><?php echo LIB_Matricule; ?></th>
                                    <th><?php echo LIB_Nom; ?></th>
                                    <th><?php echo LIB_Prenom; ?></th>
                                    <th><?php echo LIB_Email; ?></th>
                                    <th><?php echo LIB_Note; ?></th>
                                    <th><?php echo LIB_DureeMin; ?></th>
                                    <th><?php echo LIB_DatePassage; ?></th>
                                    <th><?php echo LIB_Actions; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($resultats as $resultat): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($resultat['Matricule_Etud']); ?></td>
                                        <td><?php echo htmlspecialchars($resultat['Nom_Etud']); ?></td>
                                        <td><?php echo htmlspecialchars($resultat['Prenom_Etud']); ?></td>
                                        <td><?php echo htmlspecialchars($resultat['Email_Etud']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $resultat['note'] >= 10 ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($resultat['note']); ?>/20
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($resultat['duree']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($resultat['date_passage'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#resultDetailsModal" data-result-id="<?php echo $resultat['resultat_id']; ?>">
                                                <i class="fas fa-eye"></i> <?php echo LIB_Details; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?php echo LIB_AucunResultatQCM; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Result Details Modal Structure -->
    <div class="modal fade" id="resultDetailsModal" tabindex="-1" aria-labelledby="resultDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultDetailsModalLabel"><?php echo LIB_DetailsResultat; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo LIB_Fermer; ?>"></button>
                </div>
                <div class="modal-body" id="resultDetailsModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo LIB_Fermer; ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery is needed for the AJAX call -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // JavaScript to handle the modal and AJAX call
        $(document).ready(function() {
            $('#resultDetailsModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var resultId = button.data('result-id'); // Extract info from data-* attributes
                var lang = '<?php echo $_SESSION['lang']; ?>'; // Get current language

                // Use AJAX to fetch the details of the result
                $.ajax({
                    url: 'get_result_details.php?id=' + resultId + '&lang=' + lang, // Pass language
                    type: 'GET',
                    success: function(data) {
                        $('#resultDetailsModalBody').html(data); // Populate the modal body
                    },
                    error: function() {
                        // Use a language constant for the error message
                        $('#resultDetailsModalBody').html('<?php echo addslashes(LIB_ErreurChargementDetails); ?>');
                    }
                });
            });
        });
    </script>
</body>
</html>