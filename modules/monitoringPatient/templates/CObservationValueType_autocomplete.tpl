{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view" style="display: none;">{{$match->_view}}</span>

<span class="compact" style="float: right; white-space: nowrap; text-align: right;">
  {{$match->coding_system}}<br>[{{$match->code}}]
</span>

<strong>{{$match->label}}</strong>

{{if $match->desc && $match->label != $match->desc}}
  <br />
  <span class="compact">{{$match->desc}}</span>
{{/if}}