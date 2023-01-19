{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$constants item=_constant}}
  <span class="texticon">{{$_constant->_glycemie}}</span> ({{$_constant->datetime|date_format:$conf.time}})<br>
{{/foreach}}
