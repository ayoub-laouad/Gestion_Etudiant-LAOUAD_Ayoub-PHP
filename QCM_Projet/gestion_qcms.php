<?php
// Inclure le header
include 'header_simple.php';

// Vérifier si un administrateur
if (!$isAdmin) {
    header("Location: accueil.php");
    exit();
}

// Récupérer la liste des filières pour le formulaire
try {
    $stmtFil = $lien->query("SELECT Id_Fil, Lib_Fil, Abr_Fil FROM filiere");
    $filieres = $stmtFil->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer la liste des niveaux pour le QCM (la table niveaux est référencée par qcm.niveau_qcm)
    $stmtNiv = $lien->query("SELECT Id_Niv, Lib_Niv, Abr_Niv FROM niveaux");
    $niveaux = $stmtNiv->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer la liste des QCM existants avec les libellés de filière et niveau
    $stmtQCM = $lien->query(
        "SELECT q.qcm_id, q.titre_qcm, q.niveau_qcm, q.date_creation, f.Lib_Fil, n.Lib_Niv 
         FROM qcm q
         JOIN filiere f ON q.id_filiere = f.Id_Fil
         JOIN niveaux n ON q.niveau_qcm = n.Id_Niv
         ORDER BY q.qcm_id ASC"
    );
    $qcms = $stmtQCM->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>


<head>
    <title><?php echo LIB_GestionQCMs; ?></title>
</head>
<body>
    <div class="container">
        <h2><?php echo LIB_GestionQCMs; ?></h2>
        <!-- Formulaire d'ajout d'un nouveau QCM -->
        <form action="traiter_qcm.php?action=1" method="post" class="mb-4">
            <div class="mb-3">
                <label for="titre_qcm" class="form-label"><?php echo LIB_TitreQCM; ?>:</label>
                <input type="text" class="form-control" id="titre_qcm" name="titre_qcm" required>
            </div>
            <div class="mb-3">
                <label for="niveau_qcm" class="form-label"><?php echo LIB_Niveau; ?>:</label>
                <select class="form-select" id="niveau_qcm" name="niveau_qcm" required>
                    <option value=""><?php echo LIB_SelectionnerNiveau; ?></option>
                    <?php foreach ($niveaux as $niv): ?>
                        <option value="<?php echo $niv['Id_Niv']; ?>">
                            <?php echo htmlspecialchars($niv['Lib_Niv']) . " (" . htmlspecialchars($niv['Abr_Niv']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_filiere" class="form-label"><?php echo LIB_Filiere; ?>:</label>
                <select class="form-select" id="id_filiere" name="id_filiere" required>
                    <option value=""><?php echo LIB_SelectionnerFiliere; ?></option>
                    <?php foreach ($filieres as $fil): ?>
                        <option value="<?php echo $fil['Id_Fil']; ?>">
                            <?php echo htmlspecialchars($fil['Lib_Fil']) . " (" . htmlspecialchars($fil['Abr_Fil']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo LIB_AjouterQCM; ?></button>
        </form>

        <!-- Affichage de la liste des QCM -->
        <h3><?php echo LIB_ListeQCMs; ?></h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo LIB_ID; ?></th>
                    <th><?php echo LIB_TitreQCM; ?></th>
                    <th><?php echo LIB_Niveau; ?></th>
                    <th><?php echo LIB_Filiere; ?></th>
                    <th><?php echo LIB_DateCreation; ?></th>
                    <th><?php echo LIB_Actions; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($qcms as $q): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($q['qcm_id']); ?></td>
                        <td><?php echo htmlspecialchars($q['titre_qcm']); ?></td>
                        <td><?php echo htmlspecialchars($q['Lib_Niv']); ?></td>
                        <td><?php echo htmlspecialchars($q['Lib_Fil']); ?></td>
                        <td><?php echo htmlspecialchars($q['date_creation']); ?></td>
                        <td>
                            <a href="resultat_qcm.php?id=<?php echo $q['qcm_id']; ?>" class="btn btn-success btn-sm">
                                <i class="fa-regular fa-eye"></i> <?php echo LIB_Voir; ?>
                            </a>
                            <a href="modifier_qcm.php?id=<?php echo $q['qcm_id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-edit"></i> <?php echo LIB_Modifier; ?>
                            </a>
                            <a href="traiter_qcm.php?action=4&id=<?php echo $q['qcm_id']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('<?php echo LIB_ConfirmationSuppressionQCM; ?>');">
                                <i class="fa fa-trash"></i> <?php echo LIB_Supprimer; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <footer class="text-center mt-4">
        <p>&copy; 2025 <?php echo LIB_NomProjet; ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
