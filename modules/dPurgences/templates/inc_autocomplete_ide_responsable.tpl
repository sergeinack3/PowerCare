{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches item=match}}
    <li id="match-{{$match->_id}}" data-id="{{$match->_id}}" data-name="{{$match->_view}}">
      <strong class="view">{{$match->_view|emphasize:$keywords}}</strong><br />
    </li>
    {{foreachelse}}
    <li style="text-align: left;"><span class="informal">{{tr}}No result{{/tr}}</span></li>
  {{/foreach}}
</ul>