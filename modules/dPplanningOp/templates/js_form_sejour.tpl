{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modurgence value=0}}
{{mb_default var=contextual_call value=0}}

{{assign var=ald_mandatory value="dPplanningOp CSejour ald_mandatory"|gconf}}

<script>

checkAld = function(){
  var oForm = getForm("editSejour");
  var url = new Url("planningOp", "ajax_check_ald");
  url.addParam("patient_id", $V(oForm.patient_id));
  url.addParam("sejour_id", $V(oForm.sejour_id));
  url.requestUpdate(SystemMessage.id,
    {
      insertion: function(receiver, text){
        {{if "dPplanningOp CSejour fields_display show_c2s_ald"|gconf}}
          $("ald_patient").update(text);
          var form_sejour = getForm('editSejour');
          if (form_sejour) {
            form_sejour.removeClassName('prepared');
            prepareForm(form_sejour);
          }
        {{/if}}
        {{if $conf.dPplanningOp.CSejour.easy_ald_c2s}}
          $("ald_patient_easy").update(text);

          var form_op_easy = getForm('editOpEasy');
          if (form_op_easy) {
            form_op_easy.removeClassName('prepared');
            prepareForm(form_op_easy);
          }
        {{/if}}
      }
    }
  );
};

updateTutelle = function() {

};

var Value = {
  // Synchronize elements value between Easy and Expert forms
  synchronize: function(element, expert, fire) {
    expert = expert || "editOp";
    if (fire === undefined) {
      fire = true;
    }

    var other = element.form.name == expert ?
      document.editOpEasy :
      document.forms[expert];

    if (other) {
      // Pour la synchro d'une date / heure, on met à jour la vue du champ
      if (element_da = element.form.elements[element.name + '_da']) {
        $V(other.elements[element_da.name], element_da.value);
      }

      // Pour la synchro du praticien, on met à jour la vue également
      if (element_view = element.form.elements[element.name + '_view']) {
        $V(other.elements[element_view.name], element_view.value);
      }

      // Pour la synchro d'une case à cocher, on met à jour à la vue
      if (element_checkbox = element.form.elements['__' + element.name]) {
        other.elements['__' + element.name].checked = element_checkbox.checked;
      }

      $V(other[element.name], element.value, fire);
      if (other[element.name]) {
        other[element.name].fire('ui:change');
      }
    }
  }
};

modifPrat = function() {
  var oForm = document.editSejour;

  if (oForm._protocole_prescription_chir_id) {
    $V(oForm._protocole_prescription_chir_id, "");
  }
  var libelle = $("editSejour_libelle_protocole");
  if (libelle) {
    libelle.value = "";
  }
};

refreshViewProtocoleAnesth = function(prescription_id) {
  if($("prot_anesth_view")) {
    var url = new Url("dPplanningOp", "httpreq_vw_protocole_anesth");
    url.addParam("prescription_id", prescription_id);
    url.requestUpdate("prot_anesth_view");
  }
};

checkDureeHospi = function(sType) {
  var oForm = getForm("editSejour");
  oTypeField  = oForm.type;
  if (oForm.type_no_check && $V(oForm.type_no_check) === '1') {
    oTypeField = oForm.type_no_check;
  }
  oDureeField = oForm._duree_prevue;
  if (oForm._date_entree_reelle && oForm._date_sortie_reelle) {
    var _date_entree_reelle = new Date($V(oForm._date_entree_reelle));
    var _date_sortie_reelle = new Date($V(oForm._date_sortie_reelle));

    oDureeField = oForm._duree_reelle;
    $V(oDureeField, (_date_sortie_reelle - _date_entree_reelle) / (60 * 60 * 24));
  }

  if(sType == "syncType") {
    if($V(oDureeField) == 0 && $V(oTypeField) == "comp") {
      $V(oTypeField, "ambu");
    } else if($V(oDureeField) > 0 && $V(oTypeField) == "ambu") {
        $V(oTypeField, "comp");
    }
  } else if(sType == "syncDuree") {
    if($V(oDureeField) > 0 && $V(oTypeField) == "ambu") {
      $V(oDureeField, "0");
    }
  } else {
    if($V(oTypeField) == "comp" && ($V(oDureeField) == 0 || $V(oDureeField) == '')) {
      $V(oDureeField, prompt("Veuillez saisir une durée prévue d'hospitalisation d'au moins 1 jour", "1"));
      oDureeField.focus();
      return false;
    }
    if ($V(oTypeField) == "ambu" && $V(oDureeField) != 0 && $V(oDureeField) != '') {
      alert('Pour une admission de type Ambulatoire, la durée du séjour doit être de 0 jour.');
      oDureeField.focus();
      return false;
    }
    return true;
  }
};

