{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        window.print()
    })
</script>
<div id="messages-content">
    {{foreach from=$messages item=usermessage name=listmessages}}
        <div class="message-content" style="page-break-after: auto">
            <table class="form" style="width: 100%; margin-top: 5px; margin-bottom: 10px;">
                <tr>
                    <th colspan="4" class="title">{{mb_value object=$usermessage field=subject}}</th>
                </tr>
                <tr>
                    <th class="narrow">{{tr}}CUserMessageDest-from_user_id{{/tr}}</th>
                    <td>
                        <div class="mediuser">
                            {{$usermessage->_ref_user_creator}}
                        </div>
                    </td>
                    <th class="narrow">{{tr}}CUserMessageDest-to_user_id{{/tr}}</th>
                    <td>
                        <div class="me-display-flex me-flex-wrap">
                            {{foreach from=$usermessage->_ref_destinataires item=_dest name=ref_dest}}
                                {{$_dest->_ref_user_to}}
                                {{if !$smarty.foreach.ref_dest.last}}, {{/if}}
                            {{/foreach}}
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="narrow">{{tr}}CUserMessageDest-datetime_sent{{/tr}}</th>
                    <td>
                        {{$usermessage->_ref_dest_user->_datetime_sent}}
                    </td>
                    <th class="narrow">{{tr}}CUserMessageDest-datetime_read{{/tr}}</th>
                    <td>
                        {{$usermessage->_ref_dest_user->_datetime_read}}
                    </td>
                </tr>
            </table>
            <div class="me-margin-top-20 me-margin-bottom-20">
                {{$usermessage->content|smarty:nodefaults}}
            </div>
        </div>
        {{if !$smarty.foreach.listmessages.last}}
            <hr/>
        {{/if}}
    {{/foreach}}
</div>
