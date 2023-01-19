{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=edit_planning}}

<script>
  updateBloc = function() {
    var oform   = getForm('selectBloc');
    var blocs_ids = $V(oform.blocs_ids);
    var type_view_planning = $V(oform.type_view_planning);

    if (blocs_ids) {
      var url = new Url("bloc", "vw_edit_planning");
      url.addParam('blocs_ids[]', blocs_ids, true);
      url.addParam('type_view_planning', type_view_planning);
      url.requestUpdate("refresh_bloc");
    }
  };
</script>

{{if !$listBlocs|@count}}
  <div class="small-warning">
    {{tr}}dPbloc-msg-no_bloc{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="main" id="refresh_bloc">
  <tr>
    <td class="greedyPane not-printable" style="text-align:center; height: 1px;">
      {{if $can->edit && ($nbIntervNonPlacees || $nbIntervHorsPlage || $nbAlertesInterv)}}
        <div class="warning" style="float: right; text-align:left;">
          <a href="#1" onclick="EditPlanning.showAlerte('{{$date}}', '{{$type_view_planning}}', $V(getForm('selectBloc').blocs_ids))">
          {{if $nbAlertesInterv}}
            {{$nbAlertesInterv}} alerte(s) sur des interventions
            <br />
          {{/if}}
          {{if $nbIntervNonPlacees}}
            {{$nbIntervNonPlacees}} intervention(s) non validée(s)
            <br />
          {{/if}}
          {{if $nbIntervHorsPlage}}
            {{$nbIntervHorsPlage}} intervention(s) hors plage
            <br />
          {{/if}}
          </a>
        </div>
      {{/if}}

      <form name="selectBloc" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="vw_edit_planning" />
          <select name="blocs_ids" onchange="updateBloc();" multiple="3">
            {{foreach from=$listBlocs item=curr_bloc name=bloc}}
              <option value="{{$curr_bloc->_id}}"
                      {{if (is_array($blocs_ids) && in_array($curr_bloc->_id, $blocs_ids))}}selected{{/if}}>
                {{$curr_bloc->nom}}
              </option>
            {{/foreach}}
          </select>
        <select name="type_view_planning" onchange="updateBloc();" style="width: 14em;">
          <option value="day" {{if $type_view_planning == "day"}}selected{{/if}}>
            {{tr}}Day{{/tr}}
          </option>
          <option value="week" {{if $type_view_planning == "week"}}selected{{/if}}>
            {{tr}}Week{{/tr}}
          </option>
        </select>
        <button type="button" class="print" onclick="window.print();">{{tr}}Print{{/tr}}</button>
      </form>
    </td>
    <td rowspan="100" class="not-printable me-bloc-edit-planning-right-layout me-bg-white">
      {{mb_include module=bloc template=inc_legende_planning}}
    </td>
  </tr>
  <tr>
    <td class="greedyPane">
      <table class="planningBloc" id="planning_bloc_day">
      {{foreach from=$listDays key=curr_day item=plagesPerDay}}
        {{foreach from=$blocs key=key_bloc item=bloc}}
          {{mb_include module=bloc template=inc_planning_day}}
        {{/foreach}}
      {{/foreach}}
      </table>
    </td>
  </tr>
</table>