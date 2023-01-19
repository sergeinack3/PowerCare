{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patient->_id}}
  <!-- Informations générales sur l'intervention et le patient -->
  <br />
  <table class="tbl">
    <tr>
      <th class="title text" colspan="2" style="vertical-align:middle;">
        <a style="float: left" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}">
          {{mb_include module=patients template=inc_vw_photo_identite patient=$patient size=42}}
        </a>
        {{if $sejour->_ref_grossesse && $sejour->_ref_grossesse->_id && "maternite"|module_active}}
          <a href="#" style="float:left;" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_ref_grossesse->_guid}}');">
            <img src="modules/maternite/images/icon.png" alt="" style="width:42px; height:42px; margin-left:5px; background-color: white" />
          </a>
        {{/if}}
        <a class="action" style="float: right;" title="{{tr}}CPatient-action-Modify administrative file{{/tr}}" href="?m=dPpatients&tab=vw_edit_patients&patient_id={{$patient->_id}}">
          {{me_img src="edit.png" icon="edit" class="me-primary"}}
        </a>

        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient->_view}}</span>
        ({{$patient->_age}}
        {{if $patient->_annees != "??"}}- {{mb_value object=$patient field="naissance"}}{{/if}})
        <br />
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">{{if $sejour->presence_confidentielle}}{{mb_include module=planningOp template=inc_badge_sejour_conf}}{{/if}}{{tr}}CSejour{{/tr}} {{tr}}date.from{{/tr}} {{mb_value object=$sejour field=entree}}</span>
        {{tr}}date.to{{/tr}} {{mb_value object=$sejour field=sortie_prevue}}
        <br />
        {{mb_title object=$selOp field=libelle}} : <span onmouseover="ObjectTooltip.createEx(this, '{{$selOp->_guid}}');">{{mb_value object=$selOp field=libelle}}</span>
      </th>
    </tr>
    <tr>
      <td>
        <div class="small-info" style="text-align: center;">
          {{tr}}CPatient-Validation of patient identity{{/tr}}
          <br />
          {{if $auto_entree_bloc}}
            <form name="valid_entree_bloc" method="post" action="?" onsubmit="return onSubmitFormAjax(this, function() {
              Control.Modal.close();
            });">
              {{mb_key object=$selOp}}
              {{mb_class object=$selOp}}
              <input type="hidden" name="entree_bloc" value="now"/>
              <button type="button" class="clock clock-tick notext search_nda big" onclick="this.form.onsubmit();">
                {{tr var1=$dnow|date_format:'%H:%M'}}COperation-auto_entree_bloc{{/tr}}
              </button>
            </form>
          {{/if}}
          {{if $sejour_id && $sejour->patient_id !== $patient->_id}}
            <div class="warning" style="display: inline;">
              {{tr var1=$sejour->_ref_patient->_view}}CPatient-Identity mismatch{{/tr}}
            </div>
          {{else}}
            <a class="search_nda button tick notext big" title="{{tr}}Validate{{/tr}}"
               {{if $sejour_id}}
                 href="#" onclick="Control.Modal.close(); SalleOp.topHoraires('{{$selOp->_id}}');"
               {{elseif !$modal}}
                 href="?m=salleOp&tab=vw_operations&operation_id={{$selOp->_id}}&date={{$selOp->date}}&salle={{$selOp->salle_id}}&fragment=timing_tab"
               {{else}}
                 href="#" onclick="Control.Modal.close(); HPlanning.openOperationTimings('{{$selOp->_id}}'{{if $onclose_modal}}, {{$onclose_modal|smarty:nodefaults}}{{/if}});"
              {{/if}}>
            </a>
          {{/if}}
          <button type="button" onclick="Control.Modal.close();" class="search_nda cancel notext big">
            {{tr}}Close{{/tr}}
          </button>
        </div>
      </td>
    </tr>
  </table>
{{else}}
  <br />
  <table class="tbl">
    <tr>
      <td>
        <div class="small-warning" style="text-align: center;">
          {{tr}}CPatient-Patient found.empty{{/tr}}
        </div>
      </td>
    </tr>
  </table>
{{/if}}