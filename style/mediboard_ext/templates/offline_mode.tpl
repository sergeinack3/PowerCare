{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $conf.offline_non_admin}}
  <div class="small-warning">
    <strong>{{tr}}common-warning-Mediboard is in maintenance mode accessible to administrators{{/tr}}</strong>
  </div>
{{/if}}