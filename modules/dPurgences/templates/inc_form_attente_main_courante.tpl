{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uid_attente}}

{{assign var=attente value=$rpu->_ref_last_attentes.$type_attente}}
{{assign var=img_attente value="modules/soins/images/radio"}}
{{assign var=timing_radio value=""}}
{{if $type_attente == "bio"}}
  {{assign var=img_attente value="images/icons/labo"}}
{{elseif $type_attente == "specialiste"}}
  {{assign var=img_attente value="modules/soins/images/stethoscope"}}
{{/if}}
{{if $attente->demande}}
  {{assign var=timing_radio value="demande"}}
{{/if}}

<form name="editAttente-{{$uid_attente}}-{{$attente->_id}}"
      onsubmit="
      {{if $type == "MainCourante"}}
        return onSubmitFormAjax(this, {onComplete: function() { MainCourante.start() }});
      {{elseif $type == "UHCD"}}
        return onSubmitFormAjax(this, {onComplete: function() { UHCD.start() }});
      {{elseif $type == "imagerie"}}
        return onSubmitFormAjax(this, {onComplete: function() { Imagerie.start() }});
      {{/if}}
      "  method="post" action="?">
  {{mb_class object=$attente}}
  {{mb_key   object=$attente}}
  <input type="hidden" name="depart" value="{{$attente->depart}}" />
  <input type="hidden" name="retour" value="{{$attente->retour}}" />

  {{if $attente->depart || $attente->demande}}
    <img src="{{$img_attente}}{{if !$attente->retour}}_grey{{/if}}.png"
         {{if $timing_radio == "demande"}}style="opacity:0.7"{{/if}}
      {{if $attente->demande && !$attente->depart}}
         title="{{tr}}CRPUAttente-{{$type_attente}}-demande{{/tr}} à {{$attente->demande|date_format:$conf.time}}"
      {{elseif !$attente->retour}}
        onclick="fillRetour(getForm('editAttente-{{$uid_attente}}-{{$attente->_id}}'))" href="#1" style="cursor: pointer;"
        title="{{tr}}CRPUAttente-{{$type_attente}}-depart{{/tr}} à {{$attente->depart|date_format:$conf.time}}"
      {{else}}
        title="{{tr}}CRPUAttente-{{$type_attente}}-retour{{/tr}} à {{$attente->retour|date_format:$conf.time}}"
      {{/if}}
    />
  {{elseif !$attente->retour}}
    <img src="images/icons/placeholder.png" />
  {{/if}}
</form>
