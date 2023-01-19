{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sejour->_ref_prescription_sejour->_id}}
  <div class="small-info">
    {{tr}}CSejour-prescription-none{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=ssr script=seance_collective ajax=true}}
<script>
  Main.add(function () {
    {{if $evts_collectifs|@count}}
      Seance.checkCountSejours('evt');
    {{/if}}
  });
</script>
<form name="select_evt_collectif">
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}">
  <table class="main tbl">
    <tr>
      <th colspan="7" class="title">
        {{tr}}mod-dPssr-tab-vw_seances_collectives_dispo-title{{/tr}}:
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
          {{$sejour->_view}}
        </span>
      </th>
    </tr>
    <tr>
      <th class="category narrow">
        {{if $evts_collectifs|@count}}
          <input name="check_all_evts" type="checkbox" onchange="Seance.selectSejours($V(this), 'evt');"/>
        {{/if}}
      </th>
      <th class="category narrow">{{mb_title class=CEvenementSSR field=debut}}</th>
      <th class="category narrow">{{mb_title class=CEvenementSSR field=duree}}</th>
      <th class="category">{{mb_title class=CEvenementSSR field=therapeute_id}}</th>
      <th class="category">{{tr}}CCategoryPrescription{{/tr}}</th>
      <th class="category">{{mb_label class=CEvenementSSR field=prescription_line_element_id}}</th>
      <th class="category">{{tr}}CEvenementSSR-nb_patient_seance_inscrit{{/tr}}</th>
    </tr>
    {{foreach from=$evts_collectifs item=_evt_collectif}}
      {{assign var=line_element value=$_evt_collectif->_ref_prescription_line_element}}
      <tr>
        <td style="text-align: center;">
          <input type="checkbox" name="_evt_view_{{$_evt_collectif->_id}}" class="evt_collectif"
                 onchange="Seance.jsonSejours['{{$_evt_collectif->_id}}'].checked = (this.checked ? 1 : 0);Seance.checkCountSejours('evt');"/>
          <script>
            var jsonLine = {checked : 0};
            Seance.jsonSejours["{{$_evt_collectif->_id}}"] = jsonLine;
            $('select_evt_collectif__evt_view_'+'{{$_evt_collectif->_id}}').onchange();
          </script>
        </td>
        <td>{{mb_value object=$_evt_collectif field=debut}}</td>
        <td>{{mb_ditto name=duree value=$_evt_collectif->duree}}</td>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_evt_collectif->_ref_therapeute}}</td>
        <td>{{mb_ditto name=category value=$line_element->_ref_element_prescription->_ref_category_prescription->_view}}</td>
        <td>{{mb_ditto name=line_element_ssr value=$line_element->_view}}</td>
        <td>{{$_evt_collectif->_ref_evenements_seance|@count}}</td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="7" class="empty">
          {{if !$sejour->_ref_prescription_sejour->_id}}
            {{tr}}CSejour-prescription-none{{/tr}}
          {{else}}
            {{tr}}ssr-no_evts_collectifs_dispo_for_this_sejour{{/tr}}
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="7" class="button" style="background-color:white;border-color: white;">
        {{if $evts_collectifs|@count}}
          <button type="button" class="tick" onclick="Seance.editCodesEvtsToPatient()" id="add_select_evt_collectif">
            {{tr}}mod-dPssr-tab-vw_seances_collectives_dispo-add{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
