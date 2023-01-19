{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=prefix_form   value="anesth"}}
{{mb_default var=graph_lock    value=0}}
{{mb_default var=parto         value=false}}
{{mb_default var=complete_view value=0}}

{{mb_script module=salleOp script=salleOp ajax=1}}

{{if $complete_view}}
  <script>
    Main.add(function () {
      SalleOp.showInformations('{{$selOp->_id}}', '{{$modif_operation}}', '{{$show_cormack}}', '{{$type}}', 0);
    });
  </script>
{{/if}}

{{assign var=use_alr_ag value="dPsalleOp timings use_alr_ag"|gconf}}

{{if !$complete_view}}
  {{if !$parto}}
    <button type="button" class="search not-printable me-tertiary" onclick="SalleOp.showInformations('{{$selOp->_id}}', '{{$modif_operation}}', '{{$show_cormack}}', '{{$type}}', 1);">{{tr}}COperation-action-Intervention information-court|pl{{/tr}}</button>
  {{else}}
    <button type="button" class="print not-printable me-tertiary" onclick="SalleOp.printFicheAnesth('{{$consult_anesth->_id}}', '{{$selOp->_id}}');">{{tr}}CAnesthPerop-action-Anesthesia sheet{{/tr}}</button>
  {{/if}}
{{/if}}

{{if !$complete_view}}
  <div id="show_type_anesth_cormack_{{$type}}" class="me-inline-block">
    {{mb_include module=salleOp template=inc_vw_type_anesth_cormack}}
  </div>
{{/if}}

<div id="infos_interv"></div>

{{if "dPsalleOp COperation check_identity_pat"|gconf}}
  {{assign var=modif_operation value=false}}
{{/if}}

<form name="editSortieSansSSPI{{$selOp->_id}}" method="post" class="prepared">
  {{mb_key    object=$selOp}}
  <input type="hidden" name="m" value="planningOp"/>
  <input type="hidden" name="sortie_sans_sspi" value="current" />
  <input type="hidden" name="dosql" value="do_planning_aed"/>
</form>

