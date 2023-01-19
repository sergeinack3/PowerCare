{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{if "appFineClient"|module_active}}
  {{mb_script module=appFineClient script=appFineClient ajax=true}}
{{/if}}

{{mb_default var=display value="icon"}}
{{mb_default var=ondblclick value="popFile('`$file->object_class`', '`$file->object_id`', '`$file->_class`', '`$file->_id`', '0')"}}

{{if $display == "icon"}}
  <table class="layout table_icon_fileview" onmouseover="ObjectTooltip.createEx(this, '{{$file->_guid}}')">
    <tr>
      <td style="text-align: center; height: 120px; vertical-align: middle; position: relative;">
        {{mb_include module=files template="inc_file_synchro" docItem=$file}}

        <div class="icon_fileview" onclick="toggleSelectFile(this);" ondblclick="{{$ondblclick}}" ontouchend="{{$ondblclick}}"
             style="line-height: 90px; position: relative;"
             data-docitem-guid="{{$file->_guid}}"
             data-file-id="{{$file->_id}}"
             data-filename="{{$file->file_name}}">
          {{if in_array($file->_file_type, array("pdf", "image"))}}
            {{thumbnail document=$file profile=medium style="max-width: 64px; max-height: 92px;"}}
          {{elseif $file->file_type == "image/fabricjs"}}
            <span style="font-family: FontAwesome; font-size: 11pt;">
              &#xf1fc;
            </span>
          {{else}}
            <img src="images/pictures/medifile_black.png" style="background: white; max-width: 64px; max-height: 92px;" />
          {{/if}}
        </div>
      </td>
    </tr>
    <tr>
      <td class="text item_name" style="text-align: center; vertical-align: top;">
        {{if $file->file_category_id}}<span class="compact circled">{{$file->_ref_category}}</span>{{/if}} {{$file->_icon_name}}
      </td>
    </tr>
  </table>

  {{mb_return}}
{{/if}}

<tr {{if $file->annule}}class="doc_canceled hatching"{{/if}}>
  <td class="narrow">
    <span style="font-family: FontAwesome; font-size: 11pt;">
      {{if $file->file_type == "image/fabricjs"}}
        &#xf1fc;
      {{elseif $file->_file_type == "pdf"}}
        &#xf1c1;
      {{elseif $file->_file_type == "image"}}
        &#xf1c5;
      {{elseif $file->_file_type == "text"}}
        &#xf0f6;
      {{elseif $file->_file_type == "excel"}}
        &#xf1c3;
      {{elseif $file->_file_type == "word"}}
        &#xf1c2;
      {{else}}
        &#xf016;
      {{/if}}
    </span>
  </td>
  <td class="item_name">
    <span onclick="{{$ondblclick}}"
          onmouseover="ObjectTooltip.createEx(this, '{{$file->_guid}}')"
          data-docitem-guid="{{$file->_guid}}"
          data-file-id="{{$file->_id}}"
          data-filename="{{$file->file_name}}"
          style="cursor: pointer;">
      {{$file}}
    </span>
  </td>
  <td>
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$file->_ref_author}}
  </td>
  <td style="width: 25%">
    {{if $file->file_category_id}}<span class="compact circled">{{$file->_ref_category}}</span>{{/if}}
  </td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$file->_ref_object->_guid}}')">
      {{$file->_ref_object}}
    </span>
  </td>
  <td class="narrow">
    {{mb_value object=$file field=file_date}}
  </td>
</tr>
