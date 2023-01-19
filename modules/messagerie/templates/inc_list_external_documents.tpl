{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePage = function(page_nb) {
    listDocuments('{{$account_id}}', '{{$mode}}', page_nb);
  };

  changeMode = function(mode) {
    listDocuments('{{$account_id}}', mode, 0);
  };

  Main.add(function() {
    $('li_tabs_{{$doc->_ref_account->_guid}}').down('small').update("({{$nb_unlinked}})");
  });
</script>


<p style="float:left;">
  {{foreach from=$_status item=_statu}}
    <label>
      <input type="radio" name="mode" {{if $_statu == $mode}}checked="checked"{{/if}} value="{{$_statu}}" onchange="changeMode($V(this));"/>{{tr}}CDocumentExterne-_status_available.{{$_statu}}{{/tr}}
    </label>
  {{/foreach}}
</p>

{{mb_include module=system template=inc_pagination total=$nb_total_documents current=$page step=$iteration change_page=changePage}}

<table class="tbl">
  <tr>
    <th class="narrow">
      <input type="checkbox" name="unused" onclick="selectAll(this);"/>
      <select name="action" style="" onchange="do_multi_action($V(this));">
        <option value="">&mdash; {{tr}}Action{{/tr}}</option>
        <option value="star">Favoris</option>
        <option value="archive">Archiver</option>
        <option value="delete">{{tr}}Delete{{/tr}}</option>
      </select>{{tr}}CDocumentExterne.document_date{{/tr}}</th>
    <th>{{tr}}CDocumentExterne.document_name{{/tr}}</th>
    <th>{{tr}}CDocumentExterne.patient_lastname{{/tr}}</th>
    <th class="narrow">{{tr}}CDocumentExterne._status{{/tr}}</th>
  </tr>
  {{foreach from=$documents item=_document}}
    <tr>
      <td>
        <input type="checkbox" class="input_doc" name="document[{{$_document->_class}}][{{$_document->_id}}]" data-object_id="{{$_document->_id}}" data-object_guid="{{$_document->_guid}}"/>
        {{mb_value object=$_document field=document_date}}
      </td>
      <td class="text compact">
        {{mb_value object=$_document field=document_name}}
      </td>
      <td class="text">
        {{mb_value object=$_document field=patient_firstname}} {{mb_value object=$_document field=patient_lastname}}
      </td>
      <td>
        {{mb_include module=messagerie template=inc_status object=$_document}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}{{$doc->_class}}.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{mb_include module=system template=inc_pagination total=$nb_total_documents current=$page step=$iteration change_page=changePage}}
