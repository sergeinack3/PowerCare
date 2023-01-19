{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr id="category_line_{{$_category->_id}}">
  {{if $can->admin}}
    <td class="{{$class_important}} button">
      <input type="checkbox" name="select_{{$_category->_id}}" data-id="{{$_category->_id}}" onclick="FilesCategory.checkMergeSelected(this)"/>
    </td>
  {{/if}}
  <td class="button {{$class_important}}">
    <button class="edit notext" onclick="FilesCategory.edit('{{$_category->_id}}', this);">{{tr}}Edit{{/tr}}</button>
  </td>
  <td class="button {{$class_important}}">
    <button type="button" class="print notext me-tertiary" onclick="FilesCategory.printEtiquettes('{{$_category->_id}}')">
      Etiquettes
    </button>
  </td>
  <td class="button {{$class_important}}">
    <button type="button" class="fa fa-share-alt notext me-tertiary"
            {{if $_category->_count_receivers > 0}}style="color: forestgreen !important"{{/if}}
            onclick="FilesCategory.viewRelatedReceivers('{{$_category->_id}}')">
      {{tr}}CInteropReceiver{{/tr}}
    </button>
  </td>
  <td class="text {{$class_important}}">
    {{mb_value object=$_category field=nom}}
  </td>
  <td class="{{if !$_category->class}}empty{{/if}} {{$class_important}}">
    {{tr}}{{$_category->class|default:"All"}}{{/tr}}
  </td>
  <td class="{{if !$_category->group_id}}empty{{/if}} {{$class_important}}">
    {{if $_category->group_id}}
      {{mb_value object=$_category field=group_id}}
    {{else}}
      {{tr}}All{{/tr}}
    {{/if}}
  </td>
  <td class="{{$class_important}} button">
    {{mb_value object=$_category field=send_auto iconography=true}}
  </td>
  <td class="{{$class_important}} button">
    {{mb_value object=$_category field=eligible_file_view iconography=true}}
  </td>
  {{if "dmp"|module_active && 'Ox\Interop\Dmp\CDMP::getAuthentificationType'|static_call:""}}
    {{assign var=trad value="CFile.type_doc_dmp.`$_category->type_doc_dmp`"}}
    <td class="{{$class_important}} button">
      {{mb_include module=system template=inc_vw_bool_icon value=$_category->type_doc_dmp size='lg' ok_title=$trad}}
    </td>
  {{/if}}
  {{if "sisra"|module_active}}
    {{assign var=trad value="CFile.type_doc_sisra.`$_category->type_doc_sisra`"}}
    <td class="{{$class_important}} button">
      {{mb_include module=system template=inc_vw_bool_icon value=$_category->type_doc_sisra size='lg' ok_title=$trad}}
    </td>
  {{/if}}
  <td class="{{$class_important}}">
    {{if $_category->_count.default_cats}}
      {{$_category->_count.default_cats}}
    {{else}}
      &ndash;
    {{/if}}
  </td>
</tr>