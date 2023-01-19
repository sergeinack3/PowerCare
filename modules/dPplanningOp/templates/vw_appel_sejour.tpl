{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode_interv      value=0}}
{{mb_default var=appel_modal_ambu value=0}}

{{assign var=appel value=$sejour->_ref_appels_by_type.$type}}
{{assign var=color value="gray"}}

{{if $appel->etat == "realise"}}
  {{assign var=color value="#10BA2C"}}
{{elseif $appel->etat == "echec"}}
  {{assign var=color value="orange"}}
{{/if}}

{{assign var=etat value="none"}}
{{if $appel->_id}}
  {{assign var=etat value=$appel->etat}}
{{/if}}

{{* Dans le cas des appels de la veille et du lendemain, si l'appel n'a pas été fait à la bonne date, il n'est pas prit en compte pour l'affichage de l'état *}}
{{if ($type == 'admission' && $etat != 'none' && ('Ox\Core\CMbDT::daysRelative'|static_call:$appel->datetime:$sejour->entree:true > 2 || $sejour->entree < $appel->datetime))
  || ($type == 'sortie' && $etat != 'none' && ('Ox\Core\CMbDT::daysRelative'|static_call:$sejour->sortie:$appel->datetime:true > 2 || $sejour->sortie > $appel->datetime))}}
  {{assign var=color value="gray"}}
  {{assign var=etat value="none"}}
{{/if}}

{{if $mode_interv}}
  <i class="fa fa-phone event-icon {{if $appel_modal_ambu}}big_pointer{{/if}}" style="background-color: {{$color}}; {{if !$appel_modal_ambu}}font-size: 100%;{{/if}} cursor: pointer;"
     title="{{if !$appel_modal_ambu}}{{tr}}CAppelSejour.etat.{{$etat}}{{/tr}}{{if $appel->commentaire}} - {{/if}}{{/if}}{{$appel->commentaire}}"
     onclick="Appel.edit(0, '{{$type}}', '{{$sejour->_id}}', '{{if $interv}}{{$interv->_id}}{{/if}}', '{{$appel_modal_ambu}}');"></i>
{{else}}
  <button type="button" class="fa fa-phone notext" style="color:{{$color}} !important; border: {{$color}} 2px solid;"
          title="{{if !$appel_modal_ambu}}{{tr}}CAppelSejour.etat.{{$etat}}{{/tr}}{{if $appel->commentaire}} - {{/if}}{{/if}}{{$appel->commentaire}}"
          onclick="Appel.edit(0, '{{$type}}', '{{$sejour->_id}}', '{{$appel_modal_ambu}}');"></button>
{{/if}}

{{if $appel_modal_ambu}}
  <div style="background-color: {{$color}}; color: white; text-align: center; margin-top: 3px;">
    {{tr}}CAppelSejour.etat.{{$etat}}{{/tr}}
  </div>
{{/if}}
