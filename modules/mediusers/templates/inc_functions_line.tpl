{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr class="{{if !$type}}mediuser_group_line{{/if}}">
  <td class="narrow button">
    {{if $type === 'primary'}}
      <button class="text-button mediuser-flat-button mediuser-flat-button-pf" title="{{tr}}CMediusers-function_id{{/tr}}">P</button>
    {{elseif $type === 'secondary'}}
        <button type="button" class="text-button{{if !$can->edit}} mediuser-flat-button{{/if}} mediuser-button-sf"
                {{if $element->_id !== $user->function_id && $can->edit}}
                  onclick="CMediuserFunctions.upgradeFunction({{$function->_id}}, {{$secondary_function->_id}})"
                {{/if}} title="{{tr}}CSecondaryFunction{{/tr}}">S</button>
      </form>
    {{elseif $type === 'permission'}}
      <button type="button" class="text-button mediuser-flat-button mediuser-flat-button-wp" title="{{tr}}Permission{{/tr}}">A</button>
    {{elseif !$type}}
      <button type="button" class="text-button mediuser-flat-button mediuser-flat-button-g {{if !$perm}}opacity-50{{/if}}"
              title="{{tr}}CGroups{{/tr}}">
        <i class="fas fa-building"></i>
      </button>
    {{/if}}
  </td>
  <td>
    {{if $type}}
      &rarrhk; <span {{if $type === 'primary'}}style="font-weight: bold"{{/if}}>{{$element}}</span>
    {{else}}
      {{$element}}
    {{/if}}
  </td>
  <td>
    {{if $perm}}
      {{if $can->edit}}
        <button type="button" class="edit notext"
                onclick="CMediuserFunctions.editPermission({{$perm->_id}}, {{$perm->permission}}, '{{$element->_view|htmlspecialchars}}');">
          {{tr}}CMediuser-Functions edit perm{{/tr}}
        </button>
      {{/if}}
      {{mb_value object=$perm field=permission}}
    {{elseif $can->edit}}
        <button type="button" class="add notext"
                onclick="CMediuserFunctions.addPermission('{{$element->_id}}', '{{$element->_class}}');">
          {{tr}}CMediuser-Functions add perm{{/tr}}
        </button>
        <span class="empty">{{tr}}None{{/tr}}</span>
    {{/if}}
  </td>
  <td class="narrow">
    {{if $can->edit}}
      {{if $type === 'secondary'}}
        <button type="button" class="trash notext" onclick="CMediuserFunctions.deleteFunction(
                {{$secondary_function->_id}},
                '',
                {{if $perm}}{{$perm->_id}}{{else}}null{{/if}})">
          {{tr}}CMediuser-Functions delete secondary function{{/tr}}
        </button>
      {{elseif $type === 'permission'}}
        <button type="button" class="add notext"
                onclick="CMediuserFunctions.updateFunction({{$element->_id}}, '{{$element->_view|htmlspecialchars}}', '{{$user}}')">
          {{tr}}CMediuser-Functions add secondary function{{/tr}}
        </button>
      {{/if}}
    {{/if}}
  </td>
</tr>