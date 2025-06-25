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

// Inclure le fichier de connexion PDO
include 'connexion.php';

// Récupérer l'ID de l'étudiant à supprimer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idEtudiant = $_GET['id'];

    try {
        // Préparer la requête SQL pour supprimer l'étudiant
        $sql = "DELETE FROM etudiants WHERE ID_Etud = :id";
        $stmt = $lien->prepare($sql);

        // Lier le paramètre
        $stmt->bindParam(':id', $idEtudiant);

        // Exécuter la requête
        $stmt->execute();

        // Rediriger vers la page de gestion des étudiants
        header("Location: gestion_etudiants.php");
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Si l'ID n'est pas valide, rediriger vers la page de gestion des étudiants
    header("Location: gestion_etudiants.php");
    exit();
}
?>