{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCategory" action="#" method="post" onsubmit="return CategoryFactu.submit(this);">
  {{mb_class object=$category}}
  {{mb_key   object=$category}}
  {{mb_field object=$category field=group_id hidden=true}}
  {{mb_field object=$category field=function_id hidden=true}}
  <input type="hidden" name="del" value="0"/>
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$category}}
    <tr>
      <th>{{mb_label object=$category field=libelle}}</th>
      <td>
        <div class="dropdown">
          {{mb_field object=$category field=libelle}}
        </div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$category field=code}}</th>
      <td>{{mb_field object=$category field=code}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="return CategoryFactu.submit(this.form);">
          {{tr}}{{if $category->_id}}Save{{else}}Create{{/if}}{{/tr}}
        </button>
        {{if $category->_id}}
          <button type="button" class="trash" onclick="CategoryFactu.confirmDeletion(this.form);">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>