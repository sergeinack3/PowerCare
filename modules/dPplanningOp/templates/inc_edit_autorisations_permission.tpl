{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=autorisation_permission ajax=$ajax}}

{{assign var=is_praticien value=$app->_ref_user->isPraticien()}}

{{if $is_praticien}}
  <button type="button" class="new" onclick="AutorisationPermission.edit(null, '{{$sejour->_id}}');">
    {{tr}}CAutorisationPermission-title-create{{/tr}}
  </button>
{{/if}}

<table class="tbl">
  <tr>
    {{if $is_praticien}}
      <th class="narrow"></th>
    {{/if}}
    <th>
      {{mb_title class=CAutorisationPermission field=praticien_id}}
    </th>
    <th>
      {{mb_title class=CAutorisationPermission field=debut}}
    </th>
    <th>
      {{mb_title class=CAutorisationPermission field=duree}}
    </th>
    <th>
      {{mb_title class=CAutorisationPermission field=motif}}
    </th>
    <th>
      {{mb_title class=CAutorisationPermission field=rques}}
    </th>
  </tr>

  {{foreach from=$sejour->_ref_autorisations_permission item=_autorisation_permission}}
    <tr>
      {{if $is_praticien}}
        <td>
          <button type="button" class="edit notext"
                  onclick="AutorisationPermission.edit('{{$_autorisation_permission->_id}}');"></button>
        </td>
      {{/if}}
      <td>
        {{mb_value object=$_autorisation_permission field=praticien_id}}
      </td>
      <td>
        {{mb_value object=$_autorisation_permission field=debut}}
      </td>
      <td>
        {{mb_value object=$_autorisation_permission field=duree}} {{tr}}common-hour|pl{{/tr}}
      </td>
      <td>
        {{mb_value object=$_autorisation_permission field=motif}}
      </td>
      <td>
        {{mb_value object=$_autorisation_permission field=rques}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CAutorisationPermission.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
