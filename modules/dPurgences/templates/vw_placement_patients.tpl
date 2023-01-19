{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences script=drag_patient}}
{{mb_script module=urgences script=urgences}}
{{mb_script module=hospi    script=etat_des_lits}}
{{mb_script module=hospi    script=info_group}}
{{if $isImedsInstalled}}
  {{mb_script module=dPImeds script=Imeds_results_watcher}}
{{/if}}

{{if "maternite"|module_active}}
  {{mb_script module=urgences script=avis_maternite}}
{{/if}}

<script>
  Main.add(function() {
    Rafraichissement.start();
    PairEffect.initGroup("serviceEffect");
    Rafraichissement.tabs = Control.Tabs.create('tabs-urgences', true, {
      afterChange: function(container) {
        $('etat-des-lits-displayer').stopObserving('click')
          .observe('click', function() {EtatDesLits.showModale(container.id, '{{$date}}')});
        if (container.id === 'information_service') {
          InfoGroup.refreshInfoServices();
        }
      }
    });
    conf_resa = '{{"dPurgences Placement use_reservation_box"|gconf}}';

    {{if $isImedsInstalled}}
      ImedsResultsWatcher.loadResults();
    {{/if}}

    {{if $services_id}}
      {{if $services_id.uhcd}}
        EtatDesLits.serviceUhcd = {{$services_id.uhcd}};
      {{/if}}
      {{if $services_id.urgences}}
        EtatDesLits.serviceUrgences = {{$services_id.urgences}};
        InfoGroup.listInfoServices('information_service', '{{$date|date_format:"%Y-%m-%d"}}', '{{$services_id.urgences}}');
      {{/if}}
    {{/if}}
  });

  if (window.AvisMaternite) {
    AvisMaternite.start(3, {{$avis_maternite_refresh_frequency}});
  }

  Rafraichissement = {
    delay: {{$conf.dPurgences.vue_topo_refresh_frequency}},
    handler_init: null,
    tabs: null,

    init: function() {
      new Url("urgences", "patientsPlacementView", "tab").redirect();
    },

    start: function() {
      this.handler_init = this.init.delay(this.delay);
    },

    refreshSejour: function(sejour_id) {
      var div = $("placement_" + sejour_id);
      // On réinitialise l'infobulle
      delete div.oTooltip;
      new Url("urgences", "patientsPlacementView")
        .addParam("sejour_id", sejour_id)
        .addParam("name_grille", div.get("name_grille"))
        .addParam("zone_id", div.get("zone_id"))
        .requestUpdate(div);
    }
  };
</script>

<ul id="tabs-urgences" class="control_tabs">
 {{assign var=superposition_service value="dPurgences Placement superposition_service"|gconf}}
 {{foreach from=$grilles item=_grille key=_name_grille}}
    <li>
      <a href="#{{$_name_grille}}"
      {{if $superposition_service || $_name_grille == "uhcd"}}
        title="{{tr var1=$lits_occupe.$_name_grille}}
          CRPU-%s patient placed at the current time in {{if $_name_grille == "uhcd"}}UHCD{{else}}Emergency{{/if}}|pl
        {{/tr}}"
      {{else}}
      title="{{tr var1=$lits_occupe.$_name_grille var2=$name_services.$_name_grille}}
        CRPU-%s patient placed at the current time in Emergency %s|pl
        {{/tr}}"
      {{/if}}>
      {{tr}}{{if $_name_grille == "uhcd"}}CRPU-_UHCD{{else}}Emergency{{/if}}{{/tr}}
      {{if !$superposition_service && $_name_grille != "uhcd"}} - {{$name_services.$_name_grille}}
       {{/if}}({{$lits_occupe.$_name_grille}})
      </a>
    </li>
 {{/foreach}}

  <li><a href="#information_service" class="empty">{{tr}}CInfoGroup-Service Information{{/tr}}</a></li>

  {{if "maternite"|module_active}}
    <li><a href="#avis_maternite" class="empty">{{tr}}CRPU-Avis maternite{{/tr}} <small>(&ndash;)</small></a></li>
  {{/if}}

  <li style="width: 20em; text-align: center">
    <strong><big>{{$date|date_format:$conf.longdate}}</big></strong>

    <form action="#" name="changeDate" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
    </form>
  </li>
  <button type="button" class="search me-margin-top-8" onclick="legendPlacement()" style="float: right;">{{tr}}Legende{{/tr}}</button>

  <a class="button new me-margin-top-8" href="#1" style="float: right;" onclick="Urgences.pecInf()">
    {{tr}}CRPU-title-create{{/tr}}
  </a>
  <button id="etat-des-lits-displayer" type="button" class="search me-margin-top-8" style="float: right">
    {{tr}}mod-dPhospi-tab-vw_recherche{{/tr}}
  </button>
</ul>

{{foreach from=$grilles item=_grille key=name_grille}}
  <div id="{{$name_grille}}" style="display: none;" class='vue_topologique'>
    {{mb_include module=dPurgences template=inc_vw_plan_urgences button_name="button_$name_grille"}}
  </div>
{{/foreach}}

<div id="information_service" style="display: none;">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>

{{if "maternite"|module_active}}
  <div id="avis_maternite" style="display:none" class="me-padding-0">
    <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
  </div>
{{/if}}
