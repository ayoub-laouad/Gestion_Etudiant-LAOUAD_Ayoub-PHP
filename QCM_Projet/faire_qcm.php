<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) die('QCM invalide');

// Inclure le header
include 'header_simple.php';

// Vérifier qu'il n'a pas déjà passé ce QCM
$stmtCheck = $lien->prepare("
  SELECT COUNT(*) 
    FROM resultat_qcm 
   WHERE id_qcm = ? AND id_etudiant = ?");
$stmtCheck->execute([$id, $_SESSION['ID_Etud']]);
if ($stmtCheck->fetchColumn() > 0) {
    die(LIB_Voir . ' : ' . LIB_QuestionAjoutee);
}

// Récupérer le titre du QCM
$stmtTitle = $lien->prepare("SELECT titre_qcm FROM qcm WHERE qcm_id = ?");
$stmtTitle->execute([$id]);
$titre_qcm = $stmtTitle->fetchColumn();

// Récupérer questions + réponses
$stmtQ = $lien->prepare("SELECT question_id, question_text FROM questions WHERE qcm_id = ?");
$stmtQ->execute([$id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque question, récupérer les réponses
$q_with_reps = [];
foreach ($questions as $q) {
    $stmtR = $lien->prepare("
      SELECT reponse_id, reponse_text 
        FROM reponses 
       WHERE question_id = ? 
    ");
    $stmtR->execute([$q['question_id']]);
    $q_with_reps[] = [
      'question' => $q,
      'reponses' => $stmtR->fetchAll(PDO::FETCH_ASSOC)
    ];
}

// Enregistrer l'heure de début dans la session
$_SESSION['qcm_start_time'] = time();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
  <title><?= LIB_PasserQCM ?> - <?= htmlspecialchars($titre_qcm) ?></title>
  <style>
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
    .qcm-timer {
        position: fixed;
        top: 10px;
        right: 10px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 50px;
        padding: 10px 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        z-index: 1000;
        font-size: 1.1rem;
    }
    .question-card {
        border-left: 5px solid #3498db;
        margin-bottom: 30px;
        transition: all 0.3s;
    }
    .question-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .question-header {
        background-color: #f8f9fa;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    .question-body {
        padding: 20px;
    }
    .question-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        background-color: #3498db;
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        margin-right: 10px;
    }
    .reponse-option {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        transition: background-color 0.2s;
    }
    .reponse-option:hover {
        background-color: #f8f9fa;
    }
    .submit-button {
        padding: 12px 25px;
        font-size: 1.1rem;
        font-weight: 600;
    }
    .progress-indicator {
        position: sticky;
        top: 70px;
        background-color: white;
        padding: 10px;
        margin-bottom: 20px;
        z-index: 100;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="p-4">
  <div class="container mb-5">
    <div class="qcm-timer">
        <i class="fas fa-clock me-2"></i><span id="timer">00:00:00</span>
    </div>
    
    <a href="passer_qcm.php" class="back-link">
        <i class="fas fa-arrow-left"></i> <?= LIB_Retour ?>
    </a>
    
    <div class="card shadow mb-4">
      <div class="card-header bg-primary text-white">
          <h2><i class="fas fa-clipboard-check me-2"></i><?= htmlspecialchars($titre_qcm) ?></h2>
          <p class="mb-0"><?= LIB_PasserQCM ?></p>
      </div>
      <div class="card-body">
        <div class="progress-indicator">
            <div class="progress" style="height: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="text-end mt-1">
                <small id="progress-text">0/<?= count($q_with_reps) ?> <?= LIB_QuestionsRepondues ?></small>
            </div>
        </div>
        
        <form action="traiter_qcm_resultat.php" method="post" id="qcm-form">
          <input type="hidden" name="action" value="passer">
          <input type="hidden" name="qcm_id" value="<?= $id ?>">
          <input type="hidden" name="duree" id="duree-field" value="0">
          
          <?php foreach ($q_with_reps as $i => $qr): 
              $qid = $qr['question']['question_id'];
          ?>
            <div class="question-card card mb-4" id="question-<?= $qid ?>">
              <div class="question-header">
                <h5>
                  <span class="question-number"><?= ($i+1) ?></span>
                  <?= htmlspecialchars($qr['question']['question_text']) ?>
                </h5>
              </div>
              <div class="question-body">
                <?php foreach ($qr['reponses'] as $r): ?>
                  <div class="reponse-option">
                    <div class="form-check">
                      <input 
                        class="form-check-input qcm-answer" 
                        type="radio" 
                        name="answer[<?= $qid ?>]" 
                        id="r<?= $r['reponse_id'] ?>" 
                        data-question="<?= $qid ?>"
                        value="<?= $r['reponse_id'] ?>">
                      <label class="form-check-label" for="r<?= $r['reponse_id'] ?>">
                        <?= htmlspecialchars($r['reponse_text']) ?>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="text-center">
            <button type="submit" class="btn btn-success submit-button">
                <i class="fas fa-check-circle me-2"></i><?= LIB_Enregistrer ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <footer class="text-center mt-5 py-3 text-muted border-top">
    <p>&copy; 2025 <?php echo LIB_NomProjet; ?></p>
  </footer>
  
  <script>
    // Fonction pour formater le temps (secondes -> HH:MM:SS)
    function formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        
        return [
            h.toString().padStart(2, '0'),
            m.toString().padStart(2, '0'),
            s.toString().padStart(2, '0')
        ].join(':');
    }
    
    // Variables pour le timer
    let startTime = Date.now();
    let timerInterval;
    let elapsedSeconds = 0;
    
    // Démarrer le timer
    function startTimer() {
        timerInterval = setInterval(function() {
            elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
            document.getElementById('timer').textContent = formatTime(elapsedSeconds);
            document.getElementById('duree-field').value = elapsedSeconds;
        }, 1000);
    }
    
    // Suivre la progression des réponses
    function updateProgress() {
        const totalQuestions = <?= count($q_with_reps) ?>;
        const answeredQuestions = document.querySelectorAll('.qcm-answer:checked').length;
        const percentage = (answeredQuestions / totalQuestions) * 100;
        
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-text').textContent = answeredQuestions + '/' + totalQuestions + ' <?= LIB_QuestionsRepondues ?>';
        
        // Changer la couleur de la carte pour les questions répondues
        document.querySelectorAll('.qcm-answer').forEach(function(radio) {
            if (radio.checked) {
                const questionId = radio.getAttribute('data-question');
                document.getElementById('question-' + questionId).style.borderLeftColor = '#2ecc71';
            }
        });
    }
    
    // Événements
    document.addEventListener('DOMContentLoaded', function() {
        // Démarrer le timer
        startTimer();
        
        // Écouter les changements de réponses
        document.querySelectorAll('.qcm-answer').forEach(function(radio) {
            radio.addEventListener('change', updateProgress);
        });
        
        // Soumettre le formulaire
        document.getElementById('qcm-form').addEventListener('submit', function(e) {
            clearInterval(timerInterval);
        });
    });
  </script>
</body>
</html>