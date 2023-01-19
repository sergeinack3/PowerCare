{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$grossesses item=_grossesse}}
  {{assign var=consult value=$_grossesse->_ref_last_consult}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_ref_parturiente->_guid}}');">
        {{mb_value object=$_grossesse->_ref_parturiente field=nom}} {{mb_value object=$_grossesse->_ref_parturiente field=prenom}}
      </span>

      {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_grossesse->_ref_parturiente}}
    </td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_guid}}');">
        {{mb_value object=$_grossesse field=terme_prevu}}
      </span>
    </td>
    <td {{if !$_grossesse->multiple}}class="empty"{{/if}}>
      {{mb_value object=$_grossesse field=multiple}}
    </td>
    <td {{if !$_grossesse->num_semaines}}class="empty"{{/if}}>
      {{$_grossesse->num_semaines}}
    </td>
    <td>
      <button class="edit notext" onclick="Tdb.editGrossesse('{{$_grossesse->_id}}')"></button>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="5" class="empty">{{tr}}CGrossesse.none{{/tr}}</td>
  </tr>
{{/foreach}}
