<?php
session_start();
// Vérification de la session et du rôle d'administrateur
if (!isset($_SESSION['ID_Etud'])) {
    header("Location: index.php");
    exit();
}

// Inclure le fichier de connexion PDO
include 'connexion.php';

// Inclure le fichier de langue
include 'langues/' . $_SESSION['lang'] . '.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_POST['action'] !== 'passer') {
    header('Location: passer_qcm.php');
    exit();
}

$id_qcm = (int)$_POST['qcm_id'];
$id_etud = $_SESSION['ID_Etud'];
$answers = $_POST['answer'] ?? [];

// Récupérer la durée soumise ou la calculer à partir du timestamp de début
if (isset($_POST['duree']) && is_numeric($_POST['duree'])) {
    $duree = (int)$_POST['duree'];
} else {
    // Fallback si le JavaScript a échoué
    $duree = isset($_SESSION['qcm_start_time']) ? (time() - $_SESSION['qcm_start_time']) : 0;
}

// Nettoyer la variable de session (plus besoin)
unset($_SESSION['qcm_start_time']);

// Récupérer la bonne réponse pour chaque question
$stmt = $lien->prepare("
  SELECT question_id, reponse_id 
    FROM reponses 
   WHERE est_juste = 1
     AND question_id IN (SELECT question_id FROM questions WHERE qcm_id = ?)
");
$stmt->execute([$id_qcm]);
$corrects = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
// $corrects[question_id] = reponse_id

// Calcul du score
$score = 0;
foreach ($corrects as $qid => $correct_rid) {
    if (!isset($answers[$qid])) {
        // aucune réponse => 0 point
        continue;
    }
    $given = (int)$answers[$qid];
    if ($given === $correct_rid) {
        $score += 2;
    } else {
        $score -= 1;
    }
}
if ($score < 0) {
    $score = 0;
}

// Formater la durée en texte lisible
$duree_texte = sprintf(
    "%02d:%02d:%02d", 
    floor($duree / 3600),
    floor(($duree % 3600) / 60),
    $duree % 60
);

// Insérer le résultat
$stmtIns = $lien->prepare("
  INSERT INTO resultat_qcm (id_etudiant, id_qcm, note, duree, date_passage) 
  VALUES (?, ?, ?, ?, NOW())
");
$stmtIns->execute([$id_etud, $id_qcm, $score, $duree]);

// Rediriger vers la liste avec message
header("Location: passer_qcm.php?success=" . urlencode(LIB_QuestionAjoutee . " ($score pts) - " . LIB_Duree . ": $duree_texte"));
exit();