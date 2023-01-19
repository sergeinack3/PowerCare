{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page=refreshRegistre total=$count current=$page step=50}}

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow">{{tr}}CNaissance-num_naissance-court{{/tr}}</th>
    <th style="width: 40%">{{tr}}CNaissance-name{{/tr}}</th>
    <th>{{tr}}CPatient-_p_birth_date{{/tr}}</th>
    <th>{{tr}}CNaissance-Child sex{{/tr}}</th>
    <th>{{tr}}CConstantesMedicales-poids{{/tr}} (g)</th>
    <th>{{tr}}CNaissance-Mother{{/tr}}</th>
  </tr>

  {{foreach from=$naissances item=_naissance}}
    {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
    {{assign var=patient_enfant value=$sejour_enfant->_ref_patient}}

    {{assign var=sejour_maman value=$_naissance->_ref_sejour_maman}}
    {{assign var=patient_maman value=$sejour_maman->_ref_patient}}
    <tr>
      <td>
        {{if $can->admin}}
          <button type="button" class="edit notext compact"
                  onclick="toggleEditNumNaissance(this, '{{$_naissance->_id}}')">{{tr}}Edit{{/tr}}</button>
          <img src="./images/icons/updown.gif" usemap="#map-{{$_naissance->_id}}" />
          <map name="map-{{$_naissance->_id}}">
            <area coords="0,0,10,7" href="#1"
                  onclick="alterRank('{{$_naissance->_id}}', '{{math equation=x-1 x=$_naissance->num_naissance|intval}}', '{{$page}}', '{{$date_min}}', '{{$date_max}}');" />
            <area coords="0,8,10,14" href="#1"
                  onclick="alterRank('{{$_naissance->_id}}', '{{math equation=x+1 x=$_naissance->num_naissance|intval}}', '{{$page}}', '{{$date_min}}', '{{$date_max}}');" />
          </map>
        {{/if}}
      </td>
      <td style="text-align: right;">
      <span id="view_num_naissance_{{$_naissance->_id}}" onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_guid}}');">
        {{$_naissance->num_naissance}}
      </span>
        <span id="edit_num_naissance_{{$_naissance->_id}}" style="display: none;">
        <button type="button" class="tick notext compact"
                onclick="alterRank('{{$_naissance->_id}}', this.next('input').value, '{{$page}}', '{{$date_min}}', '{{$date_max}}')">{{tr}}Validate{{/tr}}</button>
        <input type="text" value="{{$_naissance->num_naissance}}" size="2" />
      </span>
      </td>
      <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient_enfant->_guid}}');">
        {{$patient_enfant}}
      </span>
      </td>
      <td>
        {{mb_value object=$patient_enfant field="naissance"}}
      </td>
      <td>
        {{mb_value object=$patient_enfant field="sexe"}}
      </td>
      <td>
        {{mb_value object=$patient_enfant->_ref_first_constantes field="poids"}}
      </td>
      <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient_maman->_guid}}');">
        {{$patient_maman}}
      </span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="7">
        {{tr}}CNaissance.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>