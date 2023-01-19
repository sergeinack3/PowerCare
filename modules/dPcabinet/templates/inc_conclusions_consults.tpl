{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  setConclusion = function(conclusion) {
    var form = window.opener.getForm("editFrmExams");
    if (form && form.conclusion) {
      $V(form.conclusion, (form.conclusion.value ? form.conclusion.value + "\n" : "") + conclusion);
    }
    window.close();
  }
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      Conclusions des consultations du <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">{{$sejour}}</span>
    </th>
  </tr>
  <tr>
    <th>Date</th>
    <th>Conclusion</th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$sejour->_ref_consultations item=_consult}}
    {{assign var=conclusion value=$_consult->conclusion}}
    <tr>
      <td class="narrow">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
          {{$_consult->_datetime|date_format:$conf.datetime}}
        </span>
      </td>
      <td>
        {{$conclusion}}
      </td>
      <td>
        {{if $conclusion}}
          <button type="button" class="add notext" onclick="setConclusion('{{$conclusion|JSAttribute}}')"></button>
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">
        {{tr}}CConsultation.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>