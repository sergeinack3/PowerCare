{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=HPlanning ajax=true}}
{{mb_script module=planningOp script=operation ajax=true}}
{{mb_script module=patients script=patient ajax=true}}
{{mb_script module=bloc script=blocage ajax=true}}
{{mb_script module=salleOp script=salleOp ajax=true}}

{{math assign=interval equation="a*1000" a="dPbloc affichage time_autorefresh"|gconf}}

<script type="text/javascript">
  Blocage.afterEditBlocage = function(blocage_id) {
    Control.Modal.close();
  };

  Main.add(function() {
    HPlanning.setAutoRefreshInterval({{$interval}});
    HPlanning.display(getForm('timeline_filters'));
    Calendar.regField(getForm('timeline_filters').elements['date'], null, {noView: true});
    Calendar.regField(getForm('changeOperationRoom').elements['time_operation'], null, {timePicker: true, datePicker: false});
    {{if $display == 'normal'}}
      if (Preferences.startAutoRefreshAtStartup == 1) {
        HPlanning.toggleAutoRefresh($('btn_auto_refresh_timeline'));
      }
    {{else}}
      HPlanning.toggleAutoRefresh($('btn_auto_refresh_timeline'));
    {{/if}}

    if (!window.events_attached) {
      Event.observe(window, 'resize', HPlanning.setContainerWidth);

      window.events_attached = true;
    }
  });
</script>

<div class="buttons_placeholder" style="min-height: 22px;">
  {{if $display == 'normal'}}
    <span style="float: right;">
      <button id="btn_auto_refresh_timeline" type="button" class="play notext me-tertiary" onclick="HPlanning.toggleAutoRefresh(this);">
        Rechargement automatique de la vue ({{tr}}config-dPbloc-CPlageOp-time_autorefresh-{{"dPbloc affichage time_autorefresh"|gconf}}{{/tr}})
      </button>
      <button type="button" class="print me-tertiary" onclick="HPlanning.print();">
        {{tr}}Print{{/tr}}
      </button>
      <button type="button" class="lookup" onclick="HPlanning.presentationMode();">
        Mode présentation
      </button>
      <button type="button" class="search" onclick="HPlanning.legend();">
        {{tr}}Legend{{/tr}}
      </button>
      <button type="button" class="fa fa-cog notext" onclick="HPlanning.selectView();">
        Changer le mode d'affichage de la vue
      </button>
    </span>
  {{else}}
    <button type="button" onclick="App.fullscreen();" style="float: right;">
      <i class="fas fa-lg expand-arrows-alt"></i>
      Plein écran
    </button>
  {{/if}}
</div>

