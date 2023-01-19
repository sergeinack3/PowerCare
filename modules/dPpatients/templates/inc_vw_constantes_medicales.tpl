{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=view value='full'}}
{{mb_default var=iframe value=false}}
{{mb_script module=patients script=constants_graph ajax=true}}

<script>
  const_ids = {{$const_ids|@json}};
  keys_selection = {{$custom_selection|@array_keys|@json}};
  paginate = {{$paginate|@json}};

  newConstants = function (context_guid) {
    window.oGraphs.getHiddenGraphs();
    var url = new Url('patients', 'httpreq_vw_form_constantes_medicales');
    url.addParam("context_guid", context_guid);
    url.addParam("selection[]", keys_selection);
    url.addParam("patient_id", '{{$patient->_id}}');
    url.addParam('unique_id', '{{$unique_id}}');
    url.requestUpdate('constantes-medicales-form', {
      onComplete: function () {
        window.oGraphs.initCheckboxes();
      }
    });
  };

  editConstants = function (const_id, context_guid, start) {
    window.oGraphs.getHiddenGraphs();
    var url = new Url('patients', 'httpreq_vw_form_constantes_medicales');
    url.addParam("const_id", const_id);
    url.addParam("context_guid", context_guid);
    url.addParam("start", start || 0);
    url.addParam("selection[]", keys_selection);
    url.addParam("patient_id", '{{$patient->_id}}');
    url.addParam('unique_id', '{{$unique_id}}');
    url.requestUpdate('constantes-medicales-form', {
      onComplete: function () {
        window.oGraphs.initCheckboxes();
      }
    });
  };

  toggleConstantTable = function () {
    $('constantes-table').down('.constantes-horizontal').toggle();
    $('constantes-table').down('.constantes-vertical').toggle();
    if ($('constantes-table').down('.constantes-horizontal').visible()) {
      fixConstantsTableHeaders();
    } else {
      fixConstantsTableVertHeader();
    }
  };

  Main.add(function () {
    {{if $iframe}}
    refreshConstantesMedicales{{$unique_id}} = window.parent.refreshConstantesMedicales{{$unique_id}};
    {{/if}}

    var graphs_data = {{$graphs_data|@json}};
    window.oGraphs = new ConstantsGraph(
      graphs_data,
      {{$min_x_index}},
      {{$min_x_value}},
      false,
      '{{$context_guid}}',
      '{{$display.mode}}',
      {{if $display.time}}{{$display.time}}{{else}}null{{/if}},
      {{$hidden_graphs|@json}},
      {{$graphs_structure|@json}}
      );
    window.oGraphs.draw();

    if (window.tabsConsult || window.tabsConsultAnesth) {
      Control.Tabs.setTabCount("constantes-medicales", '{{$total_constantes}}');
    }

    {{if !$print}}
    {{assign var=_context value=$context}}
    {{assign var=_readonly value=false}}
    {{if !$_context || $context->_guid != $context_guid}}
    {{assign var=_readonly value=true}}
    {{/if}}
    {{if !$_context}}
    {{assign var=_context value=$patient}}
    {{/if}}

    Control.Tabs.create("surveillance-tab", true, {
      afterChange: function (container) {
        switch (container.id) {
          case "tab-constantes-medicales":
            break;

          case "tab-ex_class":
            ExObject.loadExObjects(
              '{{$_context->_class}}',
              '{{$_context->_id}}',
              'ex_class-list',
              0,
              null,
              {
                readonly: {{$_readonly|ternary:1:0}},
                creation_context_class: "{{$current_context->_class}}",
                creation_context_id:    "{{$current_context->_id}}"

                {{if $_context|instanceof:'Ox\Mediboard\Patients\CPatient'}}
                ,
                cross_context_class: "{{$_context->_class}}",
                cross_context_id:    "{{$_context->_id}}"
                {{/if}}
              }
            );
            break;

          case "tab-fiches":
          {{if $context|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
            refreshFiches('{{$context->_id}}');
          {{/if}}
            break;
        }
      }
    });

    var tabs_graph = Control.Tabs.create("tabs-constantes-graph-table", true, {
      afterChange: function (container) {
        switch (container.id) {
          case 'constantes-table':
            if ($('constantes-table').down('.constantes-horizontal').visible()) {
              fixConstantsTableHeaders();
            } else {
              fixConstantsTableVertHeader();
            }
            break;
          default:
        }
      }
    });
    {{if $app->user_prefs.constantes_show_view_tableau || $view == 'pmsi'}}
    tabs_graph.setActiveTab('constantes-table');
    {{/if}}

    var header_constants = $('header-constants');
    var content_dossier = $('content-dossier-soins');
    var tab_constants = $('tab-constantes-medicales');

    {{if $mode_pdf}}
    if (!content_dossier) {
      content_dossier = header_constants.up();
      content_dossier.setStyle({height: "700px"});
    }
    tab_constants.setStyle({height: "100%"});
    {{else}}
    if (!content_dossier) {
      content_dossier = header_constants.up();
      ViewPort.SetAvlHeight(content_dossier, 1.0);
    }
    tab_constants.setStyle({height: (content_dossier.getHeight() - header_constants.getHeight()) + 'px'});
    {{/if}}

    {{/if}}

    Calendar.regField(getForm('formFilterConstants').date_min);
    Calendar.regField(getForm('formFilterConstants').date_max);
  });

  loadConstantesMedicales = function (context_guid) {
    var url = new Url("patients", "httpreq_vw_constantes_medicales"),
      container = $("constantes-medicales") || $("constantes") || $("Constantes") || $("constantes-tri"); // case sensitive ?

    url.addParam("context_guid", '{{$context_guid}}');
    url.addParam("patient_id", '{{$patient->_id}}');
    url.addParam("selection[]", keys_selection);
    url.addParam("selected_context_guid", context_guid);
    url.addParam('can_select_context', '{{$can_select_context}}');
    url.addParam('view', '{{$view}}');
    url.addParam('date_min', $V(getForm('formFilterConstants').date_min));
    url.addParam('date_max', $V(getForm('formFilterConstants').date_max));
    url.addParam("paginate", window.paginate || 0);
    url.addParam("count", $V($('count_constantes')));
    if (window.oGraphs) {
      url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
    }
    url.requestUpdate(container);
  };

  refreshFiches = function (sejour_id, tab) {
    var url = new Url("soins", "ajax_vw_fiches");
    url.addParam("sejour_id", sejour_id);
    if (tab) {
      url.addParam('selected_tab', tab);
    }
    url.requestUpdate("tab-fiches");
  };

  toggleForm = function () {
    var form = $('constantes-medicales-form');
    var graph = $('constantes-medicales-graphs');

    form.toggle();
    if (form.visible()) {
      graph.setStyle({width: '70%', left: '30%'});
    } else {
      graph.setStyle({width: '100%', left: '0px'});
    }
  };
</script>

<div id="header-constants">
  <table class="tbl me-no-box-shadow me-margin-0 me-padding-0 me-no-border me-dossier-soin-sel-sej"
         {{if $print || $view == 'simple'}}style="display: none;"{{/if}}>
    <tr>
      <th colspan="10" class="title me-no-title me-padding-0 me-bg-white me-color-black-high-emphasis">
        Surveillance dans le cadre de:
        <br class="me-no-display" />
        {{if !$can_select_context}}
          <input type="hidden" id="select_context" name="context" value="{{$context->_guid}}" />
          {{$context}}
        {{else}}
          <select id="select_context" name="context" onchange="loadConstantesMedicales($V(this));">
            <option value="all" {{if $all_contexts}} selected {{/if}}>{{tr}}soins-All contexts{{/tr}}</option>
            {{foreach from=$list_contexts item=curr_context}}
              <option value="{{$curr_context->_guid}}"
                      {{if !$all_contexts && $curr_context->_guid == $context->_guid}}selected{{/if}}
                {{if !$all_contexts && $curr_context->_guid == $context_guid}}style="font-weight:bold;"{{/if}}
              >
                {{if !$all_contexts && $curr_context->_guid == $context_guid}}&rArr;{{/if}}
                {{$curr_context}}
              </option>
            {{/foreach}}
          </select>
        {{/if}}
      </th>
    </tr>
  </table>

  {{if $print}}
  <div id="constantes-medicales-graph" style="text-align: center;"></div>
</div>
{{else}}


{{* Contexte de la page  : $current_context *}}
{{* Contexte sélectionné : $_context *}}

  <ul class="control_tabs me-small me-margin-top-4" id="surveillance-tab">
    <li>
      <a href="#tab-constantes-medicales">Constantes</a>
    </li>

    {{if $view != 'simple'}}
      {{if "forms"|module_active}}
        <li>
          <a href="#tab-ex_class">{{tr}}CExClass|pl{{/tr}}</a>
        </li>
      {{/if}}

      {{if $context|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
        <li>
          <a href="#tab-fiches">Fiches</a>
        </li>
      {{/if}}
    {{/if}}
  </ul>
  </div>

  <div id="tab-constantes-medicales" style="position: relative;">
    {{if $view != 'pmsi'}}
      <div id="constantes-medicales-form" style="position: absolute; top: 0px; left: 0px; width: 30%; height: 100%;">
        {{mb_include module=patients template=inc_form_edit_constantes_medicales context_guid=$context_guid}}
      </div>
    {{/if}}
    <div id="constantes-medicales-graphs"
         style="position: absolute; top: 0px;{{if $view == 'pmsi'}} left: 0%; width: 100%;{{else}} left: 30%; width: 70%;{{/if}} height: 100%; overflow-y: auto;">
      {{unique_id var=uniq_id_constantes}}

      {{* {{if $paginate}}
        {{mb_include module=system template=inc_pagination total=$total_constantes current=$start change_page="changePage$uniq_id_constantes" step=$count}}
      {{/if}} *}}

      <ul class="control_tabs small me-no-border-radius me-tabs-constantes-graph" id="tabs-constantes-graph-table">
        <li><a class="me-no-border-radius" href="#constantes-graph">Graphiques</a></li>
        <li><a href="#constantes-table">Tableau</a></li>
        <li><a class="me-no-border-radius" href="#glycemie">{{tr}}CConstantesMedicales-_glycemie{{/tr}}</a></li>
        <li class="me-dossier-soin-tabs-filter me-tabs-buttons me-small-fields">
          <form action="?" method="post" name="formFilterConstants" onsubmit="return false;">
            {{if $paginate}}
              <label>
                Afficher les
                <select id="count_constantes" name="count_constantes" onchange="loadConstantesMedicales($V($('select_context')));"
                        style="margin: 0;">
                  {{assign var=_counts value=","|@explode:"50,100,200,500"}}
                  {{foreach from=$_counts item=_count}}
                    <option value="{{$_count}}" {{if $count == $_count}} selected {{/if}}>{{$_count}}</option>
                  {{/foreach}}
                </select>
                derniers ({{$total_constantes}} au total)
              </label>
            {{/if}}
            <label for="date_min">{{tr}}date.From{{/tr}}
              <input type="hidden" class="dateTime" name="date_min" value="{{$date_min}}" />
            </label>
            <label for="date_max">{{tr}}date.to{{/tr}}
              <input type="hidden" class="dateTime" name="date_max" value="{{$date_max}}" />
            </label>
            <button type="button" class="tick notext"
                    onclick="loadConstantesMedicales($V($('select_context')));">{{tr}}Filter{{/tr}}</button>
          </form>
        </li>
      </ul>

      {{if $const_ids|@count == $count}}
        <div class="small-warning">
          Le nombre de constantes affichées est limité à {{$count}}.
        </div>
      {{/if}}

      <div id="constantes-graph" class="me-constantes-graph me-bg-transparent" style="min-height: 290px;">
        {{if $view != 'pmsi'}}
          <button class="hslip notext me-tertiary me-dark" style="float: left;" onclick="toggleForm();" type="button">
            Afficher/Cacher le formulaire
          </button>
        {{/if}}

        <button id="constantes-medicales-graph-before" class="left me-tertiary" style="float: left;" onclick="window.oGraphs.shift('before');">
          Avant
        </button>
        <button id="constantes-medicales-graph-after" class="right rtl me-tertiary" style="float: right;" onclick="window.oGraphs.shift('after');">
          Après
        </button>
        <br />

        <div id="graphs" style="clear: both;">
          {{foreach from=$graphs_data key=_rank item=_graphs_for_rank}}
            {{foreach from=$_graphs_for_rank key=_graph_id item=_graph}}
              <div id="graph_row_{{$_rank}}_{{$_graph_id}}" style="display: inline-block;">
                <table class="layout">
                  <tr>
                    <td>
                      <p style="text-align: center"><strong>{{$_graph.title}}</strong></p>
                      <div id="placeholder_{{$_rank}}_{{$_graph_id}}"
                           style="width: {{$_graph.width}}px; height: 175px; margin-bottom: 5px; margin-left: {{$_graph.margin_left}}px;"></div>
                    </td>
                    <td style="padding-top: 1.2em; width: 10em">
                      <div id="legend_{{$_rank}}_{{$_graph_id}}"></div>
                    </td>
                  </tr>
                </table>
              </div>
            {{/foreach}}
          {{/foreach}}
        </div>
      </div>

      <div id="constantes-table" class="me-constantes-table me-bg-transparent" style="display: none; text-align: left;">
        <button class="change" onclick="toggleConstantTable();">
          Changer l'orientation
        </button>

        <div
          class="constantes-horizontal"{{if $app->user_prefs.constants_table_orientation != 'horizontal'}} style="display: none;"{{/if}}>
          {{mb_include module=patients template=print_constantes fixed_header=1 view='view_constantes'}}
        </div>
        <div class="constantes-vertical"
             style="vertical-align: top;{{if $app->user_prefs.constants_table_orientation != 'vertical'}} display: none;{{/if}}">
          {{mb_include module=patients template=print_constantes_vert fixed_header=1 view='view_constantes'}}
        </div>
      </div>

      <div id="glycemie" class="me-constantes-table me-bg-transparent">
        {{mb_include module=patients template=print_followup_glycemie}}
      </div>
    </div>
  </div>

  <div id="tab-ex_class" style="display: none;">
    <div id="ex_class-list" class="x-scroll"></div>
    {{if $context->_class == "CSejour"}}
      <br />
      <button type="button" class="search"
              onclick="ExObject.searchForms('CSejour', '{{$context->_id}}', '{{$context->entree|iso_date}}', '{{$context->sortie|iso_date}}');">
        Recherche avancée
      </button>
    {{/if}}

      {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
          {{mb_include module=appFineClient template=inc_show_sas_tabs tabs="order" patient=$context->_ref_patient count=$count_order}}
      {{/if}}
  </div>
  <div id="tab-fiches" style="display: none;"></div>
{{/if}}
