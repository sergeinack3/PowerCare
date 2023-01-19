{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="width: 100%;">
  <tr>
    <th style="width: 10%;">{{tr}}CActe-Acte{{/tr}}</th>
    <th>{{tr}}CActeNGAP-motif{{/tr}}</th>
  </tr>
  {{foreach from=$actes item=_acte}}
    <tr>
      <td>
        {{assign var=field value="code"}}
        {{if $_acte->_class|property_exists:"code_acte"}}
          {{assign var=field value="code_acte"}}
        {{/if}}

        {{$_acte->$field}}
      </td>
      <td>
        {{mb_value object=$_acte field=motif}}

        {{mb_value object=$_acte field=motif_unique_cim}}

        <div>
          {{if $_acte->_ref_prescription->_ref_object}}{{$_acte->_ref_prescription->_ref_object->_date|date_format:$conf.date}} - {{/if}}  {{$_acte->_ref_prescription}} :

          <ul>
            {{foreach from=$_acte->_ref_prescription->_ref_prescription_lines_element item=_line_elt name=line}}
              {{mb_include module=prescription template=inc_print_element
                   elt=$_line_elt
                   praticien=$_line_elt->_ref_praticien
                   prescription=$_acte->_ref_prescription
              }}
            {{/foreach}}
          </ul>
        </div>
      </td>
    </tr>
  {{/foreach}}
</table>
