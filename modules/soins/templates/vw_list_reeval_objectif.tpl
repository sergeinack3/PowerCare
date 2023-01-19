{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="3">
      <button style="float:right" type="button" class="add notext"
              onclick="Soins.editReevalObjectif(0, '{{$objectif_soin->_id}}');">
        {{tr}}CObjectifSoinReeval-title-create{{/tr}}
      </button>
      {{tr}}CObjectifSoinReeval.all{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="category">{{mb_label class=CObjectifSoinReeval field=date}}</th>
    <th class="category">{{mb_label class=CObjectifSoinReeval field=commentaire}}</th>
    <th class="category narrow">{{tr}}Action{{/tr}}</th>
  </tr>
  {{foreach from=$objectif_soin->_ref_reevaluations item=_reeval}}
    <tr>
      <td>{{mb_value object=$_reeval field=date}}</td>
      <td>{{mb_value object=$_reeval field=commentaire}}</td>
      <td class="button">
        <button type="button" class="edit notext"
                onclick="Soins.editReevalObjectif('{{$_reeval->_id}}', '{{$objectif_soin->_id}}');">
          {{tr}}CObjectifSoinReeval-title-modify{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">{{tr}}CObjectifSoinReeval.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>