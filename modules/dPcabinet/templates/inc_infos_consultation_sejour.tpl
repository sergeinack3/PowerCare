{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$sejour->_ref_consultations item=_consult}}
  <tr>
    {{if $_consult->annule}}
      <th class="category cancelled">
        <strong onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}');">{{tr}}CConsultation-annule{{/tr}}</strong>
      </th>
    {{else}}
      <td></td>
    {{/if}}
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
    </td>
    <td>{{$_consult->_date|date_format:$conf.date}}</td>
    <td>{{mb_value object=$_consult field=heure}}</td>

  </tr>
  {{if @$modules.brancardage->_can->read
  && "brancardage General use_brancardage"|gconf}}
    <tr>
      <td class="button" colspan="3">
        <div id="brancardage-{{$_consult->_guid}}">
          {{mb_include module=brancardage template=inc_exist_brancard colonne="patientPret" object=$_consult
          brancardage_to_load="aller"}}
        </div>
      </td>
    </tr>
  {{/if}}
  {{foreachelse}}
  <tr><td colspan="3" class="empty">{{tr}}CConsultation.none{{/tr}}</td></tr>
{{/foreach}}
