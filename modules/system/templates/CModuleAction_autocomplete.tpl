{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <span class="view">{{if $show_view || !$f}}{{$match->_view}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}</span>

  <div style="text-align: right; color: #999; font-size: 0.8em;">{{mb_value object=$match field=module}}</div>
</div>