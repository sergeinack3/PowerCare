{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=width       value="900px"}}
{{assign var=yaxis_width value=75}}

{{foreach from=$surveillance_data item=_data key=type_graph}}
  {{math assign=left_col_width equation="x*y" x=$_data.yaxes_count y=$yaxis_width}}

  <script>
    Main.add(function () {
      SurveillanceTimeline.initLocales();

      SurveillanceTimeline.current = new SurveillanceTimeline(
        $("surveillance-timeline-{{$type}}"),
        true,
        '{{$type}}',
        {{$_data.timeline_options|@json}},
        {{$pack->_timing_values|@json}},
        {{$_data.display_current_time|@json}},
        1
      );

      SurveillanceTimeline.register(SurveillanceTimeline.current);
      ConcentratorCommon.replayDataIntervention(objectConcentrator, '{{$operation->_id}}', $("surveillance-timeline-{{$type}}", '{{$type}}'));
    });
  </script>
  <style>
    .supervision-{{$type}} .vis-labelset {
      width: {{$left_col_width}}px !important;
    }
  </style>
  <table class="main print" style="page-break-inside: avoid;">
    <tr>
      <th class="category" colspan="4">
        {{tr}}CSupervisionGraph-type-{{$type}}{{/tr}}
        {{if ($type == 'preop') && $operation->entree_bloc && $operation->entree_salle}}
          ({{tr var1=$operation->entree_bloc|date_format:$conf.time var2=$operation->entree_salle|date_format:$conf.time}}common-From %s to %s-variant{{/tr}})
        {{elseif ($type == 'perop') && $operation->entree_salle && ($operation->sortie_salle || $operation->sortie_sans_sspi)}}
          {{mb_ternary var=time_sortie test=$operation->sortie_sans_sspi value=$operation->sortie_sans_sspi other=$operation->sortie_salle}}
          ({{tr var1=$operation->entree_salle|date_format:$conf.time var2=$time_sortie|date_format:$conf.time}}common-From %s to %s-variant{{/tr}})
        {{elseif ($type == 'sspi') && $operation->entree_reveil && $operation->sortie_reveil_reel}}
          ({{tr var1=$operation->entree_reveil|date_format:$conf.time var2=$operation->sortie_reveil_reel|date_format:$conf.time}}common-From %s to %s-variant{{/tr}})
        {{/if}}
      </th>
    </tr>
    <tr>
      <td>
        <div style="position: relative;" class="supervision supervision-{{$type}} surveillance-timeline-container"
             id="surveillance-timeline-{{$type}}">
          <div style="text-align: right;">
            <button class="timeline-action-{{$type}} zoom-in notext not-printable me-tertiary me-dark"
                    data-action="zoom-in"></button>
            <button class="timeline-action-{{$type}} zoom-out notext not-printable me-tertiary me-dark"
                    data-action="zoom-out"></button>
          </div>
          <div style="display: none;">
            <form name="change-operation-graph-pack-" method="post" action="?" onsubmit="return false;">
              {{if ($operation->graph_pack_id || $operation->graph_pack_sspi_id) && $concentrators !== null}}
                {{mb_include module=patientMonitoring template=inc_select_concentrator type='' interv=$operation}}
              {{/if}}
            </form>
          </div>

          {{foreach from=$_data.graphs item=_graph key=i}}
            {{if $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionGraph'}}
              {{assign var=_graph_data value=$_graph->_graph_data}}
              {{math assign=labels_height equation="x+y" x=$_graph->height y=10}}
              <div class="yaxis-labels" style="height:{{$labels_height}}px;">
                {{foreach from=$_graph_data.yaxes|@array_reverse item=_yaxis}}
                  <div class="axis" style="color: {{$_yaxis.color}};">
                    {{$_yaxis.label}}
                    <div class="symbol">{{$_yaxis.symbolChar|smarty:nodefaults}}&nbsp;</div>
                  </div>
                {{/foreach}}

                {{if $_graph->display_legend}}
                  <div id="legend-{{$type}}-{{$i}}" class="graph-legend" style="position: absolute; bottom: 1px;"></div>
                {{/if}}
              </div>
              <div id="placeholder-{{$type}}-{{$i}}" style="height:{{$_graph->height}}px; width: 100%; padding-bottom: 10px;"
                   class="graph-placeholder timeline-item" data-graphguid="{{$_graph->getIdentifier()}}"></div>
              <script>
                {{assign var=_graph_data value=$_graph->_graph_data}}

                Main.add(function () {
                  var ph = jQuery("#placeholder-{{$type}}-{{$i}}");
                  var series = {{$_graph_data.series|@json}};
                  var xaxes = {{$_graph_data.xaxes|@json}};

                  xaxes[0].ticks = 15;
                  xaxes[0].tickFormatter = SurveillancePerop.xTickFormatter;

                  ph.bind("plothover", SurveillancePerop.plothover);

                  var options = {
                    grid:   {
                      hoverable:       true,
                      markings:        [
                        {
                          xaxis: {
                            from: {{$_data.time_debut_op}},
                            to: {{$_data.time_fin_op}}
                          },
                          color: "rgba(213,221,246,.4)"
                        }
                      ],
                      margin:          0,
                      tickMargin:      0,
                      labelMargin:     2,
                      minBorderMargin: 0,
                      borderWidth:     1
                    },
                    legend: {
                      show: {{$_graph->display_legend|ternary:'true':'false'}},
                      container: "#legend-{{$type}}-{{$i}}",
                      noColumns: 5,
                      margin:    0,
                      sorted:    "reverse"
                    },
                    series: SupervisionGraph.defaultSeries,
                    xaxes:  xaxes,
                    yaxes: {{$_graph_data.yaxes|@json}}
                  };

                  // Navigate
                  options.xaxis = {
                    panRange: null
                  };
                  options.yaxis = {
                    panRange: false
                  };
                  options.pan = {
                    interactive: true,
                    frameRate:   25
                  };

                  if (bowser.tablet || bowser.mobile) {
                    options.touch = {
                      pan:   'x',
                      scale: 'x'
                    };
                  }

                  var graph = {
                    key:            '{{$_graph->_guid}}',
                    plot:           jQuery.plot(ph, series, options),
                    holder:         ph,
                    container:      ph[0],
                    series:         series,
                    options:        options,
                    isGraph:        true,
                    isConcentrator: {{if $_graph->automatic_protocol == 'Kheops-Concentrator' || $_graph->automatic_protocol == 'MD-Stream'}}true{{else}}false{{/if}},
                    scale: {{$_graph_data.scale|@json}}
                  };

                  ph.bind("plotpan", function (event, plot) {
                    SurveillanceTimeline.current.updateOffsetsFromFlot.bind(SurveillanceTimeline.current)(plot);
                  });

                  ph.bind("plotzoom", function (event, plot) {
                    SurveillanceTimeline.current.updateOffsetsFromFlot.bind(SurveillanceTimeline.current)(plot);
                  });

                  ph.bind("touchrelease", function (event, plot) {
                    SurveillanceTimeline.current.updateOffsetsFromFlot.bind(SurveillanceTimeline.current)(plot);
                  });

                  graph.update = (function (graph, data) {
                    graph.series = data.series;

                    graph.plot.setData(graph.series);
                    graph.plot.draw();
                  }).curry(graph);

                  SurveillanceTimeline.current.append(graph.key, graph);
                });
              </script>
            {{elseif $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionTimeline'}}
              {{unique_id var=timeline_uid}}
              <div id="timeline-{{$timeline_uid}}" class="timeline-item" data-graphguid="{{$_graph->getIdentifier()}}"></div>
              <script>
                Main.add(function () {
                  App.loadJS(['lib/visjs/vis'], (function (currentTL, vis) {
                    new SurveillanceTimelineItem(
                      vis,
                      currentTL,
                      '{{$_graph->getIdentifier()}}',
                      $("timeline-{{$timeline_uid}}"),
                      {{$_graph->groups|@json}},
                      {{$_graph->items|@json}},
                      {{$_graph->options|@json}}
                    );
                  }).curry(SurveillanceTimeline.current));

                  SurveillanceTimeline.current.applyOffsets(false);
                });
              </script>
            {{elseif $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionTable' && $_graph->_ref_rows|@count}}
              {{unique_id var=timeline_uid}}
              <div id="timeline-{{$timeline_uid}}" class="timeline-item" data-graphguid="{{$_graph->getIdentifier()}}"></div>
              <script>
                Main.add(function () {
                  App.loadJS(['lib/visjs/vis'], (function (currentTL, vis) {
                    var data = new vis.DataSet({{$_graph->_timeline_items|@json}});
                    var groups = {{$_graph->_timeline_groups|@json}};
                    var stli = new SurveillanceTimelineItem(
                      vis,
                      currentTL,
                      '{{$_graph->getIdentifier()}}',
                      $("timeline-{{$timeline_uid}}"),
                      groups,
                      data,
                      {{$_graph->_timeline_options|@json}}
                    );
                    stli.isTable = true;
                    stli.samplingFrequency = parseInt('{{$_graph->sampling_frequency}}');
                    {{if $_graph->automatic_protocol == 'Kheops-Concentrator'|| $_graph->automatic_protocol == 'MD-Stream'}}
                    stli.isConcentrator = true;
                    stli.groups = groups;
                    stli.data = data;
                    {{/if}}
                  }).curry(SurveillanceTimeline.current));
                });
              </script>
            {{/if}}
          {{/foreach}}

          <table class="main evenements" style="table-layout: fixed; width: 100%; border-spacing: 0;">
            <col style="width: {{$left_col_width}}px;"/>

            <tr>
              <th style="padding: 0; border: none;"></th>
              <td style="padding: 0; border: none;" class="timing-container"></td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>
{{/foreach}}
