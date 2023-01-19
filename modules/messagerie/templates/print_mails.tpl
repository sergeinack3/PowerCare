{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        $('mails-content').print()
    })
</script>
<div id="mails-content">
    {{foreach from=$mails item=mail name=listmails}}
        <div class="mail-content"
          {{if !$smarty.foreach.listmails.first}}
            style="page-break-before: always"
          {{/if}}>
            <table class="form">
                <tr>
                    <th class="title" colspan="4">{{mb_value object=$mail field=subject}}</th>
                </tr>
                <tr>
                    <th class="narrow">{{mb_label object=$mail field=from}}</th>
                    <td style="text-align: left;">{{mb_value object=$mail field=from}}</td>
                    <th>{{mb_label object=$mail field=to}}</th>
                    <td class="me-ws-wrap" style="text-align: left;">{{mb_value object=$mail field=to}}</td>
                </tr>
                <tr>
                    <th>{{mb_label object=$mail field=date_inbox}}</th>
                    <td>{{mb_value object=$mail field=date_inbox}}</td>
                    <th>{{mb_label object=$mail field=date_read}}</th>
                    <td>{{mb_value object=$mail field=date_read}}</td>
                </tr>
            </table>
            <hr/>
          {{if $mail->_text_html && $mail->_text_html->content}}
            {{$mail->_text_html->content|html_entity_decode}}
          {{else}}
            {{$mail->_text_plain->content|nl2br}}
          {{/if}}
        </div>
    {{/foreach}}
</div>
