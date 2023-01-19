{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=true}}

{{if $timing === "entree_bloc" && !"dPsalleOp timings use_entry_room"|gconf}}
  {{mb_return}}
{{/if}}

{{if $timing === "induction_debut" && !"dPsalleOp timings timings_induction"|gconf}}
  {{mb_return}}
{{/if}}

{{if in_array($timing, array("entree_salle", "sortie_salle")) && !"dPsalleOp timings use_entry_exit_room"|gconf}}
  {{mb_return}}
{{/if}}

{{if $timing === "preparation_op" && !"dPsalleOp timings use_preparation_op"|gconf}}
  {{mb_return}}
{{/if}}

{{if in_array($timing, array("debut_op", "fin_op")) && !"dPsalleOp timings use_end_op"|gconf}}
  {{mb_return}}
{{/if}}

{{if in_array($timing, array("pec_anesth", "remise_anesth", "patient_stable")) && !"dPsalleOp timings see_$timing"|gconf}}
  {{mb_return}}
{{/if}}

{{if $timing === "remise_chir" && !"dPsalleOp timings use_delivery_surgeon"|gconf}}
  {{mb_return}}
{{/if}}

{{if $timing === "entree_reveil" && !"dPsalleOp timings see_entree_reveil_timing"|gconf}}
  {{mb_return}}
{{/if}}

{{if $timing === "sortie_reveil_reel" && !"dPsalleOp timings use_exit_without_sspi"|gconf}}
  {{mb_return}}
{{/if}}

{{if $timing === "fin_pec_anesth" && !"dPsalleOp timings see_fin_pec_anesth"|gconf}}
  {{mb_return}}
{{/if}}

{{assign var=timing_title value=$timing}}

{{if $timing === "sortie_reveil_reel"}}
  {{assign var=timing_title value="sortie_sans_sspi"}}
{{/if}}

<div id="top-horaire-{{$operation->_id}}-{{$timing}}"
     class="top_horaire top_horaire{{if $operation->$timing}}_filled{{else}}_to_fill{{/if}} {{if !$edit}}top_horaire_locked{{/if}}"
     title="{{tr}}COperation-{{$timing_title}}-desc{{/tr}}"
  {{if $edit && !$operation->$timing}}
    onclick="this.down('form').onsubmit();"
  {{/if}}>
  {{mb_include module=salleOp template=inc_content_top_horaire edit=$edit}}
</div>