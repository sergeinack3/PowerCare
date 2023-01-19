{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=vue_alternative value=0}}

{{assign var=grossesse value=$_sejour->_ref_grossesse}}
{{assign var=patient   value=$_sejour->_ref_patient}}
{{assign var=operation value=$_sejour->_ref_last_operation}}
{{assign var=rowspan   value=$grossesse->_ref_naissances|@count}}

{{assign var=manage_provisoire value="maternite CGrossesse manage_provisoire"|gconf}}

<tbody class="hoverable">
<tr>
  <td rowspan="{{$rowspan}}">
    <button
      class="{{if !$_sejour->entree_reelle}}tick{{else}}edit notext{{/if}}"
      onclick="
      {{if !$_sejour->entree_reelle && !$operation->annulee && $operation->date > $dnow}}
        Grossesse.askCancelIntervention('{{$_sejour->_id}}');
      {{else}}
        IdentityValidator.manage('{{$patient->status}}', '{{$patient->_id}}',
          Admissions.validerEntree.curry('{{$_sejour->_id}}', null, function() {
            if (window.Tdb && Tdb.vue_alternative) {
              Tdb.views.listTermesPrevus(false);
            }
            else {
              document.location.reload();
            }
          }
        ));
      {{/if}}">
      {{if !$_sejour->entree_reelle}}{{tr}}CSejour-admit{{/tr}}{{else}}Modifier Admission{{/if}}
    </button>

    {{if $_sejour->entree_reelle}}
      Entrée réelle : {{mb_value object=$_sejour field=entree_reelle}}
      <br />
      {{if $_sejour->mode_sortie}}
        {{tr}}CSejour.mode_sortie.{{$_sejour->mode_sortie}}{{/tr}}
      {{/if}}
    {{/if}}
  </td>
  <td colspan="2" rowspan="{{$rowspan}}" class="text">
      <span class="CPatient-view {{if $vue_alternative && !$_sejour->entree_reelle}}patient-not-arrived{{/if}}"
            onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
        {{if "dPsante400"|module_active}}
          {{mb_include module=dPsante400 template=inc_manually_ipp_nda sejour=$_sejour patient=$patient
          callback=document.location.reload}}
        {{/if}}
        {{$_sejour->_ref_patient}}
      </span>

    {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_sejour->_ref_patient}}
  </td>
  <td rowspan="{{$rowspan}}">
    <button class="print notext me-tertiary me-dark" onclick="Naissance.printDossier('{{$_sejour->_id}}');">
      {{tr}}Print{{/tr}}
    </button>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
        {{mb_value object=$_sejour field=entree date=$date}}
      </span>
  </td>
  {{if $_sejour->grossesse_id}}
  <td rowspan="{{$rowspan}}" class="button">
    {{if $operation->_id}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}');">
                      {{mb_value object=$operation field=time_operation}}
                    </span>
      <button type="button" class="edit notext me-tertiary" onclick="Grossesse.editOperation('{{$operation->_id}}');"></button>
    {{elseif $_sejour->grossesse_id}}
      <button type="button" class="add notext me-secondary"
              onclick="Grossesse.editOperation(0, '{{$_sejour->_id}}', '{{$_sejour->grossesse_id}}');"></button>
    {{/if}}
  </td>
  <td rowspan="{{$rowspan}}">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$grossesse->_guid}}')">
        {{$grossesse->terme_prevu|date_format:$conf.date}}
      </span>
    {{if $vue_alternative}}
      <button class="grossesse notext me-tertiary" onclick="Tdb.editGrossesse('{{$grossesse->_id}}');">{{tr}}CGrossesse.edit{{/tr}}</button>
    {{/if}}
  </td>
  <td class="text" rowspan="{{$rowspan}}">
    {{foreach from=$grossesse->_praticiens item=_praticien}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_praticien}}
    {{/foreach}}
  </td>
  {{foreach from=$grossesse->_ref_naissances item=_naissance name=foreach_naissance}}
  {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
  {{assign var=enfant value=$sejour_enfant->_ref_patient}}
  {{if !$smarty.foreach.foreach_naissance.first}}
<tr>
  {{/if}}
  <td {{if !$_naissance->date_time}}class="empty"{{/if}}>
    <button class="edit notext" onclick="Naissance.edit('{{$_naissance->_id}}', null, null, 0, 'document.location.reload');">
      {{tr}}Edit{{/tr}}
    </button>
    {{if $_naissance->date_time}}
      Le {{$_naissance->date_time|date_format:$conf.date}} à {{$_naissance->date_time|date_format:$conf.time}}
    {{else}}
      {{$_naissance}}
    {{/if}}
  </td>
  <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$enfant->_guid}}');">
        {{$enfant}}
      </span>
  </td>
  <td>
    <button class="print notext me-tertiary me-dark" onclick="Naissance.printDossier('{{$sejour_enfant->_id}}');">
      {{tr}}Print{{/tr}}
    </button>
    {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour_enfant->_guid}}');">
        {{mb_value object=$sejour_enfant field=entree date=$date}}
      </span>
  </td>
  {{if $smarty.foreach.foreach_naissance.first}}
  {{if $manage_provisoire}}
    <td class="narrow" rowspan="{{$rowspan}}">
      <button type="button" class="add notext me-secondary" title="Créer un dossier provisoire"
              onclick="Naissance.edit(null, null, '{{$_sejour->_id}}', 1, 'document.location.reload');"></button>
    </td>
  {{/if}}
</tr>
{{/if}}
{{foreachelse}}
<td colspan="3"></td>
{{if $manage_provisoire}}
  <td class="narrow" rowspan="{{$rowspan}}">
    <button type="button" class="add notext" title="Créer un dossier provisoire"
            onclick="Naissance.edit(null, null, '{{$_sejour->_id}}', 1, 'document.location.reload')"></button>
  </td>
{{/if}}
{{/foreach}}
{{else}}
<td colspan="7" rowspan="{{$rowspan}}">
  <div class="small-info">
    {{tr}}CGrossesse-no_link_sejour{{/tr}}
  </div>
</td>
{{/if}}
</tbody>
