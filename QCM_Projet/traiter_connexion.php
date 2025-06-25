<?php
session_start();

// Inclure le fichier de connexion PDO
include 'connexion.php';

// Récupérer les données du formulaire
$login = $_POST['Login'];
$motPasse = $_POST['MP'];

try {
    // Préparer la requête SQL
    $stmt = $lien->prepare("SELECT * FROM etudiants WHERE Login_Etud = :login");
    $stmt->bindParam(':login', $login);
    $stmt->execute();

    // Récupérer les résultats
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Vérifier le mot de passe (utiliser password_verify si vous avez haché les mots de passe)
      if($motPasse == $result['MP_Etud']) {
            // L'utilisateur existe et le mot de passe est correct
            $_SESSION['ID_Etud'] = $result['ID_Etud'];
            $_SESSION['Nom_Etud'] = $result['Nom_Etud'];

            // Rediriger vers la page d'accueil
            header("Location: accueil.php");
            exit();
      } else {
             // Mot de passe incorrect
            echo "Login ou mot de passe incorrect.";
            header("Location: index.php?error=1");
      }
    } else {
        // L'utilisateur n'existe pas
        echo "Login ou mot de passe incorrect.";
         header("Location: index.php?error=1");
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>