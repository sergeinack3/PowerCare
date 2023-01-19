{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ccam script=code_ccam ajax=true}}

{{mb_default var=with_thead value=0}}

<!-- S'il y a deja des actes codés, affichage seulement des actes codes -->
{{if $subject->_ref_actes_ccam && ($vue == "complete" || $vue =="view")}}
<table class="tbl print_sejour">
  {{mb_include module=soins template=inc_thead_dossier_soins colspan=10 with_thead=$with_thead}}

  <tr>
    <th class="title" colspan="10">
      {{tr}}CActeCCAM-Coding of CCAM acts{{/tr}}
    </th>
  </tr> 
  {{foreach from=$subject->_ref_actes_ccam item=_acte name=acte}}
  {{if $smarty.foreach.acte.first}}
  <tr>
    <th>{{mb_title object=$_acte field=code_acte         }}</th>
    <th>{{mb_title object=$_acte field=modificateurs     }}</th>
    <th>{{mb_title object=$_acte field=code_association  }}</th>
    {{if $vue == "complete"}}
      <th>{{mb_title object=$_acte field=executant_id      }}</th>
    {{/if}}
    <th>{{mb_title object=$_acte field=execution         }}</th>
    <th>{{mb_title object=$_acte field=coding_datetime   }}</th>
    {{if @$extra == "comment"}}
    <th>{{mb_title object=$_acte field=commentaire       }}</th>
    {{/if}}
    {{if @$extra == "tarif"}}
    <th>{{mb_title object=$_acte field=montant_base       }}</th>
    <th>{{mb_title object=$_acte field=montant_depassement}}</th>
    <th>{{mb_title object=$_acte field=_montant_facture   }}</th>
    {{/if}}
    {{if @$extra == "total"}}
      <th>{{mb_title object=$_acte field=_montant_facture   }}</th>
    {{/if}}
  </tr>
  {{/if}}
  
  {{assign var=code value=$_acte->code_acte}}
  {{if $_acte->code_activite != ""}}
    {{assign var=codeActivite value=$_acte->code_activite}}
    {{assign var=code value="$code-$codeActivite"}}
    {{if $_acte->code_phase != ""}}
      {{assign var=codePhase value=$_acte->code_phase}}
      {{assign var=code value="$code-$codePhase"}}
    {{/if}}
  {{/if}}
  <tr>
    <td>
      <a href="#CodeCCAM-show-{{$code}}" onclick="CodeCCAM.show('{{$code}}','{{$subject->_class}}')" style="display:inline-block;">
        {{mb_value object=$_acte field=code_acte}}
      </a>
      <span class="circled" style="background-color: #eeffee;">
        {{$_acte->code_activite}}-{{$_acte->code_phase}}
      </span>
    </td>
    <td class="text">
      {{foreach from=$_acte->_modificateurs item=modificateur}}
        <span class="circled me-color-black-high-emphasis">{{$modificateur}}</span>
      {{/foreach}}
    </td>
    <td class="text">{{mb_value object=$_acte field=code_association}}</td>

    {{if $vue == "complete"}}
      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant}}
      </td>
    {{/if}}

    <td class="text">{{mb_value object=$_acte field=execution}}</td>
    <td class="text">{{mb_value object=$_acte field=coding_datetime}}</td>

    {{if @$extra == "comment"}}
    <td class="text">{{mb_value object=$_acte field=commentaire}}</td>    
    {{/if}}

    {{if @$extra == "tarif"}}
    <td class="text" style="text-align: right">{{mb_value object=$_acte field=montant_base}}</td>
    <td  class="text" style="text-align: right">{{mb_value object=$_acte field=montant_depassement}}</td>
    <td  class="text" style="text-align: right">{{mb_value object=$_acte field=_montant_facture}}</td>
    {{/if}}
    {{if @$extra == "total"}}
      <td  class="text">{{mb_value object=$_acte field=_montant_facture}}</td>
    {{/if}}
  </tr>
  
  {{/foreach}}
</table>  
<!-- Sinon, affichage des actes prevus -->

{{elseif $subject->_ext_codes_ccam}}
<table class="tbl">
  {{mb_include module=soins template=inc_thead_dossier_soins colspan=2 with_thead=$with_thead}}

  <tr>
    <th class="category" colspan="2">{{tr}}CActeCCAM{{/tr}}</th>
  </tr>
  {{foreach from=$subject->_ext_codes_ccam item=_code}}
  <tr>
    <td>
      <a href="#CodeCCAM-Show-{{$_code->code}}" onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}')" style="display:inline-block;">
        {{$_code->code}}
      </a>
  
    </td>
    <td class="text" style="max-width:22em">
      {{$_code->libelleLong}}

      {{if @$view_tarif}}
      <!-- Tarifs des activités (phase 0) -->
      <em>(
      {{foreach from=$_code->activites item=_actvite name=tarif}}
      {{tr}}CCodageCCAM-activite_anesth{{/tr}} {{$_actvite->numero}} : {{$_actvite->phases.0->tarif|currency}}
      {{if !$smarty.foreach.tarif.last}}&mdash;{{/if}}
      {{/foreach}}
      )</em>
      {{/if}}
    </td>
  </tr>
  {{/foreach}}
</table>
{{/if}}
