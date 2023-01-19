{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view me-padding-left-3"{{if $match->color}} style="border-left: 3px solid #{{$match->color}}" data-color="#{{$match->color}}"{{else}} data-color=""{{/if}}>{{$match}}</span>