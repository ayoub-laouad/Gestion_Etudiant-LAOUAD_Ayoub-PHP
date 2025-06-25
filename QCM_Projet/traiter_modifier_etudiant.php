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

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $ID_Etud = $_POST['ID_Etud'];
    $Matricule = $_POST['Matricule'];
    $Nom = $_POST['Nom'];
    $Prenom = $_POST['Prenom'];
    $Filiere = $_POST['Filiere'];
    $AS = $_POST['AS'];
    $Niveau = $_POST['Niveau'];
    $Sexe = $_POST['Sexe'];
    $Mail = $_POST['Mail'];
    $Login = $_POST['Login'];
    $MP = $_POST['MP'];

    // Gestion de la photo (si une nouvelle photo est téléchargée)
    $Photo = $_FILES['Photo']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["Photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Vérification si le fichier est une image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["Photo"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Vérification de la taille du fichier
    if ($_FILES["Photo"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Autorisation de certains formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Vérification si $uploadOk est à 0 par une erreur
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // si tout va bien, essayez de télécharger le fichier
    } else {
        if (move_uploaded_file($_FILES["Photo"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["Photo"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    try {
        // Préparer la requête SQL pour mettre à jour les informations de l'étudiant
        $sql = "UPDATE etudiants SET Matricule_Etud = :matricule, Nom_Etud = :nom, Prenom_Etud = :prenom, 
                Fil_Etud = :filiere, AS_Etud = :as, Niv_Etud = :niveau, Sexe_Etud = :sexe, 
                Email_Etud = :mail, Login_Etud = :login, MP_Etud = :mp";
        // Ajouter la mise à jour de la photo si une nouvelle photo est téléchargée
        if (!empty($Photo)) {
            $sql .= ", Photo_Etud = :photo";
        }
        $sql .= " WHERE ID_Etud = :id";

        $stmt = $lien->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':id', $ID_Etud);
        $stmt->bindParam(':matricule', $Matricule);
        $stmt->bindParam(':nom', $Nom);
        $stmt->bindParam(':prenom', $Prenom);
        $stmt->bindParam(':filiere', $Filiere);
        $stmt->bindParam(':as', $AS);
        $stmt->bindParam(':niveau', $Niveau);
        $stmt->bindParam(':sexe', $Sexe);
        $stmt->bindParam(':mail', $Mail);
        $stmt->bindParam(':login', $Login);
        $stmt->bindParam(':mp', $MP);
       if (!empty($Photo)) {
           $stmt->bindParam(':photo', $Photo);
       }

        // Exécuter la requête
        $stmt->execute();

        // Rediriger vers la page de gestion des étudiants
        header("Location: gestion_etudiants.php");
        exit();

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Si le formulaire n'a pas été soumis, rediriger vers la page de gestion des étudiants
    header("Location: gestion_etudiants.php");
    exit();
}
?>