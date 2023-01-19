/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatSelector = window.PatSelector || {
  sForm:       null,
  sFormEasy:   null,
  sId:         null,
  sView:       null,
  sSexe:       null,
  sName:       null,
  sFirstName:  null,
  sNom:        null,
  sPrenom:     null,
  sNaissance:  null,
  sTutelle:    null,
  sALD:        null,
  sId_easy:    null,
  sView_easy:  null,
  parturiente: null,
  sBirthYear:  null,
  sBirthMonth: null,
  sBirthDay:   null,
  options:     {
    width:     900,
    height:    600,
    useVitale: 0
  },
  prepared:    null,

  pop: function () {
    new Url("patients", "pat_selector")
      .addParam("useVitale", this.options.useVitale)
      .addParam("nom", this.sName)
      .addParam("prenom", this.sFirstName)
      .addParam("parturiente", this.parturiente)
      .addParam('Date_Year', this.sBirthYear)
      .addParam('Date_Month', this.sBirthMonth)
      .addParam('Date_Day', this.sBirthDay)
      .addParam('dateNaissance', this.sNaissance)
      .addParam('mode', "selector")
      .requestModal(this.options.width, this.options.height, this.options);
  },

  set: function (patient) {
    this.prepared = patient;
    this.prepared.id = patient.patient_id;
    this.prepared.view = patient._view;
    // Lancement de l'execution du set
    window.setTimeout(window.PatSelector.doSet, 1);
  },

  doSet: function () {
    var oForm = document[PatSelector.sForm];
    var oFormEasy = document[PatSelector.sFormEasy];

    // Alerte si le patient sélectionné est connu BHRe+
    if (PatSelector.prepared._bmr_bhre_status === "BHReP") {
      alert($T("CBMRBHRe-alert_BHReP"));
    }

    $V(oForm[PatSelector.sId], PatSelector.prepared.id);
    $V(oForm[PatSelector.sView], PatSelector.prepared.view);
    $V(oForm[PatSelector.sNom], PatSelector.prepared.nom);
    $V(oForm[PatSelector.sPrenom], PatSelector.prepared.prenom);
    $V(oForm[PatSelector.sSexe], PatSelector.prepared.sexe);
    $V(oForm[PatSelector.sNaissance], PatSelector.prepared.naissance);
    $V(oForm[PatSelector.sTutelle], PatSelector.prepared.tutelle);
    if (PatSelector.sALD) {
      $V(oForm[PatSelector.sALD], PatSelector.prepared.ald);
    }
    if (oFormEasy) {
      $V(oFormEasy[PatSelector.sId_easy], PatSelector.prepared.id);
      $V(oFormEasy[PatSelector.sView_easy], PatSelector.prepared.view);
    }

    if (window.Sejour) {
      Sejour.checkTutelle(PatSelector.prepared.id, PatSelector.prepared.tutelle);
    }
  },

  init: function () {
    alert("Selecteur non initialisé");
  },

  cancelFastSearch: function (e) {
    if (Event.key(e) == Event.KEY_ESC) {
      PatSelector.init();
    }
  },

  reset: function (form) {
    $V(form.nom, "");
    $V(form.Date_Day, "");
    $V(form.Date_Month, "");
    $V(form.Date_Year, "");
    $V(form.prenom, "");
    $V(form.patient_ipp, "");
  }
};
