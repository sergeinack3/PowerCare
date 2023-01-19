{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCoeff" action="#" method="post" onsubmit="return Coeff.submit(this);">
  {{mb_class object=$coeff}}
  {{mb_key   object=$coeff}}
  {{mb_field object=$coeff field=group_id hidden=true}}
  <input type="hidden" name="del" value="0"/>
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$coeff}}
    <tr>
      <th>{{mb_label object=$coeff field=praticien_id}}</th>
      <td>
        <select name="praticien_id" onchange="choicePrat(this.value);">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat selected=$coeff->praticien_id}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$coeff field=nom}}</th>
      <td>{{mb_field object=$coeff field=nom}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$coeff field=coeff}}</th>
      <td>{{mb_field object=$coeff field=coeff}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$coeff field=description}}</th>
      <td>{{mb_field object=$coeff field=description}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="Coeff.submit(this.form);">
          {{tr}}{{if $coeff->_id}}Save{{else}}Create{{/if}}{{/tr}}
        </button>
        {{if $coeff->_id}}
          <button type="button" class="trash" onclick="Coeff.confirmDeletion(this.form);">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>