removePlageOp = function(bIgnoreGroup){
  var oFormOp = document.editOp;
  var oFormSejour = document.editSejour;
  if(oFormOp){
    if((oFormOp._group_id.value != oFormSejour.group_id.value) || bIgnoreGroup){
      oFormOp._group_id.value = oFormSejour.group_id.value;
      {{if !$modurgence}}
        $V(oFormOp.plageop_id, "");
        $V(oFormOp._date, "");
        $V(oFormOp.date, "");
      {{/if}}
    }
  }
};

CanBloc = {{$modules.dPbloc->_can|json}};

checkCancelAlerts = function() {
  {{if $sejour->_id && is_array($sejour->_cancel_alerts)}}
    var msg = "Vous êtes sur le point d'annuler ce séjour, ceci entraîne :";
    msg += "\n\n1. Tous les placements dans les lits seront supprimés.";

    {{if count($sejour->_cancel_alerts.operations.all)}}
      msg += "\n\n2. Attention, vous allez également annuler des opérations :";
      {{foreach from=$sejour->_cancel_alerts.operations.all item=alert}}
        msg += "\n\t- " + "{{$alert|smarty:nodefaults|escape:'javascript'}}";
      {{/foreach}}
    {{/if}}
    {{if count($sejour->_cancel_alerts.consultations.all)}}
      msg += "\n\n{{if count($sejour->_cancel_alerts.operations.all)}}3{{else}}2{{/if}}. Attenion, vous allez également annuler des consultations :";
      {{foreach from=$sejour->_cancel_alerts.consultations.all item=alert}}
      msg += "\n\t- " + "{{$alert|smarty:nodefaults|escape:'javascript'}}";
      {{/foreach}}
    {{/if}}
    msg += "\n\nSouhaitez-vous continuer ?";
    if (!confirm(msg)) {
      return;
    }

    {{assign var=count_ops value=$sejour->_cancel_alerts.operations.acted|@count}}
    {{assign var=count_cons value=$sejour->_cancel_alerts.consultations.acted|@count}}

    {{if $count_ops}}
      msg = "Ce séjour contient {{if $count_ops > 1}}plusieurs interventions qui ont{{else}}une intervention qui a{{/if}} probablement déjà eu lieu :";
      {{foreach from=$sejour->_cancel_alerts.operations.acted item=alert}}
      msg += "\n\t- " + "{{$alert|smarty:nodefaults|escape:'javascript'}}";
      {{/foreach}}
    {{/if}}
    {{if $count_cons}}
      {{if $count_ops}}
      msg += "\n\nEt également {{if $count_cons > 1}}plusieurs consultations qui sont{{else}}une consultation qui est{{/if}} probablement déjà terminée{{if $count_cons > 1}}s{{/if}} :";
      {{else}}
      msg = "Ce séjour contient {{if $count_cons > 1}}plusieurs consultations qui sont{{else}}une consultation qui est{{/if}} probablement déjà terminée{{if $count_cons > 1}}s{{/if}} :";
      {{/if}}
      {{foreach from=$sejour->_cancel_alerts.consultations.acted item=alert}}
        msg += "\n\t- " + "{{$alert|smarty:nodefaults|escape:'javascript'}}";
      {{/foreach}}
    {{/if}}

    {{if $count_cons || $count_ops}}
      if (CanBloc.edit) {
        if (!confirm(msg + "\n\nVoulez-vous malgré tout {{if $count_ops + $count_cons > 1}}les annuler{{else}}l'annuler{{/if}} ?")) {
          return;
         }
      }
      else {
        {{if $count_ops && $count_cons}}
          alert(msg + "\n\nVeuillez vous adresser au responsable de bloc pour annuler {{if $count_ops > 1}}ces interventions{{else}}cette intervention{{/if}} et au{{if $count_cons > 1}}x{{/if}} praticien{{if $count_cons >1}}s{{/if}} concerné{{if $count_cons > 1}}s{{/if}} pour annuler {{if $count_cons > 1}}ces consultations{{else}}cette consultation{{/if}}.");
        {{elseif $count_cons}}
          alert(msg + "\n\nVeuillez vous adresser au{{if $count_cons > 1}}x{{/if}} praticien{{if $count_cons > 1}}s{{/if}} concerné{{if $count_cons > 1}}s{{/if}} pour annuler {{if $count_cons > 1}}ces consultations{{else}}cette consultation{{/if}}.");
        {{else}}
          alert(msg + "\n\nVeuillez vous adresser au responsable de bloc pour annuler {{if $count_ops > 1}}ces interventions{{else}}cette intervention{{/if}}.");
        {{/if}}

        return;
      }
    {{/if}}
    return true;
  {{/if}}
};

