{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $preference}} 
  <span onmouseover="ObjectTooltip.createEx(this, '{{$preference->_guid}}');">
    {{if $preference->value === ""}}
      <em>({{tr}}empty{{/tr}})</em> 
    {{elseif $preference->value === null}}
      <em>({{tr}}ditto{{/tr}})</em> 
    {{else}}
      {{$preference->value}}
    {{/if}}
  </span>
{{/if}}
