{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM ajax=true}}

<script>
  onSubmitDiag = function(oForm) {
    return onSubmitFormAjax(oForm, reloadDiagnostic.curry('{{$sejour->_id}}', '{{$consult->_id}}'));
  };

  deleteDiag = function(form, field) {
    $V(form.keywords_code, "");
    $V(form.elements[field], "");
    form.onsubmit();
  };

  Main.add(function() {
    CIM.autocomplete(getForm("editDP").keywords_code, null, {
      limit_favoris: '{{$app->user_prefs.cim10_search_favoris}}',
      chir_id: '{{$sejour->praticien_id}}',
      {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
        sejour_type: '{{$sejour->type}}',
        field_type: 'dp',
      {{/if}}
      afterUpdateElement: function(input) {
        $V(getForm("editDP").DP, input.value);
      }
    });

    CIM.autocomplete(getForm("editDR").keywords_code, null, {
      {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
        sejour_type: '{{$sejour->type}}',
        field_type: 'dr',
      {{/if}}
      afterUpdateElement: function(input) {
        $V(getForm("editDR").DR, input.value);
      }
    });

    CIM.autocomplete(getForm("editDA").keywords_code, null, {
      {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
        sejour_type: '{{$sejour->type}}',
        field_type: 'da',
      {{/if}}
      afterUpdateElement: function(input) {
        $V(getForm("editDA")._added_code_cim, input.value);
      }
    });

    {{if $consult->_id}}
      if (window.tabsConsult || window.tabsConsultAnesth) {
        var count_items = 0;
        {{if $sejour->DP}}
        count_items++;
        {{/if}}
        {{if $sejour->DR}}
        count_items++;
        {{/if}}
        {{if $sejour->_diagnostics_associes}}
        count_items += {{$sejour->_diagnostics_associes|@count}};
        {{/if}}
        Control.Tabs.setTabCount("cim", count_items);
      }
    {{/if}}
  });
</script>

<table class="form me-no-align me-no-box-shadow">
  <tr>
    <th class="category" style="width: 50%">
      <button type="button" class="search" style="float:left" onclick="CIM.showAnciensDiags('{{$sejour->_guid}}',
              reloadDiagnostic.curry('{{$sejour->_id}}','{{$consult->_id}}'));">
        {{tr}}CCodeCIM10-old sejour diag|pl{{/tr}}
      </button>
      {{mb_label object=$sejour field="DP"}}
    </th>
    <th class="category" style="width: 50%">{{mb_label object=$sejour field="DR"}}</th>
  </tr>
  <tr>
    <!-- Diagnostic Principal -->
    <td class="button me-text-align-center">
      <form name="editDP" method="post" onsubmit="return onSubmitDiag(this);">
        {{mb_class object=$sejour}}
        {{mb_key object=$sejour}}
        <input type="hidden" name="praticien_id" value="{{$sejour->praticien_id}}" />
        <input type="hidden" name="DP" value='' onchange="this.form.onsubmit();"/>
        <input type="text"   name="keywords_code" value="{{$sejour->DP}}" class="autocomplete str code cim10" size="10" />
        <button type="button" class="search notext me-tertiary" onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dp'{{/if}});">
          {{tr}}button-CCodeCIM10-choix{{/tr}}
        </button>
        <button type="button" class="cancel notext me-tertiary me-dark" onclick="deleteDiag(this.form, 'DP')">{{tr}}Delete{{/tr}}</button>
      </form>
    </td>

    <!-- Diagnostic Relié -->
    <td class="button me-text-align-center">
      <form name="editDR" action="?" method="post" onsubmit="return onSubmitDiag(this);">
        {{mb_class object=$sejour}}
        {{mb_key object=$sejour}}
        <input type="hidden" name="praticien_id" value="{{$sejour->praticien_id}}" />
        <input type="hidden" name="DR" value='' onchange="this.form.onsubmit();"/>
        <input type="text"   name="keywords_code" value="{{$sejour->DR}}" class="autocomplete str code cim10" size="10" />
        <button type="button" class="search notext me-tertiary" onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dr'{{/if}});">
        {{tr}}button-CCodeCIM10-choix{{/tr}}
      </button>
      <button type="button" class="cancel notext me-tertiary me-dark" onclick="deleteDiag(this.form, 'DR')">{{tr}}Delete{{/tr}}</button>
      </form>
    </td>
  </tr>

  <tr>
    <td class="text button">
      {{if $sejour->_ext_diagnostic_principal}}
      <strong>{{$sejour->_ext_diagnostic_principal->libelle}}</strong>
        {{if $codes_dp|@count && in_array($sejour->_ext_diagnostic_principal->code, $codes_dp)}}
          <div class="small-warning" style="margin-left: 10px; display: inline-block;">{{tr}}CCIM10-Prohibited diagnosis{{/tr}}</div>
        {{/if}}
      {{/if}}
    </td>
    <td class="text button">
      {{if $sejour->_ext_diagnostic_relie}}
      <strong>{{$sejour->_ext_diagnostic_relie->libelle}}</strong>
      {{/if}}
    </td>
  </tr>
  <tr>
    <th class="category" colspan="2">
      Diagnostics associés ({{$sejour->_ref_dossier_medical->_ext_codes_cim|@count}})
    </th>
  </tr>
  <tr>
    <td class="button me-text-align-center" colspan="2">
      <form name="editDA" method="post" onsubmit="return onSubmitDiag(this);">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_class" value="CSejour" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
        <input type="hidden" name="_added_code_cim" onchange="this.form.onsubmit();"/>
        <input type="text"   name="keywords_code" size="5" class="autocomplete str code cim10" />
        <button class="search notext me-secondary" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_cim']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'da'{{/if}});">
          Chercher un diagnostic
        </button>
      </form>
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      {{foreach from=$sejour->_ref_dossier_medical->_ext_codes_cim item="curr_cim"}}
      <form name="delCodeAsso-{{$curr_cim->code}}" method="post" onsubmit="return onSubmitDiag(this);">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_class" value="CSejour" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="_deleted_code_cim" value="{{$curr_cim->code}}" />
        <button class="trash notext me-tertiary" type="button" onclick="this.form.onsubmit()">
          {{tr}}Delete{{/tr}}
        </button>
      </form>
      {{$curr_cim->code}} : {{$curr_cim->libelle}}
      <br />
      {{/foreach}}
    </td>
  </tr>
</table>
