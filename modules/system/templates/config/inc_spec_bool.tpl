{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <label>
    <input type="radio" class="{{$_prop.string}}" name="c[{{$_feature}}]" value="1" {{if $value == 1}} checked {{/if}} {{if $is_inherited}} disabled {{/if}} />
    {{tr}}Yes{{/tr}}
  </label>
  <label>
    <input type="radio" class="{{$_prop.string}}" name="c[{{$_feature}}]" value="0" {{if $value == 0}} checked {{/if}} {{if $is_inherited}} disabled {{/if}} />
    {{tr}}No{{/tr}}
  </label>
{{else}}
  {{if $value === "1"}}
    {{tr}}Yes{{/tr}}
  {{elseif $value === "0"}}
    {{tr}}No{{/tr}}
  {{else}}
    {{tr}}Unknown{{/tr}}
  {{/if}}
{{/if}}