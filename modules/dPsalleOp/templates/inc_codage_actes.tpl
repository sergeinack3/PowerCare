{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="do_subject_aed" value="do_planning_aed"}}
{{assign var="object" value=$subject}}

{{mb_include module=salleOp template=js_codage_ccam}}

<script>
  loadActesNGAP = function(operation_id) {
    var url = new Url("dPcabinet", "httpreq_vw_actes_ngap");
    url.addParam("object_id", operation_id);
    url.addParam("object_class", "COperation");
    url.addParam('page', '0');
    url.requestUpdate('listActesNGAP');
  };

  loadTarifsSejour = function(operation_id) {
    var url = new Url("dPsalleOp", "ajax_tarifs_operation");
    url.addParam("operation_id", operation_id);
    url.requestUpdate("tarif");
  };

  function reloadActes(operation_id, praticien_id) {
    if($('listActesNGAP')){
      loadActesNGAP(operation_id);
    }
    if($('ccam')){
      ActesCCAM.refreshList(operation_id, praticien_id);
    }
    if ($('tarif')) {
      loadTarifsSejour(operation_id);
    }
  }

  Main.add (function () {
    Control.Tabs.create('codage_tab_group', true, {foldable: true});
    $('codage_tab_group').down('li.control_tabs_fold').click();
    reloadDiagnostic('{{$subject->sejour_id}}');

    {{if $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
      if ($('tarif')) {
        loadTarifsSejour({{$subject->_id}});
      }
    {{/if}}
  });
</script>

<form name="patAldForm" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="dosql" value="do_patients_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="patient_id" value="">
  <input type="hidden" name="ald" value="">
  <input type="hidden" name="c2s" value="">
  <input type="hidden" name="acs" value="">
</form>

<ul id="codage_tab_group" class="control_tabs">
  {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
    <li><a href="#ccam_tab"{{if $subject->_ref_actes_ccam|@count == 0}} class="empty"{{/if}}>CCAM <small id="count_ccam_{{$subject->_guid}}">({{$subject->_ref_actes_ccam|@count}})</small></a></li>
    <li><a href="#ngap_tab"{{if $subject->_ref_actes_ngap|@count == 0}} class="empty"{{/if}}>NGAP <small id="count_ngap_{{$subject->_guid}}">({{$subject->_ref_actes_ngap|@count}})</small></a></li>
    {{assign var=subject_class value=$subject->_class}}
    {{if "dPccam frais_divers use_frais_divers_$subject_class"|gconf}}
      <li><a href="#fraisdivers">Frais divers</a></li>
    {{/if}}
    <li onmousedown="reloadDiagnostic('{{$subject->sejour_id}}');"><a href="#diag_tab">Diags.</a></li>
    {{if $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
      {{assign var=sejour value=$subject->_ref_sejour}}
      <li style="float: right" class="keep_content me-tabs-buttons me-float-none">
        <table class="narrow">
          <tr>
            <td id="tarif"></td>
            <td class="me-ws-nowrap">
              <form name="editSejour" method="post" onsubmit="return onSubmitFormAjax(this)">
                <input type="hidden" name="m" value="planningOp">
                <input type="hidden" name="dosql" value="do_sejour_aed">
                <input type="hidden" name="patient_id" value="{{$sejour->patient_id}}">
                {{mb_key object=$sejour}}
                <table class="main">
                  {{mb_include module=planningOp template=inc_check_ald patient=$subject->_ref_patient onchange="this.form.onsubmit()" circled=false}}
                </table>
              </form>
            </td>
          </tr>
        </table>
      </li>
    {{/if}}
  {{/if}}
</ul>

{{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
  <div id="ccam_tab" style="display:none; clear: both;">
    <div id="ccam">
      {{mb_include module=salleOp template=inc_codage_ccam}}
    </div>
  </div>

  <div id="ngap_tab" style="display:none; clear: both;">
    <div id="listActesNGAP" data-object_id="{{$subject->_id}}" data-object_class="{{$subject->_class}}">
      {{mb_include module=cabinet template=inc_codage_ngap}}
    </div>
  </div>

  {{if "dPccam frais_divers use_frais_divers_$subject_class"|gconf}}
    <div id="fraisdivers" style="display: none; clear: both;">
      {{mb_include module=ccam template=inc_frais_divers object=$subject}}
    </div>
  {{/if}}
{{/if}}

<div id="diag_tab" style="display: none">
  <div id="cim"></div>
</div>
