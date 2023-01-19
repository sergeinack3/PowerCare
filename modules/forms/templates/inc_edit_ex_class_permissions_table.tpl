{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th></th>
    <th class="narrow" title="{{tr}}CExClass.permissions.c{{/tr}}">
      <label>
        <i class="far fa-file"></i><br/>
        <input type="checkbox" onclick="ExClass.togglePermissionColumn(this, 'c')"/>
      </label>
    </th>
    <th class="narrow" title="{{tr}}CExClass.permissions.e{{/tr}}">
      <label>
        <i class="fa fa-edit"></i><br/>
        <input type="checkbox" onclick="ExClass.togglePermissionColumn(this, 'e')"/>
      </label>
    </th>
    <th class="narrow" title="{{tr}}CExClass.permissions.v{{/tr}}">
      <label>
        <i class="fa fa-eye"></i><br/>
        <input type="checkbox" onclick="ExClass.togglePermissionColumn(this, 'v')"/>
      </label>
    </th>
    <th class="narrow" title="{{tr}}CExClass.permissions.d{{/tr}}">
      <label>
        <i class="fa fa-ban"></i><br/>
        <input type="checkbox" onclick="ExClass.togglePermissionColumn(this, 'd')"/>
      </label>
    </th>
  </tr>
  {{foreach from=$ex_class->_permissions key=_type item=_perms}}
    {{if array_key_exists($_type, $list)}}
      <tr class="{{if $_perms|@array_sum > 0}}active{{/if}} {{if $_perms.d}}denied{{/if}}">
        <td class="title">
          {{if $all_types.$_type == "Administrator"}}
            Administrateur
          {{else}}
            {{$all_types.$_type}}
          {{/if}}
        </td>
        {{foreach from=$_perms key=_perm item=_active}}
          <td class="checkbox">
            {{if $_type != -10 || $_perm != 'c'}}
              <label>
                <input type="checkbox" data-type="{{$_type}}" data-perm="{{$_perm}}"
                       onchange="ExClass.togglePermission(this)"
                       {{if $_perms.d && $_perm !== 'd'}}disabled{{/if}}
                       {{if $_active}}checked{{/if}}
                />
              </label>
            {{/if}}
          </td>
        {{/foreach}}
      </tr>
    {{/if}}
  {{/foreach}}
</table>
