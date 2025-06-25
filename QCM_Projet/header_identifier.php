<?php
session_start();
$ID_Etud = $_SESSION['ID_Etud'];

// Inclure le fichier de connexion PDO
include 'connexion.php';

// Vérification la connexion
if (!isset($_SESSION['ID_Etud'])) {
    header("Location: index.php");
    exit();
}

// Vérification le rôle de l'utilisateur
try {
    $stmt = $lien->prepare("SELECT * FROM etudiants WHERE ID_Etud = :id");
    $stmt->bindParam(':id', $ID_Etud);
    $stmt->execute();
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
    // Assumer que le rôle admin est défini par une valeur spécifique dans une colonne de la table etudiants (par exemple, `Role_Etud`)
    $isAdmin = ($etudiant['Role_Etud'] == 'admin'); // Remplacez 'admin' par la valeur appropriée
    $_SESSION['admin'] = $isAdmin;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Définition de la langue par défaut
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

// Changer la langue si une sélection est faite
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['fr', 'en', 'ar', 'es'])) {
        $_SESSION['lang'] = $lang;
    }
}

// Charger le fichier de langue correspondant
include "langues/" . $_SESSION['lang'] . ".php";
?>


<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" <?php if ($_SESSION['lang'] == 'ar') echo 'dir="rtl"'; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <nav>
        <div class="logo">
            <img src="img/logo.png" alt="Logo" style="width: 50px; height: 50px;">
            <span><?= LIB_NomProjet ?></span>
        </div>
        <div>
            <ul>
                <li><a href="accueil.php"><?= LIB_Accueil ?></a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="gestion_etudiants.php"><?= LIB_GestionEtudiants ?></a></li>
                    <li><a href="gestion_qcms.php"><?= LIB_GestionQCMs ?></a></li>
                <?php else: ?>
                    <li><a href="passer_qcm.php"><?= LIB_PasserQCM ?></a></li>
                <?php endif; ?>
                <li><a href="deconnexion.php"><?= LIB_Quitter ?></a></li>
            </ul>
        </div>
        <div class="languages">
            <a href="?id=<?= $id ?>&lang=ar"><img src="img/Arabic.png" alt="Arabic"></a>
            <a href="?id=<?= $id ?>&lang=es"><img src="img/Spain.png" alt="Spanish"></a>
            <a href="?id=<?= $id ?>&lang=en"><img src="img/USA.svg" alt="English"></a>
            <a href="?id=<?= $id ?>&lang=fr"><img src="img/France.svg" alt="French"></a>
        </div>
    </nav>
</body>

