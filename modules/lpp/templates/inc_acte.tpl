{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_ternary var=form test=$acte_lpp->_id value="editActeLPP-"|cat:$acte_lpp->_id other='createActeLPP'}}

<script>
  Main.add(function() {
    {{if !$acte_lpp->_id}}
      /* Autocomplete du code LPP */
      var url = new Url('lpp', 'codeAutocomplete');
      url.autoComplete(getForm('{{$form}}-code').code, 'code_llp_auto_complete', {
        minChars: 0,
        updateElement: function(selected) {
          $V(getForm('{{$form}}-code').code, selected.readAttribute('data-code'), true);
          $V(getForm('{{$form}}-code_prestation').code_prestation, selected.readAttribute('data-code_prestation'), true);
          $V(getForm('{{$form}}-type_prestation').type_prestation, selected.readAttribute('data-type_prestation'), true);
          $V(getForm('{{$form}}-montant_base').montant_base, selected.readAttribute('data-montant_base'), true);
          if (selected.readAttribute('data-dep')) {
            $('{{$form}}-button_dep').show();
            $('{{$form}}-display_dep').hide();
          }
          else {
            $('{{$form}}-button_dep').hide();
            $('{{$form}}-display_dep').show();
          }

          $$('select#{{$form}}-qualif_depense_qualif_depense option').each(function(option) {
            option.disabled = false;
          });
          /* Désactive les qualificatifs de dépense non autorisés */
          selected.readAttribute('data-qualif_depense').split('|').each(function(qualif) {
            $$('select#{{$form}}-qualif_depense_qualif_depense option[value="' + qualif + '"')[0].disabled = true;
          });
        },
        callback: function(input, queryString) {
          {{if $m == 'dPcabinet'}}
            queryString = queryString + '&executant_id=' + $V(getForm('{{$form}}-executant_id').executant_id);
          {{/if}}

          return queryString + '&date=' + $V(getForm('{{$form}}-date').date);
        }
      });
    {{/if}}

    /* Autocomplete des executants */
    var url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('edit', '1');
    url.addParam('input_field', '_executant_view');
    url.addParam('prof_sante', 1);
    url.autoComplete(getForm('{{$form}}-executant_id')._executant_view, null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var form = getForm('{{$form}}-executant_id');
        $V(form._executant_view, selected.down('.view').innerHTML);
        $V(form.executant_id, selected.getAttribute('id').split('-')[2]);
      }
    });
  });
</script>

