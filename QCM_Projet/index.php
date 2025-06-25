<?php
session_start();

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
    <title><?php echo LIB_Connexion; ?></title>
    <link rel="stylesheet" href="styleLogin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
</head>
<body>
    <nav>
        <div class="logo">
            <img src="img/logo.png" alt="Logo" style="width: 50px; height: 50px;">
            <span><?php echo LIB_NomProjet; ?></span>
        </div>
        <div class="languages">
            <a href="?lang=ar"><img src="img/Arabic.png" alt="Arabic"></a>
            <a href="?lang=es"><img src="img/Spain.png" alt="Spanish"></a>
            <a href="?lang=en"><img src="img/USA.svg" alt="English"></a>
            <a href="?lang=fr"><img src="img/France.svg" alt="French"></a>
        </div>
    </nav>
    <div class="container">
        <div class="form-box login">
            <form action="traiter_connexion.php" method="post">
                <h1><?php echo LIB_Connexion; ?></h1>
                <div class="input-box">
                    <input type="text" name="Login" placeholder="<?php echo LIB_Login; ?>" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="MP" placeholder="<?php echo LIB_MP; ?>" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <button type="submit" class="btn"><?php echo LIB_Connexion; ?></button>
            </form>
        </div>
        <div class="toggle-box" style="<?php if ($_SESSION['lang'] == 'ar') echo '
                                                border-top-left-radius: 0px;
                                                border-bottom-left-radius: 0px;
                                                border-top-right-radius: 100px;
                                                border-bottom-right-radius: 100px;'; ?>">
            <div class="toggle-panel toggle-left">
                <h1><?php echo LIB_Bienvenue; ?>!</h1>
                <img src="img/LogIn.png" alt="Login Image">
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4">
        <p>&copy; 2025 <?php echo LIB_NomProjet; ?></p>
    </footer>
</body>
</html>
<?php
    if (isset($_GET['error'])) {
        // affaiche message d'erreur
        if ($_GET['error']==1) {
            echo '<script>alert(\'' . LIB_ErorLogin . '\');</script>';
        }
    }
?>