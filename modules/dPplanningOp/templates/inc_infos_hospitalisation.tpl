{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_thead value=0}}

{{if $sejour->_id && !$sejour->_canRead}}
  <div class="small-info">{{tr}}CSejour-msg-You do not have access to the detail of the stays.{{/tr}}</div>

  {{mb_return}}
{{/if}}

<table class="tbl print_sejour">
  {{mb_include module=soins template=inc_thead_dossier_soins colspan=4 with_thead=$with_thead}}

  <tr>
    <th class="title" colspan="4">
      {{tr}}CSejour-back-affectations{{/tr}}
    </th>
  </tr>

  <tr>
    <th>{{mb_label class=CAffectation field=lit_id}}</th>
    <th>{{mb_label class=CAffectation field=entree}}</th>
    <th>{{mb_label class=CAffectation field=sortie}}</th>
    <th>{{mb_label class=CAffectation field=effectue}}</th>
  </tr>

  {{foreach from=$sejour->_ref_affectations item=_affectation}}
    <tr>
      <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_affectation->_guid}}');">
        {{$_affectation->_view}}
      </span>
      </td>
      <td>{{mb_value object=$_affectation field=entree}}</td>
      <td>{{mb_value object=$_affectation field=sortie}}</td>
      <td>
        {{mb_include module=system template=inc_object_history object=$_affectation}}
        {{mb_value object=$_affectation field=effectue}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CAffectation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
