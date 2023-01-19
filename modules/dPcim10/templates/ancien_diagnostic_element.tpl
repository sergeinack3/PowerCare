{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
  {{if $diagnostic}}
    <button type="button" title="{{tr}}CCodeCIM10-copy{{/tr}}" data-cim="{{$diagnostic->code}}"
            class="notext {{$diagnostic_type}}-setter {{if $cancel}}cancel{{else}}add{{/if}}"
            onclick="AnciensDiagnostics.{{if $cancel}}removeDiag{{else}}setDiag{{/if}}('{{$diagnostic->code}}',
                    '{{$current_object->_guid}}',
                    '{{$diagnostic_type}}',
                    false)"></button>
    {{$diagnostic->code}} :
    <span style="font-weight: bold; cursor: default" title="{{$diagnostic->libelle}}">{{$diagnostic->libelle|truncate:30:"...":true}}</span>
  {{else}}
    <span class="empty">{{tr}}CDiagnostic.none{{/tr}}</span>
  {{/if}}
</td>
