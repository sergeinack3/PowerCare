{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPmedicament"|module_active}}
  {{mb_script module="medicament" script="medicament_selector"}}
  {{mb_script module="medicament" script="equivalent_selector"}}
{{/if}}

{{if "dPprescription"|module_active}}
    {{mb_script module=mpm script=mpm}}
{{/if}}

{{if "dPprescription"|module_active}}
  {{mb_script module="prescription" script="prescription"}}
  {{mb_script module="prescription" script="prescription_editor"}}
  {{mb_script module="prescription" script="element_selector"}}
{{/if}}

{{if "maternite"|module_active}}
  {{mb_script module=maternite script=tdb}}
{{/if}}

{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=cabinet     script=edit_consultation}}
{{mb_script module=patients    script=documentV2}}
{{mb_script module=patients    script=patient}}

{{if "doctolib"|module_active}}
  {{mb_script module=doctolib script=calls_to_doctolib}}
{{/if}}

{{if 'teleconsultation'|module_active}}
    {{mb_script module=teleconsultation script=teleconsultation ajax=true}}
{{/if}}

<script>
  {{if !$synthese_rpu && $consult->_id && !$consult->_canEdit}}
    App.readonly = true;
  {{/if}}

  reloadConsultAnesth = function() {
    var sejour_id = document.addOpFrm.sejour_id.value;

    // Mise a jour du sejour_id
    DossierMedical.updateSejourId(sejour_id);

    // refresh de la liste des antecedents du sejour
    DossierMedical.reloadDossierPatient();
    DossierMedical.reloadDossierSejour();

    // Reload Intervention
    loadIntervention();

    // Reload Infos Anesth
    loadInfosAnesth();

    Prescription.reloadPrescSejour('', DossierMedical.sejour_id,'', null, null, null,'', null, false);

    if($('facteursRisque')){
      refreshFacteursRisque();
    }
  };

  submitAll = function() {
    var oForm;
    oForm = getForm('editFrmIntubation');
    if(oForm) {
      onSubmitFormAjax(oForm);
    }
    oForm = getForm('editExamCompFrm');
    if(oForm) {
      onSubmitFormAjax(oForm);
    }
    $$('form.editFrmExam').each(function(_form) {
      onSubmitFormAjax(_form);
    });
  };

  submitOpConsult = function() {
    onSubmitFormAjax(getForm("addOpFrm"), { onComplete: reloadConsultAnesth } );
  };

  reloadDiagnostic = function(sejour_id, consult_id) {
    var url = new Url("salleOp", "httpreq_diagnostic_principal");
    url.addParam("sejour_id", sejour_id);
    url.addParam("consult_id", consult_id);
    url.requestUpdate("cim");
  };

  view_history_consult = function(id) {
    var url = new Url("cabinet", "vw_history");
    url.addParam("consultation_id", id);
    url.popup(600, 500, "consult_history");
  };

  submitForm = function(oForm) {
    onSubmitFormAjax(oForm);
  };

  printAllDocs = function() {
    var url = new Url('cabinet', 'print_select_docs');
    if (document.editFrmFinish.consultation_id) {
      url.addElement(document.editFrmFinish.consultation_id);
    } else {
      url.addElement(document.editFrmFinish[0].consultation_id);
    }
    if(DossierMedical.sejour_id) {
      url.addParam("sejour_id", DossierMedical.sejour_id);
    }
    url.popup(700, 500, "printDocuments");
  };

  pdfConsultAnesth = function(dossier_anesth_id) {
    new Url("cabinet", "print_to_pdf_consult_anesth", "raw")
      .addParam('dossier_anesth_id', dossier_anesth_id)
      .popup(800, 600);
  };

  printFiche = function() {
    new Url("cabinet", "print_fiche")
      .addParam("dossier_anesth_id", $V(getForm("editFrmFinish")._consult_anesth_id))
      .addParam("print", true)
      .popup(700, 500, "printFiche");
  };
</script>

{{if $consult->_id}}
  {{assign var="patient" value=$consult->_ref_patient}}
  <div id="finishBanner" {{if $synthese_rpu}}style="display: none;"{{/if}}>
    {{mb_include module=cabinet template=inc_finish_banner}}
  </div>

  {{if $patient->status === "VIDE"}}
    <div class="small-warning">
      {{tr}}CPatient-Need validation identity{{/tr}}
    </div>

    </div>
    {{mb_return}}
  {{/if}}

  {{mb_include module=cabinet template=inc_patient_infos_accord_consult}}
  {{if $consult_anesth}}
    {{mb_include module=cabinet template=inc_consult_anesth/accord}}
  {{else}}
    {{mb_include module=cabinet template=acc_consultation}}
  {{/if}}
{{/if}}
