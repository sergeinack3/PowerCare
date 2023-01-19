{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=colspan value=2}}
{{mb_default var=can_delete value=true}}
{{mb_default var=options value="{}"}}
{{mb_default var=options_ajax value="{}"}}


<tr>
  <td class="button" colspan="{{$colspan}}">
    <button class="save">{{tr}}Save{{/tr}}</button>
    {{if $object->_id && $can_delete}}
      <button class="trash" type="button" onclick="
      confirmDeletion(this.form,
      {{$options}}
      ,
      {{$options_ajax}})
      ">{{tr}}Delete{{/tr}}</button>
    {{/if}}
  </td>
</tr>