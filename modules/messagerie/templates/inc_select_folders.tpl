{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=select_types value=true}}
{{mb_default var=iteration_max value=2}}
{{mb_default var=subject value=false}}

<select name="_folder" onchange="selectParent(this);">
  <option value="">&mdash; {{tr}}Select{{/tr}}</option>
  {{assign var=iteration value=0}}
  {{foreach from=$folders key=_type item=_subfolders}}
    <option value="{{$_type}}" data-type="{{$_type}}"{{if !$select_types}} disabled{{elseif !$parent_id && $type == $_type}} selected{{/if}}>
      {{tr}}CUserMail-title-{{$_type}}{{/tr}}
    </option>
    {{if $_subfolders|@count != 0}}
      {{foreach from=$_subfolders item=_folder}}
        {{mb_include module=messagerie template=inc_select_folder folder=$_folder type=$_type iteration=$iteration+1}}
      {{/foreach}}
    {{/if}}
  {{/foreach}}
</select>