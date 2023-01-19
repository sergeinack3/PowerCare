{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{if !$receivers}}
  <div class="small-warning">
    {{tr var1=$docItem->_view}}CDocumentItem-msg-No recipient to share document{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<script>
  Main.add(function(){
    DocumentItem.refreshNavMenu(0, {{$count_receivers}}, true, '{{$docItem->_guid}}');
  });
</script>

<table class="main" style="height:100%">
  <tr>
    <td style="width:100px; height:auto">
      {{mb_include module=dPfiles template=menu_share_document}}
    </td>
    <td style=" height:auto" id="send_docItem">
    </td>
  </tr>
</table>







