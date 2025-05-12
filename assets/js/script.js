document.addEventListener("DOMContentLoaded", function () {
  var options = document.querySelectorAll(".option");

  options.forEach(function (option) {
    option.addEventListener("click", function () {
      if (!this.classList.contains("disabled")) {
        this.classList.add("disabled"); // Ajouter la classe disabled
      }
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  var options = document.querySelectorAll(".optionEtu");

  options.forEach(function (option) {
    option.addEventListener("click", function () {
      // Désactive tous les boutons
      options.forEach(function (otherOption) {
        otherOption.classList.remove("disabled");
      });

      // Active uniquement le bouton cliqué
      this.classList.add("disabled");
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  var option = document.querySelector(".option"); // Sélectionne uniquement le premier élément avec la classe "option"

  option.addEventListener("click", function () {
    if (!this.classList.contains("disabled")) {
      this.classList.add("disabled"); // Ajoute la classe disabled
    }
  });
});

function choix() {
  window.location.href = "listeLits";
}
function choixs() {
  window.location.href = "detailsLits";
}
function refreshValidation() {
  window.location.href = "validation";
}

// Fonction pour définir un cookie
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// Fonction pour récupérer la valeur d'un cookie
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function populateData() {
  var selectFac = document.getElementById("selectFac");
  var selectDep = document.getElementById("selectDep");
  var selectClasse = document.getElementById("selectClasse");
  var fac = selectFac.value;
  var dep = selectDep.value;
  var classe = selectClasse.value;
  // selectData.innerHTML = "";

  var newURL = "niveau.php?fac=" + fac + "&dep=" + dep + "&classe=" + classe+"&actu=0";
  window.history.pushState({ path: newURL }, "", newURL);

  // Sauvegarder la sélection dans un cookie
  setCookie("lastSelectedfac", fac, 30); // Le cookie expire dans 30 jours
  setCookie("lastSelectedep", dep, 30); // Le cookie expire dans 30 jours
  setCookie("lastSelecteClasse", classe, 30); // Le cookie expire dans 30 jours
}

// Au chargement initial, peuple les données en fonction de la valeur sélectionnée par défaut ou en fonction du cookie
window.onload = function () {
  var lastSelectedfac = getCookie("lastSelectedfac");
  var lastSelectedep = getCookie("lastSelectedep");
  var lastSelecteClasse = getCookie("lastSelecteClasse");
  var selectFac = document.getElementById("selectFac");
  var selectDep = document.getElementById("selectDep");
  var selectClasse = document.getElementById("selectClasse");
  if (lastSelectedfac) {
    selectFac.value = lastSelectedfac;
  }
  if (lastSelectedep) {
    selectDep.value = lastSelectedep;
  }
  if (lastSelecteClasse) {
    selectClasse.value = lastSelecteClasse;
  }
  populateData();
  selectFac.addEventListener("change", function () {
    populateData();
    document.getElementById("selectForm").submit();
  });
  // populateData();
  selectDep.addEventListener("change", function () {
    populateData();
    document.getElementById("selectForm").submit();
  });
  // populateData();
  selectClasse.addEventListener("change", function () {
    populateData();
    document.getElementById("selectForm").submit();
  });
};

//  filter dynamique
document.getElementById('filter').addEventListener('change', function() {
  document.getElementById('filterForm').submit();
});

// fonction des formulaire du wallet paiement
function checkInput() {
  const input = document.getElementById('numEtudiant').value;
  const submitBtn = document.getElementById('submitBtn');
  
  // Vérifier si l'entrée est composée exactement de neuf caractères alphanumériques en majuscule
  if (/^[A-Z0-9]{9}$/.test(input)) {
      submitBtn.disabled = false;
  } else {
      submitBtn.disabled = true;
  }
}

// Ajouter un écouteur d'événement pour valider l'entrée à chaque changement dans le champ de saisie
document.getElementById('numEtudiant').addEventListener('input', checkInput);

function validateInput() {
  const input = document.getElementById('numEtudiant').value;
  const inputMessage = document.getElementById('inputMessage');
  if (/^[A-Z0-9]{9}$/.test(input)) {
      inputMessage.textContent = '';
  } else {
      inputMessage.textContent = 'Neuf chiffres est requis';
  }
}
// Ajouter un écouteur d'événement pour valider l'entrée à chaque changement
document.getElementById('numEtudiant').addEventListener('input', validateInput);
function validateForm() {
  const input = document.getElementById('numEtudiant').value;
  if (/^[A-Z0-9]{9}$/.test(input)) {
      return true;
  } else {
      alert('Le numéro étudiant doit contenir au moins 7 chiffres.');
      return false;
  }
}

// traitement de la conversion du texte miniscule en majuscule dans les pages de recherche du numero de l'etudiant