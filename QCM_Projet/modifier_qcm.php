<?php
$id = (int)$_GET['id'];

// Inclure le header
include 'header_identifier.php';

// Vérifier si un administrateur
if (!$isAdmin) {
    header("Location: accueil.php");
    exit();
}

$error = isset($_GET['error']) ? urldecode($_GET['error']) : null;
$success = isset($_GET['success']) ? urldecode($_GET['success']) : null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die(LIB_QCM_ID_INVALIDE);
}

$stmtQCM = $lien->prepare("SELECT * FROM qcm WHERE qcm_id = :id");
$stmtQCM->bindParam(':id', $id, PDO::PARAM_INT);
$stmtQCM->execute();
$qcm = $stmtQCM->fetch(PDO::FETCH_ASSOC);

if (!$qcm) {
    die(LIB_QCM_INTROUVABLE);
}

$stmtQuestions = $lien->prepare("SELECT * FROM questions WHERE qcm_id = :id");
$stmtQuestions->bindParam(':id', $id, PDO::PARAM_INT);
$stmtQuestions->execute();
$questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);

$stmtReponses = $lien->prepare("SELECT * FROM reponses WHERE question_id IN (SELECT question_id FROM questions WHERE qcm_id = :id)");
$stmtReponses->bindParam(':id', $id, PDO::PARAM_INT);
$stmtReponses->execute();
$reponses = $stmtReponses->fetchAll(PDO::FETCH_ASSOC);

$tables = [
    'annees_sco' => "SELECT Id_AS, Lib_AS FROM annee_sco",
    'niveaux' => "SELECT Id_Niv, Lib_Niv FROM niveaux", 
    'filieres' => "SELECT Id_Fil, Lib_Fil FROM filiere"
];

