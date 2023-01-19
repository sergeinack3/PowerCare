{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="main" id="menu_share_docs" style="height:100%">
  <fieldset>
    <legend>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$docItem->_guid}}')">
        {{$docItem}}
       </span>
    </legend>

    <table class="main tbl" style="height:20%">
      <tr>
        <td class="text"><strong>{{mb_label object=$docItem field="object_id"}}</strong> : {{mb_value object=$docItem field="object_id"}}</td>
      </tr>
      <tr>
        <td><strong>{{mb_label object=$docItem field="author_id"}}</strong> : {{mb_value object=$docItem field="author_id"}}</td>
      </tr>
      <tr>
        <td><strong>{{mb_label object=$docItem field="_file_date"}}</strong> : {{mb_value object=$docItem field="_file_date"}}</td>
      </tr>
    </table>
  </fieldset>

  <div style="overflow: auto; height:80%; position:relative">
    <ul class="timeline" id="nav_timeline_share_docs" style="margin-left:-50%;font-size:1.4em">
      {{counter start=0 assign="step"}}
      {{foreach from=$receivers key=_module_name item=_receivers}}
        {{foreach from=$_receivers key=_key item=_receiver_item}}
          {{assign var=receiver           value=$_receiver_item.receiver}}
          {{assign var=document_reference value=$_receiver_item.document_reference}}

          <li class="evenement-span" style="margin-top: 10%"
              data-receiver_guid="{{$receiver->_guid}}"
              data-module_name="{{$_module_name}}"
              data-document_reference_guid="{{$document_reference->_id}}">
            <div class="timeline_share_doc_label {{if $document_reference && $document_reference->_id}}passed{{/if}}">
              <img src="modules/{{$_module_name}}/images/icon.png" width="16"/> {{$receiver}}
            </div>
          </li>
          {{counter}}
        {{/foreach}}
      {{/foreach}}
    </ul>
  </div>
</div>