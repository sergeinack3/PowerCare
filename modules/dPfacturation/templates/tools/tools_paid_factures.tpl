{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture   ajax=true}}
{{mb_script module=cabinet     script=reglement ajax=true}}

{{mb_default var=lite value=false}}
{{unique_id var=help_container}}
{{if !$lite}}
{{mb_include module=facturation template=tools/tools_filters submit_fun="showPaidFactures" container="tt_paidf"}}
<table class="tbl">
  <tr>
    <th class="title" colspan="9">{{tr}}CFactureCabinet{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CFactureCabinet{{/tr}}</th>
    <th>{{tr}}CFactureCabinet-ouverture{{/tr}}</th>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th>{{tr}}CFacture-praticien_id{{/tr}}</th>
    <th>{{tr}}CConsultation{{/tr}} / {{tr}}CEvenementPatient{{/tr}}</th>
    <th>{{tr}}CFactureCabinet-du_patient{{/tr}}</th>
    <th>{{tr}}CReglement{{/tr}}</th>
    <th>{{tr}}CUserLog-diff{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  <tbody id="tt_paidf">
{{/if}}
  {{foreach from=$factures item=_facture_elements}}
    {{assign var=_facture value=$_facture_elements.facture}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}')">[{{$_facture}}]</span>
      </td>
      <td>{{mb_value object=$_facture field=ouverture}}</td>
      <td>
        {{mb_include module=system template=inc_vw_mbobject object=$_facture->_ref_patient}}
      </td>
      <td>
        {{mb_include module=system template=inc_vw_mbobject object=$_facture->_ref_praticien}}
      </td>
      <td>
        {{assign var=first_consult value=$_facture->_ref_first_consult}}
        {{if $first_consult->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$first_consult->_guid}}')">
            {{$first_consult}} - {{$first_consult->_date}}
            {{"%02u"|sprintf:$first_consult->_hour}}:{{"%02u"|sprintf:$first_consult->_min}}
          </span>
        {{elseif $_facture->_ref_first_evt->_id}}
          {{mb_include module=system template=inc_vw_mbobject object=$_facture->_ref_first_evt}}
        {{/if}}
      </td>
      <td>{{$_facture_elements.du_patient}}</td>
      <td>{{$_facture_elements.reglement}}</td>
      <td>{{$_facture_elements.delta}}</td>
      <td>
        <button type="button" class="search notext" onclick="Facture.edit('{{$_facture->_id}}', '{{$_facture->_class}}')">
          {{tr}}Open{{/tr}} {{tr}}CFactureCabinet{{/tr}}
        </button>
      </td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="9">
      {{mb_include module=system template=inc_pagination total=$total current=$current step=$nb
      change_page="FactuTools.showPaidFacturesPage.curry(\"tt_paidf\")"}}
    </td>
  </tr>
{{if !$lite}}
  </tbody>
</table>
<div style="display: none; width: 200px;" id="help_{{$help_container}}">
  <table class="form">
    <tr>
      <th class="title">
        {{tr}}Help{{/tr}}
        <button type="button" class="cancel notext" style="float:right" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </th>
    </tr>
    <tr>
      <td>
        <p>{{tr}}Facturation-tools-error-paid-factures-help{{/tr}}</p>
      </td>
    </tr>
  </table>
</div>
{{/if}}