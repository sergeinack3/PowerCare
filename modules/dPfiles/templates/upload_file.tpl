{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file_category register=true}}

{{assign var=patient_related value=false}}

{{if $object|instanceof:'Ox\Mediboard\Patients\CPatient' || $object|instanceof:'Ox\Mediboard\Patients\IPatientRelated'}}
    {{assign var=patient_related value=true}}
{{/if}}

<form name="uploadFrm me-align-auto" action="?" enctype="multipart/form-data" method="post" onsubmit="return FilesCategory.onSubmit(this)">
  <input type="hidden" name="m" value="files" />
  <input type="hidden" name="a" value="upload_file" />
  <input type="hidden" name="dosql" value="do_file_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="ajax" value="1" />
  <input type="hidden" name="suppressHeaders" value="1" />
  <input type="hidden" name="callback" value="reloadCallback" />
  <input type="hidden" name="object_class" value="{{$object->_class}}" />
  <input type="hidden" name="object_id" value="{{$object->_id}}" />
  <input type="hidden" name="named" value="{{$named}}" />
  <input type="hidden" name="_merge_files" value="0" />
  <input type="hidden" name="_category_id" value="{{$file_category_id}}" />

  {{mb_field object=$file field=_ext_cabinet_id hidden=true}}

  {{if $named}}
  <input type="hidden" name="_rename" value="{{$_rename}}" />
  {{/if}}

  <table class="form">
    <tr>
      <th class="title" colspan="6">
        {{if $named}}
          {{tr var1=$_rename}}File.add %s for{{/tr}}
        {{else}}
          {{tr}}File.add_for{{/tr}}
        {{/if}}
        <br/>'{{$object->_view}}'
      </th>
    </tr>

    <tr>
      <td class="button" colspan="6">
        <div class="small-info">
          <div>{{tr}}config-dPfiles-General-upload_max_filesize{{/tr}} : <strong>{{"dPfiles General upload_max_filesize"|gconf}}</strong></div>
          <div>{{tr}}config-dPfiles-General-extensions{{/tr}} : <strong>{{"dPfiles General extensions"|gconf}}</strong></div>
        </div>
      </td>
    </tr>

    {{if !$named}}
      <tr>
        <th style="width: 120px; {{if $file_category_id}} display: none;{{/if}}">
          {{mb_label object=$file field="file_category_id" typeEnum=checkbox}}
        </th>
        <td {{if $file_category_id}} style="display: none;"{{/if}}>
          <select name="_file_category_id" style="width: 15em;" onchange="FilesCategory.checkTypeDocDmpSisra(this.form, this.value);">
            <option value="" {{if !$file->file_category_id}}selected{{/if}}>&mdash; {{tr}}None|f{{/tr}}</option>
            {{foreach from=$listCategory item=curr_cat}}
            <option value="{{$curr_cat->file_category_id}}" {{if $curr_cat->file_category_id == $file->file_category_id}}selected{{/if}}>
              {{$curr_cat->nom}}
            </option>
            {{/foreach}}
          </select>
        </td>
        <th>
          <label title="{{tr}}CFile-_rename-desc{{/tr}}">{{tr}}CFile-_rename{{/tr}}</label>
        </th>
        <td>
          <input type="text" name="_rename" value="{{$_rename}}"/>
        </td>
        <th>
          {{mb_label object=$file field="language"}}
        </th>
        <td>
          {{mb_field object=$file field="language"}}
        </td>
      </tr>
      {{if $patient_related && ("dmp"|module_active || "sisra"|module_active)}}
        <tr>
          {{if "dmp"|module_active}}
            <th>
              {{mb_label object=$file field="type_doc_dmp"}}
            </th>
            <td>
              {{mb_field object=$file field="type_doc_dmp" emptyLabel="Choose" style="width: 15em;"}}
            </td>
          {{/if}}

          {{if "sisra"|module_active}}
            <th>
              {{mb_label object=$file field="type_doc_sisra"}}
            </th>
            <td>
              {{mb_field object=$file field="type_doc_sisra" emptyLabel="Choose" style="width: 15em;"}}
            </td>
          {{/if}}

          <td colspan="{{if "dmp"|module_active && "sisra"|module_active}}2{{else}}4{{/if}}"
        </tr>
      {{/if}}
      <tr>
        {{if $patient_related}}
          <th>
            {{mb_label object=$file field="masquage_patient" typeEnum=checkbox}}
          </th>
          <td>
            {{mb_field object=$file field="masquage_patient" typeEnum=checkbox}}
          </td>
          <th>
            {{mb_label object=$file field="send" typeEnum=checkbox}}
          </th>
          <td>
            {{mb_field object=$file field="send" typeEnum=checkbox}}
          </td>
        {{/if}}
        <th>
          {{mb_label object=$file field="private" typeEnum=checkbox}}
        </th>
        <td>
          {{mb_field object=$file field="private" typeEnum=checkbox}}
        </td>
      </tr>
      {{if $patient_related}}
        <tr>
          <th>
              {{mb_label object=$file field="masquage_praticien" typeEnum=checkbox}}
          </th>
          <td colspan="5">
              {{mb_field object=$file field="masquage_praticien" typeEnum=checkbox}}
          </td>
        </tr>
        <tr>
          <th>
              {{mb_label object=$file field="masquage_representants_legaux" typeEnum=checkbox}}
          </th>
          <td colspan="5">
              {{mb_field object=$file field="masquage_representants_legaux" typeEnum=checkbox}}
          </td>
        </tr>
      {{/if}}
    {{/if}}

    <tr>
      <th colspan="6" class="category">{{tr}}CFile{{/tr}}</th>
    </tr>
  </table>
  
  <div style="max-height: 300px; overflow: auto; width: 100%; min-height: 50px;">
    {{mb_include module=system template=inc_inline_upload}}
  </div>
  
  <div style="text-align: center;">
    <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
    
    {{if "dPfiles CFile merge_to_pdf"|gconf}}
      <button class="hslip" id="add_and_merge" disabled onclick="$V(this.form._merge_files, 1);">
        {{tr}}CFile-_add_and_merge{{/tr}}
      </button>
    {{/if}}
  </div>
</form>
