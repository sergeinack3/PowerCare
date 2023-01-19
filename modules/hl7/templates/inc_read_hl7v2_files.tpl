{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    Control.Tabs.create("messages-tab");
  });
</script>

<table class="main layout">
  <tr>
    <td style="vertical-align: top; white-space: nowrap;" class="narrow">
      <ul id="messages-tab" class="control_tabs_vertical small" style="min-width: 10em;">
        {{foreach from=$messages item=_message key=_key}}
          <li>
            <a href="#message-{{$_key}}" {{if $_message->errors}} class="wrong" {{/if}} title="{{$_message->filename}}">
              <strong style="float: left; margin-right: 1em;">{{$_message->name}}</strong> 
              {{$_message->version}} 
              {{if $_message->extension}}
                ({{$_message->extension}})
              {{/if}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>

    <td class="text" style="padding: 3px;">
      {{foreach from=$messages item=_message key=_key}}
        {{mb_include module=hl7 template=inc_display_hl7v2_message message=$_message key=$_key}}
      {{/foreach}}
    </td>
  </tr>
</table>