<table class="main me-no-align">
  <tr>
    <td colspan="3">
      <fieldset class="me-small me-align-auto">
          <legend>{{tr}}CSupervisionGraphPack-timing_fields-court{{/tr}}</legend>
        <form name="{{$prefix_form}}-anesthTiming" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
            {{mb_key   object=$selOp}}
            {{mb_class object=$selOp}}
            <table class="form me-small-form me-no-align me-margin-bottom-0 me-no-box-shadow">
              <tr>
                {{if $prefix_form == 'preop'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=entree_bloc}}</th>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=entree_salle}}</th>
                {{/if}}
                {{if $prefix_form != 'partogramme' && $prefix_form != 'sspi'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=induction_debut}}</th>
                  {{if $use_alr_ag}}
                    <th class="category me-no-border">{{mb_label object=$selOp field=debut_ag}}</th>
                    <th class="category me-no-border">{{mb_label object=$selOp field=debut_alr}}</th>
                  {{/if}}
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=pose_garrot}}</th>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=debut_op}}</th>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme' && "dPsalleOp timings use_incision"|gconf}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=incision}}</th>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=retrait_garrot}}</th>
                {{/if}}
                {{if $prefix_form != 'partogramme' && $prefix_form != 'sspi'}}
                  {{if $use_alr_ag}}
                    <th class="category me-no-border">{{mb_label object=$selOp field=fin_alr}}</th>
                  {{/if}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=induction_fin}}</th>
                {{/if}}
                {{if $prefix_form == "preop"}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=fin_prepa_preop}}</th>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=fin_op}}</th>
                {{/if}}
                {{if $prefix_form != "preop" && $prefix_form != 'sspi'}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=sortie_salle}}</th>
                  {{if $prefix_form != 'partogramme'}}
                    <th class="category me-no-border">{{mb_label object=$selOp field=sortie_sans_sspi}}</th>
                  {{/if}}
                {{/if}}
                {{if $prefix_form == "preop"}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=entree_salle}}</th>
                {{/if}}
                {{if $prefix_form == "sspi"}}
                  <th class="category me-no-border">{{mb_label object=$selOp field=entree_reveil}}</th>
                  <th class="category me-no-border">{{mb_label object=$selOp field=sortie_reveil_reel}}</th>
                {{/if}}
              </tr>
              <tr>
                {{if $prefix_form == 'preop'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming" field=entree_bloc submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                  </td>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi'}}
                <td style="text-align: center;" class="me-valign-top">
                  {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming" field=entree_salle submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                </td>
                {{/if}}
                {{if $prefix_form != 'partogramme' && $prefix_form != 'sspi'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if !"dPsalleOp timings timings_induction"|gconf}}
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming" field=induction_debut submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    {{else}}
                      {{mb_value object=$selOp field=induction_debut}}
                    {{/if}}
                  </td>
                  {{if $use_alr_ag}}
                    <td style="text-align: center;" class="me-valign-top">
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                      field=debut_ag submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    </td>
                    <td style="text-align: center;" class="me-valign-top">
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                      field=debut_alr submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    </td>
                  {{/if}}
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if !"dPsalleOp COperation garrots_multiples"|gconf}}
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                      field=pose_garrot submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    {{elseif $selOp->_ref_garrots && "dPsalleOp COperation garrots_multiples"|gconf}}
                      {{foreach from=$selOp->_ref_garrots item=_garrot}}
                        {{mb_value object=$_garrot field=datetime_pose}} <br>
                      {{/foreach}}
                    {{else}}
                      -
                    {{/if}}
                  </td>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if $selOp->debut_op}}
                      {{mb_value object=$selOp field=debut_op}}
                    {{else}}
                      -
                    {{/if}}
                  </td>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme' && "dPsalleOp timings use_incision"|gconf}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if $selOp->incision}}
                      {{mb_value object=$selOp field=incision}}
                    {{else}}
                      -
                    {{/if}}
                  </td>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if !"dPsalleOp COperation garrots_multiples"|gconf}}
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                        field=retrait_garrot submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    {{elseif $selOp->_ref_garrots && "dPsalleOp COperation garrots_multiples"|gconf}}
                      {{foreach from=$selOp->_ref_garrots item=_garrot}}
                        {{mb_value object=$_garrot field=datetime_retrait}} <br>
                      {{/foreach}}
                    {{else}}
                      -
                    {{/if}}
                  </td>
                {{/if}}
                {{if $prefix_form != 'partogramme' && $prefix_form != 'sspi'}}
                  {{if $use_alr_ag}}
                    <td style="text-align: center;" class="me-valign-top">
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                      field=fin_alr submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    </td>
                  {{/if}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if !"dPsalleOp timings timings_induction"|gconf}}
                      {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                      field=induction_fin submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                    {{else}}
                      {{mb_value object=$selOp field=induction_fin}}
                    {{/if}}
                  </td>
                {{/if}}
                {{if $prefix_form == "preop"}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                    field=fin_prepa_preop submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                  </td>
                {{/if}}
                {{if $prefix_form != 'preop' && $prefix_form != 'sspi' && $prefix_form != 'partogramme'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{if $selOp->fin_op}}
                      {{mb_value object=$selOp field=fin_op}}
                    {{else}}
                      -
                    {{/if}}
                  </td>
                {{/if}}
                {{if $prefix_form != "preop" && $prefix_form != 'sspi'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                    field=sortie_salle submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                  </td>
                  {{if $prefix_form != 'partogramme'}}
                    <td style="text-align: center;" class="me-valign-top">
                    {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming"
                    field=sortie_sans_sspi submit=submitAnesthForm show_label=false center_field=true graph_lock=$graph_lock}}
                  </td>
                  {{/if}}
                {{/if}}
                {{if $prefix_form == "preop"}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming" field=entree_salle submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                  </td>
                {{/if}}
                {{if $prefix_form == 'sspi'}}
                  <td style="text-align: center;" class="me-valign-top">
                    {{mb_value object=$selOp field=entree_reveil}}
                  </td>
                  <td style="text-align: center;" class="me-valign-top">
                    {{assign var=field_sortie_reveil value=sortie_reveil_possible}}
                    {{if "dPsalleOp COperation use_sortie_reveil_reel"|gconf}}
                      {{assign var=field_sortie_reveil value=sortie_reveil_reel}}
                    {{/if}}
                    {{mb_include module=salleOp template=inc_field_timing object=$selOp form="$prefix_form-anesthTiming" field=$field_sortie_reveil submit=submitAnesth show_label=false center_field=true graph_lock=$graph_lock}}
                  </td>
                {{/if}}
              </tr>
            </table>
          </form>
      </fieldset>
    </td>
    <td>
     {{if "maternite"|module_active && $selOp->_ref_sejour->grossesse_id}}
     <fieldset class="me-small me-align-auto">
        <legend>{{tr}}CGrossesse{{/tr}}</legend>
        {{assign var=_grossesse value=$selOp->_ref_sejour->loadRefGrossesse()}}
        <table class="form me-small-form me-margin-left-4 me-margin-right-4 me-margin-bottom-6">
         <tr>
          <th class="category me-no-border">{{mb_label object=$_grossesse field=datetime_debut_travail}}</th>
          <th class="category me-no-border">{{mb_label object=$_grossesse field=datetime_accouchement}}</th>
          <th class="category me-no-border">{{mb_label object=$_grossesse field=datetime_debut_surv_post_partum}}</th>
          <th class="category me-no-border">{{mb_label object=$_grossesse field=datetime_fin_surv_post_partum}}</th>
         </tr>
          <tr>
            <td class="narrow me-valign-top" style="text-align: center;">
              <span class="{{if $type === "sspi"}}opacity-70{{/if}}">
                {{mb_include module=maternite template=inc_timing_grossesse grossesse=$_grossesse operation=$selOp timing=datetime_debut_travail type=$type label=false}}
              </span>
            </td>
            <td class="narrow me-valign-top" style="text-align: center;">
              <span class="{{if $type === "sspi"}}opacity-70{{/if}}">
                {{mb_include module=maternite template=inc_timing_grossesse grossesse=$_grossesse operation=$selOp timing=datetime_accouchement type=$type label=false}}
              </span>
            </td>
            <td class="narrow me-valign-top" style="text-align: center;">
              <span class="{{if $type === "perop" || $type === "preop"}}opacity-70{{/if}}">
                {{mb_include module=maternite template=inc_timing_grossesse grossesse=$_grossesse operation=$selOp timing=datetime_debut_surv_post_partum type=$type label=false}}
              </span>
            </td>
            <td class="narrow me-valign-top" style="text-align: center;">
              <span class="{{if $type === "perop" || $type === "preop"}}opacity-70{{/if}}">
                {{mb_include module=maternite template=inc_timing_grossesse grossesse=$_grossesse operation=$selOp timing=datetime_fin_surv_post_partum type=$type label=false}}
              </span>
            </td>
          </tr>
        </table>
        </fieldset>
      {{/if}}
    </td>
  </tr>
  {{if "maternite"|module_active && $selOp->_ref_sejour->grossesse_id}}
    <tr id="personnel_partogramme">
      {{mb_include module=salleOp template=inc_vw_personnel_partogramme}}
    </tr>
  {{/if}}
</table>