cancelSejour = function() {
  var oForm = document.editSejour;
  var oElement = oForm.annule;

  // Annulation
  if (oElement.value == "0") {
    Modal.open('tooltip_annulation_sejour');
  }

  // Rétablissement
  if (oElement.value == "1") {
    if (confirm("Voulez-vous vraiment rétablir le séjour ?")) {
      oElement.value = "0";
      oForm.submit();
      return;
    }
  }
};
confirmCancelSejour = function() {
  var oForm = document.editSejour;
  var oElement = oForm.annule;
  if (checkCancelAlerts()) {
    $V(oElement, '1');
    $V(oForm.dosql, 'do_cancel_sejour');
    var form_operation = getForm("annulation_sejour_operation");
    if (form_operation) {
      $V(oForm.motif_annulation, form_operation.motif_annulation.value);
      $V(oForm.rques_annulation, form_operation.rques_annulation.value);
    }
    oForm.submit();
    return;
  }
};

resetAnnulationSejour  = function() {
  var form = document.editSejour;
  $V(form.motif_annulation, '');
  $V(form.rques_annulation, '');
};

modifSejour = function(form) {
  var oForm = form || getForm("editSejour");
  canNullOK(oForm._date_entree_prevue);
  canNullOK(oForm._date_sortie_prevue);
};

updateSortiePrevue = function(form) {
  var oForm = form || getForm("editSejour");

  if (!oForm._duree_prevue.value) {
    $V(oForm._duree_prevue, 0);
  }

  var sDate = oForm._date_entree_prevue.value;
  if (!sDate) {
    return;
  }

  // Add days
  var dDate = Date.fromDATE(sDate);
  var nDuree = parseInt(oForm._duree_prevue.value, 10);

  dDate.addDays(nDuree);

  // Update fields
  oView = oForm._date_sortie_prevue_da;
  $V(oView, dDate.toLocaleDate());
  $V(oForm._date_sortie_prevue, dDate.toDATE());
  updateHeureSortie(oForm);

  // Si meme jour, sortie apres entree
  if (nDuree == 0 && oForm._duree_prevue.value == 0){
    oForm._hour_sortie_prevue.value = Math.max(oForm._hour_sortie_prevue.value, parseInt(oForm._hour_entree_prevue.value,10)+1);
  }
};

updateDureePrevue = function(form) {
  var oForm = form || getForm("editSejour");

  if(oForm._date_entree_prevue.value) {
    var dEntreePrevue = Date.fromDATE(oForm._date_entree_prevue.value);
    var dSortiePrevue = Date.fromDATE(oForm._date_sortie_prevue.value);
    var iSecondsDelta = dSortiePrevue - dEntreePrevue;
    var iDaysDelta = iSecondsDelta / (24 * 60 * 60 * 1000);
    $V(oForm._duree_prevue, Math.round(iDaysDelta));
  }
};

updateHeureSortie = function(form) {
  var oForm = form || getForm("editSejour");

  var duree_prevu  = oForm._duree_prevue;
  var duree_prevu_heure = oForm._duree_prevue_heure;
  var heure_sortie = oForm._hour_sortie_prevue;
  var heure_entree_prevue = oForm._hour_entree_prevue;
  var heure_sortie_prevue = oForm._hour_sortie_prevue;
  var min_entree   = oForm._min_entree_prevue;
  var min_sortie   = oForm._min_sortie_prevue;

  if (!duree_prevu_heure.value) {
    duree_prevu_heure.value = 0;
  }

  if (duree_prevu.value == 0 || oForm.type_no_check) {
    form.down("span.duree_prevue_view").show();
  }
  else {
    form.down("span.duree_prevue_view").hide();
    duree_prevu_heure.value = 0;
  }

  if (duree_prevu_heure.value != 0 && duree_prevu.value == 0) {
    if (duree_prevu_heure.value >= 0) {
      var hour_sortie = parseInt(heure_entree_prevue.value) + parseInt(duree_prevu_heure.value);
    }
    else {
      var hour_sortie = parseInt(heure_entree_prevue.value) - Math.abs(parseInt(duree_prevu_heure.value));
    }
    if (hour_sortie >= 24) {
      hour_sortie = parseInt("{{$conf.dPplanningOp.CSejour.heure_fin}}");
      duree_prevu_heure.value = hour_sortie - parseInt(heure_entree_prevue.value);
    }
    heure_sortie_prevue.value = hour_sortie;
  }
  else {
    heure_sortie.value = duree_prevu.value < 1 ? "{{$heure_sortie_ambu}}" : "{{$heure_sortie_autre}}";
    if (duree_prevu.value == 0) {
      duree_prevu_heure.value = parseInt(heure_sortie.value) - parseInt(heure_entree_prevue.value);
    }
  }

  min_sortie.value = min_entree.value;
};

