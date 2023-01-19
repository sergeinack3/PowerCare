{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning      ajax=true}}
{{mb_script module=ssr script=planification ajax=true}}

{{mb_default var=in_modal value=0}}
{{if $bilan->_id && !$bilan->planification}} 
  <div class="small-info">
    {{tr}}CBilanSSR-msg-planification-off{{/tr}}
    <br />
    {{tr}}CBilanSSR-msg-planification-cf{{/tr}}
  </div>
{{else}}
  <script>
    onKeyDelete = function(e) {
      if (Event.key(e) == Event.KEY_DELETE) {
        $("editSelectedEvent_delete").onclick();
      }
    };

    Main.add(function(){
      Planification.current_m = '{{$current_m}}';
      Planification.showWeek(null, 'planif', '{{$sejour->_id}}');

      var planning = $("planning");
      var vp = document.viewport.getDimensions();
      var top = planning.cumulativeOffset().top;
      planning.setStyle({
        height: (vp.height-top-20)+"px"
      });
      document.observe("keydown", onKeyDelete);
      {{if $in_modal}}
        Planification.current_m = '{{$current_m}}';
        Planification.refresh('{{$sejour->_id}}');
      {{/if}}
    });
  </script>

  <div id="week-changer"></div>

  <table class="main" id="planning" style="table-layout: fixed;">
    <col style="width: 50%;" />

    <tr style="height: 50%;">
      <td style="height: 50%;">
        <div style="position: relative; height: 100%;">
          {{assign var=use_pdf value="ssr print_week new_format_pdf"|gconf}}
          <button type="button" style="position: absolute; top: 0; right: 0;" class="print notext me-top-2 me-tertiar me-white"
                  onclick="Planification.printPlanningSejour('{{$sejour->_id}}', null, '{{$use_pdf}}');">
            {{tr}}Print{{/tr}}
          </button>
          {{if $use_pdf}}
            <button type="button" class="far fa-window-maximize notext me-right-32 me-top-2 me-tertiary me-white" style="position: absolute; top: 0; right: 20px;"
                    onclick="Planification.printPlanningSejour('{{$sejour->_id}}', null, 0, 1);">
              {{tr}}ssr-planning_patient_plein_ecran{{/tr}}
            </button>
          {{/if}}
          <div id="planning-sejour" style="height: 100%;"></div>
        </div>
      </td>
      <td id="activites-sejour"></td>
    </tr>

    <tr style="height: 50%;">
      <td style="height: 50%;">
        <div style="position: relative; height: 100%;">
          {{if $current_m == "ssr"}}
            <button type="button" style="position: absolute; top: 0; right: 0;" class="change notext me-top-2" onclick="PlanningTechnicien.toggle();"></button>
          {{/if}}
          <div id="planning-technicien" style="height: 100%;"></div>
        </div>
      </td>
      <td style="height: 50%;">
        <!-- it's better to have the same dom here -->
        <div style="position: relative; height: 100%;">
          <div id="planning-equipement" style="height: 100%;"></div>
        </div>
      </td>
    </tr>
  </table>
{{/if}}
