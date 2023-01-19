{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    {{foreach from='Ox\Mediboard\Mediusers\CMediusers::loadFonctions'|static_call:null key=function_id item=_function}}
      <option value="{{$function_id}}" {{if $function_id == $value}}selected{{/if}}>
        {{$_function->text}}
      </option>
    {{/foreach}}
  </select>
{{else}}
  {{if $value}}
    {{$value}}
  {{/if}}
{{/if}}