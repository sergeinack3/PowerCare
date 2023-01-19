{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=js_function         value="showElements"}}
{{mb_default var=element_to_factures value=false}}
{{foreach from=$elements item=_element}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_element->_guid}}');">
        {{$_element->_date}} : {{$_element}}
      </span>
    </td>
    <td>
      {{mb_include module=system template=inc_vw_mbobject object=$_element->_ref_patient}}
    </td>
    <td class="button">
      <button type="button" class="search notext"
              onclick="FactuTools.openElement('{{$_element->_class}}', '{{$_element->_id}}', '{{$_element->_ref_patient->_id}}')">
        {{tr}}Edit{{/tr}} : {{tr}}{{$element_class}}{{/tr}}
      </button>
    </td>
  </tr>
  {{if $element_to_factures}}
    {{assign var=element_id value=$_element->_id}}
    {{assign var=factures value=$element_to_factures.$element_id}}
    {{math equation="x - y" x=$factures|@count y=1 assign=last}}
    {{foreach from=$factures item=_facture name=facture_loop}}
      {{assign var=borderStyle value=""}}
      {{if $smarty.foreach.facture_loop.index === $last}}
        {{assign var=borderStyle value="border-bottom: 2px solid black;"}}
      {{/if}}
      <tr>
        <td style="{{$borderStyle}}"></td>
        <td style="{{$borderStyle}}">
          <i class="fas fa-arrow-circle-right"></i>
          <div style="float:right">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}')">[{{$_facture}}]</span>
            {{tr}}date.from{{/tr}} {{mb_value object=$_facture field=ouverture}}
          </div>
        </td>
        <td style="{{$borderStyle}}">
          <button type="button" class="search notext" onclick="Facture.edit('{{$_facture->_id}}', '{{$_facture->_class}}')">
            {{tr}}Edit{{/tr}} : {{tr}}CFactureCabinet{{/tr}}
          </button>
          <button type="button" class="unlink notext"
                  onclick="FactuTools.unlinkFactureCabinet('{{$_element->_class}}', '{{$_element->_id}}', '{{$_facture->_id}}', this)">
            {{tr}}Unlink{{/tr}}
          </button>
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
{{/foreach}}
<tr>
  <td colspan="3">
    {{mb_include module=system template=inc_pagination total=$total current=$current step=$nb
    change_page="FactuTools.`$js_function`Page.curry(\"`$element_class`\").bind(FactuTools)"}}
  </td>
</tr>
