{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
  {{foreach from=$diagnostics item=_diagnostic}}
    {{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
      {{assign var=diagnostic_name value=$_diagnostic->libelle}}
    {{else}}
      {{assign var=diagnostic_name value=$_diagnostic->libelle}}
    {{/if}}
    <ul>
      <li>
        <button type="button" title="{{tr}}CCodeCIM10-copy{{/tr}}" data-cim="{{$_diagnostic->code}}"
                class="notext {{$diagnostic_type}}-setter {{if $cancel}}cancel{{else}}add{{/if}}"
                onclick="AnciensDiagnostics.{{if $cancel}}removeDiag{{else}}setDiag{{/if}}('{{$_diagnostic->code}}',
                        '{{$current_object->_guid}}',
                        '{{$diagnostic_type}}',
                        true)"></button>
        {{$_diagnostic->code}} :
        <span style="font-weight: bold" title="{{$diagnostic_name}}">
          {{$diagnostic_name|truncate:30:"...":true}}
        </span>
      </li>
    </ul>
  {{foreachelse}}
    <span class="empty">{{tr}}CDiagnostic.none{{/tr}}</span>
  {{/foreach}}
</td>
