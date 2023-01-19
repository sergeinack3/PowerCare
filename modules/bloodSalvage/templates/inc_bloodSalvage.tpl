{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloodSalvage script=bloodSalvage}}

<script>
  Main.add(function () {
    {{if $blood_salvage->_id}}
    var url = new Url("bloodSalvage", "httpreq_vw_recuperation_start_timing");
    url.addParam("blood_salvage_id", "{{$blood_salvage->_id}}");
    url.requestUpdate("start-timing");
    {{/if}}
  });
</script>

{{assign var=patient value=$selOp->_ref_sejour->_ref_patient}}
{{if $blood_salvage->_id}}
  <!-- Informations sur le patient (Groupe, rhésus, ASA, RAI...) -->
  <div id="info-patient">
    {{mb_include module=bloodSalvage template=inc_vw_patient_infos}}
  </div>
  <div id="start-timing"></div>
  <div id="materiel">
    {{mb_include module=bloodSalvage template=inc_blood_salvage_conso}}
  </div>
  <div id="unregister" style="float:left">
    <form name="inscriptionRSPO" action="?m={{$m}}" method="post">
      <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}">
      <input type="hidden" name="m" value="bloodSalvage" />
      <input type="hidden" name="del" value="1" />
      <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
      <button type="button" class="cancel"
              onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$blood_salvage->_view|smarty:nodefaults|JSAttribute}}'})">
      {{tr}}CBloodSalvage-unsubscribe{{/tr}}
      </button>
    </form>
  </div>
{{else}}
  <div class="small-info">
    {{tr}}CCellSaver-back-operation.empty{{/tr}}
  </div>
  <div id="register" style="text-align:center">
    <form name="inscriptionRSPO" method="post">
      <input type="hidden" name="operation_id" value="{{$selOp->_id}}">
      <input type="hidden" name="m" value="bloodSalvage" />
      <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
      <button type="button" class="new" onclick="submitNewBloodSalvage(this.form);">{{tr}}CBloodSalvage-msg-register_patient{{/tr}}</button>
    </form>
  </div>
{{/if}}
