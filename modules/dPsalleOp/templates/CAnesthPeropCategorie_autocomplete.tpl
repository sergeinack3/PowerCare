{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left">
  {{foreach from=$matches item=match}}
    <li id="autocomplete-{{$match->_guid}}" data-id="{{$match->_id}}" data-guid="{{$match->_guid}}"
        data-desc="{{$match->description}}">
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
