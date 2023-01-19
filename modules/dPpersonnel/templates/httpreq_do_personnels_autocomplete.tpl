{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches item=match}}
    <li id="match-{{$match->_id}}" data-id="{{$match->_id}}">
      <strong class="view">{{$match->_view|emphasize:$keywords}}</strong><br />
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