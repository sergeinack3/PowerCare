{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editPack" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$pack}}
  {{mb_key   object=$pack}}
  {{mb_field object=$pack field=_locked hidden=true}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$pack}}

    <tr>
      <th>{{mb_label object=$pack field="function_id"}}</th>
      <td>
        <select name="function_id">
          <option value="">&mdash; {{tr}}None|f{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function list=$listFunctions selected=$pack->function_id}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$pack field=libelle}}</th>
      <td>{{mb_field object=$pack field=libelle}}</td>
    </tr>

    {{mb_include module=system template=inc_form_table_footer object=$pack
                 options="{typeName: \$T('CPackExamensLabo'), objName: '`$pack->_view`'}"
                 options_ajax="Control.Modal.close"}}
  </table>
</form>

{{if $pack->_id}}
  <!-- Liste des exmanens du packsélectionné -->
  {{assign var="examens" value=$pack->_ref_examens_labo}}
  {{assign var="examen_id" value=""}}
  {{mb_include module=labo template=list_examens}}
{{/if}}