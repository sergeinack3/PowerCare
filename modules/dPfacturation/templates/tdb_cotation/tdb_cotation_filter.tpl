{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients    script=pat_selector ajax=true}}
{{mb_script module=facturation script=facture      ajax=true}}

{{mb_default var=form_name           value="cotation_filter"}}
{{mb_default var=use_factureliaison  value=false}}
{{mb_default var=filter_callback     value=false}}
{{mb_default var=filtre_avance       value=false}}
{{mb_default var=function_limitation value=false}}
{{mb_default var=use_disabled_praticien value=false}}
{{assign var=col_class value="me-w50"}}
{{assign var=colspan value=4}}
{{if $filtre_avance}}
  {{assign var=col_class value="me-w33"}}
  {{assign var=colspan value=6}}
  {{mb_default var=type_date_search value="cloture"}}
  {{mb_default var=statut value="all"}}
  {{mb_default var=facture_classe value="CFactureCabinet"}}
{{/if}}

<script>
  Main.add(
    function() {
      Calendar.regField(getForm('{{$form_name}}')._date_min);
      Calendar.regField(getForm('{{$form_name}}')._date_max);

      PatSelector.init = function(){
        this.sForm = "{{$form_name}}";
        this.sId   = "patient_id";
        this.sView = "_pat_name";
        this.pop();
      }
    }
  );
