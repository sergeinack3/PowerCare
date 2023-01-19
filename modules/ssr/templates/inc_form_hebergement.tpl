{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=medecin ajax=true}}

<form name="editSejourHebergement" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  {{mb_class object=$sejour}}
  {{mb_key object=$sejour}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="adresse_par_prat_id" value="{{$sejour->adresse_par_prat_id}}"
         onchange="Correspondant.reloadExercicePlaces($V(this), '{{$sejour->_class}}', '{{$sejour->_id}}', 'adresse_par_exercice_place_id');" />

  <table class="form me-no-box-shadow me-no-align">
    <tr>
      {{me_form_field nb_cells=4 mb_object=$sejour mb_field="etablissement_sortie_id"}}
        {{mb_field object=$sejour field="etablissement_sortie_id" hidden=true}}
        <input type="text" name="etablissement_sortie_id_view" value="{{$sejour->_ref_etablissement_transfert}}"/>

        <script>
          Main.add(function() {
            var url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
            url.addParam('field', 'etablissement_sortie_id');
            url.addParam('input_field', 'etablissement_sortie_id_view');
            url.addParam('view_field', 'nom');
            url.autoComplete(getForm('editSejourHebergement').etablissement_sortie_id_view, null, {
              minChars: 1,
              method: 'get',
              select: 'view',
              dropdown: true,
              afterUpdateElement: function(field, selected) {
                var id = selected.getAttribute("id").split("-")[2];
                $V(getForm('editSejourHebergement').etablissement_sortie_id, id);
              }
            });
          });
        </script>
      {{/me_form_field}}
    </tr>
    <tr id="correspondant_medical">
      {{assign var="object" value=$sejour}}
      <script>
        Medecin.sFormName = "editSejourHebergement";
      </script>
      {{mb_include module=patients template=inc_check_correspondant_medical}}
    </tr>
    <tr>
      <td class="me-no-display"></td>
      <td colspan="3">
          {{mb_include module=patients template=inc_adresse_par_prat
            medecin=$sejour->_ref_adresse_par_prat
            medecin_adresse_par=$medecin_adresse_par
            object=$sejour
            field=adresse_par_exercice_place_id}}
      </td>
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$sejour mb_field="recuse" field_class="me-field-max-w50"}}
        {{mb_field object=$sejour field="recuse"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$sejour mb_field="chambre_seule"}}
        {{mb_field object=$sejour field="chambre_seule"}}
      {{/me_form_bool}}
    </tr>
    <tr>
      {{me_form_field nb_cells=4 mb_object=$sejour mb_field="service_id"}}
        <select name="service_id" class="{{$sejour->_props.service_id}}" style="width: 15em">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$services item=_service}}
          <option value="{{$_service->_id}}" {{if $sejour->service_id == $_service->_id}} selected="selected" {{/if}}>
            {{$_service->_view}}
          </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$sejour mb_field="rques" field_class="me-field-max-w200"}}
        {{mb_field object=$sejour field=rques form="editSejourHebergement"}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="button" class="submit" onclick="onSubmitFormAjax(this.form);">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
