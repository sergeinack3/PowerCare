{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=csarr register=true}}
{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}

{{if !$rhs->_in_bounds}}
  <div class="small-warning">{{tr}}CRHS-no_days{{/tr}}</div>
{{/if}}

<table class="main">
  <tr>
    <td rowspan="3">
      {{if $rhs->facture == 1}}
        {{mb_include module=ssr template="inc_dependances_rhs_charged"}}
      {{else}}
        {{mb_include module=ssr template="inc_dependances_rhs"}}
      {{/if}}
    </td>
    <td class="greedyPane" id="totaux-{{$rhs->_id}}">
      {{mb_include module=ssr template="inc_totaux_rhs"}}
    </td>
  </tr>

  <tr>
    <td class="greedyPane" id="diagnostics-{{$rhs->_id}}">
      {{mb_include module=ssr template="inc_diagnostics_sejour" sejour=$rhs->_ref_sejour}}
    </td>
  </tr>

  {{if $use_acte_presta == 'csarr'}}
    <tr>
      <td>
        {{if $rhs->facture == 1}}
          <div class="small-warning">{{tr}}CRHS.charged{{/tr}}</div>
        {{else}}
        <script>
          Main.add( function(){
            var form = getForm("new-line-{{$rhs->_guid}}");
            CotationRHS.autocompleteCsARR(form);
            CotationRHS.autocompleteExecutant(form);
            Modulators = new TokenField(getForm('new-line-{{$rhs->_guid}}').modulateurs, {separator : "-"});
          } );
        </script>

        <form name="new-line-{{$rhs->_guid}}" action="?m={{$m}}" method="post" onsubmit="return CotationRHS.onSubmitLine(this);">
          <input type="hidden" name="modulateurs" value="" />

          {{mb_class class=CLigneActivitesRHS}}
          <input type="hidden" name="rhs_id" value="{{$rhs->_id}}" />

          <table class="form me-small-form">
            <tr>
              <th class="title" colspan="2">{{tr}}CLigneActivitesRHS.none.add{{/tr}}</th>
            </tr>
            <tr>
              <th>{{mb_label object=$rhs_line field=code_activite_csarr}}</th>
              <td>
                {{mb_field object=$rhs_line field=code_activite_csarr class="autocomplete" onchange="CotationRHS.showModulators(this.value, '`$rhs->_id`');"}}
                <button type="button" class="search notext" onclick="CsARR.viewSearch($V.curry(this.form.code_activite_csarr), $V(this.form.executant_id));">
                   {{tr}}CActiviteCsARR-action-search{{/tr}}
                 </button>
                <div style="display: none;" class="autocomplete activite" id="{{$rhs->_guid}}_csarr_auto_complete"></div>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$rhs_line field=executant_id}}</th>
              <td>
                {{mb_field object=$rhs_line field=executant_id hidden=true}}
                {{mb_field object=$rhs_line field=code_intervenant_cdarr hidden=true}}
                {{mb_field object=$rhs_line field=_executant class="autocomplete"}}
                <div style="display:none;" class="autocomplete executant" id="{{$rhs->_guid}}_executant_auto_complete"></div>
              </td>
            </tr>
            <tr>
              <th><label for="nb_patient_seance" id="labelFor_new-line-{{$rhs->_guid}}_nb_patient_seance"
                         title="{{tr}}CLigneActivitesRHS-nb_patient_seance{{/tr}}">{{tr}}CLigneActivitesRHS-nb_patient_seance{{/tr}}</label>
              </th>
              <td><input type="text" name="nb_patient_seance" value="{{$rhs_line->nb_patient_seance}}" onchange="CotationRHS.checkNbPatient(this.form)"
                         size="3"/>
              </td>
            </tr>
            <tr>
              <th><label for="nb_intervenant_seance" id="labelFor_new-line-{{$rhs->_guid}}_nb_intervenant_seance"
                         title="{{tr}}CLigneActivitesRHS-nb_intervenant_seance{{/tr}}">{{tr}}CLigneActivitesRHS-nb_intervenant_seance{{/tr}}</label>
              </th>
              <td><input type="text" name="nb_intervenant_seance" value="{{$rhs_line->nb_intervenant_seance}}" onchange="CotationRHS.checkNbIntervenant(this.form)"
                         size="3"/>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$rhs_line field=modulateurs}}</th>
              <td id="modulators_{{$rhs->_id}}"></td>
            </tr>
            <tr>
              <th>{{mb_label object=$rhs_line field=extension}}</th>
              <td>
                <select name="extension" style="width: 150px;">
                  <option value="">&dash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$extensions_doc item=_extension}}
                    <option value="{{$_extension->code}}">{{$_extension->_view}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <td class="button" colspan="2">
                <button class="new" type="submit">
                  {{tr}}CLigneActivitesRHS-title-create{{/tr}}
                </button>
              </td>
            </tr>
          </table>
        </form>
      </td>
      {{/if}}
    </tr>
  {{/if}}
</table>

{{if $use_acte_presta == 'csarr'}}
  {{mb_include module=ssr template="inc_lines_rhs"}}
{{elseif $use_acte_presta == 'presta'}}
  {{mb_include module=ssr template="inc_lines_rhs_acte_presta"}}
{{/if}}
