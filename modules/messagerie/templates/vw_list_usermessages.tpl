{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    UserMessage.refresh('{{$selected_folder}}', 0);
  });
</script>

<form method="get" target="?" name="list_usermessage" onsubmit="return onSubmitFormAjax(this, null, 'list-messages')">
  <input type="hidden" name="m" value="messagerie" />
  <input type="hidden" name="a" value="ajax_list_usermessage" />
  <input type="hidden" name="user_id" value="{{$user->_id}}" />
  <input type="hidden" name="mode" value="inbox"/>
  <input type="hidden" name="page" value="0" />
</form>

<div id="internalMessages" style="position: relative;">
  <section style="position: absolute; width: 15%; left: 0px;">
    <ul class="list-folders" style="list-style-type: none; position: relative; margin-top: 10px; margin-right: 10px;">
      {{foreach from=$folders key=_folder item=_count}}
        <li style="margin-bottom: 5px;">
          <div class="folder{{if $_folder == $selected_folder}} selected{{/if}}" data-folder="{{$_folder}}" onclick="UserMessage.refresh('{{$_folder}}', 0);" style="font-size: 1.1em; height: 20px; padding-bottom: 2px; padding-top: 2px; padding-left: 5px; margin: 5px; cursor: pointer;">
            <span style="float:left; margin-right: 5px;">
              <i class="msgicon folder-icon fa fa-folder{{if $selected_folder == $_folder}}-open{{/if}}"></i>
            </span>

            <span class="count circled"{{if $_count == 0}} style="display: none;"{{/if}}>
             {{$_count}}
            </span>

            <span>
              {{tr}}CUserMessage-{{$_folder}}{{/tr}}
            </span>
          </div>
        </li>
      {{/foreach}}
    </ul>
  </section>
  <section style="position: absolute; height: 80%; width: 85%; left: 15%; top: 20%;">
    <div id="list-messages">

    </div>
  </section>
</div>