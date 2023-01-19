{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="6">
        {{tr}}CDepistageGrossesse-Biochemistry{{/tr}}
      - {{tr}}CDepistageGrossesse-Hematology and Hemostasis{{/tr}}
    </th>
  </tr>

    {{if $counter_depisage.biochimie > 0}}
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
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=nfs_hb    unite=" g/dl" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=gr        unite=" /mm³" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=gb        unite=" g/L"  style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=vgm       unite=" fL"   style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=ferritine unite=" µg/l" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=glycemie  unite=" g/l"  style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=electro_hemoglobine_a1 unite=" %" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=electro_hemoglobine_a2 unite=" %" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=electro_hemoglobine_s  unite=" %" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=tp             unite=" %"           style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=tca            unite=" s"           style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=fg             unite=" g/L"         style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=nfs_plaquettes unite=" (x1000)/mm³" style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=depistage_diabete style_label="print_label" no_value=true class_value="me-text-align-right"}}
        {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_biochimie   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{else}}
      <tr>
        <td class="empty">
            {{tr}}CDossierPerinat.lieu_surveillance.{{/tr}}
        </td>
      </tr>
    {{/if}}
</table>
