{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Reception.form = 'selType';
  });
</script>

{{assign var=patient value=$_sejour->_ref_patient}}
{{assign var=sejour_id value=$_sejour->_id}}

<td class="text">
  {{mb_value object=$_sejour field="entree_reelle"}}
</td>

<td class="text">
  {{mb_value object=$_sejour field="sortie_reelle"}}
</td>

<td colspan="2">
  {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
  <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
    {{$patient}}
  </span>
</td>

<td class="text">
  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
</td>

<td class="text">
  <span class="CSejour-view" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
    {{$_sejour->_shortview}}
  </span>

  <div class="compact text">
    {{$_sejour->_motif_complet}}
  </div>
</td>

<td class="button">
  <form name="sejour_reception_sortie_{{$sejour_id}}" action="?" method="post">
    {{mb_class object=$_sejour}}
    {{mb_key   object=$_sejour}}

    {{if $_sejour->reception_sortie}}
      {{if $_sejour->sans_dmh}}
        <input type="hidden" name="reception_sortie" />
        <input type="hidden" name="completion_sortie" />
        <input type="hidden" name="sans_dmh" value="0" />
        <button class="cancel" type="button" onclick="Reception.subitEtatPmsi(this.form, '{{$sejour_id}}');">{{tr}}pmsi-action-Cancel without folder{{/tr}}</button>
      {{else}}
        {{mb_field object=$_sejour field="reception_sortie" form="sejour_reception_sortie_$sejour_id" register=true onchange="Reception.subitEtatPmsi(this.form, '$sejour_id');"}}
        {{if @$modules.dPpmsi->_can->edit}}
          <button type="button" class="search" onclick="Sejour.showDossierPmsi('{{$sejour_id}}', '{{$patient->_id}}', Reception.reloadListDossiers);">
            {{tr}}mod-dPpmsi-tab-vw_dossier_pmsi{{/tr}}
          </button>
        {{/if}}
      {{/if}}
    {{else}}
      <input type="hidden" name="reception_sortie" value="now" />
      <input type="hidden" name="completion_sortie" />
      <input type="hidden" name="sans_dmh" value="0" />
      <button class="tick" type="button" onclick="Reception.subitEtatPmsi(this.form, '{{$sejour_id}}');">{{tr}}CSejour-reception_sortie{{/tr}}</button>
      <button class="tick" type="button"
              onclick="$V(this.form.completion_sortie, 'now'); $V(this.form.sans_dmh, '1'); Reception.subitEtatPmsi(this.form, '{{$sejour_id}}');">{{tr}}pmsi-action-Without folder{{/tr}}</button>
    {{/if}}
  </form>
</td>

<td class="button">
  {{mb_include module=pmsi template=inc_relance sejour=$_sejour callback="Reception.reloadDossier.curry(`$sejour_id`)"}}
</td>

<td class="button">
  {{if !$_sejour->sans_dmh}}
    <form name="sejour_completion_sortie_{{$sejour_id}}" action="?" method="post">
      {{mb_class object=$_sejour}}
      {{mb_key   object=$_sejour}}

      {{if $_sejour->completion_sortie}}
        {{mb_field object=$_sejour field="completion_sortie" form="sejour_completion_sortie_$sejour_id" register=true onchange="Reception.subitEtatPmsi(this.form, '$sejour_id');"}}
      {{else}}
        <input type="hidden" name="completion_sortie" value="now" />
        <button class="tick" type="button" onclick="Reception.subitEtatPmsi(this.form, '{{$sejour_id}}');">{{tr}}CSejour-completion_sortie{{/tr}}</button>
      {{/if}}
    </form>
  {{/if}}
</td>