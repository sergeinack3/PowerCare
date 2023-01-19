{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  toggleDestination = function (button) {
    var form = button.form;

    var button_valider = form.down('button[class=tick]');

    var other_button = form.down('button[class=\'big ' + (button.hasClassName('domicile') ? 'etab_externe' : 'domicile') + '\']');

    button.setStyle({border: '3px solid #080'});
    other_button.setStyle({border: '1px solid #999'});

    $V(form.type_destination, button.hasClassName('domicile') ? 'domicile' : 'etab_externe');

    var dest_etab_externe = $('dest_etab_externe');
    var blocage_lit = $('blocage_lit');

    switch (button.get('type_dest')) {
      case "domicile":
        $V(form.mode_sortie, '8');
        $V(form.destination, '');
        $V(form.etablissement_sortie_id, '');
        $V(form.etablissement_sortie_id_view, '');

        dest_etab_externe.hide();
        blocage_lit.show();
        form._block_lit.checked = true;

        button_valider.writeAttribute('disabled', null);
        break;
      case "etab_externe":
        $V(form.mode_sortie, '0');
        form._block_lit.checked = false;
        dest_etab_externe.show();
        blocage_lit.hide();

        button_valider.writeAttribute('disabled', $V(form.etablissement_sortie_id) ? null : 'disabled');
    }
  };

  Main.add(function () {
    var form = getForm("editEtabExterne");
    new Url("etablissement", "ajax_autocomplete_etab_externe")
      .addParam("field", "etablissement_sortie_id")
      .addParam("input_field", "etablissement_sortie_id_view")
      .addParam("view_field", "nom")
      .autoComplete(form.etablissement_sortie_id_view, null, {
        minChars:           0,
        method:             'get',
        select:             'view',
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var span = selected.down("span.view");
          $V(form.etablissement_sortie_id_view, span.getText().trim());
          var id = selected.getAttribute("id").split("-")[2];
          var destination = span.get("destination");
          $V(form.etablissement_sortie_id, id);
          if (destination) {
            $V(form.destination, destination);
          }
          if (id) {
            form.down('button[class=tick]').writeAttribute("disabled", null);
          }
        }
      });
  });
</script>

<form name="editEtabExterne" method="post"
      onsubmit="return onSubmitFormAjax(
        this,
        function() {
          Control.Modal.close();
          if (window.refreshMouvements) {
          refreshMouvements(null, '{{$affectation->lit_id}}');
          }
          Placement.loadTableau();
        }
      );">
  {{mb_class object=$affectation}}
  {{mb_key   object=$affectation}}

  {{mb_field object=$affectation field=mode_sortie hidden=true}}
  {{mb_field object=$affectation field=etablissement_sortie_id hidden=true}}

  <table class="main">
    <tr>
      <th class="halfPane">
        <label>
          <button type="button" class="big domicile" data-type_dest="domicile" style="font-size: 10pt; width: 150px;"
                  onclick="toggleDestination(this);">
            <i class="fas fa-home" style="font-size: 1.5em;"></i>
            <br />
            {{tr}}CSejour.mode_entree.8{{/tr}}</button>
          <br />
          <input type="radio" name="type_destination" value="domicile" onclick="this.previous('button').click();" />
        </label>
      </th>
      <th>
        <label>
          <button type="button" class="big etab_externe" data-type_dest="etab_externe" style="font-size: 10pt; width: 150px;"
                  onclick="toggleDestination(this);">
            <i class="fas fa-building" style="font-size: 1.5em;"></i>
            <br />
            {{tr}}CEtabExterne{{/tr}}</button>
          <br />
          <input type="radio" name="type_destination" value="etab_externe" onclick="this.previous('button').click();" />
        </label>
      </th>
    </tr>
    <tr>
      <th>
        <div id="blocage_lit" style="display: none;">
          <label>
            {{mb_field object=$affectation field=_block_lit typeEnum=checkbox value=1}}
            {{mb_label object=$affectation field=_block_lit typeEnum=checkbox}}
          </label>
        </div>
      </th>
      <th>
        <div id="dest_etab_externe" style="display: none;">
          <input type="text" name="etablissement_sortie_id_view" style="width: 15em;"
                 placeholder="&mdash; {{tr}}CEtabExterne{{/tr}}"
                 value="{{$affectation->_ref_etablissement_transfert}}" />

          <br />

          {{mb_field object=$affectation field=destination emptyLabel="None" style="width: 19em;"}}
        </div>
      </th>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="button" class="tick" onclick="this.form.onsubmit();" disabled>{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
