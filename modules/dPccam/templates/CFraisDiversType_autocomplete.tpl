{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<strong>{{$match->code}} ({{$match->tarif|floatval}})</strong>
<small>{{$match->libelle}}</small>

<span class="view" style="display: none">{{$match->_view}}</span>
<span class="tarif" style="display: none">{{$match->tarif}}</span>
<span class="facturable" style="display: none">{{$match->facturable}}</span>
