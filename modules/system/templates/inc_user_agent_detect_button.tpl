{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="fa fa-check notext compact" type="button" {{if $detected != $ua->$field && $detected != "unknown" && $detected != "0.0"}} style="background: orange !important;" {{/if}}
        onclick="UserAgent.updateNameFromDetection('{{$detected}}', '{{$field}}', this);"></button>
{{$detected}}