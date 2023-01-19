{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=board script=tabs_prescription ajax=true}}

{{assign var=prescription_active value="dPprescription"|module_active}}
{{assign var=plan_soins_active   value="planSoins"|module_active}}

<script>
  Main.add(function() {
    Control.Tabs.create('tab-prescription', true);
    TabsPrescription.date    = '{{$date}}';
    TabsPrescription.prat_id = '{{$chirSel}}';
    TabsPrescription.function_id = '{{$function_id}}';

    {{if $prescription_active}}
      TabsPrescription.prescription_active = true;
    {{/if}}

    {{if $plan_soins_active}}
      TabsPrescription.plan_soins_active = true;
    {{/if}}

    TabsPrescription.initPeriodicalUpdaters();
  });
</script>

<table class="main layout">
  <tr>
    <td class="narrow">
      <ul id="tab-prescription" class="control_tabs_vertical small" style="width: 9em; font-size: 1.1em;">
        {{if $prescription_active}}
          <li>
             <a href="#prescriptions_non_signees" class="empty count">
               {{tr}}Worklist.prescriptions_non_signees{{/tr}} <small>(&ndash;)</small>
             </a>
          </li>
          <li>
            <a href="#inscriptions" class="empty count">
              {{tr}}Worklist.inscriptions{{/tr}} <small>(&ndash;)</small>
            </a>
          </li>
          <li>
            <a href="#antibios_reeval" class="empty count">{{tr}}Worklist.antibios_reeval{{/tr}} <small>(&ndash;)</small></a>
          </li>
          <li>
            <a href="#com_pharma" class="empty count">{{tr}}Worklist.com_pharma{{/tr}} <small>(&ndash;)</small></a>
          </li>
          {{if $plan_soins_active}}
            <li>
              <a href="#adm_annulees" class="empty count">{{tr}}Worklist.adm_cancelled{{/tr}} <small>(&ndash;)</small></a>
            </li>
          {{/if}}
          <li>
            <a href="#reeval" class="empty count">{{tr}}Worklist.reeval{{/tr}} <small>(&ndash;)</small></a>
          </li>
        {{/if}}
      </ul>
    </td>
    <td>
      {{if "dPprescription"|module_active}}
        <div id="prescriptions_non_signees" style="display: none;"></div>
        <div id="inscriptions" style="display: none;"></div>
        <div id="antibios_reeval" style="display: none;"></div>
        <div id="com_pharma" style="display: none;"></div>
        <div id="adm_annulees" style="display: none;"></div>
        <div id="reeval" style="display: none;"></div>
      {{/if}}
    </td>
  </tr>
</table>