updateDureePrevueHeure = function (form) {
  var oForm = form || getForm("editSejour");

  var duree_prevu  = oForm._duree_prevue;
  var duree_prevu_heure = oForm._duree_prevue_heure;
  var heure_sortie = oForm._hour_sortie_prevue;
  var hour_entree = oForm._hour_entree_prevue;

  if (duree_prevu.value == 0) {
    duree_prevu_heure.value = parseInt(heure_sortie.value) - parseInt(hour_entree.value);
    if (parseInt(heure_sortie.value) < parseInt(hour_entree.value)) {
      duree_prevu_heure.value = 0;
      hour_entree.value = heure_sortie.value;
    }
  }
};

checkSejoursToReload = function() {
  if(!$("selectSejours")) {
    return;
  }

  var oForm = document.editSejour;
  if(window.bChangePat) {
    window.bChangePat = 0;
    if(window.bOldPat && oForm.sejour_id.value) {
      if (confirm('Voulez-vous créer un nouveau sejour pour ce patient ?')) {
        if($("selectSejours")) {
          reloadListSejours();
        }
      }
    }
    else {
      reloadListSejours();
    }
    window.bOldPat = 1;
  }
};

reloadListSejours = function() {
  var oForm = document.editSejour;
  var iPatient_id = oForm.patient_id.value;
  var sejoursUrl = new Url("dPplanningOp", "httpreq_get_sejours");
  sejoursUrl.addParam("patient_id", iPatient_id);
  sejoursUrl.requestUpdate("selectSejours");

  // La liste des prescriptions doit etre rechargée
  if (window.PrescriptionEditor) {
    PrescriptionEditor.refresh($V(oForm.sejour_id), "CSejour", $V(oForm.praticien_id));
  }
};

reloadSejour = function() {
  var oFormSejour    = document.editSejour;
  var oFormOp        = document.editOp;

  var sDP            = $V(oFormSejour.DP);
  var sDateEntree    = $V(oFormSejour._date_entree_prevue);
  var sHeureEntree   = $V(oFormSejour._hour_entree_prevue);
  var sMinutesEntree = $V(oFormSejour._min_entree_prevue);
  var sDateSortie    = $V(oFormSejour._date_sortie_prevue);
  var sHeureSortie   = $V(oFormSejour._hour_sortie_prevue);
  var sMinutesSortie = $V(oFormSejour._min_sortie_prevue);

  var sejour_id = $V(oFormSejour.sejour_id);
  var facturable     = $V(oFormSejour.facturable);
  if (!sejour_id) {
    window.save_facturable = facturable;
  }

  var sejoursUrl = new Url("dPplanningOp", "httpreq_vw_sejour");
  sejoursUrl.addParam("sejour_id", sejour_id);
  sejoursUrl.addParam("patient_id", $V(oFormSejour.patient_id));
  if(oFormOp) {
    sejoursUrl.addParam("mode_operation", 1);
    sejoursUrl.addParam('protocole_id', $V(oFormOp.protocole_id));
  }
  sejoursUrl.addParam("contextual_call", '{{$contextual_call}}');
  sejoursUrl.requestUpdate('inc_form_sejour', { onComplete: function() {
    checkNewSejour(sDP,  sDateEntree, sHeureEntree, sMinutesEntree, sDateSortie, sHeureSortie, sMinutesSortie);}
  } );
};

