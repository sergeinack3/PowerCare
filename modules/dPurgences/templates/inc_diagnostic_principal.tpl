{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=diagCanNull value=false}}
{{mb_default var=size_th     value=""}}
{{mb_default var=form        value="editSejour"}}
{{mb_default var=with_form   value=false}}
{{mb_script module=cim10 script=CIM}}

<th {{if $size_th}}style="width: {{$size_th}}" class="text"{{/if}}>{{mb_label object=$sejour field="DP"}}</th>
<td>
  <script>
    reloadDiagnostic = function(sejour_id) {
      if ($("dp_"+sejour_id)) {
        var url = new Url("urgences", "ajax_diagnostic_principal");
        url.addParam("sejour_id", sejour_id);
        url.addParam("form", "{{$form}}");
        url.addParam("with_form", "{{$with_form}}");
        url.addParam("size_th", "{{$size_th}}");
        url.requestUpdate("dp_"+sejour_id, function(){
          /* FIXME: VIRER CE CODE */
          var formName = "{{$form}}";
          var form = getForm(formName);
          var label = form.down("label[for=DP]");
          label.className = form.DP.className;
          label.id = "labelFor_"+formName+"_DP";
          label.htmlFor = formName+"_DP";

          var oElement = form.DP;
          oElement.id = formName+"_DP";
          oElement.observe("change", notNullOK)
                  .observe("ui:change", notNullOK);
          oElement.fire("ui:change");

          // Ne donne pas la main sur les select des autres formulaires si on ne fait pas ça
          if (Prototype.Browser.IE) {
            form.keywords_code.select();
            form.keywords_code.blur();
          }
        });
      }
      if ($("cim")) {
        var url = new Url("salleOp", "httpreq_diagnostic_principal");
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate("cim");
      }
    };

    deleteCodeCim10 = function() {
      var oForm = getForm("{{$form}}");
      $V(oForm.keywords_code, '');
      $V(oForm.DP, '');
      submitSejour('{{$sejour->_id}}');
    };
    Main.add(function() {
      CIM.autocomplete(getForm("{{$form}}").keywords_code, null, {
        limit_favoris: '{{$app->user_prefs.cim10_search_favoris}}',
        chir_id: '{{$sejour->praticien_id}}',
        {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
          sejour_type: '{{$sejour->type}}',
          field_type: 'dp',
        {{/if}}
        afterUpdateElement: function(input) {
          $V(getForm("{{$form}}").DP, input.value);
        }
      });
    });
  </script>

  {{if $with_form}}
  <form name="{{$form}}" action="?" method="post"  onsubmit="return submitSejour()">
    <input type="hidden" name="m" value="planningOp" />
    <input type="hidden" name="dosql" value="do_sejour_aed" />
    <input type="hidden" name="del" value="0" />
    {{mb_key object=$sejour}}
    <input type="hidden" name="type" value="{{$sejour->type}}" />
  {{/if}}

  <input type="hidden" name="praticien_id" value="{{$sejour->praticien_id}}"/>

  {{assign var=notnull value=""}}
  {{if "dPurgences Display check_dp"|gconf == "2"}}
    {{if $diagCanNull}}
      {{assign var=notnull value="canNull"}} {{* canNull pour eviter d'avoir l'alert "notNull" sans arret *}}
    {{else}}
      {{assign var=notnull value="notNull"}}
    {{/if}}
  {{/if}}

  <input type="text" name="keywords_code" id="{{$form}}_keywords_code" class="autocomplete str" value="{{$sejour->DP}}" size="10"/>
  <input type="hidden" name="DP" class="{{$notnull}}" value="{{$sejour->DP}}" onchange="$V(this.form.keywords_code, this.value); submitSejour('{{$sejour->_id}}');"/>
  <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), '{{$sejour->praticien_id}}'{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dp'{{/if}});">
    {{tr}}button-CCodeCIM10-choix{{/tr}}
  </button>
  <button type="button" class="cancel notext" onclick="deleteCodeCim10();">
    {{tr}}Delete{{/tr}}
  </button>

  {{if $with_form}}
  </form>
  {{/if}}
</td>

