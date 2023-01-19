{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category"
        colspan="6">{{tr}}CDepistageGrossesse-Custom screening|pl{{/tr}}</th>
  </tr>

    {{if $counter_depisage.custom > 0}}
      <tr>
        <td style="width: 12em; padding-right: 0;"></td>
          {{foreach from=$grossesse->_back.depistages item=depistage}}
            <th class="print_date_depistage" style="width: 10em;">
                {{mb_value object=$depistage field=date}}
              <br/>
                {{mb_value object=$depistage field=_sa}} {{tr}}CDepistageGrossesse-_sa{{/tr}}
            </th>
          {{/foreach}}
        <td></td>
      </tr>
        {{foreach from=$depistage_field_customs key=index item=_depistage_field}}
          <tr>
            <td class="print_label" style="text-align: right;">
              <label for="{{$index}}">{{$index}}</label>
            </td>
              {{foreach from=$_depistage_field key=_key item=_field}}
                <td class="text">
                    {{$_field}}
                </td>
              {{/foreach}}
          </tr>
        {{/foreach}}
    {{else}}
      <tr>
        <td class="empty">
            {{tr}}CDossierPerinat.lieu_surveillance.{{/tr}}
        </td>
      </tr>
    {{/if}}
</table>
