{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $notification}}
  <div class="small-info" style="display: inline-block;">
    Notification param�tr�e � J-{{$notification->days}} jours
  </div>
{{else}}
  <div class="small-warning" style="display: inline-block;">
    Aucune notification param�tr�e pour cet �v�nement
  </div>
{{/if}}