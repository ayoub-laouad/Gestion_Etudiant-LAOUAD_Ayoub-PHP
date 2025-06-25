<?php
// Inclure le header
include 'header_simple.php';

// Vérifier si un administrateur
if (!$isAdmin) {
    header("Location: accueil.php");
    exit();
}

// Récupérer les données pour les select (Année Scolaire, Niveau, Filière)
try {
    $stmt_AS = $lien->query("SELECT Id_AS, Lib_AS FROM annee_sco");
    $annees_sco = $stmt_AS->fetchAll(PDO::FETCH_ASSOC);

    $stmt_Niv = $lien->query("SELECT Id_Niv, Abr_Niv, Lib_Niv FROM niveaux");
    $niveaux = $stmt_Niv->fetchAll(PDO::FETCH_ASSOC);

    $stmt_Fil = $lien->query("SELECT Id_Fil, Abr_Fil, Lib_Fil FROM filiere");
    $filieres = $stmt_Fil->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer la liste des étudiants
    $stmt_Etud = $lien->query("SELECT etudiants.*, filiere.Lib_Fil, annee_sco.Lib_AS, niveaux.Lib_Niv FROM etudiants 
                                LEFT JOIN filiere ON etudiants.Fil_Etud = filiere.Id_Fil
                                LEFT JOIN annee_sco ON etudiants.AS_Etud = annee_sco.Id_AS
                                LEFT JOIN niveaux ON etudiants.Niv_Etud = niveaux.Id_Niv");
    $etudiants = $stmt_Etud->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fonction pour afficher la liste des étudiants dans un tableau
function afficherListeEtudiants($etudiants, $lang, $lien) {
    echo '<div style="overflow-x:auto;">
            <table class="Etu_table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>' . LIB_Matricule . '</th>
                    <th>' . LIB_Nom . '</th>
                    <th>' . LIB_Prenom . '</th>
                    <th>' . LIB_AnneeScolaire . '</th>
                    <th>' . LIB_Filiere . '</th>
                    <th>' . LIB_Niveau . '</th>
                    <th>' . LIB_Photo . '</th>
                    <th style="text-align: center;">' . LIB_Actions . '</th>
                </tr>
            </thead>
            <tbody>';
    $i = 1;
    foreach ($etudiants as $etudiant) {
        echo '<tr>
                <td>' . htmlspecialchars($etudiant['ID_Etud']) . '</td>
                <td>' . htmlspecialchars($etudiant['Matricule_Etud']) . '</td>
                <td>' . htmlspecialchars($etudiant['Nom_Etud']) . '</td>
                <td>' . htmlspecialchars($etudiant['Prenom_Etud']) . '</td>
                <td>' . htmlspecialchars($etudiant['Lib_AS']) . '</td>
                <td>' . htmlspecialchars($etudiant['Lib_Fil']) . '</td>
                <td>' . htmlspecialchars($etudiant['Lib_Niv']) . '</td>
                <td><img src="uploads/' . htmlspecialchars($etudiant['Photo_Etud']) . '" alt="Photo" style="width: 45px; height: 55px;"></td>
                <td>
                    <button type="button" style="background-color: rgb(34, 172, 19);" class="styled-button details-btn" 
                        data-bs-toggle="modal" data-bs-target="#detailsModal" data-lang="' . $_SESSION['lang'] . '" data-id="' . $etudiant['ID_Etud'] . '">
                        <i class="fa fa-info-circle" style="margin: 5px;"></i>' . LIB_Details . '</button>
                    <button type="button" class="styled-button" style="background-color: rgb(0, 20, 196);" '
                        . 'onclick="window.location.href=\'modifier_etudiant.php?id=' . $etudiant['ID_Etud'] . '\'">'
                        . '<i class="fa fa-edit"></i> ' . LIB_Modifier .
                    '</button>
                    <button type="button" class="styled-button" style="background-color: rgb(181, 0, 0);" '
                        . 'onclick="if(confirm(\'' . LIB_ConfirmationSuppression . '\')) window.location.href=\'supprimer_etudiant.php?id=' . $etudiant['ID_Etud'] . '\'">'
                        . '<i class="fa fa-trash"></i> ' . LIB_Supprimer .
                    '</button>
                </td>
              </tr>';
    }
    echo '</tbody>
            </table>
            </div>';
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['Photo']) && isset($_POST['ajouterEtudiant'])) {
    // Inclure le fichier de traitement des étudiants
    include 'traiter_etudiant.php';
    exit();
}

?>


<head>
    <title><?php echo LIB_GestionEtudiants; ?></title>
</head>
<body>
    <div class="container">
        <form action="" method="post" enctype="multipart/form-data">
            <table>
                <thead>
                    <th colspan="4"><img src="img/INSEA_logo.png" style="width: 4%;"> INSEA : <?php echo LIB_GestionEtudiants; ?></th>
                </thead>
                <tr>
                    <td>
                        <label for="Matricule"><?php echo LIB_Matricule; ?>:</label>
                        <input type="text" name="Matricule" id="Matricule">
                    </td>
                    <td>
                        <label for="Nom"><?php echo LIB_Nom; ?>:</label>
                        <input type="text" name="Nom" id="Nom">
                    </td>
                    <td>
                        <label for="Prenom"><?php echo LIB_Prenom; ?>:</label>
                        <input type="text" name="Prenom" id="Prenom">
                    </td>
                    <td rowspan="3" style="width: 200px;">
                        <?php if (isset($_FILES['Photo']) && $_FILES['Photo']['name'] != "") {
                            $target_dir = "uploads/";
                            $target_file = $target_dir . basename($_FILES["Photo"]["name"]);
                            $_SESSION['Photo']=$_FILES['Photo'];
                            move_uploaded_file($_FILES["Photo"]["tmp_name"], $target_file);
                            echo '<img src="' . $target_file . '" alt="Photo" style="width: 140px; height: 180px;">';
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Filiere"><?php echo LIB_Filiere; ?>:</label>
                        <select name="Filiere" id="Filiere">
                            <option value=""><?php echo LIB_SelectionnerFiliere; ?></option>
                            <?php foreach ($filieres as $filiere): ?>
                                <option value="<?php echo $filiere['Id_Fil']; ?>"><?php echo htmlspecialchars($filiere['Abr_Fil']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <label for="AS"><?php echo LIB_AnneeScolaire; ?>:</label>
                        <select name="AS" id="AS">
                            <option value=""><?php echo LIB_SelectionnerAS; ?></option>
                            <?php foreach ($annees_sco as $annee): ?>
                                <option value="<?php echo $annee['Id_AS']; ?>"><?php echo htmlspecialchars($annee['Lib_AS']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <label for="Niveau"><?php echo LIB_Niveau; ?>:</label>
                        <select name="Niveau" id="Niveau">
                            <option value=""><?php echo LIB_SelectionnerNiveau; ?></option>
                            <?php foreach ($niveaux as $niveau): ?>
                                <option value="<?php echo $niveau['Id_Niv']; ?>"><?php echo htmlspecialchars($niveau['Abr_Niv']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span><?php echo LIB_Sexe; ?>:</span>
                        <input type="radio" name="Sexe" id="Sexe_M" value="M">
                        <label for="Sexe_M"><?php echo LIB_Sexe_M; ?></label>
                        <input type="radio" name="Sexe" id="Sexe_F" value="F">
                        <label for="Sexe_F"><?php echo LIB_Sexe_F; ?></label>
                    </td>
                    <td>
                        <label for="Mail"><?php echo LIB_Mail; ?>:</label>
                        <input type="email" name="Mail" id="Mail">
                    </td>
                    <td>
                        <label for="Login"><?php echo LIB_Login; ?>:</label>
                        <input type="text" name="Login" id="Login">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="submit" name="ajouterEtudiant" value="<?php echo LIB_Ajouter; ?>" class="styled-button">
                        <button type="button" onclick="afficherListe()" class="styled-button"><?php echo LIB_Lister; ?></button>
                        <input type="reset" value="<?php echo LIB_Annuler; ?>" class="styled-button">
                    </td>
                    <td>
                        <label for="MP"><?php echo LIB_Password; ?>:</label>
                        <input type="password" name="MP" id="MP">
                    </td>
                    <td>
                        <form action="#" method="post">
                            <div class="file-input-wrapper">
                                <label for="Photo"><?php echo LIB_Photo; ?></label>
                                <input type="file" id="Photo" name="Photo" accept="image/*" />
                            </div>
                            <input type="submit" name="Photo" style="display: none;" value="submit" id="submitPhoto">
                        </form>
                    </td>
                </tr>
            </table>
        </form>

        <div id="listeEtudiantsModal" class="modal1" style="display: none;">
            <div class="modal-content1">
                <button type="button" class="btn-close close" aria-label="Close" style="margin: 10px;"></button>
                <?php afficherListeEtudiants($etudiants, $_SESSION['lang'], $lien); ?>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true" data-backdrop="false" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content portfolio-modal">
                <div class="modal-header portfolio-modal-header">
                    <h5 class="modal-title portfolio-modal-title" id="detailsModalLabel"><?php echo LIB_DetailsEtudiant; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body portfolio-modal-body" id="detailsModalBody">
                    <div class="portfolio-item">
                        <div class="portfolio-item-header">
                            <img src="" alt="Photo de l'étudiant" id="studentPhoto" class="student-portfolio-photo">
                            <h3 id="studentFullName" class="student-portfolio-name"></h3>
                        </div>
                        <div class="portfolio-item-body">
                            <p><strong><?php echo LIB_Matricule; ?>:</strong> <span id="studentMatricule"></span></p>
                            <p><strong><?php echo LIB_Mail; ?>:</strong> <span id="studentEmail"></span></p>
                            <p><strong><?php echo LIB_Sexe; ?>:</strong> <span id="studentSexe"></span></p>
                            <p><strong><?php echo LIB_Filiere; ?>:</strong> <span id="studentFiliere"></span></p>
                            <p><strong><?php echo LIB_Niveau; ?>:</strong> <span id="studentNiveau"></span></p>
                            <p><strong><?php echo LIB_AnneeScolaire; ?>:</strong> <span id="studentAS"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <footer class="text-center mt-4">
        <p>&copy; 2025 <?php echo LIB_NomProjet; ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
