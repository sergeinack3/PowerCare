{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
<tr>
  <td class="text">
    <div id="cim-{{$sejour->_id}}">
    {{assign var="sejour" value=$sejour}}
    {{mb_include module=pmsi template=inc_diagnostic}}
    </div>
  </td>
</tr>
<tr>
  <td id="hprim_export_sej{{$sejour->_id}}">
  </td>
</tr>
<tr>
  <th class="category halfPane">{{tr}}CDossierMedical-codes_cim{{/tr}}</th>
  <th class="category halfPane">{{tr}}CAntecedent.more{{/tr}}</th>
</tr>
<tr>
  <td class="text">
    <div id="cim-list-{{$sejour->_id}}">
      {{mb_include module=pmsi template=inc_list_diags}}
    </div>
  </td>
  <td class="text" {{if is_array($patient->_ref_dossier_medical->_ref_traitements)}}{{/if}}>
    {{mb_include module=pmsi template=inc_list_antecedents}}
  </td>
</tr>

<tr>
  <th class="category" colspan="2">{{tr}}CTraitement.more{{/tr}}</th>
</tr>
<tr>
  <td colspan="2">
    {{if is_array($patient->_ref_dossier_medical->_ref_traitements)}}
      {{mb_include module=pmsi template=inc_list_traitements}}
    {{/if}}
  </td>
</tr>
</table>