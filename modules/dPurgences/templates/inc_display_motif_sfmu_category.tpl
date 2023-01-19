{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>
      {{tr}}CMotifSFMU-libelle{{/tr}}
    </th>
    <th>
      {{tr}}CMotifSFMU-code{{/tr}}
    </th>
    <th></th>
  </tr>
  {{foreach from=$list_motif_sfmu item=motif_sfmu}}
    <tr>
      <td>
        {{$motif_sfmu->libelle}}
      </td>
      <td>
        {{$motif_sfmu->code}}
      </td>
      <td>
        <button type="button" class="tick notext"
                onclick="CCirconstance.selectMotifSFMU('{{$motif_sfmu->libelle|smarty:nodefaults|JSAttribute}}', '{{$motif_sfmu->_id}}')">
          {{tr}}Select{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr><td colspan="3" class="empty">{{tr}}CMotifSFMU.none{{/tr}}</td></tr>
  {{/foreach}}
</table>