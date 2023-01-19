{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi"         script="PMSI"}}
{{mb_script module="pmsi"         script="relance"}}
{{mb_script module=cim10          script=CIM}}
{{mb_script module="ccam"         script="CCodageCCAM"}}
{{mb_script module="ccam"         script="code_ccam"}}

{{mb_script module="patients"     script="patient"}}
{{mb_script module="patients"     script="pat_selector"}}

{{mb_script module="hprim21"      script="pat_hprim_selector"}}
{{mb_script module="hprim21"      script="sejour_hprim_selector"}}

{{mb_script module="prescription" script="prescription"}}

{{mb_script module="compteRendu"  script="document"}}
{{mb_script module="compteRendu"  script="modele_selector"}}
{{mb_script module="files"        script="file"}}

<script>
  Main.add(function() {
    var tab = Control.Tabs.create('tabs-pmsi', true);
    if (tab.activeLink) {
      tab.activeLink.up().onmousedown();
    }
    getForm('dossier_pmsi_selector').NDA.focus();
  });
</script>

{{if $sejour->_id && 'dPpmsi display see_recept_dossier'|gconf}}
  <form name="sejour-{{$sejour->_id}}-reception_sortie_pmsi" action="?" method="post" style="display: none">
    {{mb_class object=$sejour}}
    {{mb_key   object=$sejour}}
    <input type="hidden" name="reception_sortie" value=""/>
  </form>
  <form name="sejour-{{$sejour->_id}}-completion_sortie_pmsi" action="?" method="post" style="display: none">
    {{mb_class object=$sejour}}
    {{mb_key   object=$sejour}}
    <input type="hidden" name="completion_sortie" value=""/>
  </form>
{{/if}}

<form name="dossier_pmsi_selector" action="?" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <table class="form">
    <tr>
      <th class="title" colspan="4">{{tr}}PMSI.search dossier pmsi{{/tr}}</th>
    </tr>
    <tr>
      <th class="category halfPane" colspan="2">{{tr}}PMSI.search fields{{/tr}}</th>
      <th class="category halfPane" colspan="2">{{tr}}PMSI.CSejour disponibles{{/tr}}</th>
    </tr>
    <tr>
        {{me_form_field label="CPatient" nb_cells=2}}
          <input type="hidden" name="patient_id" value="{{$patient->patient_id}}"/>
          <input type="text" class="me-margin-right-16" readonly="readonly" name="patient" value="{{$patient->_view}}"
                 onchange="this.form.submit()" onclick="PatSelector.init()" />
          <button class="search notext compact me-tertiary" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
          <script>
            PatSelector.init = function(){
              this.sForm = "dossier_pmsi_selector";
              this.sId   = "patient_id";
              this.sView = "patient";
              this.pop();
            }
          </script>
        {{/me_form_field}}
      <td>
        {{if $sejour->_id}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
          {{$sejour}}
        </span>
        {{else}}
          {{tr}}CSejour.none{{/tr}}
        {{/if}}
        <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
      </td>
      <td class="button" id="{{$sejour->_guid}}-reception_sortie">
        {{if $sejour->_id && 'dPpmsi display see_recept_dossier'|gconf}}
          {{mb_include module=pmsi template=inc_sejour_dossier_completion field='reception_sortie'}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <td colspan="3"></td>
      <td class="button">
        {{if $sejour->_id}}
          {{mb_include module=pmsi template=inc_relance}}
        {{/if}}
      </td>
    </tr>
    <tr>
        {{me_form_field field_class="me-form-icon barcode" label="NDA" title_label="PMSI-action-Choose a file number directly" nb_cells=2}}
          <input type="text" name="NDA" class="barcode me-margin-right-16" value="" />
          <button type="submit" class="search notext compact me-tertiary">{{tr}}Search{{/tr}}</button>
        {{/me_form_field}}
      <td>
        <span onmouseover="ObjectTooltip.createDOM(this, 'list_sejours_pat')">
          {{$patient->_ref_sejours|@count}} séjour(s) disponible(s)
        </span>
        <div id="list_sejours_pat" style="display: none;">
          {{foreach from=$patient->_ref_sejours item=_sejour}}
            <input type="radio" name="_sejour_id" value="{{$_sejour->_id}}" {{if $_sejour->_id == $sejour->_id}}checked="checked"{{/if}}
                   onchange="PMSI.setSejour({{$_sejour->_id}});" />
            <label for="_sejour_id_{{$_sejour->_id}}" class="circled{{if $_sejour->_id == $sejour->_id}} ok{{/if}}"
                  onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
              {{$_sejour}}
            </label>
            <br />
            {{foreachelse}}
            <span>{{tr}}CSejour.none{{/tr}}</span>
          {{/foreach}}
        </div>
      </td>
      <td class="button" id="{{$sejour->_guid}}-completion_sortie">
        {{if $sejour->_id && 'dPpmsi display see_recept_dossier'|gconf}}
          {{mb_include module=pmsi template=inc_sejour_dossier_completion field='completion_sortie'}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th class="title" colspan="4" style="text-align: center">
        {{$sejour}}
        {{if $sejour->RRAC}}
          <span class="texticon-atnc dhe_flag_RRAC" title="{{tr}}CSejour-RRAC-desc{{/tr}}" style="font-size: 0.7em;">
            {{tr}}CSejour-RRAC-court{{/tr}}
          </span>
        {{/if}}
      </th>
    </tr>
  </table>
</form>

{{if $patient->_id}}

<div id="view_dossier_pmsi" class="me-padding-0">

  <ul id="tabs-pmsi" class="control_tabs">
    <li onmousedown="PMSI.loadDossierSejour('{{$patient->_id}}', '{{$sejour->_id}}'); this.onmousedown='';">
      <a href="#tab-dossier-sejour">{{tr}}PMSI.DossierSejour{{/tr}}</a>
    </li>
    <li onmousedown="PMSI.loadActes('{{$sejour->_id}}'); this.onmousedown=''">
      <a href="#tab-actes">{{tr}}CCodable-actes{{/tr}} ({{$nbActes}}) et {{tr}}PMSI.Diagnostics{{/tr}}({{$nbDiag}})</a>
    </li>
    <li onmousedown="PMSI.loadDocuments('{{$sejour->_id}}'); this.onmousedown=''">
      <a href="#tab-documents">{{tr}}PMSI.Documents{{/tr}} ({{$sejour->_nb_files_docs}})</a>
    </li>
    {{if "dmi"|module_active}}
      <li onmousedown="PMSI.loadDMI('{{$sejour->_id}}'); this.onmousedown=''">
        <a href="#tab-dmi">{{tr}}CDM{{/tr}}</a>
      </li>
    {{/if}}
    {{if "search"|module_active}}
      <li onmousedown="PMSI.loadSearch('{{$sejour->_id}}'); this.onmousedown=''">
        <a href="#tab-search">{{tr}}Search{{/tr}}</a>
      </li>
    {{/if}}
    {{if $sejour->_ref_prescription_sejour && $sejour->_ref_prescription_sejour->_id}}
      <li style="float: right">
        <button type="button" class="print" onclick="Prescription.printOrdonnance('{{$sejour->_ref_prescription_sejour->_id}}');">{{tr}}PMSI.Prescription{{/tr}}</button>
      </li>
    {{/if}}
    {{if "atih"|module_active && "atih CGroupage use_fg"|gconf}}
      <li onmousedown="PMSI.loadRSS('{{$sejour->_id}}');">
        <a href="#tab-rss">{{tr}}PMSI.RSS{{/tr}}</a>
      </li>
    {{/if}}
    <li class="me-float-right" style="float: right">
      <button type="button" class="print" onclick="PMSI.printDossierComplet('{{$sejour->_id}}');">{{tr}}PMSI.Complete Dossier{{/tr}}</button>
    </li>
  </ul>

  <div id="tab-dossier-sejour" style="display:none;"></div>
  <div id="tab-actes" style="display: none;"></div>
  <div id="tab-documents" style="display: none;"></div>
  {{if "dmi"|module_active}}
    <div id="tab-dmi" style="display: none;"></div>
  {{/if}}

  {{if "search"|module_active}}
    <div id="tab-search" style="display: none;"></div>
  {{/if}}

  {{if "atih"|module_active}}
    <div id="tab-rss" style="display: none;"></div>
  {{/if}}
</div>

{{/if}}
