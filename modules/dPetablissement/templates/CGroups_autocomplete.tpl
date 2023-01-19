{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view" data-finess="{{$match->finess}}">{{if $show_view || !$f}}{{$match}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}</span>
