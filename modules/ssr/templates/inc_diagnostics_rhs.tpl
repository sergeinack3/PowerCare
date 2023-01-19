{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM}}

{{mb_default var=readonly value=0}}

<script>
  onSubmitDiag = function (oForm) {
    return onSubmitFormAjax(oForm, CotationRHS.refreshRHS.curry('{{$rhs->_id}}', '1'));
  };

  deleteDiag = function (form, field) {
    $V(form.keywords_code, "");
    $V(form.elements[field], "");
    form.onsubmit();
  };

  Main.add(function () {
    {{if !$readonly}}
      CIM.autocomplete(getForm("editFPP-{{$rhs->_id}}").keywords_code, null, {
        limit_favoris: '{{$app->user_prefs.cim10_search_favoris}}',
        chir_id: '{{$rhs->_ref_sejour->praticien_id}}',
        {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
          sejour_type: 'ssr',
          field_type: 'fppec',
        {{/if}}
        afterUpdateElement: function(input) {
          $V(getForm("editFPP-{{$rhs->_id}}").FPP, input.value);
        }
      });

      CIM.autocomplete(getForm("editMMP-{{$rhs->_id}}").keywords_code, null, {
        {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
          sejour_type: 'ssr',
          field_type: 'mmp',
        {{/if}}
        afterUpdateElement: function(input) {
          $V(getForm("editMMP-{{$rhs->_id}}").MMP, input.value);
        }
      });

      CIM.autocomplete(getForm("editAE-{{$rhs->_id}}").keywords_code, null, {
        {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
          sejour_type: 'ssr',
          field_type: 'ae',
        {{/if}}
        afterUpdateElement: function(input) {
          $V(getForm("editAE-{{$rhs->_id}}").AE, input.value);
        }
      });

      CIM.autocomplete(getForm("editDAS-{{$rhs->_id}}").keywords_code, null, {
        {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
          sejour_type: 'ssr',
          field_type: 'das',
        {{/if}}
        afterUpdateElement: function(input) {
          $V(getForm("editDAS-{{$rhs->_id}}")._added_code_das, input.value);
        }
      });

      CIM.autocomplete(getForm("editDAD-{{$rhs->_id}}").keywords_code, null, {
        afterUpdateElement: function(input) {
          $V(getForm("editDAD-{{$rhs->_id}}")._added_code_dad, input.value);
        }
      });
    {{/if}}
  });
</script>

<table class="form me-small me-no-align">
  <tr>
    <th class="category">{{mb_label object=$rhs field="FPP"}}</th>
    <th class="category">{{mb_label object=$rhs field="MMP"}}</th>
    <th class="category">{{mb_label object=$rhs field="AE"}}</th>
  </tr>
  <tr>
    <!-- Finalité principale de prise en charge -->
    <td class="button">
      {{if $readonly}}
        {{mb_value object=$rhs field=FPP}}
      {{else}}
        <form name="editFPP-{{$rhs->_id}}" method="post" onsubmit="return onSubmitDiag(this);">
          {{mb_key object=$rhs}}
          {{mb_class object=$rhs}}
          {{mb_field object=$rhs->_ref_sejour field=praticien_id hidden=true}}
          <input type="hidden" name="FPP" value='' onchange="this.form.onsubmit();" />
          <input type="text" name="keywords_code" value="{{$rhs->FPP}}" class="autocomplete str code cim10" size="10" />
          <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['FPP']), '{{$rhs->_ref_sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, 'ssr', 'fppec'{{/if}});">
            {{tr}}button-CCodeCIM10-choix{{/tr}}
          </button>
          <button type="button" class="cancel notext" onclick="deleteDiag(this.form, 'FPP')">{{tr}}Delete{{/tr}}</button>
        </form>
      {{/if}}
    </td>

    <!-- Manifestation morbide principale -->
    <td class="button">
      {{if $readonly}}
        {{mb_value object=$rhs field=MMP}}
      {{else}}
        <form name="editMMP-{{$rhs->_id}}" action="?" method="post" onsubmit="return onSubmitDiag(this);">
          {{mb_key object=$rhs}}
          {{mb_class object=$rhs}}
          {{mb_field object=$rhs->_ref_sejour field=praticien_id hidden=true}}
          <input type="hidden" name="MMP" value='' onchange="this.form.onsubmit();" />
          <input type="text" name="keywords_code" value="{{$rhs->MMP}}" class="autocomplete str code cim10" size="10" />
          <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['MMP']), '{{$rhs->_ref_sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, 'ssr', 'mmp'{{/if}});">
            {{tr}}button-CCodeCIM10-choix{{/tr}}
          </button>
          <button type="button" class="cancel notext" onclick="deleteDiag(this.form, 'MMP')">{{tr}}Delete{{/tr}}</button>
        </form>
      {{/if}}
    </td>

    <!-- Affection étiologique -->
    <td class="button">
      {{if $readonly}}
        {{mb_value object=$rhs field=AE}}
      {{else}}
        <form name="editAE-{{$rhs->_id}}" action="?" method="post" onsubmit="return onSubmitDiag(this);">
          {{mb_key object=$rhs}}
          {{mb_class object=$rhs}}
          {{mb_field object=$rhs->_ref_sejour field=praticien_id hidden=true}}
          <input type="hidden" name="AE" value='' onchange="this.form.onsubmit();" />
          <input type="text" name="keywords_code" value="{{$rhs->AE}}" class="autocomplete str code cim10" size="10" />
          <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['AE']), '{{$rhs->_ref_sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, 'ssr', 'ae'{{/if}});">
            {{tr}}button-CCodeCIM10-choix{{/tr}}
          </button>
          <button type="button" class="cancel notext" onclick="deleteDiag(this.form, 'AE')">{{tr}}Delete{{/tr}}</button>
        </form>
      {{/if}}
    </td>
  </tr>

  <tr>
    <td class="text button">
      {{if $rhs->_diagnostic_FPP}}
        <strong>{{$rhs->_diagnostic_FPP->libelle}}</strong>
      {{/if}}
    </td>
    <td class="text button">
      {{if $rhs->_diagnostic_MMP}}
        <strong>{{$rhs->_diagnostic_MMP->libelle}}</strong>
      {{/if}}
    </td>
    <td class="text button">
      {{if $rhs->_diagnostic_AE}}
        <strong>{{$rhs->_diagnostic_AE->libelle}}</strong>
      {{/if}}
    </td>
  </tr>

  <tr>
    <th class="category">
      {{if !$readonly}}
        <button type="button" class="search me-primary" style="float:left" onclick="CIM.showAnciensDiags('{{$rhs->_guid}}', CotationRHS.refreshRHS.curry('{{$rhs->_id}}', '1'))">
          {{tr}}CCodeCIM10-old rhs diag|pl{{/tr}}
        </button>
      {{/if}}
    </th>
    <th class="category">{{mb_label object=$rhs field=DAS}}</th>
    <th class="category">{{mb_label object=$rhs field=DAD}}</th>
  </tr>

  {{if !$readonly}}
  <tr>
    <td></td>
    <td class="button">
      <form name="editDAS-{{$rhs->_id}}" action="?" method="post" onsubmit="return onSubmitDiag(this);">
        {{mb_key object=$rhs}}
        {{mb_class object=$rhs}}
        {{mb_field object=$rhs->_ref_sejour field=praticien_id hidden=true}}
        <input type="hidden" name="_added_code_das" onchange="this.form.onsubmit();" />
        <input type="text" name="keywords_code" value="" class="autocomplete str code cim10" size="10" />
        <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_das']), '{{$rhs->_ref_sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, 'ssr', 'das'{{/if}});">
          {{tr}}button-CCodeCIM10-choix{{/tr}}
        </button>
      </form>
    </td>
    <td class="button">
      <form name="editDAD-{{$rhs->_id}}" action="?" method="post" onsubmit="return onSubmitDiag(this);">
        {{mb_key object=$rhs}}
        {{mb_class object=$rhs}}
        {{mb_field object=$rhs->_ref_sejour field=praticien_id hidden=true}}
        <input type="hidden" name="_added_code_dad" onchange="this.form.onsubmit();" />
        <input type="text" name="keywords_code" value="" class="autocomplete str code cim10" size="10" />
        <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_dad']), '{{$rhs->_ref_sejour->praticien_id}}');">
          {{tr}}button-CCodeCIM10-choix{{/tr}}
        </button>
      </form>
    </td>
  </tr>
  {{/if}}
  <tr>
    <td></td>
    <!--  Liste des diagnostics associés significatifs  -->
    <td class="button" style="vertical-align: top">
      <ul class="tags" style="float: none;">
        {{foreach from=$code_das item=_code_das}}
          {{if $_code_das->libelle && $_code_das->code}}
            <li class="tag me-tag" style="white-space:normal;width: 180px;" title="{{$_code_das->libelle}}">
              {{if !$readonly}}
                <form name="delCodeAsso-{{$_code_das->code}}" action="?" method="post" onsubmit="return onSubmitDiag(this);">
                  {{mb_key object=$rhs}}
                  {{mb_class object=$rhs}}
                  <input type="hidden" name="_deleted_code_das" value="{{$_code_das->code}}" />
                  <button class="delete notext" type="submit" style="display: inline-block !important;">{{tr}}Delete{{/tr}}</button>
                </form>
              {{/if}}
              {{$_code_das->code}} - {{$_code_das->libelle|truncate:20:"...":true}}
            </li>
            <br />
          {{/if}}
        {{/foreach}}
      </ul>
    </td>
    <!--  Liste des diagnostics associés à visée documentaire -->
    <td class="button" style="vertical-align: top">
      <ul class="tags" style="float: none;">
        {{foreach from=$code_dad item=_code_dad}}
          {{if $_code_dad->libelle && $_code_dad->code}}
            <li class="tag me-tag" style="white-space:normal;width: 180px;" title="{{$_code_dad->libelle}}">
              {{if !$readonly}}
                <form name="delCodeAsso-{{$_code_dad->code}}" action="?" method="post" onsubmit="return onSubmitDiag(this);">
                  {{mb_key object=$rhs}}
                  {{mb_class object=$rhs}}
                  <input type="hidden" name="_deleted_code_dad" value="{{$_code_dad->code}}" />
                  <button class="delete notext" type="submit" style="display: inline-block !important;">{{tr}}Delete{{/tr}}</button>
                </form>
                {{/if}}
              {{$_code_dad->code}} - {{$_code_dad->libelle|truncate:20:"...":true}}
            </li>
            <br />
          {{/if}}
        {{/foreach}}
      </ul>
    </td>
  </tr>
</table>
