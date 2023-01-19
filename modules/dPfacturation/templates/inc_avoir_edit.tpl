{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture_avoir ajax=1}}

<script>
  Main.add(
    function() {
      Calendar.regField(getForm('avoir_edit').date);
    }
  );
</script>
<form name="avoir_edit" method="post">
  {{mb_key object=$avoir}}
  {{mb_class object=$avoir}}
  <input type="hidden" name="object_id" value="{{$avoir->object_id}}"/>
  <input type="hidden" name="object_class" value="{{$avoir->object_class}}"/>
  <table class="form me-no-align me-no-box-shadow">
    {{mb_include module=system template=inc_form_table_header object=$avoir}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$avoir mb_field=date}}
        <input type="hidden" class="dateTime" name="date" value="{{$avoir->date}}"/>
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$avoir mb_field=montant}}
        {{mb_field object=$avoir field=montant}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$avoir mb_field=commentaire}}
        {{mb_field object=$avoir field=commentaire}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="save" type="button" onclick="FactureAvoir.save(this.form)">{{tr}}Save{{/tr}}</button>
        {{if $avoir->_id}}
          <button class="trash" type="button" onclick="FactureAvoir.delete(this.form)">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>