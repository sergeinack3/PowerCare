{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $versions}}
  <select name="version">
    <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
    {{foreach from=$versions item=_version}}
      <option value="{{$_version}}" {{if $transformation_rule_sequence->version == $_version}}selected{{/if}}>{{$_version}}</option>
    {{/foreach}}
  </select>
{{/if}}