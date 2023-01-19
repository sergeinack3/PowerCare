/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

VitaleVision = {
  xmlText: '',
  xmlDocument: null,
  applet: null,
  modalWindow: null,

  // Lecture du contenu de la carte et lancement d'une fonction après la lecture terminée
  getContent: function(callback){
    if (VitaleVision.applet && VitaleVision.applet.performRead() == "OK") {
      setTimeout(function(){
        VitaleVision.xmlText = VitaleVision.applet.getContent() + '';
        if (callback) callback();
      }, 800);
    }
  },

  // Lecture du contenu XML et création du document XML
  parseContent: function(){
    VitaleVision.xmlText = VitaleVision.xmlText.strip();

    // Firefox, Mozilla, Opera, etc.
    try {
      VitaleVision.xmlDocument = new DOMParser().parseFromString(VitaleVision.xmlText, "text/xml");
    }
    catch(e) {
      // IE
      try {
        VitaleVision.xmlDocument = new ActiveXObject("Microsoft.XMLDOM");
        VitaleVision.xmlDocument.async = false;
        VitaleVision.xmlDocument.loadXML(VitaleVision.xmlText);
      } catch(e) {
        console.log(e.message);
        return;
      }
    }

    function cleanWhitespace(node) {
      var i, notWhitespace = /\S/;
      for (i = 0; i < node.childNodes.length; i++) {
        var childNode = node.childNodes[i];
        if ((childNode.nodeType == 3) && (!notWhitespace.test(childNode.nodeValue))) {
          // that is, if it's a whitespace text node
          node.removeChild(node.childNodes[i]);
          i--;
        }
        if ( childNode.nodeType == 1) {
          // elements can have text child nodes of their own
          cleanWhitespace(childNode);
        }
      }
    }
    cleanWhitespace(VitaleVision.xmlDocument);
  },

  getDate: function(str) {
    var jour  = str.substring(0, 2);
    var mois  = str.substring(2, 4);
    var annee = str.substring(4, 8);

    var date = {locale: null, iso: null};
    if(jour){
      date.locale = jour + "/" + mois + "/" + annee;
      date.iso    = annee + "-" + mois + "-" + jour;
    }
    return date;
  },

  // Lancement de la lecture de la carte vitale
  read: function() {
    VitaleVision.getContent(VitaleVision.parseContent);

    var i, beneficiaireSelect = $('modal-beneficiaire-select'), listBeneficiaires;

    setTimeout(function(){
    try {
      listBeneficiaires = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes;
      if (listBeneficiaires.length > 0) {
        beneficiaireSelect.update();

        for (i = 0; i < listBeneficiaires.length; i++) {
          var ident = listBeneficiaires[i].getElementsByTagName("ident")[0],
              nom = getNodeValue("nomUsuel", ident),
              prenom = getNodeValue("prenomUsuel", ident),
              option = DOM.option({value: i}, nom+' '+prenom);

          var amo = listBeneficiaires[i].getElementsByTagName("amo")[0],
              finPeriodeDroits = VitaleVision.getDate(getNodeValue("listePeriodesDroits element fin", amo));

          if(finPeriodeDroits.iso) {
            var date = Date.fromDATE(finPeriodeDroits.iso);
            if (date < new Date()) {
              option.setStyle({color: "red", fontWeight: "bold"});
              option.innerHTML += " (période de droits terminée)";
            }
          }

          beneficiaireSelect.insert(option);
        }

        if (listBeneficiaires.length == 1) {
          $('msg-multiple-benef').hide();
          beneficiaireSelect.hide();
          $('msg-confirm-benef').show();
          $$('#benef-nom span')[0].update(beneficiaireSelect.options[0].innerHTML);
        }
        VitaleVision.modalWindow = Modal.open($('modal-beneficiaire'), {
          title : "Choix du bénéficiaire",
          width: 400,
          height: 200
        });
      }
    }
    catch (e) {
      alert('Erreur lors de la lecture de la carte vitale, veuillez la ré-insérer.');
      return;
    }}, 1000);
  },

  // Remplissage du formulaire de recherche en fonction du bénéficiaire sélectionné dans la fenetre modale
  search: function(form, id) {
    var benef = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes[id],
        ident = benef.getElementsByTagName("ident")[0];

    $V(form.elements.nom, getNodeValue("nomUsuel", ident));
    $V(form.elements.prenom, getNodeValue("prenomUsuel", ident));

    // For the patient selector TODO: change the field names !
    $V(form.elements.name, getNodeValue("nomUsuel", ident));
    $V(form.elements.firstName, getNodeValue("prenomUsuel", ident));

    if(getNodeValue("naissance date", ident) != "") { // Si format FR
      var dateNaissance = getNodeValue("naissance date", ident),
          jour  = dateNaissance.substring(0, 2),
          mois  = dateNaissance.substring(2, 4);

      if(dateNaissance.length == 8){
        var annee = dateNaissance.substring(4, 8);
      } else {
        var annee = dateNaissance.substring(4, 6),
            an = new Date().getFullYear();

        annee = (("20"+annee > an) ? "19" : "20")+annee;
      }
    } else { // Si format ISO
      var dateNaissance = getNodeValue("naissance dateEnCarte", ident);
      if(dateNaissance.length == 8){
        var jour  = dateNaissance.substring(6, 8),
            mois  = dateNaissance.substring(4, 6),
            annee = dateNaissance.substring(0, 4);
      } else {
        var jour  = dateNaissance.substring(4, 6),
            mois  = dateNaissance.substring(2, 4),
            annee = dateNaissance.substring(0, 2),
            an = new Date().getFullYear();

        annee = (("20"+annee > an) ? "19" : "20")+annee;
      }
    }

    $V(form.Date_Day, parseInt(jour));
    $V(form.Date_Month, mois);
    $V(form.Date_Year, annee);
    if (form.onsubmit) {
      form.onsubmit();
    }
    else {
      form.submit();
    }

  },

  // Remplissage du formulaire en fonction du bénéficiaire sélectionné dans la fenetre modale
  fillForm: function(form, id, update_administrative_data) {
    if (update_administrative_data === undefined) update_administrative_data = 0;

    var benef = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes[id],
        ident = benef.getElementsByTagName("ident")[0],
        amo = benef.getElementsByTagName("amo")[0],
        c2s = benef.getElementsByTagName("cmu")[0];

    form.insert(DOM.input({type: 'hidden', name: 'date_lecture_vitale', value: 'now'}));
    $V(form.matricule, getNodeValue("nir", ident));

    var nir_certifie = getNodeValue("nirCertifie", ident);
    var qualBenef = parseInt(getNodeValue("qualBenef", amo));

    if (nir_certifie.length === 0 && qualBenef === 0) {
      nir_certifie = getNodeValue("nir", ident);
    }

    $V(form._vitale_nir_certifie, nir_certifie);
    $V(form.matricule_certifie, nir_certifie);

    tabs.setActiveTab('identite');
    $(form.matricule).focus(); // Application du mask

    if (update_administrative_data) {
      var nom = getNodeValue("nomUsuel", ident);
      $V(form._vitale_lastname, nom);
      $V(form.nom, nom);
      var prenom = getNodeValue("prenomUsuel", ident);
      $V(form.prenom, prenom);
      $V(form._vitale_firstname, prenom);

      if((getNodeValue("nomPatronymique", ident) != "") && (getNodeValue("nomUsuel", ident) != getNodeValue("nomPatronymique", ident))) {
        $V(form.nom_jeune_fille, getNodeValue("nomPatronymique", ident));
      }
      var date = getNodeValue("naissance date", ident);
      if(date != "") { // Si format FR
        var dateNaissance = getNodeValue("naissance date", ident),
            jour  = dateNaissance.substring(0, 2),
            mois  = dateNaissance.substring(2, 4);

        if(dateNaissance.length == 8){
          var annee = dateNaissance.substring(4, 8);
        } else {
          var annee = dateNaissance.substring(4, 6),
              an = new Date().getFullYear();

          annee = (("20"+annee > an) ? "19" : "20")+annee;
        }
      } else { // Si format ISO
        var dateNaissance = getNodeValue("naissance dateEnCarte", ident);
        date = dateNaissance;
        if(dateNaissance.length == 8){
          var jour  = dateNaissance.substring(6, 8),
              mois  = dateNaissance.substring(4, 6),
              annee = dateNaissance.substring(0, 4);
        } else {
          var jour  = dateNaissance.substring(4, 6),
              mois  = dateNaissance.substring(2, 4),
              annee = dateNaissance.substring(0, 2),
              an = new Date().getFullYear();

          annee = (("20"+annee > an) ? "19" : "20")+annee;
        }
      }

      $V(form.naissance, jour + "/" + mois + "/" + annee);
      $V(form._vitale_birthdate, date);

      if($V(form.adresse) == ""){
        $V(form.adresse, (getNodeValue("adresse ligne1", ident) + "\r\n" +
                          getNodeValue("adresse ligne2", ident) + "\r\n" +
                          getNodeValue("adresse ligne3", ident) + "\r\n" +
                          getNodeValue("adresse ligne4", ident)).strip());
      }

      if (getNodeValue("qualBenef", amo) == 0) {
        var sexe, first = $V(form.assure_matricule).charAt(0);
        if (first == '1' || first == '2') {
          // Gestion des codes provisoires commencant par 3, 4, 7 ou 8
          $V(form.sexe, first == '1' ? 'm' : 'f');
        }
      }

      var ville = getNodeValue("adresse ligne5", ident);
      if($V(form.cp) == "")    $V(form.cp, ville.substring(0, 5));
      if($V(form.ville) == "") $V(form.ville, ville.substring(6));
    }

    $V(form.rang_naissance, getNodeValue("rangDeNaissance", ident));
    $V(form.qual_beneficiaire, parseInt(getNodeValue("qualBenef", amo)));

    $V(form.code_regime,  getNodeValue("codeRegime", amo));
    $V(form.caisse_gest,  getNodeValue("caisse", amo));
    $V(form.centre_gest,  getNodeValue("centreGestion", amo));
    $V(form.code_gestion, getNodeValue("codeGestion", amo));
    $V(form.centre_carte, getNodeValue("centreCarte", amo));

    var periodeDroits = VitaleVision.getDate(getNodeValue("listePeriodesDroits element debut", amo));
    if(periodeDroits.iso) {
      $V(form.deb_amo_da, periodeDroits.locale);
      $V(form.deb_amo, periodeDroits.iso);
    }

    periodeDroits = VitaleVision.getDate(getNodeValue("listePeriodesDroits element fin", amo));
    if(periodeDroits.iso) {
      $V(form.fin_amo_da, periodeDroits.locale);
      $V(form.fin_amo, periodeDroits.iso);
    }

    var libelleExo = getNodeValue("libelleExo", amo).replace(/\\r\\n/g, "\n");
    var codeExo = 0;

    // @todo: voir à recuperer cette liste directment depuis CPatient::$code_exo_guess
    var codeExoGuess = {
      "4":[
        "affection",
        "ald",
        "hors liste"],
      "5":[
        "rente AT",
        "pension d'invalidit",
        "pension militaire",
        "enceinte",
        "maternit"],
      "9":[
        "FSV",
        "FNS",
        "vieillesse"]
    };

    $H(codeExoGuess).each(function(pair){
      pair.value.each(function(rule){
        if(codeExo == 0 && libelleExo.match(new RegExp(rule, "i"))) {
          codeExo = pair.key;
        }
      });
    });

    $V(form.code_exo, codeExo);
    $V(form.libelle_exo, libelleExo);

    $V(form.medecin_traitant_declare, (getNodeValue("medecinTraitant", amo) == "Oui") ? '1' : '0');
    $V(form.c2s, (getNodeValue("typeCMU", c2s) != "") ? '1' : '0');
    //calculFinAmo(); ?

    var i, benefList = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes,
        ident,
        amo = benefList[id].getElementsByTagName("amo")[0];

    if(getNodeValue("qualBenef", amo) != 0) {
      for(i = 0; i < VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes.length; i++){
        if(getNodeValue("qualBenef", benefList[i].getElementsByTagName("amo")[0]) == 0){
          id = i;
        }
      }
    }
    benef = benefList[id];
    ident = benef.getElementsByTagName("ident")[0];
    amo = benef.getElementsByTagName("amo")[0];

    $V(form.assure_nom, getNodeValue("nomUsuel", ident));
    $V(form.assure_prenom, getNodeValue("prenomUsuel", ident));

    if((getNodeValue("nomPatronymique", ident) != "") && (getNodeValue("nomUsuel", ident) != getNodeValue("nomPatronymique", ident))) {
      $V(form.nom_jeune_fille, getNodeValue("nomPatronymique", ident));
    }

    if(getNodeValue("naissance date", ident) != "") { // Si format FR
      var dateNaissance = getNodeValue("naissance date", ident),
          jour  = dateNaissance.substring(0, 2),
          mois  = dateNaissance.substring(2, 4);

      if(dateNaissance.length == 8){
        var annee = dateNaissance.substring(4, 8);
      } else {
        var annee = dateNaissance.substring(4, 6),
            an = new Date().getFullYear();

        annee = (("20"+annee > an) ? "19" : "20")+annee;
      }
    } else { // Si format ISO
      var dateNaissance = getNodeValue("naissance dateEnCarte", ident);

      if(dateNaissance.length == 8){
        var jour  = dateNaissance.substring(6, 8),
            mois  = dateNaissance.substring(4, 6),
            annee = dateNaissance.substring(0, 4);
      } else {
        var jour  = dateNaissance.substring(4, 6),
            mois  = dateNaissance.substring(2, 4),
            annee = dateNaissance.substring(0, 2),
            an = new Date().getFullYear();

        annee = (("20"+annee > an) ? "19" : "20")+annee;
      }
    }

    $V(form.assure_naissance, jour + "/" + mois + "/" + annee);

    $V(form.assure_matricule, getNodeValue("nir", ident));

    if (getNodeValue("qualBenef", amo) == 0) {
      var sexe, first = $V(form.assure_matricule).charAt(0);
      if (first == '1' || first == '2') {
        // Gestion des codes provisoires commencant par 3, 4, 7 ou 8
        $V(form.assure_sexe, first == '1' ? 'm' : 'f');
      }
    }
    tabs.changeTabAndFocus('assure', form.assure_nom);

    if($V(form.assure_adresse) == ""){
      $V(form.assure_adresse, (getNodeValue("adresse ligne1", ident) + "\r\n" +
                               getNodeValue("adresse ligne2", ident) + "\r\n" +
                               getNodeValue("adresse ligne3", ident) + "\r\n" +
                               getNodeValue("adresse ligne4", ident)).strip());
    }

    var ville = getNodeValue("adresse ligne5", ident);
    if($V(form.assure_cp) == "")    $V(form.assure_cp, ville.substring(0, 5));
    if($V(form.assure_ville) == "") $V(form.assure_ville, ville.substring(6));

    tabs.setActiveTab('assure');
    $(form.assure_matricule).focus(); // Application du masque

    tabs.setActiveTab('identite');
    $(form.nom).focus();
  },

  formatDateNaissance: function(ident) {
    var dateNaissance = getNodeValue("naissance date", ident),
        jour, mois, annee, an;
    if(dateNaissance != "") { // Si format FR
      jour  = dateNaissance.substring(0, 2);
      mois  = dateNaissance.substring(2, 4);

      if(dateNaissance.length == 8){
        annee = dateNaissance.substring(4, 8);
      } else {
        annee = dateNaissance.substring(4, 6);
        an = new Date().getFullYear();

        annee = (("20"+annee > an) ? "19" : "20")+annee;
      }
    } else { // Si format ISO
      dateNaissance = getNodeValue("naissance dateEnCarte", ident);
      if(dateNaissance.length == 8){
        jour  = dateNaissance.substring(6, 8);
        mois  = dateNaissance.substring(4, 6);
        annee = dateNaissance.substring(0, 4);
      } else {
        jour  = dateNaissance.substring(4, 6);
        mois  = dateNaissance.substring(2, 4);
        annee = dateNaissance.substring(0, 2);
        an = new Date().getFullYear();

        annee = (("20"+annee > an) ? "19" : "20")+annee;
      }
    }
    return jour + "/" + mois + "/" + annee;
  },

  prepareUpdatePatient: function(id, patient_id, behavior) {
    var benef = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes[id],
        ident = benef.getElementsByTagName("ident")[0],
        amo = benef.getElementsByTagName("amo")[0],
        c2s = benef.getElementsByTagName("cmu")[0];

    var nom_jeune_fille = "";
    if((getNodeValue("nomPatronymique", ident) != "") && (getNodeValue("nomUsuel", ident) != getNodeValue("nomPatronymique", ident))) {
      nom_jeune_fille = getNodeValue("nomPatronymique", ident);
    }

    var adresse = (getNodeValue("adresse ligne1", ident) + "\r\n" +
                        getNodeValue("adresse ligne2", ident) + "\r\n" +
                        getNodeValue("adresse ligne3", ident) + "\r\n" +
                        getNodeValue("adresse ligne4", ident)).strip();
    var ville = getNodeValue("adresse ligne5", ident);

    var sexe = '';
    if (getNodeValue("qualBenef", amo) == 0) {
      var sexe, first = getNodeValue("nir", ident).charAt(0);
      if (first == '1' || first == '2') {
        // Gestion des codes provisoires commencant par 3, 4, 7 ou 8
        sexe = first == '1' ? 'm' : 'f';
      }
    }

    var libelleExo = getNodeValue("libelleExo", amo).replace(/\\r\\n/g, "\n");
    var codeExo = 0;

    // @todo: voir à recuperer cette liste directment depuis CPatient::$code_exo_guess
    var codeExoGuess = {
      "4":[
        "affection",
        "ald",
        "hors liste"],
      "5":[
        "rente AT",
        "pension d'invalidit",
        "pension militaire",
        "enceinte",
        "maternit"],
      "9":[
        "FSV",
        "FNS",
        "vieillesse"]
    };

    $H(codeExoGuess).each(function(pair){
      pair.value.each(function(rule){
        if(codeExo == 0 && libelleExo.match(new RegExp(rule, "i"))) {
          codeExo = pair.key;
        }
      });
    });

    var i, benefList = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes,
      benefIdent,
      benefAmo = benefList[id].getElementsByTagName("amo")[0];

    if(getNodeValue("qualBenef", amo) != 0) {
      for(i = 0; i < VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes.length; i++){
        if(getNodeValue("qualBenef", benefList[i].getElementsByTagName("amo")[0]) == 0){
          id = i;
        }
      }
    }
    benef = benefList[id];
    benefIdent = benef.getElementsByTagName("ident")[0];
    benefAmo = benef.getElementsByTagName("amo")[0];

    var assure_nom_jeune_fille = '';
    if((getNodeValue("nomPatronymique", benefIdent) != "") && (getNodeValue("nomUsuel", benefIdent) != getNodeValue("nomPatronymique", benefIdent))) {
      assure_nom_jeune_fille = getNodeValue("nomPatronymique", benefIdent);
    }

    var assure_sexe = '';
    if (getNodeValue("qualBenef", benefAmo) == 0) {
      var first = getNodeValue("nir", benefIdent).charAt(0);
      if (first == '1' || first == '2') {
        // Gestion des codes provisoires commencant par 3, 4, 7 ou 8
        assure_sexe = first == '1' ? 'm' : 'f';
      }
    }

    var assure_adresse =  (getNodeValue("adresse ligne1", benefIdent) + "\r\n" +
        getNodeValue("adresse ligne2", benefIdent) + "\r\n" +
        getNodeValue("adresse ligne3", benefIdent) + "\r\n" +
        getNodeValue("adresse ligne4", benefIdent)).strip();

    var assure_ville = getNodeValue("adresse ligne5", benefIdent);

    var vitale_data = {
      nom: getNodeValue("nomUsuel", ident),
      prenom: getNodeValue("prenomUsuel", ident),
      nom_jeune_fille: nom_jeune_fille,
      naissance: this.formatDateNaissance(ident),
      sexe: sexe,
      adresse: adresse,
      cp: ville.substring(0, 5),
      ville: ville.substring(6),
      matricule: getNodeValue("nir", ident),
      rang_naissance: getNodeValue("rangDeNaissance", ident),
      qual_beneficiaire: getNodeValue("qualBenef", amo),
      code_regime: getNodeValue("codeRegime", amo),
      caisse_gest: getNodeValue("caisse", amo),
      centre_gest: getNodeValue("centreGestion", amo),
      code_gestion: getNodeValue("codeGestion", amo),
      centre_carte: getNodeValue("centreCarte", amo),
      deb_amo: VitaleVision.getDate(getNodeValue("listePeriodesDroits element debut", amo)).iso,
      fin_amo: VitaleVision.getDate(getNodeValue("listePeriodesDroits element fin", amo)).iso,
      code_exo: codeExo,
      medecin_traitant_declare: getNodeValue("medecinTraitant", amo) == "Oui" ? '1' : '0',
      c2s: getNodeValue("typeCMU", c2s) != "" ? '1' : '0',
      assure_nom: getNodeValue("nomUsuel", benefIdent),
      assure_prenom: getNodeValue("prenomUsuel", benefIdent),
      assure_nom_jeune_fille: assure_nom_jeune_fille,
      assure_naissance: this.formatDateNaissance(benefIdent),
      assure_sexe: assure_sexe,
      assure_matricule: getNodeValue("nir", benefIdent),
      assure_adresse: assure_adresse,
      assure_cp: assure_ville.substring(0, 5),
      assure_ville: assure_ville.substring(6)
    };

    var url = new Url('patients', 'ajax_prepare_update_patient_vitale_vision');
    url.addParam('vitale_data', Object.toJSON(vitale_data));
    url.addParam('patient_id', patient_id);
    url.requestModal(750, 630, {method: 'post', getParameters: {m: 'patients', a: 'ajax_prepare_update_patient_vitale_vision'}});
  }
};

// Mapping de l'applet à l'objet VitaleVision
VitaleVision.applet = document.resultVitaleVision;
console.log('VitaleVision.js');

// Fonction de récupération de données avec syntax pseudo XPath ultra simplifié, avec noeud de base
function getNodeValue(path, node) {
  var i, parts = path.split(' ');

  for (i = 0; i < parts.length && node; i++){
    node = node.getElementsByTagName(parts[i])[0];
  }
  if (!node) return '';
  return ((node.textContent || node.text)+'').strip();
}
