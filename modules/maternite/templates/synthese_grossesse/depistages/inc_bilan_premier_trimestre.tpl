{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="6">
        {{tr}}CDepistageGrossesse-1er trimestre{{/tr}}
    </th>
  </tr>

    {{if $counter_depisage.trimestre1 > 0}}
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
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=marqueurs_seriques_t21 style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=dpni                   style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=dpni_rques             style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=pappa                  style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=hcg1 unite=" mUI/ml"   style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_t1               style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{else}}
      <tr>
        <td class="empty">
            {{tr}}CDossierPerinat.lieu_surveillance.{{/tr}}
        </td>
      </tr>
    {{/if}}
</table>
