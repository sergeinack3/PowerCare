{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="section" colspan="6">{{tr}}CPermModule{{/tr}}</th>
</tr>

{{if !$new_profile}}
  <tr>
    <th class="section" colspan="3"></th>
    <th class="section narrow">
      <input type="radio" name="check-all-perm-mod"
             onclick="ImportUsers.checkAllRadio('profile-import-use-perm-mod', 'old'); ImportUsers.checkAllRadio('profile-import-use-view', 'old');" checked/>
    </th>
    <th class="section narrow">
      <input type="radio" name="check-all-perm-mod"
             onclick="ImportUsers.checkAllRadio('profile-import-use-perm-mod', 'new'); ImportUsers.checkAllRadio('profile-import-use-view', 'new');"/>
    </th>
    <th class="section"></th>
  </tr>
{{/if}}

{{foreach from=$compare.perms_module key=_module_name item=_values}}
  <tr>
    <th rowspan="2" align="center" class="narrow">{{tr}}module-{{$_module_name}}-court{{/tr}}</th>
    <th align="center" class="narrow">{{tr}}CPermModule-permission{{/tr}}</th>

    {{if !$new_profile}}
      <td align="center" {{if $_values.old.perm == 0 && !$_values.new.perm == 0}}class="error"{{/if}}>
        {{tr}}CPermModule.permission.{{$_values.old.perm}}{{/tr}}
      </td>

      <td class="narrow" align="center">
        <input type="radio" name="use_{{$_module_name}}_perm" class="profile-import-use-perm-mod" value="old" checked/>
      </td>
      <td class="narrow" align="center">
        <input type="radio" name="use_{{$_module_name}}_perm" class="profile-import-use-perm-mod" value="new"/>
      </td>
    {{/if}}

    <td align="center" {{if $new_profile}}colspan="4"{{/if}} {{if $_values.new.perm == 0 && !$_values.old.perm == 0}}class="error"
        {{else}}{{if $_values.old.perm && $_values.old.perm != $_values.new.perm}}class="warning"{{/if}}{{/if}}>
      {{tr}}CPermModule.permission.{{$_values.new.perm}}{{/tr}}
    </td>
  </tr>

  <tr>
    <th align="center" class="narrow">{{tr}}CPermModule-view{{/tr}}</th>

    {{if !$new_profile}}
      <td align="center" {{if $_values.old.view == 0 && !$_values.new.view == 0}}class="error"{{/if}}>
        {{tr}}CPermModule.view.{{$_values.old.view}}{{/tr}}
      </td>

      <td class="narrow" align="center">
        <input type="radio" name="use_{{$_module_name}}_view" class="profile-import-use-view" value="old" checked/>
      </td>
      <td class="narrow" align="center">
        <input type="radio" name="use_{{$_module_name}}_view" class="profile-import-use-view" value="new"/>
      </td>
    {{/if}}

    <td align="center" {{if $new_profile}}colspan="4" {{/if}} {{if $_values.new.view == 0 && !$_values.old.view == 0}}class="error"
        {{else}}{{if $_values.old.view && $_values.old.view != $_values.new.view}}class="warning"{{/if}}{{/if}}>
      {{tr}}CPermModule.view.{{$_values.new.view}}{{/tr}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" align="center" colspan="6">
      {{tr}}CPermModule.none{{/tr}}
    </td>
  </tr>
{{/foreach}}