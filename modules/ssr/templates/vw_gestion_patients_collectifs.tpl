{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  // Initialisation du tableau des sejours json
  Seance.jsonSSR = {};
</script>
<form name="deletePatientsCollectif-{{$evenement->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_delete_seances_aed" />
  <input type="hidden" name="evts_to_delete" value="" />
  <table class="form">
    <tr>
      <th colspan="3" class="title">
        {{tr}}CEvenementSSR-seance_collective_id{{/tr}} '{{$evenement->_ref_prescription_line_element->_ref_element_prescription->_view}}'
        {{tr}}date.from{{/tr}} {{mb_value object=$evenement field=debut}}
      </th>
    </tr>
    <tr>
      <th class="category narrow">
        <input name="seance_ssr_patient_all" id="check_all_sejours_ssr" type="checkbox" onchange="Seance.selectAllLines(this);"/>
      </th>
      <th class="category">{{tr}}CPatient{{/tr}}</th>
      <th class="category">{{tr}}CSejour{{/tr}}</th>
    </tr>
    {{foreach from=$evenement->_ref_evenements_seance item=_evt}}
      {{assign var=sejour value=$_evt->_ref_sejour}}
      {{assign var=patient value=$sejour->_ref_patient}}
      <tr>
        <td class="button">
          <script>
            var json = {
              evt_id : "{{$_evt->_id}}",
              _checked : 0 };
            Seance.jsonSSR["{{$_evt->_id}}"] = json;
          </script>
          <input name="seance_patient-{{$_evt->_id}}" type="checkbox"
                 {{if $_evt->annule || $_evt->realise}}disabled="disabled" title="{{tr}}CEvenementSSR-{{if $_evt->realise}}realise{{else}}annule{{/if}}{{/tr}}"{{/if}}
                 onchange="Seance.showCheckbox('{{$evenement->_guid}}');Seance.jsonSSR['{{$_evt->_id}}']._checked = (this.checked ? 1 : 0);"
          />
        </td>
        <td>
          {{mb_include module=system template=inc_vw_mbobject object=$patient}}
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
            {{$sejour->_shortview}}
          </span>
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="3" class="button">
        <button type="button" class="trash" onclick="Seance.deletePatientsCollectif(this.form)">
          {{tr}}CEvenementSSR.delPatientsCollectif{{/tr}}
        </button>
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>