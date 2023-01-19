{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      {{tr}}CModule{{/tr}} {{$module->_view}} &mdash; {{$user->_view}}
    </th>
  </tr>
  <tr>
    <th class="halfPane">
      {{tr}}User template{{/tr}}
    </th>
    <th>
      {{tr}}User{{/tr}}
    </th>
  </tr>
  <tr>
    <td>
      {{mb_value object=$perm_profil field=permission}} - {{mb_value object=$perm_profil field=view}}
    </td>
    <td>
      <form name="editPerm" method="post"
            onsubmit="return confirmDeletion(
              this, {typeName: $T('CPermModule') + ' ' + $T('from'), objName:'{{$module->_view|smarty:nodefaults|JSAttribute}}'},
              function() { Perm.removeBgSpecifiqTd(); Control.Modal.close(); });">
        {{mb_class object=$perm_user}}
        {{mb_key   object=$perm_user}}
        <input type="hidden" name="del" value="1" />

        <button type="button" class="trash notext" onclick="this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
      </form>

      {{mb_value object=$perm_user field=permission}} - {{mb_value object=$perm_user field=view}}
    </td>
  </tr>
</table>