{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    ViewPort.SetAvlHeight("planning-technicien", 1);
    var height = $('planning-technicien').getDimensions().height - 50;
    PlanningTechnicien.show('{{$kine_id}}', null, null, height, true, true);
  });
</script>

<form name="planning_view" action="?" method="get">
  <input type="hidden" name="surveillance" value="0"/>
</form>

{{if $count_evts}}
  <div class="small-warning">
    <button type="button" class="search notext" onclick="PlanningTechnicien.showEvtsOldNotValide('{{$kine_id}}')"
      onmouseover="ObjectTooltip.createDOM(this, $('tooltip_not_validate'), {duration: 0})"></button>
     {{$count_evts}} {{tr}}CEvenementSSR-no_validated_week_previous{{/tr}}.
  </div>
  <div id="tooltip_not_validate" style="display: none">
    <table class="form me-no-align me-no-box-shadow">
      <tr>
        <th class="category">{{tr}}Week{{/tr}} {{tr}}Number-court{{/tr}}</th>
        <th class="category">{{tr}}mod-ssr-tab-vw_evts_kine_not_valide{{/tr}}</th>
      </tr>
      {{foreach from=$count_by_week item=_count key=_week}}
        <tr>
          <td style="text-align: center;">{{$_week}}</td>
          <td style="text-align: center;">{{$_count}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
{{/if}}

<div style="position: relative">
  <div style="position: absolute; top: 0px; left: 3em;" class="me-white-context">
    <button type="button" class="tick singleclick" {{if $kine->_id && !$kine->_can->edit}}disabled="disabled"{{/if}}
            onclick="ModalValidation.set({realise: '1'}); ModalValidation.update();">
      {{tr}}Validate{{/tr}}
    </button>
    <button type="button" class="erase notext singleclick" {{if $kine->_id && !$kine->_can->edit}}disabled="disabled"{{/if}}
            onclick="ModalValidation.set({realise: '0', annule: '0'}); ModalValidation.submit();">
      {{tr}}Erase{{/tr}}
    </button>
  </div>
  <div style="position: absolute; top: 0px; right: 0px;" class="me-white-context me-right-4">
    <button type="button" class="switch notext"
            onclick="Modal.open($('transfert_evts_technicien'), { width: '500' });">
      {{tr}}CEvenementSSR-Transfer-evts_selected{{/tr}}
    </button>
    <button type="button" class="print notext"
            onclick="Modal.open($('impressions_planning_technicien'), { width: '500' });">{{tr}}Print{{/tr}}</button>
    {{if $current_m == "ssr"}}
      <button type="button" class="change notext" onclick="PlanningTechnicien.toggle(getForm('planning_view'));">
        {{tr}}CEvenementSSR-see_planning{{/tr}}
      </button>
    {{/if}}
  </div>
  <div id="planning-technicien"></div>

  <!-- Modales de transfert et d'impression -->
  <div id="transfert_evts_technicien" style="display: none;">
    <form name="TransfertToTechnicienSSR" method="post">
      <input type="hidden" name="m" value="ssr" />
      <input type="hidden" name="dosql" value="do_modify_evenements_aed" />
      <input type="hidden" name="event_ids" value="" />
      <table class="tbl me-no-align me-no-box-shadow">
        <tr>
          <th>{{tr}}Transfer_to{{/tr}}</th>
        </tr>
        <tr>
          <td class="button">
            <select name="kine_id">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$kines}}
            </select>
          </td>
        </tr>
        <tr>
          <td class="button">
            <button type="button" class="switch me-primary" onclick="ModalValidation.switch(this.form)">
              {{tr}}Transfer{{/tr}}
            </button>
            <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
          </td>
        </tr>
      </table>
    </form>
  </div>
  <div id="impressions_planning_technicien" style="display: none;">
    <table class="tbl me-no-align me-no-box-shadow">
      <tr>
        <th>{{tr}}ssr-print_week{{/tr}}</th>
      </tr>
      <tr>
        <td class="button">
          <button type="button" class="print" onclick="PlanningTechnicien.print(null, $V(getForm('planning_view').surveillance));">{{tr}}Print{{/tr}}</button>
        </td>
      </tr>
      <tr>
        <th>{{tr}}ssr-print_day{{/tr}}</th>
      </tr>
      <tr>
        <td class="button">
          <script>
            Main.add(function () {
              Calendar.regField(getForm("DateSelectPrintPlanningTechnicienSSR").date);
            });
          </script>
          <form name="DateSelectPrintPlanningTechnicienSSR" action="?" method="get">
            <input type="hidden" name="date" class="date" value="{{$dnow}}"/>
            <button type="button" class="print" onclick="PlanningTechnicien.print($V(this.form.date));">
              {{tr}}ssr-print_current_day_technicien{{/tr}}
            </button>
          </form>
        </td>
      </tr>
      <tr>
        <td class="button">
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </div>
</div>
