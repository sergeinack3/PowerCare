{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="section" colspan="6">{{tr}}CPermObject{{/tr}}</th>
</tr>

{{if !$new_profile}}
  <tr>
    <th class="section" colspan="3"></th>
    <th class="section">
      <input type="radio" name="check-all-perms-obj" onclick="ImportUsers.checkAllRadio('profile-import-use-perm-obj', 'old');" checked/>
    </th>
    <th class="section">
      <input type="radio" name="check-all-perms-obj" onclick="ImportUsers.checkAllRadio('profile-import-use-perm-obj', 'new');"/>
    </th>
    <th class="section"></th>
  </tr>
{{/if}}

{{foreach from=$compare.perms_object key=_class_name item=_values}}
  <tr>
    <th align="center" colspan="2">{{tr}}{{$_class_name}}{{/tr}}</th>

    {{if !$new_profile}}
      <td align="center" {{if $_values.old == -1}}class="error"{{/if}}>
        {{if $_values.old != -1}}
          {{tr}}CPermObject.permission.{{$_values.old}}{{/tr}}
        {{else}}
          {{tr}}CUser-import-perm-missing{{/tr}}
        {{/if}}
      </td>

      <td class="narrow" align="center">
        <input type="radio" name="use_{{$_class_name}}_perms" class="profile-import-use-perm-obj" value="old" checked/>
      </td>
      <td class="narrow" align="center">
        <input type="radio" name="use_{{$_class_name}}_perms" class="profile-import-use-perm-obj" value="new"/>
      </td>
    {{/if}}

    <td align="center" {{if $new_profile}}colspan="4" {{/if}} {{if $_values.new == -1}}class="error"
        {{else}}{{if $_values.old != -1 && $_values.new != $_values.old}}class="warning"{{/if}}{{/if}}>
      {{if $_values.new != -1}}
        {{tr}}CPermObject.permission.{{$_values.new}}{{/tr}}
      {{else}}
        {{tr}}CUser-import-perm-missing{{/tr}}
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" align="center" colspan="6">
      {{tr}}CPermObject.none{{/tr}}
    </td>
  </tr>
{{/foreach}}