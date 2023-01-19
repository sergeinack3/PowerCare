{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editTypeRessource" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$type_ressource}}
  {{mb_key   object=$type_ressource}}
  <input type="hidden" name="callback" value="TypeRessource.afterEditTypeRessource" />
  <input type="hidden" name="del" value="0" />
  {{mb_field object=$type_ressource field=group_id hidden=true}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$type_ressource}}
    <tr>
      <th>
        {{mb_label object=$type_ressource field=libelle}}
      </th>
      <td>
        {{mb_field object=$type_ressource field=libelle}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$type_ressource field=description}}
      </th>
      <td>
        {{mb_field object=$type_ressource field=description}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $type_ressource->_id}}
          <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash" onclick="confirmDeletion(this.form, {objName: 'type de ressource', ajax: true})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>