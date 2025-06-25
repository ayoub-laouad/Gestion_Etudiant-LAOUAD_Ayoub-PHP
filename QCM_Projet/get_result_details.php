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

// Get the result ID from the GET request
$resultatId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($resultatId <= 0) {
    // Use a language constant for the error message
    echo LIB_ResultatIDInvalide;
    exit();
}

try {
    // Prepare and execute the SQL query to get result details and associated student info
    $sql = "SELECT r.resultat_id, r.note, r.date_passage, r.duree,
                   e.ID_Etud, e.Nom_Etud, e.Prenom_Etud, e.Matricule_Etud, e.Email_Etud, e.Sexe_Etud, e.Photo_Etud,
                   f.Lib_Fil, n.Lib_Niv, ans.Lib_AS
            FROM resultat_qcm r
            JOIN etudiants e ON r.id_etudiant = e.ID_Etud
            LEFT JOIN filiere f ON e.Fil_Etud = f.Id_Fil
            LEFT JOIN niveaux n ON e.Niv_Etud = n.Id_Niv
            LEFT JOIN annee_sco ans ON e.AS_Etud = ans.Id_AS
            WHERE r.resultat_id = :id";

    $stmt = $lien->prepare($sql);
    $stmt->bindParam(':id', $resultatId, PDO::PARAM_INT);
    $stmt->execute();

    $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultat) {
        // Format and output the details as HTML
        echo '<h4>' . LIB_DetailsDuResultat . '</h4>';
        echo '<div class="row">';
        echo '  <div class="col-md-4 text-center">';
        echo '    <img src="uploads/' . htmlspecialchars($resultat['Photo_Etud']) . '" alt="' . LIB_Photo . '" class="img-fluid rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">';
        echo '    <h5>' . htmlspecialchars($resultat['Nom_Etud']) . ' ' . htmlspecialchars($resultat['Prenom_Etud']) . '</h5>';
        echo '  </div>';
        echo '  <div class="col-md-8">';
        echo '    <p><strong>' . LIB_Matricule . ':</strong> ' . htmlspecialchars($resultat['Matricule_Etud']) . '</p>';
        echo '    <p><strong>' . LIB_Email . ':</strong> ' . htmlspecialchars($resultat['Email_Etud']) . '</p>';
        echo '    <p><strong>' . LIB_Sexe . ':</strong> ' . htmlspecialchars($resultat['Sexe_Etud']) . '</p>';
        echo '    <p><strong>' . LIB_Note . ':</strong> <span class="badge bg-' . ($resultat['note'] >= 10 ? 'success' : 'danger') . '">' . htmlspecialchars($resultat['note']) . '/20</span></p>';
        echo '    <p><strong>' . LIB_DureeMin . ':</strong> ' . htmlspecialchars($resultat['duree']) . ' s</p>';
        echo '    <p><strong>' . LIB_DatePassage . ':</strong> ' . date('d/m/Y H:i', strtotime($resultat['date_passage'])) . '</p>';
        echo '    <p><strong>' . LIB_Filiere . ':</strong> ' . htmlspecialchars($resultat['Lib_Fil']) . '</p>';
        echo '    <p><strong>' . LIB_Niveau . ':</strong> ' . htmlspecialchars($resultat['Lib_Niv']) . '</p>';
        echo '    <p><strong>' . LIB_AnneeScolaire . ':</strong> ' . htmlspecialchars($resultat['Lib_AS']) . '</p>';
        echo '  </div>';
        echo '</div>';


    } else {
        // Use a language constant for the error message
        echo LIB_ResultatIntrouvable;
    }

} catch (PDOException $e) {
    // Use a language constant for the error message
    echo LIB_ErreurRecupDetailsResultat . ": " . $e->getMessage();
}
?>