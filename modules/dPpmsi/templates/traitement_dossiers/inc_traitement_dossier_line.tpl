{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi" script="traitementDossiers" ajax=true}}

{{assign var=patient   value=$_sejour->_ref_patient}}
{{assign var=sejour_id value=$_sejour->_id}}
{{assign var=groupage  value=$groupages.$sejour_id}}
{{assign var=fg        value=$groupage->_result_fg}}

<tr class="sejour sejour-type-default sejour-type-{{$_sejour->type}}" id="{{$_sejour->_guid}}">
  <td>
    {{mb_value object=$_sejour field="sortie_reelle"}}
  </td>

  <td>
    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
    <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
      {{$patient}}
    </span>
  </td>

  <td class="text">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
  </td>

  <td>
    <span class="CSejour-view" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
      {{$_sejour->_shortview}}
    </span>
  </td>

  <td class="text narrow columns-2">
    <form name="sejour_traitement_dossier_{{$sejour_id}}" method="post" onsubmit=" return traitementDossiers.submitEtatPmsi(this)">
      {{mb_class object=$_sejour->_ref_traitement_dossier}}
      {{mb_key   object=$_sejour->_ref_traitement_dossier}}
      <input type="hidden" name="sejour_id" value="{{$sejour_id}}">

      {{mb_label object=$_sejour->_ref_traitement_dossier field="traitement"}}
      {{if $_sejour->_ref_traitement_dossier->traitement}}
        {{mb_field object=$_sejour->_ref_traitement_dossier field="traitement" form="sejour_traitement_dossier_$sejour_id" register=true onchange="this.form.onsubmit();"}}
      {{else}}
        <button class="tick notext" type="submit" onclick="$V(this.form.traitement, new Date().toDATETIME())">{{tr}}CTraitementDossier-traitement{{/tr}}</button>
        <input type="hidden" name="traitement" value="{{$_sejour->_ref_traitement_dossier->traitement}}">
      {{/if}}


      {{mb_label object=$_sejour->_ref_traitement_dossier field="validate"}}
      {{if $_sejour->_ref_traitement_dossier->validate}}
        {{mb_field object=$_sejour->_ref_traitement_dossier field="validate" form="sejour_traitement_dossier_$sejour_id" register=true onchange="this.form.onsubmit();"}}
      {{else}}
        <button class="tick notext" type="submit" onclick="$V(this.form.validate, (new Date().toDATETIME()))">{{tr}}CTraitementDossier-validate{{/tr}}</button>
        <input type="hidden" name="validate" value="{{$_sejour->_ref_traitement_dossier->validate}}">
      {{/if}}
      </form>
  </td>
  {{if "atih CGroupage use_fg"|gconf}}
    <td class="text" >
        {{if isset($groupage->_errors.bloquantes|smarty:nodefaults) && isset($groupage->_errors.supplementaires|smarty:nodefaults) &&
        $groupage->_error_groupage && isset($groupage->_ref_infos_ghs|smarty:nodefaults)}}
          <div class="warning" style="height: 13px; display: inline-block"></div>
          <span class="text compact" onmouseover="ObjectTooltip.createDOM(this, 'details-errors-bloq-{{$_key}}')">
            {{$groupage->_ref_infos_ghs->ghm_nro}} -

            {{if $fg->grpResultat->ghm->code !== "90Z00Z"}}
              {{$fg->grpResultat->ghm->libelle|utf8_decode}}
            {{else}}
              {{tr}}module-atih-erreur-FG-90Z00Z-long{{/tr}}
            {{/if}}
          </span>
          <div id="details-errors-bloq-{{$_key}}" style="display:none">
            {{foreach from=$groupage->_errors item=types_erreur}}
              {{foreach from=$types_erreur item=_erreur}}
                <span class="text compact" style="width:100px"> {{tr}}module-atih-erreur-FG-{{$_erreur->code|substr:1}}-court{{/tr}}</span>
                <br/>
              {{/foreach}}
            {{/foreach}}
          </div>
        {{/if}}

        {{if isset($groupage->_ref_infos_ghs|smarty:nodefaults) && !$groupage->_error_groupage}}
          <span class="text"
                {{if $fg->grpResultat->ghm->code !== "90Z00Z"}}
                  onmouseover="ObjectTooltip.createEx(this, 'CGHS-{{$groupage->_ref_infos_ghs->_id}}')"
                {{/if}}>
            <strong>{{$fg->grpResultat->ghm->code}}</strong> -

            {{if $fg->grpResultat->ghm->code !== "90Z00Z"}}
              {{$fg->grpResultat->ghm->libelle|utf8_decode}}
            {{else}}
              {{tr}}module-atih-erreur-FG-90Z00Z-long{{/tr}}
            {{/if}}
          </span>
        {{/if}}
    </td>
  {{/if}}
</tr>