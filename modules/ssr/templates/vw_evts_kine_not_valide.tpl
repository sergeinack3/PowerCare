{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="select_patients_planning_collectif" id="list_evts_not_valide" method="post"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.refresh);">
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="dosql" value="do_delete_seances_aed"/>
    <input type="hidden" name="evts_to_delete" value=""/>
    <input type="hidden" name="annule" value="1"/>

  {{mb_script module=ssr script=seance_collective ajax=true}}
  <script>
    changePageNotValide = function(page) {
      var url = new Url("ssr" , "vw_evts_kine_not_valide");
      url.addParam('page', page);
      url.requestUpdate("list_evts_not_valide");
    };
  </script>

  {{if $nb_evts > 10}}
    {{mb_include module=system template=inc_pagination total=$nb_evts current=$page step=25 change_page=changePageNotValide}}
  {{/if}}

  <table class="tbl me-no-align me-no-box-shadow">
    <tr>
      <th colspan="5" class="title">
        {{tr}}mod-ssr-tab-vw_evts_kine_not_valide{{/tr}} pour le thérapeute:
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$therapeute}}
      </th>
    </tr>
    <tr>
      <td colspan="5" class="button">
        {{if $list_evts|@count}}
          <button type="button" class="tick" onclick="Seance.confirmAnnulationEvt(this.form);">
            {{tr}}CEvenementSSR-cancel_selected{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>
        {{if $list_evts|@count}}
          <script>
            Main.add(function () {
              Seance.checkCountSejours('patients_planning');
            });
          </script>
          <input name="check_all_patients_plannings" type="checkbox" onchange="Seance.selectSejours($V(this), 'patients_planning');"/>
        {{/if}}
      </th>
      <th>{{tr}}CEvenementSSR-sejour_id{{/tr}}</th>
      <th>{{tr}}CEvenementSSR{{/tr}}</th>
      <th>{{tr}}CEvenementSSR-duree{{/tr}}</th>
    </tr>
    {{foreach from=$list_evts item=_evenement}}
      <tr>
        <td>
          <input type="checkbox" name="_patients_planning_view_{{$_evenement->_id}}" class="patients_planning_collectif"
                 onchange="Seance.jsonSejours['{{$_evenement->_id}}']._checked = (this.checked ? 1 : 0);
                   Seance.checkCountSejours('patients_planning');"/>
          <script>
            var jsonLine = {_checked : 0, evt_id: "{{$_evenement->_id}}"};
            Seance.jsonSejours["{{$_evenement->_id}}"] = jsonLine;
          </script>
        </td>
        <td>
          {{if $_evenement->type_seance == "collective" && !$_evenement->seance_collective_id}}
            {{tr}}CEvenementSSR-seance_collective_id{{/tr}}
          {{else}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_ref_sejour->_guid}}')">
              {{$_evenement->_ref_sejour->_ref_patient->_view}}
            </span>
          {{/if}}
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_guid}}')">
            {{mb_value object=$_evenement field=debut}}
          </span>
        </td >
        <td>{{mb_value object=$_evenement field=duree}}  {{tr}}common-minute|pl{{/tr}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CEvenementSSR-_no_evt_error{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>