{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="compteRendu" script="document"}}
{{mb_script module="files" script="files"}}
{{mb_script module="files" script="file"}}

<script>
  function viewCompleteItem(object_guid) {
    new Url("system", "httpreq_vw_complete_object")
      .addParam("object_guid", object_guid)
      .requestUpdate("listView");
  }

  function loadSejour(sejour_id) {
    new Url("patients", "httpreq_vw_dossier_sejour")
      .addParam("sejour_id", sejour_id)
      .requestUpdate("listView");
  }

  function saveObjectInfos(oObject) {
    new Url("patients", "httpreq_save_classKey")
      .addParam("selClass", oObject.objClass)
      .addParam("selKey", oObject.id)
      .requestUpdate('systemMsg');
  }

  function view_labo_patient() {
    new Url("Imeds", "httpreq_vw_patient_results")
      .addParam("patient_id", "{{$patient->_id}}")
      .requestUpdate('listView');
  }

  function view_labo_sejour(sejour_id) {
    new Url("Imeds", "httpreq_vw_sejour_results")
      .addParam("sejour_id", sejour_id)
      .requestUpdate('listView');
  }

  Main.add(function () {
    {{if $consultation_id}}
    viewCompleteItem('CConsultation-{{$consultation_id}}');
    {{/if}}

    {{if $operation_id}}
    viewCompleteItem('COperation-{{$operation_id}}');
    {{/if}}

    {{if $sejour_id}}
    loadSejour('{{$sejour_id}}');
    {{/if}}
  });
</script>

<table class="main">
  <tr>
    <td style="display: none;">
      <form name="FrmClass" action="?m={{$m}}" method="get" onsubmit="reloadListFile('load'); return false;">
        <input type="hidden" name="selKey" value="" />
        <input type="hidden" name="selClass" value="" />
        <input type="hidden" name="selView" value="" />
        <input type="hidden" name="keywords" value="" />
        <input type="hidden" name="file_id" value="" />
        <input type="hidden" name="typeVue" value="1" />
      </form>
    </td>

    <td id="listInfosPat" style="width: 200px;">
      {{assign var="href" value="?m=dPpatients&tab=vw_full_patients"}}
      {{mb_include module="patients" template="inc_vw_full_patients"}}
    </td>

    <td class="greedyPane me-padding-left-6">
      {{if "dmp"|module_active}}
        {{mb_include module="dmp" template="inc_dossier_patient_dmp"}}
      {{/if}}
      <div id="listView">
        {{mb_include module="patients" template="CPatient_complete"}}
      </div>
    </td>
  </tr>
</table>