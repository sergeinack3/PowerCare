{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$locations item=_location}}
    <li class="{{$_location->_guid}}">
      <span class="view">{{$_location->_shortview}}</span>
    </li>
    {{foreachelse}}
    <li>{{tr}}CProductStockLocation.none{{/tr}}</li>
  {{/foreach}}
</ul>