{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$errors item=_error}}
    {{if $_error.type == 1}}
      <div class="small-error" style="display: inline-block">
          {{tr var1=$host}}CElasticDataSource-msg-Failed to connect to elastic node : %s{{/tr}}
      </div>
    {{/if}}
{{/foreach}}
