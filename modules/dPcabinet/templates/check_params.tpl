{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function() {
  
});
</script>

<div class="small-info">
  <div>Cette vue a pour objectif de vérifier le <strong>paramétrage du module Consultation</strong>.</div>
  <div>Elle n'est accessible que pour <strong>un praticien ou une sécrétaire</strong> de cabinet médical.</div>
  <div>Dans le cas du secrétariat, les paramétrage de l'ensemble de praticiens du cabinet seront vérifiés.</div>
</div>

<h1>Contexte cabinet</h1>

{{assign var=type value=$user->_user_type}}
{{if $user->isPraticien() || $user->isSecretaire()}}
<div class="small-success">
  Vous êtes connecté en tant que 
  <strong>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}</strong>
  utilisateur de type <strong>{{$utypes.$type}}</strong>
</div>
{{else}}
<div class="small-warning">
  Vous êtes connecté en tant que 
  <strong>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}</strong>
  utilisateur de type <strong>{{$utypes.$type}}</strong>
  <!-- Traduire -->
  (ni praticien ni secrétaire)
</div>
{{/if}}

{{assign var=function value=$user->_ref_function}}
{{if $function->type == "cabinet"}}
<div class="small-success">
  Vous êtes associé à la fonction 
  <strong>{{mb_include module=mediusers template=inc_vw_function function=$function}}</strong>
  de type <strong>{{mb_value object=$function field=type}}</strong>
</div>
{{else}}
<div class="small-warning">
  Vous êtes associé à la fonction 
  <strong>{{mb_include module=mediusers template=inc_vw_function function=$function}}</strong>
  de type <strong>{{mb_value object=$function field=type}}</strong>
  <!-- Traduire -->
  (devrait être de type Cabinet)
</div>
{{/if}}

{{if !$user->isPraticien() }}
<div class="small-info">
  Vous avez accès à <strong>{{$praticiens|@count}}</strong>
  praticiens dans cette fonction
</div>
{{/if}}

<h1>Utilisation fonctionnelle</h1>

<div style="height: 480px; overflow: auto;">
  
<table class="tbl">
  <tr>
    <th>Critère</th>
    {{foreach from=$praticiens item=_praticien}}
    <th class="text" style="width: 200px;">
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_praticien}}
    </th>
    {{/foreach}}
  </tr>
  
  {{foreach from=$criteres key=_level item=_criteres}}
    <tr>
      {{assign var=colspan value=$praticiens|@count}}
      {{assign var=colspan value=$colspan+1}}

      <th class="section" colspan="{{$colspan}}">{{$_level}}</th>
    </tr>
    {{foreach from=$_criteres key=_critere item=_values}}
    <tr>
      <td>
        <div><strong>{{tr}}mod-cabinet-check_params-{{$_critere}}{{/tr}}</strong></div>
        <div class="text compact">{{tr}}mod-cabinet-check_params-{{$_critere}}-desc{{/tr}}</div>
      </td>
      {{foreach from=$_values key=_prat_id item=_value}}
      {{assign var=value value=$_value|smarty:nodefaults}}
  
      {{if $_value === null}}
      <td style="width: 200px; text-align: center" class="error">
        Vérification impossible
      </td>
      {{else}} 
      <td style="width: 200px; text-align: center" class="{{$_value|ternary:'ok':'warning'}}">
        {{if is_bool($_value|smarty:nodefaults)}}
          {{if @$details[$_level][$_critere][$_prat_id]}} 
            {{assign var=_details value=$details[$_level][$_critere][$_prat_id]}}
            {{if (is_array($_details))}} 
              {{" / "|implode:$_details}}
            {{/if}}
          {{else}} 
            {{$_value|ternary:'Oui':'Non'}}  
          {{/if}}
        {{else}}
          {{$_value}}
        {{/if}}    
      </td>
      {{/if}}
      {{/foreach}}
    </tr>
    {{/foreach}}
     
  {{foreachelse}}
  <tr><td class="empty">Aucun critère vérifié</td></tr>
  {{/foreach}}
  
</table>

</div>
