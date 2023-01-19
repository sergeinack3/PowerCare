{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="select_patients_planning_collectif" id="form_patients_planning_collectif" method="post"
      onsubmit="return TrameCollective.onsubmit(this);">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="dosql" value="manageCollectiveSSREventsPlanning"/>
  <input type="hidden" name="plage_id" value="{{$plage->_id}}"/>
  <input type="hidden" name="sejour_ids" value=""/>
  <table class="main tbl">
    <tr>
      <td colspan="6">
        <div class="small-warning">
          {{tr}}CPlageSeanceCollective.gestionPatient_info{{/tr}}
        </div>
        {{if $plage->commentaire}}
          <div class="small-info">{{mb_value object=$plage field=commentaire}}</div>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th colspan="6" class="title">
        <button type="button" class="print notext" onclick="$('form_patients_planning_collectif').print();" style="float: left">
          {{tr}}Print{{/tr}}
        </button>
        {{tr}}CPlageSeanceCollective.gestionPatient{{/tr}} :
        {{mb_value object=$plage field=day_week}} à {{mb_value object=$plage field=debut}}
        ({{mb_value object=$plage field=duree}} minutes)
        - {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_user}}
        - {{$plage->_ref_element_prescription->_view}}
      </th>
    </tr>
    <tr>
      <th class="category narrow" colspan="2">
        {{if $sejours|@count}}
          <script>
            Main.add(function () {
              Seance.checkCountSejours('patients_planning');
            });
          </script>
          <input name="check_all_patients_plannings" type="checkbox" onchange="Seance.selectSejours($V(this), 'patients_planning');"/>
        {{/if}}
      </th>
      <th class="category">
        {{mb_colonne class=CSejour field=patient_id order_col=$order_col order_way=$order_way function=TrameCollective.sortBy}}
      </th>
      <th class="category">
        {{mb_colonne class=CSejour field=entree order_col=$order_col order_way=$order_way function=TrameCollective.sortBy}}
      </th>
    </tr>
    {{foreach from=$sejours item=_sejour}}
      <tr>
        <td style="text-align: center;" colspan="2">
          {{if $_sejour->_id|in_array:$sejours_collisions}}
            <i class="fas fa-exclamation" title="{{tr}}CPlageSeanceCollective.Patient have another event{{/tr}}" style="cursor: help">
            </i>
          {{/if}}
          <input type="checkbox" name="_patients_planning_view_{{$_sejour->_id}}" class="patients_planning_collectif"
                 onchange="Seance.jsonSejours['{{$_sejour->_id}}'].checked = (this.checked ? 1 : 0);
                   Seance.checkCountSejours('patients_planning');"/>
          <script>
            var jsonLine = {checked : 0};
            Seance.jsonSejours["{{$_sejour->_id}}"] = jsonLine;
            var input_sejour = $('select_patients_planning_collectif__patients_planning_view_'+'{{$_sejour->_id}}');
            {{if in_array($_sejour->_id, $sejours_affectes)}}
            input_sejour.checked = 'checked';
            {{/if}}
            input_sejour.onchange();
          </script>
        </td>
        <td>
          {{mb_include module=system template=inc_vw_mbobject object=$_sejour->_ref_patient}}
        </td>
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{$_sejour->_shortview}}
        </span>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="6" class="empty">{{tr}}CPlageSeanceCollective.none_sejour_dispo{{/tr}}</td>
      </tr>
    {{/foreach}}
    <tr class="not-printable">
      <td colspan="6" class="button">
        {{if $sejours|@count}}
          <button type="button" class="tick" onclick="TrameCollective.confirmValidation(this.form);">
            {{tr}}Validate{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
