<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_GET['id'] ?? 0;

// Inclure le header
include 'header_identifier.php';

// Vérifier si un administrateur
if (!$isAdmin) {
    header("Location: accueil.php");
    exit();
}

// Récupération étudiant avec jointures
$sql = "SELECT e.*, f.Lib_Fil, n.Lib_Niv, a.Lib_AS 
        FROM etudiants e
        LEFT JOIN filiere f ON e.Fil_Etud = f.Id_Fil
        LEFT JOIN niveaux n ON e.Niv_Etud = n.Id_Niv
        LEFT JOIN annee_sco a ON e.AS_Etud = a.Id_AS
        WHERE e.ID_Etud = ?";
$stmt = $lien->prepare($sql);
$stmt->execute([$id]);
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];

// Récupération des données pour les selects
$tables = [
    'annees_sco' => "SELECT Id_AS, Lib_AS FROM annee_sco",
    'niveaux' => "SELECT Id_Niv, Lib_Niv FROM niveaux", 
    'filieres' => "SELECT Id_Fil, Lib_Fil FROM filiere"
];

foreach ($tables as $var => $query) {
    $$var = $lien->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
?>


<head>
    <title><?= LIB_Modifier . ' ' . LIB_Etudiant ?></title>
    <style>
        .image-preview {
            border: 2px dashed #ddd;
            min-height: 200px;
            background-size: cover;
            background-position: center;
        }
        .image-preview:hover {
            cursor: pointer;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center"><?= LIB_Modifier . ' ' . LIB_Etudiant ?></h2>
        
        <form action="traiter_modifier_etudiant.php" method="post" enctype="multipart/form-data" class="border p-4 rounded shadow">
            <input type="hidden" name="ID_Etud" value="<?= $etudiant['ID_Etud'] ?? '' ?>">

            <div class="row g-4">
                <!-- Colonne Gauche -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Matricule ?></label>
                        <input type="text" name="Matricule" class="form-control" 
                               value="<?= htmlspecialchars($etudiant['Matricule_Etud'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Nom ?></label>
                        <input type="text" name="Nom" class="form-control" 
                               value="<?= htmlspecialchars($etudiant['Nom_Etud'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Prenom ?></label>
                        <input type="text" name="Prenom" class="form-control" 
                            value="<?= htmlspecialchars($etudiant['Prenom_Etud'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Login ?></label>
                        <input type="text" name="Login" class="form-control" 
                            value="<?= htmlspecialchars($etudiant['Login_Etud'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_MP ?></label>
                        <input type="text" name="MP" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Mail ?></label>
                        <input type="email" name="Mail" class="form-control" 
                            value="<?= htmlspecialchars($etudiant['Email_Etud'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Sexe ?></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Sexe" id="Sexe_M" value="M" 
                                    <?= ($etudiant['Sexe_Etud'] ?? '') == 'M' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="Sexe_M"><?= LIB_Sexe_M ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Sexe" id="Sexe_F" value="F" 
                                    <?= ($etudiant['Sexe_Etud'] ?? '') == 'F' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="Sexe_F"><?= LIB_Sexe_F ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3" style="justify-self: center;">
                        <div class="image-preview"
                            style="background-image: url('uploads/<?= $etudiant['Photo_Etud'] ?? '' ?>');width: 140px; height: 180px; margin-top: 15px;"
                            onclick="document.getElementById('Photo').click()"></div>
                        <div class="file-input-wrapper">
                            <label for="Photo"><?php echo LIB_Photo; ?></label>
                            <input type="file" id="Photo" name="Photo" accept="image/*" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Filiere ?></label>
                        <select name="Filiere" class="form-select" required>
                            <option value=""><?= LIB_SelectionnerFiliere ?></option>
                            <?php foreach ($filieres as $f): ?>
                            <option value="<?= $f['Id_Fil'] ?>" <?= ($f['Id_Fil'] == ($etudiant['Fil_Etud'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['Lib_Fil']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_AnneeScolaire ?></label>
                        <select name="AS" class="form-select" required>
                            <option value=""><?= LIB_SelectionnerAS ?></option>
                            <?php foreach ($annees_sco as $a): ?>
                            <option value="<?= $a['Id_AS'] ?>" <?= ($a['Id_AS'] == ($etudiant['AS_Etud'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['Lib_AS']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= LIB_Niveau ?></label>
                        <select name="Niveau" class="form-select" required>
                            <option value=""><?= LIB_SelectionnerNiveau ?></option>
                            <?php foreach ($niveaux as $n): ?>
                            <option value="<?= $n['Id_Niv'] ?>" <?= ($n['Id_Niv'] == ($etudiant['Niv_Etud'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($n['Lib_Niv']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i><?= LIB_Modifier ?>
                        </button>
                        <a href="gestion_etudiants.php" class="btn btn-secondary px-4">
                            <i class="fas fa-times me-2"></i><?= LIB_Annuler ?>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview d'image dynamique
        document.getElementById('Photo').addEventListener('change', function(e) {
            const preview = document.querySelector('.image-preview');
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.style.backgroundImage = `url(${e.target.result})`;
                preview.querySelector('div').style.display = 'none';
            }

            if (file) reader.readAsDataURL(file);
        });
    </script>
</body>
</html>