{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "OxLaboClient"|module_active && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  {{mb_script module=oxLaboClient script=oxlaboalert ajax=true}}
{{/if}}

<script>
  Main.add(function() {
    if (window.updateCountTab) {
      updateCountTab();
    }
  });
</script>

{{mb_default var=show_only      value=0}}
{{mb_default var=show_img       value=0}}
{{mb_default var=show_widget    value=1}}
{{mb_default var=show_hyperlink value=1}}
{{unique_id var=unique_id2}}
{{mb_default var=unique_id value=$unique_id2}}

{{foreach from=$affichageFile item=_cat key=_cat_id}}
  {{assign var=docCount value=$_cat.items|@count}}
  {{if $with_div}}
  <div id="Category-files-{{$object->_class}}-{{$_cat_id}}-{{$unique_id}}" style="display: none; clear: both;">
  {{/if}}
    <table class="form me-no-align me-no-box-shadow">
      {{foreach from=$_cat.items item=_file}}
        {{assign var=object_class value=$object->_class}}
        {{assign var=object_id    value=$object->_id}}

        <tr id="tr_{{$_file->_guid}}">
          <td id="td_{{$_file->_guid}}" class="me-padding-2">
            {{if $show_only}}
              <a href="#" class="action" id="readonly_{{$_file->_guid}}"
                 onclick="File.popup('{{$object_class}}','{{$object_id}}','{{$_file->_class}}','{{$_file->_id}}');"
                 onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}', 'objectView')">{{$_file}}</a><br/>
            {{elseif $show_img}}
              <div style="width: 66px; height: 40px;cursor: pointer;clear: both;"
                   onclick="new Url().ViewFilePopup('{{$_file->object_class}}', '{{$_file->object_id}}', 'CFile', '{{$_file->_id}}')">
                {{thumbnail document=$_file profile=small
                  style="max-width:64px; max-height:39px; border:1px solid black; vertical-align:middle;"}}
              </div>
            {{else}}
              {{mb_include template="inc_widget_line_file"}}
            {{/if}}
          </td>
        </tr>
      {{/foreach}}
    </table>
  {{if $with_div}}
  </div>
  {{/if}}
  {{foreachelse}}
    <table class="form me-margin-0">
      <tr>
        <td class="empty">
          {{tr}}{{$object->_class}}{{/tr}} :
          {{tr}}CFile.none{{/tr}}
        </td>
      </tr>
    </table>
{{/foreach}}

{{if $show_hyperlink}}
  {{mb_include module=sante400 template=inc_widget_list_hypertext_links}}
{{/if}}

