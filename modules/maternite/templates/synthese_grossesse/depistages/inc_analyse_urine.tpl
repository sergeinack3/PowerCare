{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="6">
      {{tr}}CDepistageGrossesse-urine{{/tr}}
  </th>
</tr>

{{if $counter_depisage.urine > 0}}
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
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=albuminerie    unite=" g/L" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=glycosurie     unite=" g/L" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=albuminerie_24 unite=" g/L" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=cbu style_label="print_label" no_value=true class_value="me-text-align-right"}}
{{else}}
  <tr>
    <td class="empty">
        {{tr}}CDossierPerinat.lieu_surveillance.{{/tr}}
    </td>
  </tr>
{{/if}}
