{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="6">
      {{tr}}CDepistageGrossesse-Vasculo-Renal Assessment{{/tr}}
  </th>
</tr>

{{if $counter_depisage.bactero > 0}}
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
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=acide_urique unite=" mg/24h" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=asat         unite=" UI/l"   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=alat         unite=" UI/l"   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=phosphatase  unite=" UI/l"   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=brb                 style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=sel_biliaire        style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=creatininemie       style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_bacteriologie style_label="print_label" no_value=true class_value="me-text-align-right"}}
{{else}}
  <tr>
    <td class="empty">
        {{tr}}CDossierPerinat.lieu_surveillance.{{/tr}}
    </td>
  </tr>
{{/if}}