</script>
<form name="{{$form_name}}" action="" method="get">
  <table class="form">
    <tr>
      {{me_form_field nb_cells=2 label=CPatient class=$col_class}}
        {{mb_field object=$patient field="patient_id" hidden=1}}
        <input type="text" name="_pat_name" style="width: 15em;" value="{{$patient->_view}}" readonly="readonly"
               ondblclick="PatSelector.init()" />
        <button class="cancel notext me-tertiary me-dark" type="button" onclick="$V(this.form._pat_name,''); $V(this.form.patient_id,'')">
          {{tr}}Empty{{/tr}}
        </button>
        <button class="search notext me-tertiary" type="button" onclick="PatSelector.init()">
          {{tr}}Search{{/tr}}
        </button>
        <button class="edit notext me-tertiary" type="button" onclick="Facture.viewPatient();">
          {{tr}}View{{/tr}}
        </button>
      {{/me_form_field}}
      {{me_form_field nb_cells=2 mb_object=$consultation mb_field=_date_min class=$col_class}}
        {{mb_field object=$consultation field="_date_min" form=$form_name canNull="false" register=true}}
      {{/me_form_field}}
      {{if $filtre_avance}}
        {{me_form_field nb_cells=2 label=CFactureCabinet-invoice-number class=$col_class}}
          <input name="num_facture" value="{{$facture->_id}}" type="text" />
        {{/me_form_field}}
      {{/if}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 label="common-Quick search..."}}
        <input type="text" name="_seek_patient" style="width: 13em;"/>
        <script>
          Main.add(function () {
            var form = getForm("{{$form_name}}");
            new Url("facturation", "patient_autocomplete")
              .autoComplete(
                form.elements._seek_patient,
                null,
                {
                  minChars: 3,
                  method: "get",
                  select: "view",
                  dropdown: false,
                  width: "300px",
                  afterUpdateElement: function(field,selected){
                    $V(field.form.patient_id, selected.getAttribute("id").split("-")[2]);
                    $V(field.form.elements._pat_name, selected.down('.view').innerHTML);
                    $V(field.form.elements._seek_patient, "");
                  }
                }
              );
          });
        </script>
      {{/me_form_field}}
      {{me_form_field nb_cells=2 mb_object=$consultation mb_field=_date_max}}
        {{mb_field object=$consultation field="_date_max" form=$form_name canNull="false" register=true}}
      {{/me_form_field}}
      {{if $filtre_avance}}
        {{me_form_field nb_cells=2 mb_object=$facture mb_field=statut_envoi}}
          <select name="xml_etat">
            <option value="" {{if !$facture->statut_envoi}} selected="selected" {{/if}}>-- {{tr}}common-all|f|pl{{/tr}}</option>
            <option value="echec" {{if $facture->statut_envoi === "echec"}} selected="selected" {{/if}}>
              {{tr}}CFactureEtablissement.facture.-1{{/tr}}</option>
            <option value="non_envoye" {{if $facture->statut_envoi === "non_envoye"}} selected="selected" {{/if}}>
              {{tr}}CFactureEtablissement.facture.0{{/tr}}</option>
            <option value="envoye" {{if $facture->statut_envoi === "envoye"}} selected="selected" {{/if}}>
              {{tr}}CFactureEtablissement.facture.1{{/tr}}</option>
            </option>
          </select>
        {{/me_form_field}}
      {{/if}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 label=CMediusers-praticien layout=true field_class="me-padding-0 me-no-border" animated=false
                      style_css="vertical-align: top"}}
        <input type="hidden" name="chirSel" value="{{$praticien_id}}" />
        <select name="activeChirSel" style="width: 15em;" onchange="$V(this.form.chirSel, $V(this));">
          <option value="0" {{if !$praticien_id}}selected="selected"{{/if}}>&mdash; {{tr}}CMediusers-select-professionnel{{/tr}}</option>
          {{if $facture->_class == "CFactureEtablissement"}}
            <option value="-1" {{if $praticien_id == "-1"}} selected="selected" {{/if}}><b>&mdash; {{tr}}All{{/tr}}</b></option>
          {{/if}}
          {{mb_include module=mediusers template=inc_options_mediuser selected=$praticien_id list=$praticiens}}
        </select>
        <select name="allChirSel" style="width: 15em; display: none;" onchange="$V(this.form.chirSel, $V(this));">
          <option value="0" {{if !$praticien_id}} selected="selected" {{/if}}>&mdash; {{tr}}CMediusers-select-professionnel{{/tr}}</option>
          {{if $facture->_class == "CFactureEtablissement"}}
            <option value="-1" {{if $praticien_id == "-1"}} selected="selected" {{/if}}><b>&mdash; {{tr}}All{{/tr}}</b></option>
          {{/if}}
          {{mb_include module=mediusers template=inc_options_mediuser selected=$praticien_id list=$all_praticiens}}
        </select>
        <label>
          <input type="checkbox" name="use_disabled_praticien" onclick="Facture.togglePratSelector(this.form);"
                 {{if $use_disabled_praticien}}checked{{/if}} value="1"/>
          {{tr}}common-User disabled{{/tr}}
        </label>
        {{if $use_disabled_praticien}}
          <script>
            Facture.togglePratSelector(getForm('{{$form_name}}'));
          </script>
        {{/if}}
      {{/me_form_field}}
      {{if $filtre_avance}}
        {{me_form_field nb_cells=2 label=CFactureEtablissement-date-of style_css="vertical-align: top"}}
          <select name="type_date_search">
            <option value="cloture" {{if $type_date_search === "cloture"}} selected="selected" {{/if}}>
              {{tr}}CFactureEtablissement-etat.cloture{{/tr}}
            </option>
            <option value="ouverture" {{if $type_date_search === "ouverture"}} selected="selected" {{/if}}>
              {{tr}}CFactureEtablissement-etat.ouverture{{/tr}}
            </option>
          </select>
        {{/me_form_field}}

        {{me_form_field nb_cells=2 label=CFactureCabinet-statut-invoice style_css="vertical-align: top"}}
          <select name="statut" multiple>
            <option value="all" {{if $statut == "all"}} selected="selected" {{/if}}>-- {{tr}}common-all|f|pl{{/tr}}</option>
            {{if !"dPfacturation $facture_classe use_auto_cloture"|gconf}}
              <option value="cloture" {{if $statut == "cloture"}} selected="selected" {{/if}}>
                {{tr}}CFactureCabinet-facture.cloture|f{{/tr}}
              </option>
              <option value="no-cloture" {{if $statut == "no-cloture"}} selected="selected" {{/if}}>
                {{tr}}CFactureCabinet-facture.no-cloture|f{{/tr}}
              </option>
            {{/if}}
            <option value="extourne" {{if $statut == "extourne"}} selected="selected" {{/if}}>
              {{tr}}CFactureCabinet-facture.extourne|f{{/tr}}
            </option>
            <option value="no-annule" {{if $statut == "no-annule"}} selected="selected" {{/if}}>
              {{tr}}CFactureCabinet-facture.no-annule|f{{/tr}}
            </option>
            <option value="regle" {{if $statut == "regle"}} selected="selected" {{/if}}>
              {{tr}}CFactureCabinet-facture.regle|f{{/tr}}
            </option>
            <option value="rejete" {{if $statut == "rejete"}} selected="selected" {{/if}}>
              {{tr}}CFactureCabinet-facture.rejete|f{{/tr}}
            </option>
            <option value="no-regle" {{if $statut == "no-regle"}} selected="selected" {{/if}}>
              {{tr}}CFactureCabinet-facture.no-regle|f{{/tr}}
            </option>
          </select>
        {{/me_form_field}}
      {{else}}
        <td></td>
        <td></td>
      {{/if}}
    </tr>
    <tr>
      <td class="button" colspan="{{$colspan}}">
        {{if $use_factureliaison}}
          <button class="search" style="float: left" type="button"
                  onclick="FactuTools.FactuLiaisonManager.refreshList(this.form, 'objects_list')">
            {{tr}}Filter{{/tr}} ({{tr}}CConsultation{{/tr}} / {{tr}}CEvenementPatient{{/tr}})
          </button>
          <button class="search me-primary" type="button"
                  onclick="FactuTools.FactuLiaisonManager.refreshList(this.form)">
            {{tr}}Filter{{/tr}} ({{tr}}All{{/tr}})
          </button>
          <button class="search" style="float: right" type="button"
                  onclick="FactuTools.FactuLiaisonManager.refreshList(this.form, 'factures_list')">
            {{tr}}Filter{{/tr}} ({{tr}}CFactureCabinet{{/tr}} / {{tr}}CFactureEtablissement{{/tr}})
          </button>
        {{else}}
          <button class="search me-primary" type="button"
                  onclick="{{if $filter_callback}}{{$filter_callback}}{{else}}Facture.TdbCotation.refreshList(this.form, 0){{/if}}">
            {{tr}}Filter{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