checkNewSejour = function(sDP,  sDateEntree, sHeureEntree, sMinutesEntree, sDateSortie, sHeureSortie, sMinutesSortie) {
  var oFormSejour       = getForm('editSejour');
  var oSejourChooserFrm = getForm('sejourChooserFrm');
  $V(oSejourChooserFrm.majDP    , 0);
  $V(oSejourChooserFrm.majEntree, 0);
  $V(oSejourChooserFrm.majSortie, 0);

  if(!$V(oFormSejour.DP)) {
    $V(oFormSejour.DP, sDP);
    $('chooseDiag').hide();
  } else if(sDP && sDP != $V(oFormSejour.DP)) {
    oSejourChooserFrm.elements.valueDiag[1].value =  sDP;
    $('chooseOldDiag').update(sDP);
    oSejourChooserFrm.elements.valueDiag[0].value = $V(oFormSejour.DP);
    $('chooseNewDiag').update($V(oFormSejour.DP));
    $V(oSejourChooserFrm.majDP, 1);
    $('chooseDiag').show();
  } else {
    $('chooseDiag').hide();
  }
  if(sDateEntree && sDateEntree+sHeureEntree+sMinutesEntree != $V(oFormSejour._date_entree_prevue)+$V(oFormSejour._hour_entree_prevue)+$V(oFormSejour._min_entree_prevue)) {
    var oEntreeOld = Date.fromDATETIME(sDateEntree+" "+sHeureEntree.pad('0', 2, false)+":"+sMinutesEntree.pad('0', 2, false)+":00");
    var oEntreeNew = Date.fromDATETIME($V(oFormSejour._date_entree_prevue)+" "+$V(oFormSejour._hour_entree_prevue).pad('0', 2, false)+":"+$V(oFormSejour._min_entree_prevue).pad('0', 2, false)+":00");
    oSejourChooserFrm.elements.valueAdm[1].value =  oEntreeOld.toDATETIME();
    $('chooseOldAdm').update(oEntreeOld.toLocaleDateTime());
    oSejourChooserFrm.elements.valueAdm[0].value = oEntreeNew.toDATETIME();
    $('chooseNewAdm').update(oEntreeNew.toLocaleDateTime());
    $V(oSejourChooserFrm.majEntree, 1);
    $('chooseAdm').show();
  } else {
    $('chooseAdm').hide();
  }
  if(sDateSortie && sDateSortie+sHeureSortie+sMinutesSortie != $V(oFormSejour._date_sortie_prevue)+$V(oFormSejour._hour_sortie_prevue)+$V(oFormSejour._min_sortie_prevue)) {
    var oSortieOld = Date.fromDATETIME(sDateSortie+" "+sHeureSortie.pad('0', 2, false)+":"+sMinutesSortie.pad('0', 2, false)+":00");
    var oSortieNew = Date.fromDATETIME($V(oFormSejour._date_sortie_prevue)+" "+$V(oFormSejour._hour_sortie_prevue).pad('0', 2, false)+":"+$V(oFormSejour._min_sortie_prevue).pad('0', 2, false)+":00");
    oSejourChooserFrm.elements.valueSortie[1].value =  oSortieOld.toDATETIME();
    $('chooseOldSortie').update(oSortieOld.toLocaleDateTime());
    oSejourChooserFrm.elements.valueSortie[0].value = oSortieNew.toDATETIME();
    $('chooseNewSortie').update(oSortieNew.toLocaleDateTime());
    $V(oSejourChooserFrm.majSortie, 1);
    $('chooseSortie').show();
  } else {
    $('chooseSortie').hide();
  }
  if($V(oSejourChooserFrm.majDP) == 1 || $V(oSejourChooserFrm.majEntree) == 1 || $V(oSejourChooserFrm.majSortie) == 1) {
    changeSejourModal = Modal.open($('sejour-value-chooser'));
  }
};

applyNewSejour = function() {
  var oFormSejour       = getForm('editSejour');
  var oSejourChooserFrm = getForm('sejourChooserFrm');
  if($V(oSejourChooserFrm.majDP) == 1) {
    $V(oFormSejour.DP, $V(oSejourChooserFrm.valueDiag));
  }
  if($V(oSejourChooserFrm.majEntree) == 1) {
    oEntree = Date.fromDATETIME($V(oSejourChooserFrm.valueAdm));
    $V(oFormSejour._date_entree_prevue   , oEntree.toDATE());
    $V(oFormSejour._date_entree_prevue_da, oEntree.toLocaleDate());
    $V(oFormSejour._hour_entree_prevue   , oEntree.getHours());
    $V(oFormSejour._min_entree_prevue    , oEntree.getMinutes());
  }
  if($V(oSejourChooserFrm.majSortie) == 1) {
    oSortie = Date.fromDATETIME($V(oSejourChooserFrm.valueSortie));
    $V(oFormSejour._date_sortie_prevue   , oSortie.toDATE());
    $V(oFormSejour._date_sortie_prevue_da, oSortie.toLocaleDate());
    $V(oFormSejour._hour_sortie_prevue   , oSortie.getHours());
    $V(oFormSejour._min_sortie_prevue    , oSortie.getMinutes());
  }
  changeSejourModal.close();
};

