{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPplanningOp" script="operation" ajax=1}}
<table class="tbl">
  <tr>
    <th colspan="2" class="category">Libellés</th>
  </tr>
  {{foreach from=$liaisons item=liaison key=key}}
    <tr>
      <th class="narrow">
        Libellé {{$liaison->numero}}
      </th>
      <td>
        {{if $liaison->_id}} {{$liaison->_ref_libelle->nom}}{{/if}}
      </td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="2" class="button">
      <button class="edit" type="button"
           onclick="LiaisonOp.edit('{{$operation_id}}', function() {Libelle.refreshlistLibelle('{{$operation_id}}')});">
        Modifier les libellés
      </button>
    </td>
  </tr>
</table>
