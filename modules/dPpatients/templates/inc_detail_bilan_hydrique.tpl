{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="4" class="title">
      <button type="button" class="left notext" style="float: left;"
              onclick="Control.Modal.close(); detailBilanHydrique('{{$before}}');">
      </button>
      <button type="button" class="right notext" style="float: right;"
              onclick="Control.Modal.close(); detailBilanHydrique('{{$after}}');">
      </button>
      {{tr var1=$datetime_min|date_format:$conf.datetime var2=$datetime_max|date_format:$conf.datetime}}CConstantesMedicales-_bilan_hydrique-detail{{/tr}}
    </th>
  </tr>
  {{foreach from=$bilan item=_bilans_by_datetime key=cat}}
    <tr>
      <th colspan="4" class="section">
        {{tr}}CConstantesMedicales-_bilan_hydrique-{{$cat}}{{/tr}}
      </th>
    </tr>
    <tr>
      <th class="narrow">{{tr}}Date{{/tr}}</th>
      <th class="narrow">{{tr}}Hour{{/tr}}</th>
      <th>{{tr}}Type{{/tr}}</th>
      <th>{{tr}}common-Value{{/tr}} (mL)</th>
    </tr>
    {{foreach from=$_bilans_by_datetime key=_datetime item=_bilans}}
      {{foreach from=$_bilans item=_bilan}}
        <tr>
          <td>
            {{mb_ditto name=date value=$_datetime|date_format:$conf.date}}
          </td>
          <td>
            {{mb_ditto name=time value=$_datetime|date_format:$conf.time}}
          </td>
          <td class="text">
            {{$_bilan.name}}
          </td>
          <td style="text-align: right;">
            {{$_bilan.value|float}}
          </td>
        </tr>
      </tr>
      {{/foreach}}
      {{foreachelse}}
      <tr>
        <td colspan="4" class="empty">
          {{tr}}CConstantesMedicales-_bilan_hydrique-{{$cat}}.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    {{if $_bilans_by_datetime|@count}}
      <tr>
        <td colspan="2"></td>
        <td style="text-align: right;">Total</td>
        <td style="text-align: right;">
          {{$total.$cat|float}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}

  <tr>
    <th colspan="3"></th>
    <th>{{tr}}Total{{/tr}}</th>
  </tr>
  <tr>
    <td colspan="3"></td>
    <td style="text-align: right;">
      {{$total.total|float}}
    </td>
  </tr>
</table>