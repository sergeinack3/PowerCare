{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=print value=false}}

{{if $type == "sspi" && !$interv->entree_reveil && !$force}}
  <div
    class="small-info">{{tr}}COperation-msg-The patient has not yet entered the recovery room please enter to access the surveillance{{/tr}}</div>
    {{mb_return}}
{{/if}}

{{if "maternite"|module_active && $interv->_ref_sejour->grossesse_id &&
(!"monitoringMaternite"|module_active || !"monitoringMaternite general active_graph_supervision"|gconf)}}
  <div class="small-warning">
    {{tr}}COperation-msg-Please check that the Maternity Monitoring module is activated as well as the Activate maternity monitoring configuration{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{* Dans le cas où aucun graphique n est séléctionné, on force la largeur de la colonne de gauche comme si on avait 3 axes *}}
{{if $yaxes_count == 0}}
    {{assign var=yaxes_count value=3}}
{{/if}}

{{assign var=yaxis_width value=78}}
{{math assign=left_col_width equation="$yaxes_count*$yaxis_width"}}

{{if $left_col_width < 200}}
    {{assign var=left_col_width value=200}}
{{/if}}

{{assign var=graph_pack_id value=$interv->graph_pack_id}}

{{if $type == "sspi"}}
 {{assign var=graph_pack_id value=$interv->graph_pack_sspi_id}}
{{elseif $type == "preop"}}
 {{assign var=graph_pack_id value=$interv->graph_pack_preop_id}}
{{/if}}

{{unique_id var=uid_plot}}

<style>
  {{if !$readonly}}
  .vis-item.vis-editable {
    cursor: pointer;
  }

  .vis-item:hover {
    background: #cfd2d3;
  }

  {{/if}}

  .supervision .flot-x-axis .flot-tick-label {
    margin-left: 25px;
  }

  #surveillance_cell-{{$uid_plot}} .vis-labelset {
    width: {{$left_col_width}}px !important;
  }
</style>

<script>
  Main.add(function () {
    SurveillanceTimeline.initLocales();

    SurveillanceTimeline.date_max_adm = new Date.fromDATETIME('{{$date_max_adm}}').toDATETIME(true);

    SurveillanceTimeline.current = new SurveillanceTimeline(
      $("surveillance-timeline-{{$uid_plot}}"),
            {{$readonly|@json}},
            {{$type|@json}},
            {{$timeline_options|@json}},
            {{$pack->_timing_values|@json}},
            {{$display_current_time|@json}},
      0,
      {{$frequency_automatic_graph|@json}}
    );

    SurveillanceTimeline.register(SurveillanceTimeline.current);

    var oFormProtocole = SurveillanceTimeline.current.container.down("form[name='applyProtocoleSurvPerop']");
    if (oFormProtocole) {
      var url = new Url("dPprescription", "httpreq_vw_select_protocole");
      var autocompleter = url.autoComplete(oFormProtocole.libelle_protocole, "protocole_surv_perop_auto_complete_{{$type}}", {
        dropdown:      true,
        minChars:      2,
        valueElement:  oFormProtocole.elements.pack_protocole_id,
        updateElement: function (selectedElement) {
          if (autocompleter.options.afterUpdateElement) {
            autocompleter.options.afterUpdateElement(autocompleter.element, selectedElement);
          }
          oFormProtocole.onsubmit();
        },
        callback:
           function (input, queryString) {
             var praticien_id = $V(getForm("applyProtocoleSurvPerop").praticien_id);
             return (queryString + "&praticien_id=" + praticien_id + "&perop=true&type=sejour");
           }
      });
    }

    // Fixed header timings
    var positionTop = 0;

    document.addEventListener("scroll", function (e) {
      var scrolled    = document.scrollingElement.scrollTop;

      if (($$('div[data-graphguid=supervision-timeline-geste]').length > 0) && ($$('div[data-graphguid=supervision-timeline-geste]')[0].length > 0)) {
        var element = $$('div[data-graphguid=supervision-timeline-geste]')[0].down('div.vis-panel.vis-bottom');

        if (!element.get('save')) {
          positionTop = element.cumulativeOffset().top;
          element.set('left', element.getStyle('left'));
          element.set('top', element.getStyle('top'));
          element.insert({before: '<style>.left_fixed {left: '+element.cumulativeOffset().left + 'px !important;}</style>'});
          element.set('save', 1);
        }

        if (scrolled > positionTop){
          element.setStyle({left: element.cumulativeOffset().left + 'px'});
          element.addClassName('me-bg-elevation-16 left_fixed header_fixed');
        }
        else {
          element.setStyle({left: element.get('left')});
          element.removeClassName('me-bg-elevation-16 left_fixed header_fixed');
        }
      }
    });
  });

  lockSurveillance_{{$type}} = function (operation_id, readonly, user_id) {
    var url = new Url("dPplanningOp", "do_operation_aed", "dosql");
    url.addParam("operation_id", operation_id);

    {{if $type == "sspi"}}
      url.addParam("graph_pack_sspi_locked_user_id", (readonly ? "" : user_id));
    {{elseif $type == "preop"}}
      url.addParam("graph_pack_preop_locked_user_id", (readonly ? "" : user_id));
    {{else}}
      url.addParam("graph_pack_locked_user_id", (readonly ? "" : user_id));
    {{/if}}

    url.requestUpdate(SystemMessage.id, {
      method:     "post",
      onComplete: reloadSurveillance['{{$type}}']
    });
  };

  submitPersonnel = function (oForm) {
    return onSubmitFormAjax(oForm, {onComplete: function(){
      SalleOp.refreshPersonnelPartogramme('{{$interv->_id}}');
      SurveillancePerop.showPartogramme.curry('{{$interv->_id}}', 0);
    }})
  };
