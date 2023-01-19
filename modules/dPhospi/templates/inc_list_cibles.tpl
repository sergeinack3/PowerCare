{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$cibles|@count}}
  {{mb_return}}
{{/if}}

{{assign var=new_group value="soins Transmissions new_group"|gconf}}

<script>
  Main.add(function () {
    var form = getForm("editTrans");
    $V(form._force_new_cible, form.cible_id.value ? '0' : '1');

    var checkbox_diet = $('editTrans___dietetique');
    if (!checkbox_diet.checked) {
      {{if $object_id && $object_class && $cibles|@count == 1 && $first_cible}}
      {{if $first_cible->_ref_first_transmission && $first_cible->_ref_first_transmission->dietetique}}
      checkbox_diet.click();
      checkbox_diet.disabled = 'disabled';
      {{/if}}
      {{/if}}
    }

    {{if $new_group && !$cible_id}}
    $V(form.cible_id, "");
    {{/if}}
  });
</script>

&ndash;

<label>
  Groupe :

  <select name="cible_id" style="width: 200px;"
          onchange="$V(this.form._force_new_cible, this.value ? '0' : '1');
            if (this.value) {
            updateListTransmissions('{{if $libelle_ATC}}{{$libelle_ATC|smarty:nodefaults|JSAttribute}}{{else}}{{$object_id}}{{/if}}', '{{$object_class}}', this.value);
            }
            else {
            updateListTransmissions(null, null, null);
            }">
    <option value="">&mdash; Nouveau groupe</option>
    {{foreach from=$cibles item=_cible}}
      <option value="{{$_cible->_id}}"
        {{if $_cible->_id == $cible_id || $cibles|@count == 1}}
      selected
        {{/if}}>
        {{$_cible->_view|smarty:nodefaults|truncate:50:"...":false}}
      </option>
    {{/foreach}}
  </select>
</label>