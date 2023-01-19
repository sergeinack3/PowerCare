{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $log_ok}}
  <div class="small-info">
    {{"<br/>"|implode:$log_ok}}
  </div>
{{/if}}

{{if $log_err}}
  <div class="small-error">
    {{"<br/>"|implode:$log_err}}
  </div>
{{/if}}

{{if !$log_err && !$log_ok}}
  <div class="small-error">
    Aucun log
  </div>
{{/if}}