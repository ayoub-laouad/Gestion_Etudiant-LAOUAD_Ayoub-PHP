<?php
session_start();
// Vérification de la session et du rôle d'administrateur
if (!isset($_SESSION['ID_Etud'])) {
    header("Location: index.php");
    exit();
}
// Vérifier si un administrateur
if (!$_SESSION['admin']) {
    header("Location: accueil.php");
    exit();
}

// Inclure le fichier de connexion PDO
include 'connexion.php';

// Inclure le fichier de langue
include 'langues/' . $_SESSION['lang'] . '.php';

// Validation des paramètres GET
$question_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$qcm_id = filter_input(INPUT_GET, 'qcm_id', FILTER_VALIDATE_INT);

// Vérification des IDs
if (!$question_id || !$qcm_id) {
    header("Location: traiter_modifier_qcm?id=$qcm_id&error=" . urlencode(LIB_InvalidID));
    exit();
}

try {
    // Début de la transaction
    $lien->beginTransaction();

    // Suppression des réponses liées
    $stmtDeleteReponses = $lien->prepare("DELETE FROM reponses WHERE question_id = :question_id");
    $stmtDeleteReponses->bindParam(':question_id', $question_id, PDO::PARAM_INT);
    $stmtDeleteReponses->execute();

    // Suppression de la question
    $stmtDeleteQuestion = $lien->prepare("DELETE FROM questions WHERE question_id = :question_id");
    $stmtDeleteQuestion->bindParam(':question_id', $question_id, PDO::PARAM_INT);
    $stmtDeleteQuestion->execute();

    // Validation de la transaction
    $lien->commit();

    // Redirection avec message de succès
    header("Location: modifier_qcm.php?id=$qcm_id&success=" . urlencode(LIB_QuestionDeleted));
    exit();

} catch (PDOException $e) {
    // Annulation en cas d'erreur
    $lien->rollBack();
    
    // Log de l'erreur (à adapter selon votre système de logging)
    error_log("Erreur suppression question : " . $e->getMessage());
    
    // Redirection avec message d'erreur
    header("Location: modifier_qcm.php?id=$qcm_id&error=" . urlencode(LIB_DeleteError));
    exit();
} catch (Exception $e) {
    // Redirection générique pour autres erreurs
    header("Location: modifier_qcm.php?id=$qcm_id&error=" . urlencode(LIB_GeneralError));
    exit();
}