foreach ($tables as $var => $query) {
    $$var = $lien->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
?>


<head>
    <title><?= LIB_ModifierQCM ?></title>
    <style>
        .answers-list {
            border-left: 3px solid #dee2e6;
            margin-left: 1rem;
        }

        .answer-item {
            padding: 0.5rem;
            margin: 0.3rem 0;
            border-radius: 0.25rem;
        }

        .answer-item:hover {
            background-color: #f8f9fa;
        }

        .correct-answer {
            background-color: #d4edda;
            border-left: 3px solid #28a745;
        }
        .question-container {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }
        .back-link i {
            margin-right: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <a href="gestion_qcms.php" class="back-link">
            <i class="fas fa-arrow-left"></i> <?= LIB_Retour ?>
        </a>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2><?= LIB_ModifierQCM ?></h2>
            </div>
            <div class="card-body">
                <form action="traiter_qcm.php?action=2&id=<?= $id ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Titre ?> :</label>
                        <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($qcm['titre_qcm']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= LIB_Filiere ?> :</label>
                            <select name="Filiere" class="form-select" required>
                                <?php foreach ($filieres as $f): ?>
                                    <option value="<?= $f['Id_Fil'] ?>" <?= ($f['Id_Fil'] == $qcm['id_filiere']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['Lib_Fil']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= LIB_Niveau ?> :</label>
                            <select name="Niveau" class="form-select" required>
                                <?php foreach ($niveaux as $n): ?>
                                    <option value="<?= $n['Id_Niv'] ?>" <?= ($n['Id_Niv'] == $qcm['niveau_qcm']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($n['Lib_Niv']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="modifier_qcm" class="btn btn-primary"><?= LIB_EnregistrerModifs ?></button>
                </form>

                <!-- Liste des questions existantes -->
                <h3 class="mt-5 mb-4"><?= LIB_QuestionsExistantes ?></h3>
                <?php if (count($questions) > 0): ?>
                    <?php foreach ($questions as $index => $question): 
                        // Récupération des réponses pour cette question
                        $stmtReponses = $lien->prepare("SELECT * FROM reponses WHERE question_id = :question_id");
                        $stmtReponses->bindParam(':question_id', $question['question_id'], PDO::PARAM_INT);
                        $stmtReponses->execute();
                        $reponses = $stmtReponses->fetchAll(PDO::FETCH_ASSOC);

                        // Vérifier si c'est la dernière question
                        $isLastQuestion = ($index === array_key_last($questions));
                    ?>
                    <div class="question-container">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h5 class="mb-2"><?= htmlspecialchars($question['question_text']) ?></h5>
                                <?php if (!empty($question['commentaire'])): ?>
                                    <p class="text-muted small"><?= htmlspecialchars($question['commentaire']) ?></p>
                                <?php endif; ?>
                            </div>
                            <!-- Bouton Modifier -->
                            <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" 
                                    data-bs-target="#modalModifierQuestion<?= $question['question_id'] ?>" style="margin: 5px;">
                                <i class="fa fa-edit"></i> <?= LIB_Modifier ?>
                            </button>
                            <!-- Bouton Supprimer -->
                            <a href="supprimer_question.php?id=<?= $question['question_id'] ?>&qcm_id=<?= $id ?>" 
                                class="btn btn-danger btn-sm" style="margin: 5px;" 
                                onclick="return confirm('<?= LIB_ConfirmSuppressionQuestion ?>')">
                                <i class="fa fa-trash"></i> <?= LIB_Supprimer ?>
                            </a>
                        </div>

                        <!-- Modale Bootstrap -->
                        <div class="modal fade" id="modalModifierQuestion<?= $question['question_id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $question['question_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="traiter_qcm.php?action=3" method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel<?= $question['question_id'] ?>"><?= LIB_Modifier ?> <?= LIB_Question ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="qcm_id" value="<?= $id ?>">
                                            <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label"><?= LIB_Enonce ?> :</label>
                                                <textarea name="question_text" class="form-control" required><?= htmlspecialchars($question['question_text']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><?= LIB_Commentaire ?> :</label>
                                                <textarea name="commentaire" class="form-control"><?= htmlspecialchars($question['commentaire']) ?></textarea>
                                            </div>
                                            <h5><?= LIB_Reponses ?> :</h5>
                                            <?php foreach ($reponses as $i => $rep): ?>
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text"><?= LIB_Reponse ?> <?= $i+1 ?></span>
                                                    <!-- Champ pour le texte de la réponse -->
                                                    <input type="text" name="reponses[<?= $rep['reponse_id'] ?>]" class="form-control" 
                                                        value="<?= htmlspecialchars($rep['reponse_text']) ?>" required>
                                                    <!-- Bouton radio pour indiquer si la réponse est correcte -->
                                                    <div class="input-group-text">
                                                        <input type="radio" name="correcte" value="<?= $rep['reponse_id'] ?>" <?= $rep['est_juste'] ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary"><?= LIB_Enregistrer ?></button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= LIB_Annuler ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des réponses -->
                        <div class="answers-list ps-4">
                            <?php foreach ($reponses as $reponse): ?>
                                <div class="answer-item <?= $reponse['est_juste'] ? 'correct-answer' : '' ?>">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check pe-2">
                                            <input type="radio" class="form-check-input" disabled <?= $reponse['est_juste'] ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex-grow-1">
                                            <?= htmlspecialchars($reponse['reponse_text']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info"><?= LIB_AucuneQuestion ?></div>
                <?php endif; ?>

                <form action="traiter_qcm.php?action=2&id=<?= $id ?>" method="POST">
                    <h3 class="mt-5"><?= LIB_AjouterQuestion ?></h3>
                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Enonce ?></label>
                        <textarea name="question" class="form-control" rows="2" <?php if (!isset($_POST['modifier_qcm'])) echo "required"; ?>></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Commentaire ?></label>
                        <textarea name="commentaire" class="form-control" rows="2"></textarea>
                    </div>

                    <h5><?= LIB_Reponses ?></h5>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><?= LIB_Reponse . ' ' . $i ?></span>
                            <input type="text" name="reponses[]" class="form-control" <?php if (!isset($_POST['modifier_qcm'])) echo "required"; ?>>
                            <div class="input-group-text">
                                <input type="radio" name="correcte" value="<?= $i-1 ?>" <?php if (!isset($_POST['modifier_qcm'])) echo "required"; ?>>
                            </div>
                        </div>
                    <?php endfor; ?>
                    <button type="submit" name="ajouter_question" class="btn btn-success mt-3"><?= LIB_Ajouter ?></button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>