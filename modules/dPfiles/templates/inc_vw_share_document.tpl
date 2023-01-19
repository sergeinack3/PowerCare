{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{if !$receivers || $count_receivers == 0}}
  <div class="small-warning">
      {{tr}}CFileTraceability-msg-No receiver for this file{{/tr}}
  </div>

    {{mb_return}}
{{/if}}

<div id="share-{{$docItem->_guid}}">
  <div class="small-info">
    {{tr var1=$docItem->_view}}CDocumentItem-msg-Select share document{{/tr}}
  </div>

  <form name="shareDocumentDetails-{{$docItem->_guid}}" method="post" onsubmit="return DocumentItem.shareDocumentDetails(this);">
    <input type="hidden" name="docItem_guid" value="{{$docItem->_guid}}" />

    <table class="main tbl" id="shareDocumentDetails">
      <tr>
        <th class="narrow">
          <input type="checkbox" onclick="DocumentItem.toggleShare(this.checked)"/>
        </th>
        <th>{{tr}}CInteropReceiver{{/tr}}</th>
        <th>{{tr}}CFileTraceability-status{{/tr}}</th>
      </tr>

      {{foreach from=$receivers key=_module_name item=_receiver}}
        {{if is_array($_receiver)}}
          {{foreach from=$_receiver item=_receiver_item}}
            {{mb_include module=files template="inc_vw_share_document_item" receiver=$_receiver_item}}
          {{/foreach}}
        {{else}}
            {{mb_include module=files template="inc_vw_share_document_item" receiver=$_receiver}}
        {{/if}}
      {{/foreach}}
    </table>

    <div>
      <br />
      <button class="fa fa-chevron-circle-right" type="submit">
          {{tr}}Next{{/tr}}
      </button>
    </div>
  </form>
</div>
