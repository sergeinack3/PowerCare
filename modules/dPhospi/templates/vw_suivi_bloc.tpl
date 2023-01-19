{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  updateSuiviSalle = function () {
    var oform = getForm('chgService');
    var date = $V(oform.date_suivi);
    var blocs_ids = $V(oform.suivi_blocs_ids);
    var services_ids = $V(oform.suivi_services_ids);

    if (blocs_ids || services_ids) {
      var url = new Url("hospi", "vw_suivi_bloc");
      url.addParam('suivi_blocs_ids[]', blocs_ids, true);
      url.addParam('suivi_services_ids[]', services_ids, true);
      url.addParam('date_suivi', date);
      url.requestUpdate("suivi-service");
    }
  };

  Main.add(function () {
    Calendar.regField(getForm("chgService").date_suivi, null, {noView: true});
  });
</script>
<div id="suivi-service">
  <form name="chgService" action="?m={{$m}}" method="get">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="tab" value="{{$tab}}" />
    <table class="main">
      <tr>
        <th>
          <label class="not-printable">{{tr}}CService{{/tr}} :
            <select name="suivi_services_ids" onchange="updateSuiviSalle();" multiple="3">
              <option value="" {{if !is_array($services_ids)}}selected{{/if}}>&mdash; {{tr}}CService.all{{/tr}}</option>
              {{foreach from=$services item=currService name=service}}
                <option value="{{$currService->_id}}"
                        {{if (is_array($services_ids) && in_array($currService->_id, $services_ids))}}selected{{/if}}>
                  {{$currService}}
                </option>
              {{/foreach}}
            </select>
          </label>
          <label class="not-printable">{{tr}}module-dPbloc-court{{/tr}} :
            <select name="suivi_blocs_ids" onchange="updateSuiviSalle();" multiple="3">
              <option value="" {{if !is_array($blocs_ids)}}selected{{/if}}>&mdash; {{tr}}CBlocOperatoire.all-court{{/tr}}</option>
              {{foreach from=$blocs item=currBloc name=bloc}}
                <option value="{{$currBloc->_id}}"
                        {{if (is_array($blocs_ids) && in_array($currBloc->_id, $blocs_ids))}}selected{{/if}}>
                  {{$currBloc->nom}}
                </option>
              {{/foreach}}
            </select>
          </label>
          le
          {{$date_suivi|date_format:$conf.longdate}}
          <input type="hidden" name="date_suivi" class="date" value="{{$date_suivi}}" onchange="updateSuiviSalle();" />
        </th>
      </tr>
    </table>
  </form>

  <table class="tbl">
    {{foreach from=$listServices key=keyServ item=currService}}
      {{if $currService|@count}}
        <tr>
          <th class="title" colspan="10">
            {{if $keyServ == "NP"}}
              Non placés
            {{else}}
              {{$services.$keyServ->_view}}
            {{/if}}
          </th>
        </tr>
        <tr>
          <th class="category narrow">{{tr}}COperation-Planned{{/tr}}</th>
          <th class="category" colspan="2">{{mb_title class=COperation field=_status}}</th>
          <th class="category">{{mb_title class=CSejour field=patient_id}}</th>
          <th class="category">{{mb_title class=COperation field=chir_id}}</th>
          <th class="category">{{tr}}COperation{{/tr}}</th>
          <th class="category">{{mb_title class=COperation field=cote}}</th>
          <th class="category">{{mb_title class=COperation field=salle_id}}</th>
          <th class="category">{{mb_title class=CAffectation field=lit_id}}</th>
        </tr>
        {{foreach from=$currService item=currOp}}
          <tr>
            <td class="button narrow"
              {{if $currOp->sortie_reveil_reel}}
                style="background-image:url(images/icons/ray.gif); background-repeat:repeat;"
              {{elseif $currOp->entree_bloc || $currOp->entree_salle}}
                style="background-color:#cfc"
              {{/if}}
            >
              {{if $currOp->time_operation && $currOp->time_operation != "00:00:00"}}
                {{$currOp->time_operation|date_format:$conf.time}}
              {{else}}
                -
              {{/if}}
            </td>
            <td class="button narrow"
              {{if $currOp->sortie_reveil_reel}}
              style="background-image:url(images/icons/ray.gif); background-repeat:repeat;"
              {{elseif $currOp->entree_bloc || $currOp->entree_salle}}
              style="background-color:#cfc"
              {{/if}}>
              {{if $currOp->sortie_reveil_reel}} {{mb_value object=$currOp field=sortie_reveil_reel}}
              {{elseif $currOp->entree_reveil}}  {{mb_value object=$currOp field=entree_reveil}}
              {{elseif $currOp->sortie_salle}}   {{mb_value object=$currOp field=sortie_salle}}
              {{elseif $currOp->entree_salle}}   {{mb_value object=$currOp field=entree_salle}}
              {{elseif $currOp->entree_bloc}}    {{mb_value object=$currOp field=entree_bloc}}
              {{else}} -
              {{/if}}
            </td>
            <td
              {{if $currOp->sortie_reveil_reel}}
              style="background-image:url(images/icons/ray.gif); background-repeat:repeat;"
              {{elseif $currOp->entree_bloc || $currOp->entree_salle}}
              style="background-color:#cfc"
              {{/if}}>
              {{if $currOp->sortie_reveil_reel}} Sorti(e) du bloc
              {{elseif $currOp->entree_reveil}}  En SSPI
              {{elseif $currOp->sortie_salle}}   Attente SSPI
              {{elseif $currOp->entree_salle}}   En salle d'interv.
              {{elseif $currOp->entree_bloc}}    Entré(e) au bloc
              {{else}} Attente bloc
              {{/if}}
              {{mb_include module=forms template=inc_widget_ex_class_register_multiple object=$currOp event_name=liaison}}
            </td>
            <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$currOp->_ref_sejour->_ref_patient->_guid}}')">
              {{$currOp->_ref_sejour->_ref_patient->_view}}
            </span>
            </td>
            <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$currOp->_ref_chir}}</td>
            <td class="text">{{mb_include module=planningOp template=inc_vw_operation _operation=$currOp}}</td>
            <td>{{mb_value object=$currOp field="cote"}}</td>
            <td class="text">{{mb_value object=$currOp field="salle_id"}}</td>
            <td class="text">{{if $currOp->_ref_affectation->lit_id}}{{$currOp->_ref_affectation->_ref_lit}}{{/if}}</td>
          </tr>
        {{/foreach}}
      {{/if}}
    {{/foreach}}
  </table>
  {{mb_include module=forms template=inc_widget_ex_class_register_multiple_end object_class="COperation" event_name="liaison"}}
</div>
