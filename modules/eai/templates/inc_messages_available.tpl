{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=transformation ajax=true}}

<fieldset>
  <legend>{{tr}}{{$message}}{{/tr}} <span class="compact">({{tr}}{{$message}}-desc{{/tr}})</span></legend>

  <table class="tbl form me-no-box-shadow me-no-align">
      {{assign var=transaction value=false}}
      {{foreach from=$messages_supported item=_message_supported name=messages_supported}}
          {{assign var=event      value=$_message_supported->_event}}
          {{assign var=event_name value=$event|getShortName}}

          {{if !$transaction || $transaction != $_message_supported->transaction}}
            <tr class="section">
              <th colspan="3">
                  {{tr}}{{$_message_supported->transaction}}{{/tr}}
              </th>
            </tr>
          {{/if}}
          {{assign var=transaction value=$_message_supported->transaction}}
        <tr>
          <td class="narrow"><strong>{{tr}}{{$_message_supported->message}}{{/tr}}</strong></td>
          <td class="narrow"><i class="fa fa-arrow-right"></i></td>
          <td class="text compact">{{tr}}{{$_message_supported->message}}-desc{{/tr}}</td>
        </tr>
      {{/foreach}}
    </fieldset>
  </table>
</fieldset>
