{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=checked value=0}}

{{foreach from=$constants.all item=_rank}}
  {{foreach from=$_rank item=_constant}}
    {{if strpos($_constant, '_cumul') === false}}
      <tr class="alternate" style="cursor: pointer;">
        <td class="narrow">
          <input class="check_constant" type="checkbox" id="cb_{{$_constant}}" name="{{$_constant}}"
                 onclick="ConstantConfig.oncheck(this);"{{if $checked && in_array($_constant, $checked)}} checked{{/if}}>
        </td>
        <td class="constant"
            onclick="$('cb_{{$_constant}}').checked = !$('cb_{{$_constant}}').checked; $('cb_{{$_constant}}').onclick();">
          {{tr}}CConstantesMedicales-{{$_constant}}{{/tr}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
{{/foreach}}