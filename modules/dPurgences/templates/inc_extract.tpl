{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences script=rpu_sender ajax=true}}

<table class="main form">
  <tr>
    <th class="title" colspan="2">{{tr}}extract-{{$type}}-desc{{/tr}}</th>
  </tr>

  <tr>
    <td class="narrow">
      <form name="formExtraction_{{$type}}" action="?" method="get">
        <table class="form">
          <tr>
            <th>{{mb_label object=$extractPassages field="debut_selection"}}</th>
            <td>
              {{mb_field object=$extractPassages field="debut_selection" form="formExtraction_`$type`" register="true"}}
            </td>
          </tr>
          {{if $type == "rpu"}}
          <tr>
            <th>{{mb_label object=$extractPassages field="fin_selection"}}</th>
            <td>{{mb_field object=$extractPassages field=fin_selection register=true form="formExtraction_`$type`" prop="dateTime"}}</td>
          </tr>
          {{/if}}
          <tr>
            <td colspan="2">
              <button class="tick me-primary" type="button" onclick="RPU_Sender.extract(this.form, '{{$type}}')">
                {{tr}}Extract{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    <td id="td_extract_{{$type}}"></td>
  </tr>
  <tr>
    <td>
      <button class="lock" type="button" id="encrypt_{{$type}}" onclick="RPU_Sender.encrypt('{{$type}}')">{{tr}}Encrypt{{/tr}}</button>
    </td>
    <td id="td_encrypt_{{$type}}"></td>
  </tr>

  <tr>
    <td>
      <button class="send" type="button" id="transmit_{{$type}}" onclick="RPU_Sender.transmit('{{$type}}')">{{tr}}Transmit{{/tr}}</button>
    </td>
    <td id="td_transmit_{{$type}}"></td>
  </tr>
</table>