changePat = function() {
  window.bChangePat = 1;
  const form = getForm("editSejour");

  checkSejoursToReload();
  Correspondant.checkCorrespondantMedical(form, 'CSejour', $V(form.sejour_id));
  PatientHandicap.checkDisability($V(form.patient_id));
  checkAld();

  {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    appFineClient.refreshButtonPackDemandeDHE($V(form.patient_id), "CSejour-"+$V(form.sejour_id));
    appFineClient.refresButtonAccountAppFine($V(form.patient_id), "CSejour-"+$V(form.sejour_id));
  {{/if}}
};

var OccupationServices =  {
  dateInitiale  : null,
  tauxOccupation: null,
  configBlocage : null,

  initOccupation: function() {
    var oForm = getForm("editSejour");
    this.dateInitiale = $V(oForm._date_entree_prevue);
    this.updateOccupation();
  },

  updateOccupation: function() {
    let oForm = getForm("editSejour");
    if ($V(oForm._date_entree_prevue) !== '') {
        let occupationUrl = new Url("dPplanningOp", "httpreq_show_occupation_lits");
        occupationUrl.addElement(oForm.type, "type");
        occupationUrl.addElement(oForm._date_entree_prevue, "entree");
        occupationUrl.addElement(oForm.service_id, 'service_id')
        occupationUrl.requestUpdate('occupation');
        if (document.editOp) {
            occupationUrl.requestUpdate('occupationeasy');
        }
    }
  },

  testOccupation: function() {
    if(this.configBlocage != '1') {
      return true;
    }
    var oForm = getForm("editSejour");
    if(this.dateInitiale != $V(oForm._date_entree_prevue) && this.tauxOccupation >= 100) {
      alert("L'occupation des services est de "+this.tauxOccupation+"%.\nVeuillez contacter le responsable des services");
      return false;
    }
    return true;
  }
};

setTutelle = function(input) {
  var form = getForm('patAldForm');
  $V(form.patient_id, $V(getForm('editSejour').patient_id));

  if ($V(form.patient_id)) {
    let input_tutelle = DOM.input({type: 'hidden', name: 'tutelle'});
    form.insert(input_tutelle);
    $V(form.tutelle, $V(input));
    return onSubmitFormAjax(form, function() { input_tutelle.remove(); });
  }
};

window.refreshingSejours = false;

reloadSejours = function(checkCollision, limit) {
  if (!$("list_sejours")) {
    return;
  }
  if (Object.isUndefined(checkCollision)) {
    checkCollision = 1;
  }

  var oForm = getForm("editSejour");
  var patient_id = $V(oForm.patient_id);

  if (!patient_id) {
    return;
  }

  // Changer l'entrée prévue d'un séjour change également la sortie prévue,
  // il faut donc éviter de lancer deux fois cette fonction.
  if (window.refreshingSejours) {
    return;
  }
  window.refreshingSejours = true;
  var url = new Url("planningOp", "ajax_list_sejours");
  url.addParam("check_collision", checkCollision);
  url.addParam("patient_id", patient_id);

  // L'entrée prévue est envoyée pour chercher les séjours datant de moins de 48h
  url.addParam("date_entree_prevue", $V(oForm._date_entree_prevue));
  url.addParam("hour_entree_prevue", $V(oForm._hour_entree_prevue));
  url.addParam("min_entree_prevue" , $V(oForm._min_entree_prevue));

  // Limite du nombre de séjours retournés
  url.addParam("limit", limit);

  // Dans le cas où on va checker la collision,
  // on envoie également la sortie prévue
  if (checkCollision) {
    url.addParam("date_sortie_prevue", $V(oForm._date_sortie_prevue));
    url.addParam("hour_sortie_prevue", $V(oForm._hour_sortie_prevue));
    url.addParam("min_sortie_prevue" , $V(oForm._min_sortie_prevue));
    url.addParam("sejour_id"         , $V(oForm.sejour_id));
  }
  url.requestUpdate("list_sejours", function() { window.refreshingSejours = false; });
};

window.bChangePat = 0;
window.bOldPat = 0;

</script>
