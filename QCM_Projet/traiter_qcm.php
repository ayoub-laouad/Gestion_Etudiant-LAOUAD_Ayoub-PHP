<?php
session_start();
// Vérification la connexion
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

// Traitement de la création, modification et suppression des QCMs
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_GET['action'] == 1) {
        // Code pour ajouter un QCM
        $message = "";
        // Récupérer et nettoyer les données
        $titre_qcm = !empty($_POST['titre_qcm']) ? trim($_POST['titre_qcm']) : null;
        $niveau_qcm = !empty($_POST['niveau_qcm']) ? (int) $_POST['niveau_qcm'] : null;
        $id_filiere  = !empty($_POST['id_filiere']) ? (int) $_POST['id_filiere'] : null;
        
        if ($titre_qcm && $niveau_qcm && $id_filiere) {
            try {
                $stmtInsert = $lien->prepare(
                    "INSERT INTO qcm (titre_qcm, niveau_qcm, id_filiere, date_creation) 
                    VALUES (:titre, :niveau, :filiere, NOW())"
                );
                $stmtInsert->bindParam(':titre', $titre_qcm);
                $stmtInsert->bindParam(':niveau', $niveau_qcm, PDO::PARAM_INT);
                $stmtInsert->bindParam(':filiere', $id_filiere, PDO::PARAM_INT);
                $stmtInsert->execute();
                $message = "Ajouter un QCM réussi.";
                echo "<script>alert('$message'); window.location.href='gestion_qcms.php';</script>";
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout du QCM : " . $e->getMessage();
                echo "<script>alert('$message'); window.location.href='gestion_qcms.php';</script>";
                exit();
            }
        } else {
            $message = "Veuillez remplir tous les champs obligatoires.";
            echo "<script>alert('$message'); window.location.href='gestion_qcms.php';</script>";
            exit();
        }

    } elseif ($_GET['action'] == 2) {
        // Code pour modifier un QCM
        $id = $_GET['id'];
        if (isset($_POST['modifier_qcm'])) {
            $titre = $_POST['titre'];
            $filiere = $_POST['Filiere'];
            $niveau = $_POST['Niveau'];

            $stmtUpdateQcm1 = $lien->prepare("UPDATE qcm SET titre_qcm=:titre, id_filiere=:filiere, niveau_qcm=:niveau WHERE qcm_id=:id");
            $stmtUpdateQcm1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtUpdateQcm1->bindParam(':titre', $titre);
            $stmtUpdateQcm1->bindParam(':filiere', $filiere, PDO::PARAM_INT);
            $stmtUpdateQcm1->bindParam(':niveau', $niveau, PDO::PARAM_INT);
            $stmtUpdateQcm1->execute();
        header("Location: modifier_qcm.php?id=$id&success=" . urlencode(LIB_QcmModifiee));
        exit();
        } elseif (isset($_POST['ajouter_question'])) {
            $question = $_POST['question'];
            $commentaire = $_POST['commentaire'];
            $reponses = $_POST['reponses'];
            $correcte = $_POST['correcte'];

            $stmtUpdateQcm2 =$lien->prepare("INSERT INTO questions (qcm_id, question_text, commentaire) VALUES (:id, :texte, :commentaire)");
            $stmtUpdateQcm2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtUpdateQcm2->bindParam(':texte', $question);
            $stmtUpdateQcm2->bindParam(':commentaire', $commentaire);
            $stmtUpdateQcm2->execute();
            $id_question = $lien->lastInsertId();

            foreach ($reponses as $index => $rep) {
                $est_correcte = ($index == $correcte) ? 1 : 0;
                $stmtUpdateQcm3 = $lien->prepare("INSERT INTO reponses (question_id , reponse_text, est_juste) VALUES (:id_question, :texte, :est_correcte)");
                $stmtUpdateQcm3->bindParam(':id_question', $id_question, PDO::PARAM_INT);
                $stmtUpdateQcm3->bindParam(':texte', $rep);
                $stmtUpdateQcm3->bindParam(':est_correcte', $est_correcte);
                $stmtUpdateQcm3->execute();
            }
        }
        header("Location: modifier_qcm.php?id=$id&success=" . urlencode(LIB_QuestionAjoutee));
        exit();

    } elseif ($_GET['action'] == 3) {
        $id_qcm      = (int)$_POST['qcm_id'];
        $id_question = (int)$_POST['question_id'];
        $texte       = $_POST['question_text'];
        $commentaire = $_POST['commentaire'];
        $reponses    = $_POST['reponses']; // Tableau associatif [reponse_id => reponse_text]
        $correcte    = (int)$_POST['correcte']; // Identifiant de la réponse correcte
    
        try {
            // Mettre à jour la question
            $stmt = $lien->prepare(
                "UPDATE questions 
                 SET question_text = ?, commentaire = ? 
                 WHERE question_id = ?"
            );
            $stmt->execute([$texte, $commentaire, $id_question]);
    
            // Mettre à jour chaque réponse
            foreach ($reponses as $reponse_id => $reponse_text) {
                $is_correct = ($reponse_id == $correcte) ? 1 : 0;
                $stmtUpd = $lien->prepare(
                    "UPDATE reponses 
                     SET reponse_text = ?, est_juste = ? 
                     WHERE reponse_id = ?"
                );
                $stmtUpd->execute([$reponse_text, $is_correct, $reponse_id]);
            }
    
            // Redirection avec succès
            header("Location: modifier_qcm.php?id={$id_qcm}&success=" . urlencode(LIB_QuestionModifiee));
            exit();
        } catch (PDOException $e) {
            // Gestion des erreurs
            header("Location: modifier_qcm.php?id={$id_qcm}&error=" . urlencode(LIB_GeneralError));
            exit();
        }
    }
}

if ($_GET['action'] == 4) {
    // Code pour supprimer un QCM
    $id = $_GET['id'];
    try {
        $stmtDelete1 = $lien->prepare("DELETE FROM reponses WHERE question_id IN (SELECT question_id FROM questions WHERE qcm_id = :id)");
        $stmtDelete1->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete1->execute();
        
        $stmtDelete2 =$lien->prepare("DELETE FROM questions WHERE qcm_id = :id");
        $stmtDelete2->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete2->execute();

        $stmtDelete3 =$lien->prepare("DELETE FROM qcm WHERE qcm_id = :id");
        $stmtDelete3->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete3->execute();
        header("Location: gestion_qcms.php");
        exit();
    } catch (PDOException $e) {
        $message = "Erreur lors de supprime du QCM : " . $e->getMessage();
        echo "<script>alert('$message'); window.location.href='gestion_qcms.php';</script>";
        exit();
    }
}

// Rediriger vers la page de gestion des QCMs
header("Location: gestion_qcms.php"); 
// exit();
?>