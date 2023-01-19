{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type_cell value=null}}

{{assign var=patient value=$operation->_ref_patient}}
{{if $patient->sexe == "m"}}
  {{assign var=divcolor value="#eef"}}
{{else}}
  {{assign var=divcolor value="#fee"}}
{{/if}}

<div class="circled" style="float: left;background-color: {{$divcolor}}; clear: none;
  border-left: 4px #{{$operation->_ref_chir->_ref_function->color}} solid;">
  <div class="suivi"
       onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}');"
       style="{{if $operation->urgence}}border: 2px solid red;{{/if}}{{if $type_cell == 'sspi'}}width:100px;height:50px;
       {{else}}width:auto;height:auto{{/if}}">
    <i class="fas fa-chart-line event-icon" style="float:right;background-color: {{if $operation->entree_salle}}steelblue{{else}}#bbb{{/if}}; font-size: 100%; cursor: pointer;"
       onclick="Operation.showDossierSoins('{{$operation->sejour_id}}', 'dossier_traitement', refreshSuiviBloc);" title="Dossier séjour"></i>
    <i class="fas fa-cut event-icon" onclick="Operation.dossierBloc('{{$operation->_id}}', true);" title="Dossier bloc"
       style="float:right;background-color: {{if $operation->debut_op && !$operation->fin_op}}blueviolet{{elseif $operation->fin_op}}steelblue{{else}}#bbb{{/if}}; font-size: 100%; cursor: pointer;"></i>
    {{if $operation->sortie_salle}}
      {{mb_value object=$operation field=sortie_salle}}
    {{elseif  $operation->fin_op}}
      {{mb_value object=$operation field=fin_op}}
    {{elseif  $operation->debut_op}}
      {{mb_value object=$operation field=debut_op}}
    {{elseif  $operation->entree_salle}}
      {{mb_value object=$operation field=entree_salle}}
    {{else}}
      {{mb_value object=$operation field=time_operation}}
    {{/if}}
    <br />
    {{if "dPbloc other vignette_anonyme"|gconf}}
      {{$patient->nom|spancate:3:""}} {{$patient->prenom|spancate:3:""}}
    {{else}}
      {{$patient->_view|truncate:35:"..."}}
    {{/if}}
    <br/>
    {{$operation->libelle|truncate:22:"..."}}

    <span style="float: right;">
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_chir initials=border}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_anesth initials=border}}
    </span>
    <br>
    {{mb_value object=$operation->_ref_affectation->_ref_lit field=_view}}
  </div>

  {{if $type_cell}}
    <div class="suivi_evolve" onclick="{{if $type_cell == 'interv'}}reloadTimingsSuivi('{{$operation->_id}}');{{elseif $type_cell == 'sspi' || $type_cell == "sspi_attente"}}reloadTimingsSSPISuivi('{{$operation->_id}}');{{/if}}">
      {{if $type_cell == "brancardage"}}
        {{assign var=brancardage value=$operation->_ref_brancardage->loadFirstByOperation($operation)}}
        <span class="suivi_case brancard {{if $brancardage->_ref_patient_pret}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}CBrancardage-_ref_patient_pret{{/tr}}"></span>
        <span class="suivi_case brancard {{if $brancardage->_ref_demande_brancardage}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}CBrancardage-_ref_demande_brancardage{{/tr}}"></span>
        <span class="suivi_case brancard {{if $brancardage->_ref_prise_en_charge}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}CBrancardage-_ref_prise_en_charge{{/tr}}"></span>
        <span class="suivi_case brancard {{if $brancardage->_ref_arrivee}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}CBrancardage-_ref_arrivee{{/tr}}"></span>
      {{elseif $type_cell == "interv"}}
        <span class="suivi_case interv {{if $operation->entree_salle}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-entree_salle{{/tr}}"></span>
        <span class="suivi_case interv {{if $operation->debut_op}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-debut_op{{/tr}}"></span>
        <span class="suivi_case interv {{if $operation->fin_op}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-fin_op{{/tr}}"></span>
        <span class="suivi_case interv {{if $operation->sortie_salle}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-sortie_salle{{/tr}}"></span>
      {{elseif $type_cell == "sspi" || $type_cell == "sspi_attente"}}
        <span class="suivi_case sspi {{if $operation->entree_reveil}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-entree_reveil{{/tr}}"></span>
        <span class="suivi_case sspi {{if $operation->sortie_reveil_possible}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-sortie_reveil_possible-court{{/tr}}"></span>
        <span class="suivi_case sspi {{if $operation->sortie_reveil_reel}}suivi_green{{else}}suivi_white{{/if}}" title="{{tr}}COperation-sortie_reveil_reel{{/tr}}"></span>
      {{/if}}
    </div>
  {{/if}}
</div>
