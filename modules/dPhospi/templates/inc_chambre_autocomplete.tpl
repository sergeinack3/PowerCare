{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches item=match}}
    <li id="autocomplete-{{$match->_guid}}"
        data-id="{{$match->_id}}" data-guid="{{$match->_guid}}"
        data-service-id="{{$match->service_id}}">
      {{mb_include module=system template=CMbObject_autocomplete nodebug=true}}
    </li>
    {{foreachelse}}
    <li>
      <span class="informal">
        <span class="view"></span>
        <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
      </span>
    </li>
  {{/foreach}}
</ul>