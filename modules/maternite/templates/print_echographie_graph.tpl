{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=dossierMater ajax=1}}

{{mb_default var=num_enfant value=0}}
{{mb_default var=show_select_children value=0}}

{{assign var=name_element   value=""}}
{{assign var=display_legend value=""}}

{{if $num_enfant && !$show_select_children}}
  {{assign var=name_element value="_`$num_enfant`"}}
  {{assign var=graph_size value="90%"}}

  {{if !in_array($graph_name, array("bip", "pc", "pa", "lf", "cn", "poids_foetal"))}}
    {{assign var=display_legend value="me-display-none"}}
  {{/if}}
{{/if}}

<script>
  Main.add(function () {
    drawGraph{{$graph_name}}{{$num_enfant}}();
  });

  drawGraph{{$graph_name}}{{$num_enfant}} = function () {
    var oData = {{$graph_axes|@json}};
    var oPh = jQuery("#placeholder_{{$graph_name}}{{$name_element}}{{$num_enfant}}");
    {{foreach from=$survEchoData item=_survEchoData}}
    oData = oData.concat({{$_survEchoData|@json}});
    var plot = jQuery.plot(oPh, oData, {
      width:      '{{$graph_size}}px',
      height:     '400px',
      grid:       {
        borderWidth:     1,
        minBorderMargin: 20,
        labelMargin:     10,
        margin:          {
          top:    8,
          bottom: 10,
          left:   10
        }
      },
      yaxis:      {
        labelWidth: 30,
        ticks:      10
      },
      xaxis:      {
        labelHeight:   30,
        tickFormatter: function (v) {
          return Math.round(v)
        }
      },
      legend:     {
        container: '#legend_container1_{{$graph_name}}{{$name_element}}{{$num_enfant}}',
        show:      true
      },
      shadowSize: 0
    });
    {{/foreach}}
    var canvas = plot.getCanvas();
    var image = canvas.toDataURL('image/png');
    $('placeholder_{{$graph_name}}{{$name_element}}{{$num_enfant}}')
      .down('.flot-base')
      .insert(
        {
          after: DOM.img({
            src:    image,
            width:  320,
            height: 415
          })
        })
      .remove();
    $('placeholder_{{$graph_name}}{{$name_element}}{{$num_enfant}}')
      .insert(DOM.div({class: 'axisLabel yaxisLabel'}, {{if $graph_name == 'poids_foetal'}}'g'{{else}}'mm'{{/if}}));
    var title = {{if $graph_name == 'cn'}}$T("CGrossesse-_jour_grossesse") + ' (à partir de 11 SA)'
    {{else}}$T("CGrossesse-_semaine_grossesse"){{/if}};
    $('placeholder_{{$graph_name}}{{$name_element}}{{$num_enfant}}').insert(DOM.div({class: 'axisLabel xaxisLabel'}, title));
  };
</script>

<div style="position: relative; page-break-before: always;">
  <div>
    <h2 style="text-align: center">
      <strong>{{tr}}CSurvEchoGrossesse-{{$graph_name}}{{/tr}}</strong>
    </h2>
  </div>
  <div style="width: {{$graph_size}}!important;">
    <div class="placeholder_mater" id="placeholder_{{$graph_name}}{{$name_element}}{{$num_enfant}}"
         style="width: 320px; height: 415px;"></div>
  </div>
  <div style="width: 8em;">
    <div class="{{$display_legend}}" id="legend_container_{{$graph_name}}{{$num_enfant}}"
              style="{{if !$show_select_children}}position: absolute; left: 60px; top: 60px;{{/if}}">
      {{if in_array($graph_name, array("bip", "pc", "pa", "lf", "cn", "poids_foetal"))}}
        <img src="./modules/maternite/images/legend.png" alt="{{tr}}common-Legend{{/tr}}">
      {{/if}}
      <div id="legend_container1_{{$graph_name}}{{$name_element}}{{$num_enfant}}"></div>
    </div>
  </div>
  <div >
    <p style="margin-top: 10px; color: #4d4d4d; text-align: center;">
      {{tr}}common-Source{{/tr}} :
      {{if $graph_name == 'lcc'}}
        {{tr}}CEchoGraph-legend-graph-lcc{{/tr}}
      {{else}}
        {{tr}}CEchoGraph-legend-graph-echo{{/tr}}
      {{/if}}
    </p>
  </div>
</div>
