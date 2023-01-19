{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editDP" action="?m={{$m}}" method="post" 
      onsubmit="return onSubmitFormAjax(this, reloadDiagnostic.curry({{$sejour->_id}}))">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
  
  <div style="text-align: right;">
    <script type="text/javascript">
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
    });
    
    deleteDP = function() {
      var oForm = getForm("editDP");
      $V(oForm.keywords_code, "");
      $V(oForm.DP, "");
      oForm.onsubmit();
    }
    </script>
    
    {{mb_label object=$sejour field=DP}}
    <input type="text" name="keywords_code" class="autocomplete str  code cim10" value="{{$sejour->DP}}" size="10"/>
    <input type="hidden" name="DP" onchange="this.form.onsubmit();"/>
    <button class="search notext" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dp'{{/if}});">
      {{tr}}Search{{/tr}}
    </button>
    <button type="button" class="cancel notext" onclick="deleteDP();"></button>
  </div>
</form>

{{if $sejour->_ext_diagnostic_principal}}
  <strong>{{$sejour->_ext_diagnostic_principal->libelle}}</strong>
{{/if}}
<hr />

<!--  Diagnostic Relié -->
<form name="editDR" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, { onComplete: reloadDiagnostic.curry({{$sejour->_id}}) })">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
  
  <div style="text-align: right;">
    {{mb_label object=$sejour field=DR}}
    <script>
      Main.add(function() {
        CIM.autocomplete(getForm("editDR").keywords_code, null, {
          {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
            sejour_type: '{{$sejour->type}}',
            field_type: 'dr',
          {{/if}}
          afterUpdateElement: function(input) {
            $V(getForm("editDR").DR, input.value);
          }
        });
        deleteDR = function () {
          var oForm = getForm("editDR");
          $V(oForm.keywords_code, "");
          $V(oForm.DR, "");
          oForm.onsubmit();
        }
      });
    </script>
    <input type="text" name="keywords_code" class="autocomplete str code cim10" value="{{$sejour->DR}}" size="10"/>
    <input type="hidden" name="DR" onchange="this.form.onsubmit();"/>
    <button class="search notext" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dr'{{/if}});">
      {{tr}}Search{{/tr}}
    </button>
    <button type="button" class="cancel notext" onclick="deleteDR();"></button>
  </div>
</form>

{{if $sejour->_ext_diagnostic_relie}}
  <strong>{{$sejour->_ext_diagnostic_relie->libelle}}</strong>
{{/if}}
<hr />

<form name="editDA" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, reloadDiagnostic.curry({{$sejour->_id}}))">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="object_class" value="CSejour" />
  <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
  
  <div style="text-align: right;">
    <label for="_added_code_cim" title="Diagnostics associés significatifs">DAS</label>
    <script>
      Main.add(function() {
        CIM.autocomplete(getForm("editDA").keywords_code, null, {
          {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
            sejour_type: '{{$sejour->type}}',
            field_type: 'da',
          {{/if}}
          afterUpdateElement: function(input) {
            $V(getForm("editDA")._added_code_cim, input.value);
          }
        });
      });
    </script>
    <input type="text" name="keywords_code" class="autocomplete str" value="" size="10"/>
    <input type="hidden" name="_added_code_cim" onchange="this.form.onsubmit();"/>
    <button class="search notext" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_cim']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'da'{{/if}});">
      {{tr}}Search{{/tr}}
    </button>
  </div>
</form>

{{foreach from=$sejour->_ref_dossier_medical->_ext_codes_cim item="curr_cim"}}
<form name="delCodeAsso-{{$curr_cim->code}}" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, reloadDiagnostic.curry({{$sejour->_id}}))">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="object_class" value="CSejour" />
  <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_deleted_code_cim" value="{{$curr_cim->code}}" />
  <button class="trash notext" type="submit">
    {{tr}}Delete{{/tr}}
  </button>
</form>
  {{$curr_cim->code}} : {{$curr_cim->libelle}}
  <br />
{{/foreach}}