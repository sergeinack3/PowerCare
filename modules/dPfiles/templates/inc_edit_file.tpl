{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file_category register=true}}

<form name="editFileCategorie" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$file}}
  {{mb_key   object=$file}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$file field=file_category_id}}</th>
      <td>
        <select name="file_category_id" onchange="FilesCategory.checkTypeDocDmpSisra(this.form, this.value);">
          <option value="">&mdash; {{tr}}CFilesCategory.none{{/tr}}</option>
          {{foreach from=$files_categories item=_file_categorie}}
            <option value="{{$_file_categorie->_id}}"
                    {{if $file->file_category_id == $_file_categorie->_id}}selected{{/if}}>
              {{$_file_categorie->nom}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    {{if "dmp"|module_active || "sisra"|module_active}}
      <tr>
        <th>
          {{mb_label object=$file field="type_doc_dmp"}}
        </th>
        <td>
          {{mb_field object=$file field="type_doc_dmp" emptyLabel="Choose" style="width: 15em;"}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td style="text-align: center" colspan="2">
        <button class="submit" type="submit"">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