</script>

<div id="modal_apply_protocole_surv_perop" style="display: none;">
  <div class="small-info">{{tr}}CProtocole-Application protocol in progress{{/tr}}</div>
</div>

<div id="{{$type}}-anesth">
  {{mb_include module=salleOp template=inc_vw_anesth selOp=$interv prefix_form=$type graph_lock=$readonly}}
</div>

<fieldset style="background-color: #fff;" id="surveillance-timeline-{{$uid_plot}}"
          class="surveillance-timeline-container timeline-container-{{$type}} me-planningop-fieldset-graph me-bg-transparent" data-type="{{$type}}"
          data-current_time="{{$is_current_time}}" data-limit_date_min="{{$limit_date_min}}">
  <legend>
      {{tr}}CSupervisionGraphToPack-graph_id{{/tr}}

      {{if "patientMonitoring"|module_active && ($interv->graph_pack_id || $interv->graph_pack_sspi_id || $interv->graph_pack_preop_id)}}
        {{mb_script module=patientMonitoring script=concentrator_common ajax=true}}

        {{mb_include module=patientMonitoring template=inc_concentrator_js     ajax=true}}
        {{mb_include module=patientMonitoring template=inc_concentrator_js_v2  ajax=true}}
      {{/if}}

    <form name="change-operation-graph-pack-{{$type}}" method="post" action="?"
          onsubmit="return onSubmitFormAjax(this, reloadSurveillance['{{if $type == 'partogramme'}}perop{{else}}{{$type}}{{/if}}'])">
        {{mb_class object=$interv}}
        {{mb_key   object=$interv}}

        {{if $type == "sspi"}}
          <select name="graph_pack_sspi_id" onchange="this.form.onsubmit()">
            <option value="">&ndash; {{tr}}CSupervisionGraphPack.none{{/tr}}</option>

              {{foreach from=$graph_packs item=_pack}}
                <option value="{{$_pack->_id}}" {{if $_pack->_id == $interv->graph_pack_sspi_id}}selected{{/if}}>
                    {{$_pack}}
                </option>
              {{/foreach}}
          </select>
        {{elseif $type == "preop"}}
          <select name="graph_pack_preop_id" onchange="this.form.onsubmit()">
            <option value="">&ndash; {{tr}}CSupervisionGraphPack.none{{/tr}}</option>

              {{foreach from=$graph_packs item=_pack}}
                <option value="{{$_pack->_id}}" {{if $_pack->_id == $interv->graph_pack_preop_id}}selected{{/if}}>
                    {{$_pack}}
                </option>
              {{/foreach}}
          </select>
        {{else}}
          <select name="graph_pack_id" onchange="this.form.onsubmit();">
            <option value="">&ndash; {{tr}}CSupervisionGraphPack.none{{/tr}}</option>

              {{foreach from=$graph_packs item=_pack}}
                <option value="{{$_pack->_id}}" {{if $_pack->_id == $interv->graph_pack_id}}selected{{/if}}>
                    {{$_pack}}
                </option>
              {{/foreach}}
          </select>
        {{/if}}
        {{if ($interv->graph_pack_id || $interv->graph_pack_sspi_id || $interv->graph_pack_preop_id) && $concentrators !== null}}
            {{mb_include module=patientMonitoring template=inc_select_concentrator type=$type readonly=$readonly}}
        {{/if}}
    </form>
  </legend>

  <table>
    <tbody>
    <tr>
      <td>
          {{assign var=require_right_col value=false}}
          {{foreach from=$graphs item=_graph key=i}}
            {{if $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionInstantData'}}
                {{assign var=require_right_col value=true}}
            {{/if}}
          {{/foreach}}

        <div style="position: relative; margin-top: 2px;" class="supervision" data-type="{{$type}}" data-readonly="{{$readonly}}"
             data-operation_id="{{$interv->_id}}" data-graph_pack_id="{{$graph_pack_id}}">
          <table class="main layout">
            <tr>
              <td id="surveillance_cell-{{$uid_plot}}">
                  {{foreach from=$graphs item=_graph key=i}}
                      {{if $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionGraph'}}
                          {{assign var=_graph_data value=$_graph->_graph_data}}
                        <div id="yaxis_labels_{{$type}}" class="yaxis-labels" style="height:{{$_graph->height}}px; margin-left: 10px;">
                            {{foreach from=$_graph_data.yaxes|@array_reverse item=_yaxis}}
                              <div class="axis" style="color: {{$_yaxis.color}};">
                                  {{$_yaxis.label}}
                                <div class="symbol">{{$_yaxis.symbolChar|smarty:nodefaults}}&nbsp;</div>
                              </div>
                            {{/foreach}}

                            {{if $_graph->display_legend}}
                              <div id="legend-{{$uid_plot}}-{{$i}}" class="graph-legend"
                                   style="position: absolute; bottom: 1px; left: 5px;"></div>
                            {{/if}}
                        </div>
                        <div id="placeholder-{{$uid_plot}}-{{$i}}" style="height:{{$_graph->height}}px; {{if $yaxes_count < 3}}width: 100%;{{else}}width: 99%; margin-left: 15px;{{/if}}"
                             class="graph-placeholder timeline-item" data-graphguid="{{$_graph->getIdentifier()}}"></div>
                        <script>
                            {{assign var=_graph_data value=$_graph->_graph_data}}

                            Main.add(function () {
                              var ph = jQuery("#placeholder-{{$uid_plot}}-{{$i}}");
                              var series = {{$_graph_data.series|@json}};
                              var xaxes = {{$_graph_data.xaxes|@json}};

                              {{if $yaxes_count < 3}}
                                var yaxis_width = $('yaxis_labels_{{$type}}').offsetWidth - 5;
                                var placeholder_width = (ph[0].offsetWidth - {{$left_col_width}}) + yaxis_width;
                                var marginLeft = {{$left_col_width}} -yaxis_width;

                                ph[0].setStyle({width: placeholder_width + "px", marginLeft: marginLeft + 5 + 'px'});
                                $('yaxis_labels_{{$type}}').setStyle({marginLeft: marginLeft + 5 + 'px'});
                              {{/if}}

                              xaxes[0].ticks = 15;
                              xaxes[0].tickFormatter = SurveillancePerop.xTickFormatter;

                              ph.bind("plothover", SurveillancePerop.plothover);

                                {{if !$readonly}}
                              ph.bind("plotclick", function (event, pos, item) {
                                if (!item) {
                                  return;
                                }

                                var data = item.series.data[item.dataIndex];
                                var element_main = ph[0];
                                SurveillancePerop.editObservationResultSet(data.set_id, '{{$pack->_id}}', data.result_id, null, element_main, '{{$type}}', '{{$interv->_id}}');
                              });
                                {{/if}}

                              var options = {
                                grid:   {
                                  hoverable:       true,
                                    {{if !$readonly}}
                                  clickable:       true,
                                    {{/if}}
                                  markings:        [
                                    {
                                      xaxis: {
                                        from: {{$time_debut_op}},
                                        to: {{$time_fin_op}}
                                      },
                                      color: "rgba(213,221,246,.4)"
                                    }
                                  ],
                                  legend:          {
                                    margin: 2
                                  },
                                  margin:          0,
                                  tickMargin:      0,
                                  labelMargin:     2,
                                  minBorderMargin: 0,
                                  borderWidth:     1
                                },
                                legend: {
                                  show: {{$_graph->display_legend|ternary:'true':'false'}},
                                  container: "#legend-{{$uid_plot}}-{{$i}}",
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
                                {{if $_graph->automatic_protocol == 'Kheops-Concentrator' || $_graph->automatic_protocol == 'MD-Stream'}}
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
                    <td style="padding: 0; border: none;" class="timing-container">
                      <div class="now opacity-50 now-indicator"
                           data-min="{{$time_min}}" data-max="{{$time_max}}"
                           style="display: none;">
                        <div class="marking"></div>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>

                {{if $require_right_col}}
                  <td style="width: 200px; padding-left: 3px;">
                      {{foreach from=$graphs item=_graph key=i}}
                          {{if $_graph|instanceof:'Ox\Mediboard\MonitoringPatient\CSupervisionInstantData'}}
                            <div id="container-{{$_graph->_guid}}">
                                {{$_graph->_ref_value_type}}

                              <div style="line-height: 1; color: #{{$_graph->color}};">
                                  <span style="font-size: {{$_graph->size}}px;" class="supervision-instant-data"
                                        data-value_type_id="{{$_graph->value_type_id}}"
                                        data-value_unit_id="{{$_graph->value_unit_id}}">
                                    -
                                  </span>
                                <span style="font-size: 1.2em">{{$_graph->_ref_value_unit->label}}</span>
                              </div>
                            </div>
                            <hr/>
                          {{/if}}
                      {{/foreach}}
                  </td>
                {{/if}}
            </tr>
          </table>
        </div>
      </td>
    </tr>
    </tbody>
    <thead class="me-bg-transparent" style="background-color: white; z-index: 11; border-spacing: 0;">
    <tr>
      <td>
        <table class="main">
          <tr>
            <td>
              {{if "patientMonitoring"|module_active}}
                <div class="small-info">
                  {{tr}}CSupervisionGraphToPack-Any decision must be made on the basis of the information given by the medical devices{{/tr}}
                </div>
              {{/if}}

              {{if ($type == "preop" && $interv->entree_salle) || ($type == "perop" && $interv->sortie_salle) || $readonly}}
                <div class="small-warning">
                  {{if $type == "preop" && $interv->entree_salle}}
                    <strong>
                      {{tr var1=$interv->_ref_salle->_view}}CSupervisionGraphToPack-msg-Please note that you are in room %s and the entrance to the room is already noted{{/tr}}
                    </strong>
                  {{elseif $type == "perop" && $interv->sortie_salle}}
                    <strong>
                      {{tr var1=$interv->_ref_salle->_view}}CSupervisionGraphToPack-msg-Please note that you are in room %s and the exit to the room is already noted{{/tr}}
                    </strong>
                  {{/if}}

                  {{if $readonly}}
                    <div>
                      {{tr}}CSupervisionGraphToPack-msg-This supervision graph is locked{{/tr}}
                    </div>
                  {{/if}}
                </div>
              {{/if}}
            </td>
            <td class="me-valign-middle">
              <fieldset class="me-padding-0 me-margin-0 me-no-box-shadow">
                <div>
                  {{assign var=show_all_datas_surveillance_timeline value=$app->user_prefs.show_all_datas_surveillance_timeline}}
                  <!-- pref user -->
                  <form name="editPrefGeste" method="post" onsubmit="onSubmitFormAjax(this, reloadSurveillance['{{$type}}']);">
                    <input type="hidden" name="m" value="admin"/>
                    <input type="hidden" name="dosql" value="do_preference_aed"/>
                    <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
                    <input type="hidden" name="pref[show_all_datas_surveillance_timeline]"
                           value="{{$show_all_datas_surveillance_timeline}}" onchange="this.form.onsubmit();"/>
                    <label>
                      <input type="checkbox" {{if $show_all_datas_surveillance_timeline}}checked{{/if}}
                             onclick="$V(this.form.elements['pref[show_all_datas_surveillance_timeline]'], this.checked ? 1 : 0)"/>
                      <span
                        title="{{tr}}pref-show_all_datas_surveillance_timeline-desc{{/tr}}">{{tr}}pref-show_all_datas_surveillance_timeline{{/tr}}</span>
                    </label>
                  </form>
                </div>
              </fieldset>
            </td>
          </tr>
        </table>

        <span style="float: right;">
          <button class="timeline-action notext not-printable me-tertiary me-dark fas fa-eye{{if $hide_infos}}-slash{{/if}}"
                  data-action="hide-infos" data-hide_infos="{{$hide_infos}}"
                  title="{{tr}}CPrescription-action-{{if $hide_infos}}Condensed{{else}}Detailed{{/if}} view of prescription lines{{/tr}}">
          </button>
          <button class="timeline-action not-printable me-tertiary" data-action="center"
                  title="{{tr}}CPrescription-action-Center the graph at the current date and time{{/tr}}">
            <i class="fas fa-bullseye me-padding-left-0" style="font-size: 1.4em; padding-left: 2px;" data-action="center"></i>
          </button>
          <button class="timeline-action left notext not-printable me-tertiary me-dark" data-action="move-left"></button>
          <button class="timeline-action right notext not-printable me-tertiary me-dark" data-action="move-right"></button>
          <button class="timeline-action zoom-in notext not-printable me-tertiary me-dark" data-action="zoom-in"></button>
          <button class="timeline-action zoom-out notext not-printable me-tertiary me-dark" data-action="zoom-out"></button>
          <button class="timeline-action undo notext not-printable me-tertiary me-dark" data-action="reset"
                  title="{{tr}}CPrescription-action-Refocus the display on the intervention{{/tr}}"></button>
          </span>

        {{if ($type == "perop" && $interv->graph_pack_id) || ($type == "sspi" && $interv->graph_pack_sspi_id) ||
             ($type == "preop" && $interv->graph_pack_preop_id) || ("maternite"|module_active && $interv->_ref_sejour->grossesse_id)}}
          <button class="new not-printable me-primary"
                  onclick="SurveillancePerop.createObservationResultSet(null, '{{$interv->_guid}}', '{{$pack->_id}}', null, 1, '{{$type}}')" {{if $readonly}} disabled {{/if}}>
            {{tr}}CObservationResultSet-title-create{{/tr}}
          </button>
        {{/if}}

        <button class="not-printable me-primary"
                onclick="SurveillancePerop.showProtocolesGestesPerop(this.up('.surveillance-timeline-container'), '{{$interv->_id}}', '{{$type}}');"
                {{if $readonly}}disabled{{/if}}>
          <i class="fas fa-tablets me-margin-right-4"></i>{{tr}}CProtocoleGestePerop-action-Perop gesture protocol|pl{{/tr}}
        </button>

        {{if !$readonly && $can_prescribe}}
          {{if $prescription_installed}}
            <button class="new not-printable me-secondary"
                    onclick="SurveillancePerop.modeGrillePerop({{$interv->_id}}, null, null, null, null, null, this.up('.surveillance-timeline-container'));">
              {{tr}}CPrescription.mode_grille_perop{{/tr}}
            </button>
          {{/if}}
        {{/if}}

        {{if $prescription_installed}}
          {{if $can_adm}}
            <button class="injection not-printable me-primary"
                    onclick="SurveillancePerop.editPeropAdministration('{{$interv->_id}}', this.up('.surveillance-timeline-container'), '', '', '{{$type}}')" {{if $readonly}} disabled {{/if}}>
              {{tr}}CMediusers_administer{{/tr}}
            </button>
          {{/if}}
        {{/if}}

        {{if !$readonly && $can_prescribe}}
          <button class="not-printable me-primary"
                  onclick="SurveillancePerop.showProtocolesPerop('{{$interv->_id}}', '{{$prescription_id}}', this.up('.timeline-container-{{$type}}'), '{{$type}}');">
            <i class="fas fa-pills fa-lg me-margin-right-4"></i> {{tr}}CPrescription-back-protocoles_element{{/tr}}
          </button>
        {{/if}}

          {{if $prescription_installed && !$readonly && $can_prescribe}}
            <!-- Formulaire d'ajout de prescription -->
            <form action="?" method="post" name="addPrescriptionSurvPerop" onsubmit="return checkForm(this);">
              <input type="hidden" name="m" value="dPprescription"/>
              <input type="hidden" name="dosql" value="do_prescription_aed"/>
              <input type="hidden" name="del" value="0"/>
              <input type="hidden" name="prescription_id" value=""/>
              <input type="hidden" name="object_id" value="{{$interv->sejour_id}}"/>
              <input type="hidden" name="object_class" value="CSejour"/>
              <input type="hidden" name="type" value="sejour"/>
              <input type="hidden" name="callback" value="SurveillancePerop.updateFormLineSurvPerop"/>
            </form>

            <form name="saveAnesthOp" method="post">
              {{mb_class object=$interv}}
              {{mb_key object=$interv}}
              {{mb_field object=$interv field=anesth_id hidden=true}}
            </form>

            <form name="applyProtocoleSurvPerop" method="get" action="?"
                  onsubmit="if(!this.prescription_id.value){ return onSubmitFormAjax(getForm('addPrescriptionSurvPerop'))} else { return SurveillancePerop.submitProtocoleSurvPerop(this.up('fieldset')) }">
              <input type="hidden" name="prescription_id" value="{{$prescription_id}}" onchange="this.form.onsubmit();"/>

                {{if $app->_ref_user->isPraticien() || $app->_ref_user->isSageFemme()}}
                  <input type="hidden" name="praticien_id" value="{{$app->user_id}}"/>
                {{else}}
                  <strong>{{tr}}common-Practitioner{{/tr}}</strong>
                  <select name="praticien_id" onchange="Prescription.saveAnesthOp(this.value);">
                      {{foreach from=$listAnesths item=_anesth}}
                        <option value="{{$_anesth->_id}}"
                                {{if $interv->anesth_id == $_anesth->_id || (!$interv->anesth_id && $interv->_ref_plageop->anesth_id == $_anesth->_id)}}selected{{/if}}>
                            {{$_anesth}}
                        </option>
                      {{/foreach}}
                  </select>
                {{/if}}

              <input type="hidden" name="pratSel_id" value=""/>
              <input type="hidden" name="pack_protocole_id" value=""/>
              <input type="hidden" name="advanced_prot" value="1"/>
              <input type="hidden" name="perop" value="1"/>
              <input type="text" name="libelle_protocole" value="&mdash; Choisir un protocole" class="autocomplete"
                     style="font-weight: bold; font-size: 1.3em; width: 300px;"/>
              <div style="display:none; width: 350px;" class="autocomplete" id="protocole_surv_perop_auto_complete_{{$type}}"></div>
              <input type="hidden" name="operation_id" value="{{$interv->_id}}"/>
            </form>

            {{if $app->_ref_user->isPraticien()}}
              <button type="button" class="tick me-tertiary" onclick="Prescription.valideLines('{{$prescription_id}}', '{{$app->user_id}}', 1, '{{$type}}');">
                {{tr}}common-action-Sign all{{/tr}}
              </button>
            {{/if}}
          {{/if}}

        <div id="lock-surveillance-modal-{{$type}}" style="display: none;">
          <form name="lock-surveillance-{{$type}}" method="post" action="?m=system&a=ajax_password_action"
                onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close, useFormAction: true})">
            <input type="hidden" name="callback" value="lockSurveillance_{{$type}}.curry({{$interv->_id}},{{$readonly|ternary:1:0}})"/>
            <input type="hidden" name="user_id" class="notNull" value="{{$app->_ref_user->_id}}"/>
            <table class="form">
              <tr>
                <th class="title" colspan="2">
                  {{tr}}COperation-msg-Confirm {{if $readonly}}un{{/if}}lock graph {{$type}}{{/tr}}
                </th>
              </tr>
              <tr>
                <th>{{tr}}CSejour-_nomPraticien{{/tr}}</th>
                <td>
                  <input type="text" name="_user_view" class="autocomplete" value="{{$app->_ref_user}}"/>
                  <script>
                    Main.add(function () {
                      var form = getForm("lock-surveillance-{{$type}}");
                      new Url("mediusers", "ajax_users_autocomplete")
                        .addParam("input_field", form._user_view.name)
                        .addParam("praticiens", 1)
                        .autoComplete(form._user_view, null, {
                          minChars:           0,
                          method:             "get",
                          select:             "view",
                          dropdown:           true,
                          width:              '200px',
                          afterUpdateElement: function (field, selected) {
                            $V(form._user_view, selected.down('.view').innerHTML);
                            var id = selected.getAttribute("id").split("-")[2];
                            $V(form.user_id, id);
                          }
                        });
                    });
                  </script>
                </td>
              </tr>
              <tr>
                <th>
                  <label for="user_password">{{tr}}Password{{/tr}}</label>
                </th>
                <td>
                  <input type="password" name="user_password" class="notNull password str"/>
                </td>
              </tr>
              <tr>
                <td colspan="2" class="button">
                  <button type="submit" class="tick singleclick not-printable me-secondary">{{tr}}Validate{{/tr}}</button>
                  <button type="button" class="cancel singleclick not-printable me-tertiary"
                          onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
                </td>
              </tr>
            </table>
          </form>
        </div>

        {{if ("monitoringMaternite"|module_active || "monitoringMaternite general active_graph_supervision"|gconf) && $interv->_ref_sejour->grossesse_id}}
          {{me_button icon=print label="CSupervisionGraphToPack-action-Print monitoring" onclick="SurveillancePerop.printSurveillance('`$interv->_id`')"}}

          {{me_button icon=print label="common-Voucher|pl" onclick="PlanSoins.printBons('`$prescription_id`', null, 1, '`$interv->_id`')"}}

          {{me_dropdown_button button_icon=print button_label=Print button_class="me-tertiary me-dark not-printable"
          container_class="me-dropdown-button-left"}}
        {{/if}}

        {{if !$readonly}}
          <button class="lock not-printable me-tertiary"
                  onclick="Modal.open('lock-surveillance-modal-{{$type}}', {width: '600px', height: '200px'});">
            {{tr}}common-action-Lock{{/tr}}
          </button>
        {{/if}}

        <button class="search not-printable me-tertiary me-dark"
                onclick="SurveillancePerop.showLegend();">
          {{tr}}common-Legend{{/tr}}
        </button>
      </td>
    </tr>
    {{if "maternite"|module_active && $interv->_ref_sejour->grossesse_id}}
      {{assign var=_grossesse value=$interv->_ref_sejour->loadRefGrossesse()}}
      {{assign var=dossier    value=$_grossesse->loadRefDossierPerinat()}}
      <tr>
        <td>
         <form name="save_facteur_risque_{{$dossier->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
            {{mb_key   object=$dossier}}
            {{mb_class object=$dossier}}

            <table class="main" style="width: 100%;">
              <tr>
                <th style="width: 100px; vertical-align:middle;">{{mb_label object=$dossier field=facteur_risque}}</th>
                <td>
                  {{if !$print}}
                    {{mb_field object=$dossier field=facteur_risque rows=1 onchange="this.form.onsubmit();" form=save_facteur_risque_`$dossier->_guid`
                    aidesaisie="validateOnBlur: 0"}}
                  {{else}}
                    {{mb_value object=$dossier field=facteur_risque}}
                  {{/if}}
                </td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    {{/if}}
    </thead>
  </table>
</fieldset>
