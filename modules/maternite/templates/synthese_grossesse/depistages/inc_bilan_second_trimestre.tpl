{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="6">
        {{tr}}CDepistageGrossesse-2nd trimestre{{/tr}}
    </th>
  </tr>

    {{if $counter_depisage.trimestre2 > 0}}
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
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=afp  unite=" ng/l"   style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=hcg2 unite=" mUI/ml" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=estriol  style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_t2 style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{else}}
      <tr>
        <td class="empty">
            {{tr}}CDossierPerinat.lieu_surveillance.{{/tr}}
        </td>
      </tr>
    {{/if}}
</table>
