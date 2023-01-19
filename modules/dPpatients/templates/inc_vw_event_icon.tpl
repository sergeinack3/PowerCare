{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=indent value=0}}

{{if $indent > 0}}
  <div style="display: inline-block; width: {{$indent}}em;"></div>
{{/if}}

<i class="{{$event_icon.icon}} event-icon" style="background-color: {{$event_icon.color}};" title="{{$event_icon.title}}"></i>