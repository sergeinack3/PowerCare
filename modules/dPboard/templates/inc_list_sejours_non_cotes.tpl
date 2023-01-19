{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=class value='CSejour'}}

{{if !$all_prats && $total_non_cotes}}
  <tr class="clear">
    <td colspan="9">
      <div class="small-warning" style="cursor: pointer;" onclick="showNonCotees();">
        Attention, vous avez {{$total_non_cotes}} {{tr}}{{$class}}|pl{{/tr}} non coté(s) durant les 3 derniers mois.
      </div>
    </td>
  </tr>
{{/if}}
<tr>
  <th class="section" colspan="10">{{tr}}{{$class}}|pl{{/tr}} ({{$objects|@count}} / {{$totals.$class}})</th>
</tr>

{{foreach from=$objects item=_sejour}}
  {{assign var=codes_ccam value=$_sejour->codes_ccam}}
  <tr class="alternate">
      {{if $chirSel && $object_classes|@count == 1 && $display_seances}}
      <td class="narrow">
        <input type="checkbox" class="select_objects" data-guid="{{$_sejour->_guid}}" name="select_{{$_sejour->_guid}}" onchange="checkObject();"/>
      </td>
    {{/if}}
    {{if $all_prats}}
      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
      </td>
    {{/if}}
    <td class="text">
      {{assign var=patient value=$_sejour->_ref_patient}}
      <a href="{{$patient->_dossier_cabinet_url}}">
        <strong class="{{if !$_sejour->entree_reelle}}patient-not-arrived{{/if}}"
                onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient}}
        </strong>

        {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </a>
    </td>
    <td class="text">
      <a href="#1" onclick="Operation.showDossierSoins('{{$_sejour->_id}}', 'Actes', updateActes); return false;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
            {{$_sejour}}
          </span>
      </a>
      {{if $_sejour->libelle}}
        <div class="compact">
          {{$_sejour->libelle}}
        </div>
      {{/if}}
    </td>
    <td>
      {{if !$_sejour->_count_actes && !$_sejour->_ext_codes_ccam}}
        <div class="empty">Aucun prévu</div>
      {{else}}
        {{$_sejour->_actes_non_cotes}} acte(s)
      {{/if}}
    </td>
    <td class="text">
      {{foreach from=$_sejour->_ext_codes_ccam item=code}}
        <div>
          {{$code->code}}
        </div>
      {{/foreach}}
    </td>

    <td>
      {{foreach from=$_sejour->_ref_actes_ccam item=_acte}}
        {{if $_acte->executant_id == $_sejour->praticien_id}}
          <div class="">
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant initials=border}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                {{$_acte->code_acte}}-{{$_acte->code_activite}}-{{$_acte->code_phase}}
              {{if $_acte->modificateurs}}
                MD:{{$_acte->modificateurs}}
              {{/if}}
              {{if $_acte->montant_depassement}}
                DH:{{$_acte->montant_depassement|currency}}
              {{/if}}
              </span>
          </div>
        {{/if}}
      {{/foreach}}
      {{foreach from=$_sejour->_ref_actes_ngap item=_acte}}
        {{if $_acte->executant_id == $_sejour->praticien_id}}
          <div class="">
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant initials=border}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                {{if $_acte->quantite > 1}}{{mb_value object=$_acte field=quantite}} x {{/if}}{{$_acte->code}}{{if $_acte->coefficient != 1}} ({{mb_value object=$_acte field=coefficient}}){{/if}}
              {{if $_acte->complement}}
                <span class="circled" title="{{mb_value object=$_acte field=complement}}">{{$_acte->complement}}</span>
              {{/if}}
              {{mb_value object=$_acte field=_tarif}}
            </span>
          </div>
        {{/if}}
      {{/foreach}}
    </td>
    <td>
      {{if $_sejour->praticien_id|array_key_exists:$_sejour->_ref_codages_ccam}}
        {{assign var=_chir_id value=$_sejour->praticien_id}}
        {{assign var=_codages value=$_sejour->_ref_codages_ccam[$_chir_id]}}
        {{assign var=locked value=true}}
        {{foreach from=$_codages item=_codage}}
          {{if !$_codage->locked}}
            {{assign var=locked value=false}}
          {{/if}}
          {{foreachelse}}
          {{assign var=locked value=false}}
        {{/foreach}}

        {{if $locked}}
          <i class="fa fa-check" style="color: #078227"></i> Validée
        {{else}}
          <i class="fa fa-times" style="color: #820001"></i> En cours
        {{/if}}
      {{/if}}
    </td>
    {{if $display_operations}}
      <td>
      </td>
      <td>
      </td>
    {{/if}}
    {{if $show_unexported_acts}}
      <td>
        {{foreach from=$_sejour->_ref_actes_ccam item=_acte}}
          {{if !$_acte->sent}}
            <div>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                  {{$_acte->code_acte}}-{{$_acte->code_activite}}-{{$_acte->code_phase}}
                </span>
            </div>
          {{/if}}
        {{/foreach}}
      </td>
    {{/if}}
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="9" class="empty">{{tr}}{{$class}}.none_non_cotee{{/tr}}</td>
  </tr>
{{/foreach}}
