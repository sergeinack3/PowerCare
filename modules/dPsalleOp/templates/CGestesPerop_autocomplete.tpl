{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left">
  {{foreach from=$matches item=match}}
    <li id="autocomplete-{{$match->_guid}}"
        data-id="{{$match->_id}}"
        data-guid="{{$match->_guid}}"
        data-desc="{{$match->description}}"
        data-categorie_id="{{$match->categorie_id}}"
        data-incident="{{$match->incident}}"
        data-antecedent_code_cim="{{$match->antecedent_code_cim}}">
      {{$match}}
    </li>
    {{foreachelse}}
    <li>
    <span class="informal">
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </span>
    </li>
  {{/foreach}}
</ul>
