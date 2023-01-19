{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=modFSE value="fse"|module_active}}

{{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
{{mb_include module="patients" template="inc_vitalevision" debug=false keepFiles=true}}
{{elseif $app->user_prefs.LogicielLectureVitale == 'none' && $modFSE && $modFSE->canRead()}}
<script>
  var urlFSE = new Url;
  urlFSE.addParam("useVitale", 1);
  {{if $board}}
  urlFSE.updateElement = 'dossiers';
  urlFSE.setModuleAction("patients", "vw_idx_patients");
  urlFSE.addParam('board', 1);
  {{else}}
  urlFSE.setModuleTab("patients", "vw_idx_patients");
  {{/if}}
  window.urlFSE = urlFSE;
</script>
{{/if}}

<script>
  onMergeComplete = function () {
    location.reload();
  };

  window.checkedMerge = [];
  checkOnlyTwoSelected = function (checkbox) {
    checkedMerge = checkedMerge.without(checkbox);

    if (checkbox.checked) {
      checkedMerge.push(checkbox);
    }

    if (checkedMerge.length > 2) {
      checkedMerge.shift().checked = false;
    }
  };

  reloadPatient = function (patient_id, link, vw_cancelled) {
    var url = new Url('patients', 'httpreq_vw_patient');
    url.addParam('patient_id', patient_id);
    url.addParam("vw_cancelled", vw_cancelled);
    url.requestUpdate('vwPatient', {onComplete: markAsSelected.curry(link)});
  };

  emptyForm = function () {
    var form = getForm("find");
    $V(form.Date_Day, '');
    $V(form.Date_Month, '');
    $V(form.Date_Year, '');
    $V(form.prat_id, '');
    $V(form.sexe, '');
    $V(form.nom_jeune_fille, '');
    form.select("input[type=text]").each(function (elt) {
      $V(elt, '');
    });
    form.nom.focus();
  };
</script>

<div id="modal-beneficiaire" style="display:none; text-align:center;">
  <p id="msg-multiple-benef">
    {{tr}}CPatient.card_lot_of_benifits{{/tr}} :
  </p>
  <p id="msg-confirm-benef" style="display: none;"></p>
  <p id="benef-nom">
    <select id="modal-beneficiaire-select"></select>
    <span></span>
  </p>
  <div>
    <button type="button" class="tick"
            onclick="VitaleVision.search(getForm('find'), $V($('modal-beneficiaire-select'))); VitaleVision.modalWindow.close();">{{tr}}Choose{{/tr}}</button>
    <button type="button" class="cancel" onclick="VitaleVision.modalWindow.close();">{{tr}}Cancel{{/tr}}</button>
  </div>
</div>

<!-- formulaire de recherche -->
{{mb_include module=dPpatients template=inc_form_search_patient}}


<div id="search_result_patient" class="me-no-align me-padding-top-8">
</div>