<tr>
  <td>
    <form name="{{$form}}-code" action="?" method="post" onsubmit="return false">
      {{if $acte_lpp->_id}}
        {{mb_field object=$acte_lpp field=code onchange="syncActLppField('$form', 'code', this);" readonly=true}}
      {{else}}
        {{mb_field object=$acte_lpp field=code onchange="syncActLppField('$form', 'code', this);"}}
        <div style="display: none; width: 300px;" class="autocomplete" id="code_llp_auto_complete"></div>
      {{/if}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-code_prestation" action="?" method="post" onsubmit="return false">
      {{mb_field object=$acte_lpp field=code_prestation readonly=true onchange="syncActLppField('$form', 'code_prestation', this);"}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-type_prestation" action="?" method="post" onsubmit="return false">
      {{mb_field object=$acte_lpp field=type_prestation readonly=true emptyLabel="Select" onchange="syncActLppField('$form', 'type_prestation', this);"}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-executant_id" action="?" method="post" onsubmit="return false">
      {{mb_field object=$acte_lpp field=executant_id onchange="syncActLppField('$form', 'executant_id', this);" hidden=true}}
      <input type="text" name="_executant_view" value="{{$acte_lpp->_ref_executant->_view}}" style="width: 8em;"/>
    </form>
  </td>
  <td>
    <form name="{{$form}}-date" action="?" method="post" onsubmit="return false">
      {{mb_field object=$acte_lpp field=date register=true form=$form|cat:'-date' onchange="syncActLppField('$form', 'date', this);"}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-date_fin" action="?" method="post" onsubmit="return false">
      {{mb_field object=$acte_lpp field=date_fin register=true form=$form|cat:'-date_fin' onchange="syncActLppField('$form', 'date_fin', this);"}}
    </form>
  </td>
  <td>
    <button id="{{$form}}-button_dep"type="button" class="edit notext" onclick="editDEP('{{$form}}');"{{if !$acte_lpp->_dep}} style="display: none;"{{/if}}>{{tr}}CActeLPP-accord_prealable{{/tr}}</button>
    <span id="{{$form}}-display_dep"{{if $acte_lpp->_dep}} style="display: none;"{{/if}}>&mdash;</span>
  </td>
  <td>
    <form name="{{$form}}-qualif_depense" action="?" method="post" onsubmit="return false">
      <select name="qualif_depense" onchange="syncActLppField('{{$form}}', 'accord_prealable', $V(this));" style="width: 10em;">
        <option value="" {{if !$acte_lpp->qualif_depense}} selected="selected"{{/if}}>{{tr}}CActeLPP.qualif_depense.{{/tr}}</option>
        {{foreach from=$acte_lpp->_qual_depense item=_qualif}}
          <option value="{{$_qualif}}"{{if $_qualif == $acte_lpp->qualif_depense}} selected="selected"{{/if}}{{if $_qualif|in_array:$acte_lpp->_unauthorized_qual_depense}} disabled="disabled"{{/if}}>
            {{tr}}CActeLPP.qualif_depense.{{$_qualif}}{{/tr}}
          </option>
        {{/foreach}}
      </select>
    </form>
  </td>
  <td>
    <form name="{{$form}}-quantite" action="?" method="post" onsubmit="return false">
    {{mb_field object=$acte_lpp field=quantite onchange="changeFinalPrice('$form', 'quantite', this);" size=3}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-montant_base" action="?" method="post" onsubmit="return false">
    {{mb_field object=$acte_lpp field=montant_base readonly=true onchange="changeFinalPrice('$form', 'montant_base', this);" style="size: 4;"}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-montant_final" action="?" method="post" onsubmit="return false">
    {{mb_field object=$acte_lpp field=montant_final onchange="updateTotalPrice('$form', 'montant_final', this);" style="size: 4;" readonly=true}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-montant_depassement" action="?" method="post" onsubmit="return false">
    {{mb_field object=$acte_lpp field=montant_depassement onchange="updateTotalPrice('$form', 'montant_depassement', this);" style="size: 4;"}}
    </form>
  </td>
  <td>
    <form name="{{$form}}-montant_total" action="?" method="post" onsubmit="return false">
    {{mb_field object=$acte_lpp field=montant_total onchange="syncActLppField('$form', 'montant_total', this);" style="size: 4;" readonly=true}}
    </form>
  </td>
  <td class="buttons compact">
    <form name="{{$form}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, updateActesLPP.curry());">
      {{mb_class object=$acte_lpp}}
      {{mb_key object=$acte_lpp}}

      {{mb_field object=$acte_lpp field=object_id hidden=true}}
      {{mb_field object=$acte_lpp field=object_class hidden=true}}
      {{mb_field object=$acte_lpp field=execution hidden=true}}
      {{mb_field object=$acte_lpp field=code hidden=true}}
      {{mb_field object=$acte_lpp field=code_prestation hidden=true}}
      {{mb_field object=$acte_lpp field=type_prestation hidden=true}}
      {{mb_field object=$acte_lpp field=executant_id hidden=true}}
      {{mb_field object=$acte_lpp field=date hidden=true}}
      {{mb_field object=$acte_lpp field=date_fin hidden=true}}
      {{mb_field object=$acte_lpp field=accord_prealable hidden=true}}
      {{mb_field object=$acte_lpp field=date_demande_accord hidden=true}}
      {{mb_field object=$acte_lpp field=reponse_accord hidden=true}}
      {{mb_field object=$acte_lpp field=qualif_depense hidden=true}}
      {{mb_field object=$acte_lpp field=quantite hidden=true}}
      {{mb_field object=$acte_lpp field=montant_base hidden=true}}
      {{mb_field object=$acte_lpp field=montant_final hidden=true}}
      {{mb_field object=$acte_lpp field=montant_depassement hidden=true}}
      {{mb_field object=$acte_lpp field=montant_total hidden=true}}

      {{if $acte_lpp->_id}}
        <input type="hidden" name="del" value="0"/>
        <button type="button" class="save notext" onclick="this.form.onsubmit();">{{tr}}Edit{{/tr}}</button>
        <button type="button" class="trash notext" onclick="$V(this.form.del, 1); this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
      {{else}}
        <button id="addActeLPP" type="button" class="add notext" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
      {{/if}}
    </form>

    <div style="display: none;" id="{{$form}}-dep_modal">
      <form name="{{$form}}-dep" action="?" method="post" onsubmit="return false">
        <table class="form">
          <tr>
            <th>
              {{mb_label object=$acte_lpp field=accord_prealable}}
            </th>
            <td>
              {{mb_field object=$acte_lpp field=accord_prealable}}
            </td>
          </tr>
          <tr>
            <th>
              {{mb_label object=$acte_lpp field=date_demande_accord}}
            </th>
            <td>
              {{mb_field object=$acte_lpp field=date_demande_accord register=true form=$form|cat:'-dep'}}
            </td>
          </tr>
          <tr>
            <th>
              {{mb_label object=$acte_lpp field=reponse_accord}}
            </th>
            <td>
              {{mb_field object=$acte_lpp field=reponse_accord emptyLabel=""}}
            </td>
          </tr>
          <tr>
            <td class="buttons" style="text-align: center;" colspan="2">
              <button type="button" class="tick" onclick="syncDEPFields('{{$form}}');">{{tr}}Validate{{/tr}}</button>
              <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </td>
</tr>
