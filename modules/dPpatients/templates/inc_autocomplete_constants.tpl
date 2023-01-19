{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$results key=_constant item=_params}}
    {{if $show_formfields && array_key_exists('formfields', $_params) && count($_params.formfields) > 1}}
      {{foreach from=$_params.formfields item=_field}}
        <li data-constant="{{$_field}}" data-unit="{{$_params.unit}}" data-min="{{$_params.min}}" data-max="{{$_params.max}}">
          <strong class="view">
            {{tr}}CConstantesMedicales-{{$_field}}-desc{{/tr}}
          </strong>
          {{if $show_main_unit}}
            <small>({{$_params.unit}})</small>
          {{/if}}
        </li>
      {{/foreach}}
    {{else}}
      <li data-constant="{{$_constant}}" data-unit="{{$_params.unit}}" data-min="{{$_params.min}}" data-max="{{$_params.max}}">
        <strong class="view">
          {{tr}}CConstantesMedicales-{{$_constant}}-desc{{/tr}}
        </strong>
        {{if $show_main_unit}}
          <small>({{$_params.unit}})</small>
        {{/if}}
      </li>
    {{/if}}
    {{foreachelse}}
    <li>
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>