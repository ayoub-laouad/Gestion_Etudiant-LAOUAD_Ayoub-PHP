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

header('Content-Type: application/json'); // Définir le type de contenu comme JSON

// Récupérer l'ID de l'étudiant à afficher
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idEtudiant = $_GET['id'];

    try {
        // Préparer la requête SQL pour récupérer toutes les informations de l'étudiant
        $sql = "SELECT etudiants.*, filiere.Lib_Fil, niveaux.Lib_Niv, annee_sco.Lib_AS FROM etudiants
                LEFT JOIN filiere ON etudiants.Fil_Etud = filiere.Id_Fil
                LEFT JOIN niveaux ON etudiants.Niv_Etud = niveaux.Id_Niv
                LEFT JOIN annee_sco ON etudiants.AS_Etud = annee_sco.Id_AS
                WHERE ID_Etud = :id";
        $stmt = $lien->prepare($sql);
        $stmt->bindParam(':id', $idEtudiant);
        $stmt->execute();
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si l'étudiant existe
        if ($etudiant) {
            // Retirer les données sensibles
            unset($etudiant['Login_Etud'], $etudiant['MP_Etud']);

            // Renvoyer les données en JSON
            echo json_encode($etudiant);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Aucun étudiant trouvé avec cet ID.']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID d\'étudiant non valide.']);
}
?>