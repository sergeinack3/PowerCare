{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main me-w100" style="font-size: 100%;border: 1px solid #90A4AE">
  <tr>
    <th class="category" colspan="6">
        {{tr}}CPatient.civilite.enf-long{{/tr}} {{$key_num}}
    </th>
  </tr>

  <tr>
    <td style="width: 12em; padding-right: 0;"></td>
      {{foreach from=$echographies item=_echographie}}
        <th class="print_date_depistage" style="width: 10em;">
            {{mb_value object=$_echographie field=date}}
          <br/>
            {{mb_value object=$_echographie field=_sa}} {{tr}}CDepistageGrossesse-_sa{{/tr}}
          &ndash; {{mb_value object=$_echographie field=type_echo}}
        </th>
      {{/foreach}}
    <td></td>
  </tr>

    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=lcc             unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=cn              unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=bip             unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=pc              unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=dat             unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=pa              unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=lf              unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=lp              unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=dfo             unite="mm" style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=poids_foetal    unite="g"  style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=remarques       unite=""   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=opn             unite=""   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=avis_dan        unite=""   style_label="print_label" no_value=true class_value="me-text-align-right"}}
    {{mb_include module=maternite template=inc_surv_echo_line_cte cte=pos_placentaire unite=""   style_label="print_label" no_value=true class_value="me-text-align-right"}}
</table>
{{if !$offline}}
  <table style="page-break-before: always;">
    <tr>
      <td>
        <table class="main" id="graph_mos_container_{{$key_num}}" style="border: 1px solid #90A4AE;">
          <tbody class="viewported">
          <tr>
              {{foreach from=$all_graphs key=graph_name item=_graph name=graph_loop}}
              {{if !$smarty.foreach.graph_loop.first && $smarty.foreach.graph_loop.iteration %2 == 1}}
          </tr>
          <tr>
              {{/if}}
            <td class="viewport width50"
                id="graph_mos_container_{{$smarty.foreach.graph_loop.iteration}}_child_{{$key_num}}">
                {{mb_include module=maternite template=print_echographie_graph graph_name=$graph_name
                graph_axes=$_graph.$key_num.graph_axes survEchoData=$_graph.$key_num.survEchoData
                num_enfant=$key_num show_select_children=0 graph_size="100%"}}
            </td>
              {{if $smarty.foreach.graph_loop.iteration == 4}}
        </table>
        <table class="main" style="border: 1px solid #90A4AE;">
            {{/if}}
              {{/foreach}}
              {{if $list_graphs|@count %2 == 1}}
                <td></td>
              {{/if}}
          </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </table>
{{/if}}
