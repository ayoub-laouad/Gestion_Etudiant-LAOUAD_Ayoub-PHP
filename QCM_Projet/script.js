// Ajout de code bootstrapJS
$('#detailsModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget); // Bouton qui a déclenché le modal
  var idEtudiant = button.data('id'); // Extraire l'ID de l'étudiant
  var lang = button.data('lang'); // Récupérer la langue de la session PHP

  // Utiliser AJAX pour récupérer les détails de l'étudiant
  $.ajax({
      url: 'get_etudiant_details.php?id=' + idEtudiant + '&lang=' + lang,
      type: 'GET',
      dataType: 'json', // S'assurer que la réponse est traitée comme du JSON
      success: function(data) {
          console.log(data); // Afficher les données reçues dans la console
          
          // Mettre à jour les éléments du modal avec les données reçues
          $('#studentPhoto').attr('src', 'uploads/' + data.Photo_Etud);
          $('#studentFullName').text(data.Nom_Etud + ' ' + data.Prenom_Etud);
          $('#studentMatricule').text(data.Matricule_Etud);
          $('#studentEmail').text(data.Email_Etud);
          $('#studentSexe').text(data.Sexe_Etud === 'M' ? 'Masculine' : 'Feminine');
          $('#studentFiliere').text(data.Lib_Fil);
          $('#studentNiveau').text(data.Lib_Niv);
          $('#studentAS').text(data.Lib_AS);
      },
      error: function(xhr, status, error) {
          $('#detailsModalBody').html('<p>Erreur lors du chargement des détails.</p>');
          console.error('Erreur AJAX:', error); // Afficher l'erreur dans la console
      }
  });
});
document.getElementById('Photo').addEventListener("change", function() {
      document.getElementById("submitPhoto").click();
  });
// Code bootstrapJS
$(document).ready(function(){
  $('#detailsModal').modal({
      keyboard: true,
      backdrop: "static",
      show:false,
  }).on('show.bs.modal', function(){
      // Code à exécuter avant l'affichage du modal
  }).on('shown.bs.modal', function(){
      // Code à exécuter après l'affichage du modal
  }).on('hidden.bs.modal', function(){
      // Code à exécuter après la fermeture du modal
  });
});

document.getElementById('Photo').addEventListener("change", function() {
  document.getElementById("submitPhoto").click();
});

// Récupérer le modal
var modal = document.getElementById("listeEtudiantsModal");

// Récupérer le bouton qui ouvre le modal
var btn = document.querySelector("button[onclick='afficherListe()']");

// Récupérer l'élément <span> qui ferme le modal
var span = document.getElementsByClassName("close")[0];

// Quand l'utilisateur clique sur le bouton, ouvrir le modal
function afficherListe() {
  modal.style.display = "block";
}

// Quand l'utilisateur clique sur <span> (x), fermer le modal
span.onclick = function() {
  modal.style.display = "none";
}

// Quand l'utilisateur clique en dehors du modal, le fermer
window.onclick = function(event) {
  if (event.target == modal) {
      modal.style.display = "none";
  }
}