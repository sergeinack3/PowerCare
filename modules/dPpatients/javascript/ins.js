/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

INS = {
  testInsc: function (action) {
    var page = "";
    switch (action) {
      case "auto" :
        page = "ajax_test_insc_auto";
        break;
      case "saisi" :
        page = "ajax_test_insc_saisi";
        break;
      case "manuel" :
        INS.readCarte(function (data) {
          new Url("dPpatients", "ajax_test_insc_manuel")
            .addParam("listPerson", data)
            .requestUpdate("test_insc");
        });
        break;
      default:
        return false;
    }

    new Url("dPpatients", page)
      .requestUpdate("test_insc");
  },

  readCarte: function (callback) {
    var listPerson = [];
    switch (Preferences.LogicielLectureVitale) {
      case "vitaleVision":
        VitaleVision.getContent(VitaleVision.parseContent);
        setTimeout(function () {
          var listBeneficiaires = VitaleVision.xmlDocument.getElementsByTagName("listeBenef")[0].childNodes;

          for (var i = 0; i < listBeneficiaires.length; i++) {
            var person = {};
            var ident = listBeneficiaires[i].getElementsByTagName("ident")[0];
            var amo = listBeneficiaires[i].getElementsByTagName("amo")[0];
            person["date"] = getNodeValue("dateEnCarte", ident);

            if (person["date"].length === 0) {
              person["date"] = getNodeValue("date", ident);
            }

            person["prenom"] = getNodeValue("prenomUsuel", ident);
            person["nirCertifie"] = getNodeValue("nirCertifie", ident);
            var qualBenef = getNodeValue("qualBenef", amo);

            if (person["nirCertifie"].length === 0 && qualBenef === '0') {
              person["nirCertifie"] = getNodeValue("nir", ident);
            }

            person["nom"] = getNodeValue("nomUsuel", ident);
            listPerson.push(person);
          }
          callback(Object.toJSON(listPerson));
        }, 1000);
        break;
      case "mbHost":
        window.mbHostVitale = new VitaleCard();
        MbHost.call('card/vitale/read', null, function (result) {
          var listBeneficiaires = result.vitale.t_AsnDonneesVitale.listeBenef;

          for (var i = 0; i < listBeneficiaires.length; i++) {
            var person = {};
            var benef = listBeneficiaires[i];
            var ident = benef.ident;
            var amo = benef.amo;
            person["date"] = String(ident.naissance.dateEnCarte);

            if (person["date"] === 'undefined') {
              person["date"] = String(ident.naissance.date);
            }

            person["prenom"] = ident.prenomUsuel;
            person["nirCertifie"] = String(ident.nirCertifie);
            var qualBenef = String(amo.qualBenef.raw);

            if (person["nirCertifie"] === 'undefined' && qualBenef === "0") {
              person["nirCertifie"] = String(ident.nir);
            }

            if (person["nirCertifie"] === 'undefined') {
              person["nirCertifie"] = null;
            }

            person["nom"] = ident.nomUsuel;
            listPerson.push(person);
          }

          callback(Object.toJSON(listPerson));
        }, this.onNetworkError, null, 120000);
        break;
      default:
        return;
    }
  },

  /**
   * Modal pour lire le datamatrix INS
   *
   * @param search
   */
  openModalReadDatamatrixINS: function (search = 1) {
    new Url('dPpatients', 'openModalReadDatamatrixINS')
      .addParam('search', search)
      .requestModal();
  },

  /**
   * Recherche d'un patient à partir d'un datamatrix INS
   *
   *
   * @param form
   * @param search
   */
  readDatamatrixINS: function (form, search = 1) {
    new Url('dPpatients', 'readDatamatrixINS')
      .addParam('ins', $V(form.datamatrix_ins))
      .addParam('search', search)
      .requestUpdate('systemMsg');
  },

  /**
   * Remplir le formulaire de recherche de patient à partir du datamatrix INS
   *
   * @param params
   */
  fillFormSearchPatient: function (params) {
    Control.Modal.close();
    var form = getForm('find');
    $V(form.find_nom, params.nom);
    $V(form.find_prenom, params.prenoms);
    $V(form.find_Date_Day, params.jour);
    $V(form.find_Date_Month, params.mois);
    $V(form.find_Date_Year, params.annee);
    $V(form.find_sexe, params.sexe.toLowerCase());
    $V(form.find__matricule, params.matricule);
    $V(form.find_nom_jeune_fille, params.nom);
    $(ins_list_patient_button_search).click();
  },

  /**
   * Remplir les champs pour la création d'un patient
   *
   * @param params
   */
  createPatient: function (params) {
    Control.Modal.close();
    var form = getForm('editFrm');
    $V(form.editFrm_nom_jeune_fille, params.nom);
    $V(form.editFrm_prenoms, params.prenoms);
    $V(form.editFrm_prenom, params.premier_prenom);
    $V(form.editFrm_naissance, params.jour + "/" + params.mois + "/" + params.annee);
    $V(form.sexe, params.sexe.toLowerCase());
    $V(form.editFrm_matricule, params.matricule);
    if (params.commune_naissance_insee) {
      $V(form.editFrm_lieu_naissance, params.lieu_naissance);
      $V(form.editFrm__pays_naissance_insee, params.nom_pays_naissance_insee);
      $V(form.editFrm__source__pays_naissance_insee, params.pays_naissance_insee);
      $V(form.editFrm_commune_naissance_insee, params.commune_naissance_insee);
      $V(form.editFrm__code_insee, params.commune_naissance_insee);
      $V(form.editFrm_cp_naissance, params.cp_naissance);
    } else if (params.pays_naissance_insee) {
      $V(form.editFrm__pays_naissance_insee, params.nom_pays_naissance_insee);
      $V(form.editFrm__source__pays_naissance_insee, params.pays_naissance_insee);
      $V(form.editFrm__code_insee, params.code_insee);
      $V(form.editFrm_lieu_naissance, "");
      $V(form.editFrm_commune_naissance_insee, "");
      $V(form.editFrm_cp_naissance, "");
    } else {
      $V(form.editFrm__pays_naissance_insee, "");
      $V(form.editFrm__source__pays_naissance_insee, "");
      $V(form.editFrm_lieu_naissance, "");
      $V(form.editFrm_commune_naissance_insee, "");
      $V(form.editFrm_cp_naissance, "");
    }
  }
};
