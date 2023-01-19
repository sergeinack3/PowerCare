{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=change_heure}}
{{if $attente->depart && !$attente->retour}}
  <form name="editRPU{{$change_heure}}" action="?" method="post"
   onsubmit="return onSubmitFormAjax(this, refreshAttente.curry(this, '{{$rpu->_id}}'))">
    {{mb_class object=$attente}}
    {{mb_key   object=$attente}}
    <input type="hidden" name="type_attente" value="{{$type_attente}}" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="retour" value="" onchange="this.form.onsubmit();"/>
    <button class="submit" type="button" onclick="$V(this.form.retour, 'current');">
    {{tr}}{{$attente->_class}}-{{$type_attente}}-retour{{/tr}}
    </button>
  </form>
{{elseif $attente->retour}}
  <script>
    Main.add(function() {
      var form = getForm("editHeure{{$change_heure}}");
      Calendar.regField(form._fin);
    });
  </script>
  <form name="editHeure{{$change_heure}}" method="post" action="?">
    {{mb_class object=$attente}}
    {{mb_key   object=$attente}}
    <input type="hidden" name="type_attente" value="{{$type_attente}}" />
    <input type="hidden" name="ajax" value="1" />
    <input type="hidden" name="retour" value="" />
    <input type="text" name="_fin_da" value="{{$attente->retour|date_format:$conf.time}}" class="time" readonly="readonly"/>
    <input type="hidden" name="_fin" autocomplete="off" id="editHeure{{$change_heure}}_fin" value="{{$attente->retour|iso_time}}" class="time notNull"
           onchange="$V(this.form.retour, '{{$attente->retour|iso_date}} ' + $V(this.form._fin));
             onSubmitFormAjax(this.form, refreshAttente.curry(this.form, '{{$rpu->_id}}'))" />
  </form>
{{/if}}