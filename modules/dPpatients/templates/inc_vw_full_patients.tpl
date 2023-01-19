{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPImeds"|module_active}}
  {{mb_script module=Imeds script=Imeds_results_watcher}}
{{/if}}

{{mb_script module=compteRendu script=modele_selector}}

<script>
  var ViewFullPatient = {
    select: function (eLink) {
      // Unselect previous row
      if (this.idCurrent) {
        $(this.idCurrent).removeClassName("selected");
      }

      // Select current row
      this.idCurrent = $(eLink).up(1).identify();
      $(this.idCurrent).addClassName("selected");
    }
  };

  function popEtatSejour(sejour_id) {
    var url = new Url("hospi", "vw_parcours");
    url.addParam("sejour_id", sejour_id);
    url.pop(1000, 700, 'Etat du Séjour');
  }

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

  function doMerge(oForm) {
    var operation_checkbox = $V(oForm["operation_ids[]"]);

    var checkboxs, object_class;
    if (operation_checkbox && operation_checkbox.length > 0) {
      checkboxs = operation_checkbox;
      object_class = "COperation";
    } else {
      checkboxs = $V(oForm["objects_id[]"]);
      object_class = "CSejour";
    }

    var url = new Url("system", "object_merger");
    url.addParam("objects_class", object_class);
    url.addParam("objects_id", checkboxs.join("-"));
    url.popup(800, 600, "merge_sejours");
  }

  onMergeComplete = function () {
    location.reload();
  };

  {{if $isImedsInstalled}}
  Main.add(function () {
    ImedsResultsWatcher.loadResults();
  });
  {{/if}}
</script>

<form name="fusion" method="get" onsubmit="return false;">

  <table class="tbl me-table-patient-event" style="vertical-align: middle;">
    <tr>
      <th class="title text" colspan="4">
        <a href="#{{$patient->_guid}}" onclick="viewCompleteItem('{{$patient->_guid}}'); ViewFullPatient.select(this);">
          {{$patient->_view}} ({{$patient->_age}})
        </a>
      </th>
      <th class="title">
        {{if $patient->_canRead}}
          <div style="float: right">
            {{if "telemis"|module_active}}
                {{mb_include module=telemis template=inc_viewer_link patient=$patient compact=true}}
            {{/if}}
          </div>
        {{/if}}
      </th>
      <th class="title">
        {{if $patient->_canRead}}
          <div style="float:right;">
            {{if $isImedsInstalled}}
              <a href="#{{$patient->_guid}}" onclick="view_labo_patient()" class="me-button notext me-secondary">
                <img align="top" src="images/icons/labo.png" title="{{tr}}CImeds-Laboratory result-desc|pl{{/tr}}" />
              </a>
            {{/if}}
            {{mb_include module=patients template=inc_form_docitems_button object=$patient compact=true}}
          </div>
        {{/if}}
      </th>
    </tr>

    {{mb_include module=dPpatients template=inc_vw_patient_events th_colspan=6 compact=true show_actions=false show_merge_chkbx=true show_files_btn=true}}

  </table>

</form>

<hr class="me-no-display"/>

<table class="tbl">
  <tr>
    <th class="title me-no-border">{{tr}}CPatient-Patient folder{{/tr}}</th>
  </tr>

  <tbody>
  <tr>
    <td class="button">
      <!-- Dossier résumé -->
      <button class="search" onclick="Patient.showSummary('{{$patient->_id}}')">
        {{tr}}Summary{{/tr}}
      </button>

      {{if $app->_ref_user->isAdmin() && $patient->hasBeenMerged()}}
        {{mb_script module=dPpatients script=patient_unmerge}}
        <button class="fa fa-expand me-tertiary" type="button" onclick="PatientUnmerge.showUnmergePatient('{{$patient->_id}}')">
          {{tr}}CPatient-unmerge{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
  </tbody>
</table>

<!-- Planifier -->
<table class="tbl">
  <tr>
    <th class="title me-no-border">{{tr}}common-action-Plan{{/tr}}</th>
  </tr>

  <tbody id="planifier">
  <tr>
    <td class="button">
      {{if "ecap"|module_active && $current_group|idex:"ecap"|is_numeric}}
        {{mb_include module=ecap template=inc_button_dhe patient_id=$patient->_id praticien_id=""}}
      {{/if}}
      {{if !$app->user_prefs.simpleCabinet
        && ((!"ecap"|module_active || !$current_group|idex:"ecap"|is_numeric)
            || ("ecap"|module_active && $current_group|idex:"ecap"|is_numeric && 'ecap Display show_buttons_dhe'|gconf))}}
          {{if $canPlanningOp->edit}}
              {{me_button label=COperation icon=new
              link="?m=planningOp&tab=vw_edit_planning&pat_id=`$patient->_id`&operation_id=0&sejour_id=0"}}
          {{/if}}
          {{if $canPlanningOp->read}}
              {{me_button label="CIntervHorsPlage-action-Interventions out of range-court" icon=new
              link="?m=planningOp&tab=vw_edit_urgence&pat_id=`$patient->_id`&operation_id=0&sejour_id=0"}}
              {{me_dropdown_button button_icon="new" button_label=COperation container_class="me-dropdown-button-top"}}
            <a class="button new" href="?m=planningOp&tab=vw_edit_sejour&patient_id={{$patient->_id}}&sejour_id=0">
              {{tr}}CSejour{{/tr}}
            </a>
          {{/if}}
      {{/if}}
      {{if $canCabinet->read}}
        {{me_button mediboard_ext_only=true  label=CConsultation link="?m=cabinet&tab=edit_planning&pat_id=`$patient->_id`&consultation_id=" icon=new}}
        {{me_button mediboard_ext_only=true label="CConsultation-action-Immediate"  icon=new
        onclick="Consultation.openConsultImmediate('`$patient->_id`', '', '', '', '', 'tous')"}}
        {{me_dropdown_button button_icon="new" button_label=CConsultation container_class="me-dropdown-button-top"}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <td class="button me-no-display">
      {{if $canCabinet->read}}
          <a class="button new" href="?m=cabinet&tab=edit_planning&pat_id={{$patient->_id}}&consultation_id=">
              {{tr}}CConsultation{{/tr}}
          </a>
        {{mb_include module="cabinet" template="inc_button_consult_immediate" patient_id=$patient->_id}}
      {{/if}}
    </td>
  </tr>
  </tbody>
</table>

{{if "doctolib"|module_active && "doctolib staple_authentification client_access_key_id"|gconf}}
  <table class="tbl">
    <tr>
      <th class="title me-no-border">{{tr}}Doctolib{{/tr}}</th>
    </tr>
    <tr>
      <td class="button">
        {{if isset($patient->_ref_doctolib_idex|smarty:nodefaults) && $patient->_ref_doctolib_idex->id400}}
            {{mb_include module=doctolib template=buttons/inc_book}}
            {{mb_include module=doctolib template=buttons/inc_patient_historic}}
        {{else}}
            {{mb_include module=doctolib template=buttons/inc_create_patient}}
        {{/if}}
      </td>
    </tr>
  </table>
{{/if}}
