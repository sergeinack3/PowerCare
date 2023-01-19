{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    new Url('compteRendu', 'autocomplete')
      .addParam("user_id", '{{$praticien->_id}}')
      .addParam('object_class', '{{$class}}')
      .addParam('object_id'   , '{{$target_id}}')
      .addParam('fast_edit'   , 0)
      .addParam('modele_vierge', 0)
      .autoComplete(getForm('chooseDoc_{{$class}}').keywords_modele, '',
        {
          method: 'get',
          minChars: 2,
          updateElement: function (selected) {
            var id = selected.down('.id').getText();
            setClose(id, '{{$modelesId.$class}}');
          },
          dropdown: true,
          width: "250px"
        }
      );
  });
</script>

{{assign var=count_sections value=$modeles|@count}}

<table id="{{$class}}" class="form" style="display: none;">
  <tr>
    <td colspan="{{$count_sections}}">
      <!-- Autocomplete pour choisir un modèle -->
      <form name="chooseDoc_{{$class}}" method="get" class="prepared">
        <input type="text" name="keywords_modele" placeHolder="&mdash; {{tr}}CCompteRendu-modele-one{{/tr}}"
               class="autocomplete str" style="width: 215px;" />
      </form>
    </td>
  </tr>
  <tr>
  {{foreach from=$modeles item=owned_modeles key=owner}}
    <th class="category" style="width: {{math equation="100/x" x=$count_sections}}%">{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}</th>
  {{/foreach}}
  </tr>
  <tr>
  {{foreach from=$modeles item=owned_modeles key=owner}}
    <td style="text-align: center; width: {{math equation="100/x" x=$count_sections}}%;">
      <select name="modele_{{$class}}_prat" style="width: 100%" size="20"
              {{if $appfine}}
                onchange="selectModele(this.value, '{{$order_id}}');"
              {{else}}
                onchange="if (this.value) setClose(this.value,'{{$modelesId.$class}}', this.options[this.selectedIndex].get('fast_edit'));"
              {{/if}}
               >
      {{foreach from=$owned_modeles item=modele}}
        <option value="{{$modele->_id}}" data-fast_edit="{{if $modele->fast_edit || $modele->fast_edit_pdf}}1{{else}}0{{/if}}">{{$modele->nom}}</option>
      {{foreachelse}}
        <option value="">{{tr}}CCompteRendu.none{{/tr}}</option>
      {{/foreach}}
      </select>
    </td>
  {{/foreach}}
  </tr>
</table>
