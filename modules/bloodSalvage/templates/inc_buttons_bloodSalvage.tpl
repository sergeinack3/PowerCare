{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=salvage value=$operation->_ref_blood_salvage}}

{{if $salvage->_id}}
  <div style="float:left ; display:inline">
    <button type="button" class="me-tertiary me-small" onclick="viewRSPO({{$operation->_id}});"
            title="{{tr}}CBloodSalvage-action-See the RSPO procedure{{/tr}}">
      <i class="fas fa-search"></i>
        {{if $salvage->_totaltime > "00:00:00"}}
            {{tr var1=$salvage->_recuperation_start|date_format:$conf.time}}CBloodSalvage-Started at %s{{/tr}}
        {{else}}
            {{tr}}CBloodSalvage-Not started{{/tr}}
        {{/if}}
    </button>
  </div>
    {{if $salvage->_totaltime|date_format:$conf.time > "05:00"}}
      <div class="me-float-right" style="display:inline">

        {{me_img src="warning.png" icon="warning" class="me-warning" title="CBloodSalvage-Legal duration soon to be reached" alt="alerte-durée-RSPO"}}
    {{/if}}
  </div>
{{else}}
    {{tr}}CBloodSalvage-Not registered{{/tr}}
{{/if}}
