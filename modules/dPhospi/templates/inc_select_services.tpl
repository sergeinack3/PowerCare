{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  savePref = function (form) {
    var formPref = getForm('editPrefService');
    var services_ids_hospi_elt = formPref.elements['pref[services_ids_hospi]'];

    var services_ids_hospi = $V(services_ids_hospi_elt).evalJSON();

    services_ids_hospi.g{{$group_id}} = $V(form.select('input.service:checked'));

    if (services_ids_hospi.g{{$group_id}} == null) {
      services_ids_hospi.g{{$group_id}} = "";
    }
    else {
      services_ids_hospi.g{{$group_id}} = services_ids_hospi.g{{$group_id}}.join("|");
    }

    $V(services_ids_hospi_elt, Object.toJSON(services_ids_hospi));
    return onSubmitFormAjax(formPref, function () {
      {{if $ajax_request}}
      {{if $callback}}
      {{$callback}}
      {{else}}
      form.onsubmit();
      {{/if}}
      {{else}}
      form.submit();
      {{/if}}
      Control.Modal.close();
    });
  };

  checked_radio = false;
  toggleChecked = function () {
    var form = getForm("selectServices");
    form.select("input[type=checkbox]").each(function (elt) {
      elt.checked = checked_radio;
    });
    checked_radio = !checked_radio;
  };

  changeServicesScission = function () {
    var form = getForm("searchLit");
    var formPref = getForm('selectServices');
    var services_ids_suggest = $V(formPref.select('input.service:checked'));
    $V(form.services_ids_suggest, $A(services_ids_suggest).join(','));
    form.onsubmit();
    Control.Modal.close();
  };

  Main.add(function () {
    Control.Modal.stack.last().position();
  });
</script>

<!-- Formulaire de sauvegarde des services en préférence utilisateur -->
<form name="editPrefService" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input type="hidden" name="pref[services_ids_hospi]" value="{{$services_ids_hospi}}" />
</form>

{{math equation=x+1 x=$secteurs|@count assign=colspan}}

<div style="overflow-x: auto;">
  <form name="selectServices" method="get"
        {{if $ajax_request}}onsubmit="return onSubmitFormAjax(this, null, '{{$view}}{{if $element_id}}-{{$element_id}}{{/if}}')"{{/if}}>
    {{if $view == "listSorties"}}
      <input type="hidden" name="m" value="pmsi" />
    {{elseif in_array($view, array("listAdmissions", "sortie","admissions", "sorties", "listPresents", "accueilPatient"))}}
      <input type="hidden" name="m" value="admissions" />
    {{elseif $view == "tdb"}}
      <input type="hidden" name="m" value="board" />
    {{elseif $view == "listAmbuDiv"}}
      <input type="hidden" name="m" value="ambu" />
    {{elseif $view == "list_cleanups"}}
      <input type="hidden" name="m" value="hotellerie" />
    {{elseif $view == "list_transports"}}
      <input type="hidden" name="m" value="transport" />
    {{elseif $view == "tdb_naissances"}}
      <input type="hidden" name="m" value="maternite" />
    {{elseif in_array($view, array("ecap", "soins", "feuille_transmissions", "reeduc"))}}
      <input type="hidden" name="m" value="soins" />
    {{elseif $view == "rhs-no-charge"}}
      <input type="hidden" name="m" value="ssr" />
    {{elseif $view == "personnel"}}
      <input type="hidden" name="m" value="soins" />
    {{else}}
      <input type="hidden" name="m" value="hospi" />
    {{/if}}
    {{if $view == "mouvements"}}
      <input type="hidden" name="tab" value="edit_sorties" />
    {{elseif $view == "tableau"}}
      <input type="hidden" name="a" value="vw_affectations" />
    {{elseif $view == "topologique"}}
      <input type="hidden" name="a" value="vw_placement_patients" />
    {{elseif $view == "etat_lits"}}
      <input type="hidden" name="tab" value="vw_recherche" />
    {{elseif $view == "listSorties"}}
      <input type="hidden" name="a" value="httpreq_vw_sorties" />
    {{elseif in_array($view, array("listAdmissions", 'admissions'))}}
      <input type="hidden" name="tab" value="vw_idx_admission" />
    {{elseif $view == "accueilPatient"}}
      <input type="hidden" name="tab" value="vw_accueil_patient" />
      <input type="hidden" name="active_filter_services" value="1" />
    {{elseif in_array($view, array("sortie", 'sorties'))}}
      <input type="hidden" name="tab" value="vw_idx_sortie" />
    {{elseif $view == "listPresents"}}
      <input type="hidden" name="tab" value="vw_idx_present" />
    {{elseif $view == "listAmbuDiv"}}
      <input type="hidden" name="tab" value="vw_tdb" />
    {{elseif $view == "list_cleanups"}}
      <input type="hidden" name="tab" value="vw_nettoyages" />
    {{elseif $view == "list_transports"}}
      <input type="hidden" name="tab" value="vw_tdb" />
    {{elseif $view == "tdb_naissances"}}
      <input type="hidden" name="a" value="ajax_vw_list_naissances" />
    {{elseif in_array($view, array("soins", "tdb"))}}
      <input type="hidden" name="tab" value="viewIndexSejour" />
    {{elseif $view == "feuille_transmissions"}}
      <input type="hidden" name="a" value="vw_feuille_transmissions" />
      <input type="hidden" name="dialog" value="1" />
    {{elseif $view == "ecap"}}
      <input type="hidden" name="tab" value="vwSejours" />
      <input type="hidden" name="select_view" value="1" />
      <input type="hidden" name="praticien_id" />
      <input type="hidden" name="function_id" />
    {{elseif $view == "reeduc"}}
      <input type="hidden" name="tab" value="vw_sejours_reeducation" />
    {{elseif $view == "rhs-no-charge"}}
      <input type="hidden" name="a" value="ajax_sejours_to_rhs_date_monday" />
      <input type="hidden" name="rhs_date_monday" value="{{$element_id}}" />
    {{elseif $view == "personnel"}}
      <input type="hidden" name="tab" value="vw_affectations_soignant" />
    {{else}}
      <input type="hidden" name="a" value="vw_mouvements" />
    {{/if}}
    <input type="hidden" name="services_ids[]" value="" />
    <table class="tbl me-no-box-shadow me-no-align">
      <tr>
        <th colspan="{{$colspan}}">
          <button type="button" style="float: left;" class="tick notext" onclick="toggleChecked()"
                  title="Tout cocher / décocher"></button>
          {{tr}}CService-title-selection{{/tr}}
        </th>
      </tr>
      <tr>
        {{assign var=i value=0}}
        {{foreach from=$secteurs item=_secteur}}
        {{if $i == 6}}
        {{assign var=i value=0}}
      </tr>
      <tr>
        {{/if}}
        <td style="vertical-align: top;">
          <label class="me-line-height-normal">
            <input class="me-small" type="checkbox" name="_secteur_{{$_secteur->_id}}" {{if $_secteur->_all_checked}}checked{{/if}}
                   onclick="$$('.secteur_{{$_secteur->_id}}').each(function(elt) { elt.down('input').checked=this.checked ? 'checked' : ''; }.bind(this));" />
            <strong>{{mb_value object=$_secteur field=nom}}</strong>
          </label>
          {{foreach from=$_secteur->_ref_services item=_service}}
            <p class="secteur_{{$_secteur->_id}} me-line-height-normal">
              <label class="me-line-height-normal">
                <input style="margin-left: 1em;" type="checkbox" name="services_ids[{{$_service->_id}}]" value="{{$_service->_id}}"
                       data-service_id="{{$_service->_id}}"
                       {{if !in_array($_service->_id, array_keys($services_allowed))}}disabled{{/if}} class="service me-small"
                  {{if $services_ids && in_array($_service->_id, $services_ids)}}checked{{/if}}/> {{$_service}}
              </label>
            </p>
          {{/foreach}}
        </td>
        {{math equation=x+1 x=$i assign=i}}
        {{/foreach}}
        <td style="vertical-align: top;" colspan="{{math equation=x-y x=$secteurs|@count y=$i}}">
          <strong>Hors secteur</strong>
          {{foreach from=$all_services item=_service}}
            <p class="me-line-height-normal">
              <label class="me-line-height-normal">
                <input type="checkbox" name="services_ids[{{$_service->_id}}]" value="{{$_service->_id}}" class="service me-small"
                       data-service_id="{{$_service->_id}}"
                       {{if !in_array($_service->_id, array_keys($services_allowed))}}disabled{{/if}}
                  {{if $services_ids && in_array($_service->_id, $services_ids)}}checked{{/if}} /> {{$_service}}
              </label>
            </p>
          {{/foreach}}
          {{if $show_np}}
            <p class="me-line-height-normal">
              <label class="me-line-height-normal">
                <input type="checkbox" name="services_ids[NP]" value="NP" class="service me-small"
                       {{if $services_ids && in_array("NP", $services_ids)}}checked{{/if}} /> Non placés
              </label>
            </p>
          {{/if}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="{{$colspan}}">
          {{if $view == "cut"}}
            <button type="button" class="tick me-primary" onclick="changeServicesScission();">{{tr}}Validate{{/tr}}</button>
          {{else}}
            <button {{if $ajax_request}}type="button"
                    onclick="{{if $callback}}{{$callback}}{{else}}Control.Modal.close(); this.form.onsubmit();{{/if}}"{{/if}}
                    class="tick me-primary">{{tr}}Validate{{/tr}}</button>
            <button type="button" class="save me-secondary" onclick="savePref(form);">
              {{tr}}Validate{{/tr}} {{tr}}and{{/tr}} {{tr}}Save{{/tr}}
            </button>
          {{/if}}
          <button type="button" class="cancel me-tertiary" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>
