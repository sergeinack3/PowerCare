{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=sectors value=false}}

{{foreach from=$contexts item=_context name=contexts}}
  {{if $smarty.foreach.contexts.index % 6 == 0}}
    <tr>
  {{/if}}
  <td style="vertical-align: top;">
    <label class="me-small-fields">
      <input type="checkbox" name="context_{{$_context->_guid}}" data-guid="{{$_context->_guid}}"
             data-name="{{$_context}}" data-class="{{$context_class}}"
             onchange="{{if $context_class == 'CService' && $sectors}}Context.oncheckSector(this);{{else}}Context.oncheck(this);{{/if}}" />
      {{if $context_class == 'CService' && $sectors}}
        <strong>{{$_context}}</strong>
      {{else}}
        {{$_context}}
      {{/if}}
    </label>
    {{if $context_class == 'CService' && $sectors}}
      <ul style="list-style-type: none; margin-left: 1em;" id="sector_{{$_context->_guid}}">
        {{foreach from=$_context->_ref_services item=_service}}
          <li>
            <label class="me-small-fields">
              <input type="checkbox" name="context_{{$_service->_guid}}" data-guid="{{$_service->_guid}}"
                     data-name="{{$_service}}" data-class="{{$context_class}}" onchange="Context.oncheck(this);" />
              {{$_service}}
            </label>
          </li>
        {{/foreach}}
      </ul>
    {{/if}}
  </td>
  {{if $smarty.foreach.contexts.index % 6 == 5 && !$smarty.foreach.contexts.last}}
    </tr>
  {{/if}}
{{/foreach}}

{{if $count != 0}}
  {{* Affichage des services hors secteur *}}
  {{if $context_class == 'CService' && $out_sector|@count != 0}}
    {{assign var=colspan value=0}}
    {{if $count % 6 != 0}}
      {{math assign=colspan equation="5 - (x % 6)" x=$count}}
      <td>
    {{elseif $count > 6}}
      </tr>
      <tr>
      <td colspan="6">
    {{/if}}
    <input type="checkbox" name="context_out" data-guid="out" onchange="Context.oncheckSector(this);" />
    <strong>Hors secteur</strong>
    <ul id="sector_out" style="list-style-type: none; margin-left: 1em;">
      {{foreach from=$out_sector item=_service}}
        <li>
          <label class="me-small-fields">
            <input type="checkbox" name="context_{{$_service->_guid}}" data-guid="{{$_service->_guid}}"
                   data-name="{{$_service}}" data-class="{{$context_class}}" onchange="Context.oncheck(this);" />
            {{$_service}}
          </label>
        </li>
      {{/foreach}}
    </ul>
    </td>
    {{if $colspan != 0 && $count > 6}}
      <td colspan="{{$colspan}}">

      </td>
    {{/if}}

    </tr>
  {{else}}
    {{if $count % 6 != 0 && $count > 6}}
      {{math assign=colspan equation="6 - (x % 6)" x=$count}}
      <td colspan="{{$colspan}}"></td>
    {{/if}}
    </tr>
  {{/if}}
{{else}}
  <tr>
    <td class="empty">Aucun contexte</td>
  </tr>
{{/if}}
