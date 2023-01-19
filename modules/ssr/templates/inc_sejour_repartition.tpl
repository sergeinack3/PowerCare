{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$remplacement}}
<div class="ssr-sejour-bar" title="arrivée il y a {{$sejour->_entree_relative}}j et départ prévu dans {{$sejour->_sortie_relative}}j ">
  <div style="width: {{if $sejour->_duree}}{{math equation='100*(-entree / (duree))' entree=$sejour->_entree_relative duree=$sejour->_duree format='%.2f'}}{{else}}100{{/if}}%;"></div>
</div>
{{/if}}
  
{{assign var=bilan value=$sejour->_ref_bilan_ssr}}
{{assign var=patient value=$sejour->_ref_patient}}
<span {{if $bilan->_encours}} class="encours" {{/if}} onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
  {{$patient->nom}} {{$patient->prenom}}
</span>

{{mb_include module=patients template=inc_icon_bmr_bhre}}

<div class="libelle">
  <div style="float: right;">
    ({{$patient->_age}})
  </div>

  {{if $sejour->hospit_de_jour && $bilan->_demi_journees}}
    <img style="float: right;" title="{{mb_value object=$bilan field=_demi_journees}}" src="modules/ssr/images/dj-{{$bilan->_demi_journees}}.png" />
  {{/if}}
  
  {{assign var=libelle value=$sejour->libelle|upper|smarty:nodefaults}}
  {{assign var=color value=$colors.$libelle}}
  {{if $color->color}}
    <div class="motif-color" style="background-color: #{{$color->color}};" title="{{$sejour->libelle}}"></div>
  {{else}}
    {{mb_value object=$sejour field=libelle}}
  {{/if}}
</div>

