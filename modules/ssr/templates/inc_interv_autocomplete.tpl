{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left;">
  {{foreach from=$mediusers item=_mediuser}}
    <li data-id="{{$_mediuser->_id}}">
      <span class="view">{{$_mediuser->_view}}</span>
    </li>
  {{/foreach}}
</ul>