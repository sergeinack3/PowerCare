{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=change_heure}}
{{mb_default var=rpu_link value=""}}

{{assign var=attente value=$rpu->_ref_last_attentes.$type_attente}}
<td {{if $_sejour->sortie_reelle}}class="opacity-60"{{/if}}>
  {{if $attente->depart && $attente->_id}}
    <script>
      Main.add(function() {
        Calendar.regField(getForm("editHeure{{$change_heure}}")._debut);
      });
    </script>
    <form name="editHeure{{$change_heure}}" method="post" action="?">
      {{mb_class object=$attente}}
      {{mb_key   object=$attente}}
      <input type="hidden" name="type_attente" value="{{$type_attente}}" />
      <input type="hidden" name="ajax" value="1" />
      <input type="hidden" name="depart" value="" />
      <input type="text" name="_debut_da" value="{{$attente->depart|date_format:$conf.time}}" class="time" readonly="readonly"/>
      <input type="hidden" name="_debut" autocomplete="off" id="editHeure{{$change_heure}}_debut" value="{{$attente->depart|iso_time}}" class="time notNull"
             onchange="$V(this.form.depart, '{{$attente->depart|iso_date}} ' + $V(this.form._debut));
               onSubmitFormAjax(this.form, refreshAttente.curry(this.form, '{{$rpu->_id}}'))" />
      {{if $isImedsInstalled && ($type_attente == "bio")}}
        {{mb_include module=Imeds template=inc_sejour_labo sejour=$_sejour link="$rpu_link#Imeds"}}
      {{/if}}
    </form>
  {{/if}}
</td>
<td id="retour-{{$type_attente}}-{{$rpu->_id}}" {{if $_sejour->sortie_reelle}}class="opacity-60"{{/if}}>
  {{if $attente->_id}}
    {{mb_include module=urgences template=inc_vw_fin_attente}}
  {{/if}}
</td>