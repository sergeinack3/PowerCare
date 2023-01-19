{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $info.instance_size > 0}}
  {{math assign=instance_use     equation="(y/x)*100"   x=$info.total y=$info.instance_size}}
  {{math assign=total_use        equation="(y/x)*100"   x=$info.total y=$info.instance_size}}
  {{math assign=total_use_offset equation="(y/x)*100-z" x=$info.total y=$info.used z=$instance_use}}
{{else}}
  {{assign var=instance_use     value=0}}
  {{assign var=total_use        value=0}}
  {{assign var=total_use_offset value=0}}
{{/if}}
<div class="tab-bargraph" style="background-image: linear-gradient(90deg,#62afcf {{$instance_use}}%,#e6a201 {{$instance_use}}%,#e6a201 {{$total_use_offset}}%,transparent {{$total_use_offset}}%);"></div>
