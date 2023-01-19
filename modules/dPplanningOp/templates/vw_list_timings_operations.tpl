{{*
 * @package Mediboard\PlanningOp
 * @autdor  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=see_pec_anesth     value="dPsalleOp timings see_pec_anesth"|gconf}}
{{assign var=place_pec_anesth   value="dPsalleOp timings place_pec_anesth"|gconf}}
{{assign var=use_remise_chir    value="dPsalleOp timings use_delivery_surgeon"|gconf}}
{{assign var=use_garrot         value="dPsalleOp timings use_garrot"|gconf}}
{{assign var=use_debut_fin_op   value="dPsalleOp timings use_end_op"|gconf}}
{{assign var=timings_induction  value="dPsalleOp timings timings_induction"|gconf}}
{{assign var=garrots_multiples  value="dPsalleOp COperation garrots_multiples"|gconf}}
{{assign var=place_remise_chir  value="dPsalleOp timings place_remise_chir"|gconf}}

<table class="main print">
  {{foreach from=$sejour->_ref_operations item=_operation}}
    <tr>
      <th class="category" colspan="2">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">
          {{$_operation->_view}}
        </span>
      </th>
    </tr>
    <tr>
      <td style="width: 150px">{{mb_label object=$_operation field=debut_prepa_preop}}</td>
      <td>{{mb_value object=$_operation field=debut_prepa_preop}}</td>
    </tr>
    <tr>
      <td>{{mb_label object=$_operation field=fin_prepa_preop}}</td>
      <td>{{mb_value object=$_operation field=fin_prepa_preop}}</td>
    </tr>
    {{if "dPsalleOp timings use_entry_room"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=entree_bloc}}</td>
        <td>{{mb_value object=$_operation field=entree_bloc}}</td>
      </tr>
    {{/if}}
    {{if $see_pec_anesth && $place_pec_anesth == "under_entree_bloc"}}
      <tr>
        <td>{{mb_label object=$_operation field=pec_anesth}}</td>
        <td>{{mb_value object=$_operation field=pec_anesth}}</td>
      </tr>
    {{/if}}
    {{if $use_remise_chir && $place_remise_chir == "below_entree_salle"}}
      <tr>
        <td>{{mb_label object=$_operation field=remise_chir}}</td>
        <td>{{mb_value object=$_operation field=remise_chir}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_preparation_op"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=preparation_op}}</td>
        <td>{{mb_value object=$_operation field=preparation_op}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_entry_exit_room"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=entree_salle}}</td>
        <td>{{mb_value object=$_operation field=entree_salle}}</td>
      </tr>
    {{/if}}
    {{if $use_remise_chir && $place_remise_chir == "under_entree_salle"}}
      <tr>
        <td>{{mb_label object=$_operation field=remise_chir}}</td>
        <td>{{mb_value object=$_operation field=remise_chir}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings see_fin_pec_anesth"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=fin_pec_anesth}}</td>
        <td>{{mb_value object=$_operation field=fin_pec_anesth}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_debut_installation"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=installation_start}}</td>
        <td>{{mb_value object=$_operation field=installation_start}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_fin_installation"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=installation_end}}</td>
        <td>{{mb_value object=$_operation field=installation_end}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_tto"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=tto}}</td>
        <td>{{mb_value object=$_operation field=tto}}</td>
      </tr>
    {{/if}}
    {{if $see_pec_anesth && $place_pec_anesth == "end_preparation"}}
      <tr>
        <td>{{mb_label object=$_operation field=pec_anesth}}</td>
        <td>{{mb_value object=$_operation field=pec_anesth}}</td>
      </tr>
    {{/if}}
    {{if $timings_induction}}
      <tr>
        <td>{{mb_label object=$_operation field=induction_debut}}</td>
        <td>{{mb_value object=$_operation field=induction_debut}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_alr_ag"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=debut_alr}}</td>
        <td>{{mb_value object=$_operation field=debut_alr}}</td>
      </tr>
      <tr>
        <td>{{mb_label object=$_operation field=fin_alr}}</td>
        <td>{{mb_value object=$_operation field=fin_alr}}</td>
      </tr>
      <tr>
        <td>{{mb_label object=$_operation field=debut_ag}}</td>
        <td>{{mb_value object=$_operation field=debut_ag}}</td>
      </tr>
      <tr>
        <td>{{mb_label object=$_operation field=fin_ag}}</td>
        <td>{{mb_value object=$_operation field=fin_ag}}</td>
      </tr>
    {{/if}}
    {{if $timings_induction}}
      <tr>
        <td>{{mb_label object=$_operation field=induction_fin}}</td>
        <td>{{mb_value object=$_operation field=induction_fin}}</td>
      </tr>
    {{/if}}
    {{if $use_garrot && !$garrots_multiples}}
      <tr>
        <td>{{mb_label object=$_operation field=pose_garrot}}</td>
        <td>{{mb_value object=$_operation field=pose_garrot}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_prep_cutanee"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=prep_cutanee}}</td>
        <td>{{mb_value object=$_operation field=prep_cutanee}}</td>
      </tr>
    {{/if}}
    {{if $use_debut_fin_op}}
      <tr>
        <td>{{mb_label object=$_operation field=debut_op}}</td>
        <td>{{mb_value object=$_operation field=debut_op}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_incision"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=incision}}</td>
        <td>{{mb_value object=$_operation field=incision}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_suture"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=suture_fin}}</td>
        <td>{{mb_value object=$_operation field=suture_fin}}</td>
      </tr>
    {{/if}}
    {{if $use_debut_fin_op}}
      <tr>
        <td>{{mb_label object=$_operation field=fin_op}}</td>
        <td>{{mb_value object=$_operation field=fin_op}}</td>
      </tr>
    {{/if}}
    {{if $use_garrot && !$garrots_multiples}}
      <tr>
        <td>{{mb_label object=$_operation field=retrait_garrot}}</td>
        <td>{{mb_value object=$_operation field=retrait_garrot}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_entry_exit_room"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=sortie_salle}}</td>
        <td>{{mb_value object=$_operation field=sortie_salle}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings use_cleaning_timings"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=cleaning_start}}</td>
        <td>{{mb_value object=$_operation field=cleaning_start}}</td>
      </tr>
      <tr>
        <td>{{mb_label object=$_operation field=cleaning_end}}</td>
        <td>{{mb_value object=$_operation field=cleaning_end}}</td>
      </tr>
    {{/if}}
    {{if "dPsalleOp timings see_entree_reveil_timing"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=entree_reveil}}</td>
        <td>{{mb_value object=$_operation field=entree_reveil}}</td>
      </tr>
    {{/if}}
    <tr>
      <td>{{mb_label object=$_operation field=sortie_reveil_possible}}</td>
      <td>{{mb_value object=$_operation field=sortie_reveil_possible}}</td>
    </tr>
    <tr>
      <td>{{mb_label object=$_operation field=sortie_reveil_reel}}</td>
      <td>{{mb_value object=$_operation field=sortie_reveil_reel}}</td>
    </tr>
    {{if "dPsalleOp timings use_validation_timings"|gconf}}
      <tr>
        <td>{{mb_label object=$_operation field=validation_timing}}</td>
        <td>{{mb_value object=$_operation field=validation_timing}}</td>
      </tr>
    {{/if}}
    <tr>
      <td>
        <label title="{{tr}}COperation-_passage_bloc-desc{{/tr}}">
          {{tr}}COperation-_passage_bloc{{/tr}}
        </label>
      </td>
      <td>{{'Ox\Core\CMbDT::minutesRelative'|static_call:$_operation->entree_bloc:$_operation->sortie_salle}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">{{tr}}COperation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>