<div id="timeline_layout">
  <div id="timeline_header" style="text-align : center;">
    <form name="timeline_filters" method="get" onsubmit="return HPlanning.display(this, true);">
      <h1 class="no-break" style="margin-top: 0; margin-bottom: 5px;">
        <span id="date_placeholder">
          {{$date|date_format:$conf.longdate}}
        </span>
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="HPlanning.refreshDate(this.form, true);">
      </h1>

      <div id="blocs_spaceholder" style="font-weight: bold;">
        <button type="button" class="search notext" title="Sélectionner les blocs opératoires" onclick="HPlanning.openFilters();"></button>
        Blocs :
        {{foreach from=$blocs item=_bloc}}
          <span class="bloc_display" data-bloc_id="{{$_bloc->_id}}"{{if !array_key_exists($_bloc->_id, $selected_blocs) || ($selected_blocs|@count == 0 && $smarty.foreach.blocs.first)}} style="display: none;"{{/if}}>{{$_bloc}}</span>
        {{/foreach}}
        <span id="bloc_none"{{if $blocs|@count != 0}} style="display: none;"{{/if}}>
          Aucun bloc sélectionné
        </span>
      </div>

      <table id="timeline_salle_filters" class="form" style="display: none;">
        <tr>
          <th>
            <label for="bloc_ids">Blocs</label>
          </th>
          <td>
            <select name="blocs_ids" multiple="3" onchange="HPlanning.refreshSelectedBlocs(this.form);">
              {{foreach from=$blocs item=_bloc name=blocs}}
                <option value="{{$_bloc->_id}}" data-bloc_name="{{$_bloc}}" {{if array_key_exists($_bloc->_id, $selected_blocs) || ($selected_blocs|@count == 0 && $smarty.foreach.blocs.first)}} selected{{/if}}>
                  {{$_bloc}}
                </option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <th>
            <label>Salles</label>
          </th>
          <td>
            {{foreach from=$blocs item=_bloc name=blocs_salles}}
              {{if $_bloc->_ref_salles|@count}}
                <div id="bloc_{{$_bloc->_id}}_salles"{{if !array_key_exists($_bloc->_id, $selected_blocs) || ($selected_blocs|@count == 0 && !$smarty.foreach.blocs_salles.first)}} style="display: none;"{{/if}}>
                  {{foreach from=$_bloc->_ref_salles item=_salle}}
                    <label class="salle_display" style="font-weight: normal;" data-bloc_id="{{$_bloc->_id}}">
                      <input type="checkbox" class="salle_selector bloc-{{$_bloc->_id}}" data-salle_id="{{$_salle->_id}}" {{if array_key_exists($_bloc->_id, $selected_blocs) || ($selected_blocs|@count == 0 && $smarty.foreach.blocs_salles.first)}} checked{{/if}}/>
                          {{$_salle->nom}}
                    </label>
                  {{/foreach}}
                </div>
              {{/if}}
            {{/foreach}}
          </td>
        </tr>
        <!-- Eventuellement afficher la sélection des postes d'affichages (permettant de modifier les prefs associées -->
        <tr>
          <td class="button" colspan="2">
            <button type="button" class="tick" onclick="Control.Modal.close(); this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
          </td>
        </tr>
      </table>
    </form>
  </div>

  <div id="timeline_body" style="position: relative; width: 100%; vertical-align: top;">

  </div>

  <div id="changeOperationRoom" style="display: none;">
    <form name="changeOperationRoom" method="post" action="?" onsubmit="return false;">
      <input type="hidden" name="m" value="planningOp">
      <input type="hidden" name="dosql" value="do_planning_aed">
      <input type="hidden" name="operation_id" value="">
      <input type="hidden" name="salle_id" value="">

      <table class="form">
        <tr>
          <th>
            {{tr}}COperation{{/tr}}
          </th>
          <td id="changeOperationRoom-operation">
          </td>
        </tr>
        <tr id="changeOperationRoom-msg" style="display: none;">
          <td colspan="2">
            <div class="small-info"></div>
          </td>
        </tr>
        <tr id="changeOperationRoom-hors_plage" style="display: none;">
          <th>
            <label for="time_operation" title="{{tr}}COperation-time_operation-desc{{/tr}}">{{tr}}COperation-time_operation{{/tr}}</label>
          </th>
          <td>
            <input type="hidden" name="time_operation" class="time" value=""/>
          </td>
        </tr>
        <tr>
          <td class="button" colspan="2">
            <button type="button" class="tick" onclick="HPlanning.changeOperationRoom()">
              {{tr}}Validate{{/tr}}
            </button>
            <button type="button" class="cancel" onclick="Control.Modal.close();">
              {{tr}}Cancel{{/tr}}
            </button>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>

<div id="select_planning_view" style="display: none;">
  {{mb_include module=bloc template=select_planning_bloc_view}}
</div>

<table class="tbl" id="horizontal_planning_legend" style="display: none;">
  <!-- Légende -->
  <tr>
    <td style="width: 20px; height: 20px; background-color: #74cd67;"></td>
    <td>Durée préop / postop</td>
  </tr>
  <tr>
    <td style="width: 20px; height: 20px; background-color: #1e69cd;"></td>
    <td>Durée d'induction</td>
  </tr>
  <tr>
    <td style="width: 20px; height: 20px; background-color: #dddddd;"></td>
    <td>Intervention non commencée</td>
  </tr>
  <tr>
    <td style="width: 20px; height: 20px; background-color: #cd5c1f;"></td>
    <td>Intervention commencée</td>
  </tr>
  <tr>
    <td style="width: 20px; height: 20px; background: repeating-linear-gradient( 45deg, #cccccc, #cccccc 10px, #ffffff 10px, #ffffff 20px);"></td>
    <td>Intervention terminée</td>
  </tr>
  <tr>
    <td style="width: 20px; height: 20px; background-color: #a81613;"></td>
    <td>Intervention dépassant la durée prévue</td>
  </tr>
  <tr>
    <td>
      <div style="border: #60140c dashed 1px; box-sizing: border-box; height: 20px; width: 20px;"></div>
    </td>
    <td>Intervention planifiée déplacée</td>
  </tr>
  <tr>
    <td style="width: 20px; height: 20px; background: repeating-linear-gradient(45deg, #871119, #871119 10px, #626669 10px, #626669 20px);"></td>
    <td>Blocage de salle</td>
  </tr>
</table>