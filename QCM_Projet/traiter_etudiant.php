<?php
// Récupérer les données du formulaire
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

// Gestion de la photo
$Photo = $_SESSION['Photo']['name'];
$target_dir = "uploads/";
$target_file = $target_dir . basename($_SESSION["Photo"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Vérification si le fichier est une image
if(isset($_POST["submit"])) {
    $check = getimagesize($_SESSION["Photo"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}

// Vérification de la taille du fichier
if ($_SESSION["Photo"]["size"] > 500000) {
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
}

try {
    // Préparer la requête SQL
    $sql = "INSERT INTO etudiants (Matricule_Etud, Nom_Etud, Prenom_Etud, Fil_Etud, AS_Etud, Niv_Etud, Sexe_Etud, Email_Etud, Login_Etud, MP_Etud, Photo_Etud) 
            VALUES (:matricule, :nom, :prenom, :filiere, :as, :niveau, :sexe, :mail, :login, :mp, :photo)";

    $stmt = $lien->prepare($sql);

    // Lier les paramètres
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
    $stmt->bindParam(':photo', $Photo);

    // Exécuter la requête
    $stmt->execute();

    // Rediriger vers la page de gestion des étudiants
    header("Location: gestion_etudiants.php");
    exit();

} catch(PDOException $e) {
    $message = "Error: " . $e->getMessage();
    echo "<script>alert('$message'); window.location.href='gestion_etudiants.php';</script>";
    exit();
}
?>