{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $tutelle != "aucune" && !$has_tutelle}}
  <div class="big-warning">
    {{tr}}CPatient-alert_tutelle{{/tr}}
  </div>
{{/if}}