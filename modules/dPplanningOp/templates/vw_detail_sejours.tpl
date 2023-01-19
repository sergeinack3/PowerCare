{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page="StatProtocole.changePage" total=$total current=$page step=30}}

<table class="tbl">
  <tr>
    <th class="title" colspan="4">
      {{tr var1=$debut_stat|date_format:$conf.date var2=$fin_stat|date_format:$conf.date var3=$protocole->_view}}
        CProtocole-List sejours for protocole
      {{/tr}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th class="narrow">
      {{mb_title class=CSejour field=entree}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=sortie}}
    </th>
    <th>
      {{mb_title class=CSejour field=patient_id}}
    </th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
    <tr>
      <td>
        <button type="button" class="soins notext" onclick="Operation.showDossierSoins('{{$_sejour->_id}}', null, Prototype.emptyFunction);"></button>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{mb_value object=$_sejour field=entree}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{mb_value object=$_sejour field=sortie}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_ref_patient->_guid}}');">
          {{$_sejour->_ref_patient->_view}}
        </span>
      </td>
    </tr>
  {{/foreach}}
</table>