{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$group->service_urgences_id}}
  <div class="small-warning">{{tr}}dPurgences-no-service_urgences_id{{/tr}}</div>
  {{mb_return}}
{{/if}}

{{mb_script module=urgences   script=main_courante}}
{{mb_script module=urgences   script=uhcd}}
{{mb_script module=urgences   script=imagerie}}
{{mb_script module=urgences   script=urgences}}
{{mb_script module=admissions script=identito_vigilance}}
{{mb_script module=patients   script=pat_selector}}
{{mb_script module=sante400   script=Idex}}
{{mb_script module=hospi      script=info_group}}

{{if $isImedsInstalled}}
  {{mb_script module=Imeds script=Imeds_results_watcher}}
{{/if}}

{{if "maternite"|module_active}}
  {{mb_script module=urgences script=avis_maternite}}
{{/if}}

{{assign var=imagerie_etendue value="dPurgences CRPU imagerie_etendue"|gconf}}

<script>
  Consultations = {
    updater: null,
    start: function(frequency) {
      if (isNaN(frequency)) {
        frequency = 100;
      }
      var url = new Url("cabinet", "vw_journee");
      url.addParam("date", "{{$date}}");
      url.addParam("mode_urgence", true);
      Consultations.updater = url.periodicalUpdate('consultations', {frequency: frequency} );
    },

    stop: function() {
      Consultations.updater.stop();
    }
  };

  onMergeComplete = function() {
    IdentitoVigilance.start(0, 80);
    MainCourante.start(1, 60);
  };

  reloadSynthese = function() {
    window.url_show_synthese.refreshModal();
  };

  showSynthese = function(sejour_id) {
    window.url_show_synthese = new Url("soins", "ajax_vw_suivi_clinique");
    window.url_show_synthese.addParam("sejour_id", sejour_id);
    window.url_show_synthese.requestModal(800);
  };

  Main.add(function () {
    // Delays prevent potential overload with periodical previous updates

    // Main courante
    MainCourante.start(0, {{$main_courante_refresh_frequency}});

    {{if $imagerie_etendue}}
    Imagerie.date = "{{$date}}";
    Imagerie.start(1, {{$uhcd_refresh_frequency}});
    {{/if}}

    // UHCD
    UHCD.date = "{{$date}}";
    UHCD.start(2, {{$uhcd_refresh_frequency}});

    // Reconvocations
    {{if $conf.dPurgences.gerer_reconvoc == "1"}}
      Consultations.start.delay(3, 100);
    {{/if}}

    // Identito-vigilance
    IdentitoVigilance.date = "{{$date}}";
    IdentitoVigilance.start(4, {{$identito_vigilance_refresh_frequency}});

    if (window.AvisMaternite) {
      AvisMaternite.start(5, {{$avis_maternite_refresh_frequency}});
    }

    Urgences.tabs = Control.Tabs.create('tab_main_courante', true);

    {{if $rpu_id}}
      Urgences.pecInf(null, "{{$rpu_id}}");
    {{/if}}

    {{if 'dPurgences CRPU impose_degre_urgence'|gconf}}
      var form = getForm('selView');
      if (form) {
        form.elements['ccmu'].removeClassName('notNull');
      }
      {{/if}}

    {{if $services|@count > 0}}
      {{assign var=service_urgence value=$services|smarty:nodefaults|@reset}}
      InfoGroup.listInfoServices('information_service', '{{$date}}', '{{$service_urgence->_id}}');
      InfoGroup.infoServiceDelay = 90;
    {{/if}}
  });

</script>

<ul id="tab_main_courante" class="control_tabs">
  <li style="float: right">
    <form action="?" name="FindSejour" method="get">
      <label for="sip_barcode" title="{{tr}}CRPU-Please scan the file number on a document or enter it by hand{{/tr}}">
        {{tr}}CRPU-File number{{/tr}}
      </label>

      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <input type="text" size="5" name="sip_barcode" onchange="this.form.submit()" />

      <button type="submit" class="search notext">{{tr}}Search{{/tr}}</button>
    </form>
  </li>
  <li><a href="#holder_main_courante">{{tr}}Main_courante{{/tr}} <small>(&ndash;)</small></a></li>
  {{if $imagerie_etendue}}
    <li><a href="#holder_imagerie" class="empty">{{tr}}CRPU-radio{{/tr}} <small>(&ndash;)</small></a></li>
  {{/if}}
  <li><a href="#holder_uhcd" class="empty">{{tr}}CSejour-UHCD{{/tr}} <small>(&ndash;)</small></a></li>
  {{if $conf.dPurgences.gerer_reconvoc == "1" && "dPurgences CRPU type_sejour"|gconf !== "urg_consult"}}
  <li><a href="#consultations" class="empty">{{tr}}CRPU-reconvoc|pl{{/tr}} <small>(&ndash; / &ndash;)</small></a></li>
  {{/if}}
  <li><a href="#identito_vigilance" class="empty">{{tr}}Identito-vigilance{{/tr}} <small>(&ndash;)</small></a></li>
  <li><a href="#information_service" class="empty">{{tr}}CInfoGroup-Service Information{{/tr}}</a></li>
  {{if "maternite"|module_active}}
    <li><a href="#avis_maternite" class="empty">{{tr}}CRPU-Avis maternite{{/tr}} <small>(&ndash;)</small></a></li>
  {{/if}}
  <li style="width: 20em; text-align: center">
    <script>
    Main.add(function() {
      Calendar.regField(getForm("changeDate").date, null, {noView: true} );
    } );
    </script>
    <strong><big>{{$date|date_format:$conf.longdate}}</big></strong>

    <form name="changeDate" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit();" />
    </form>
  </li>
</ul>

<div id="holder_main_courante" style="display: none;" class="me-padding-0">
  <table style="width: 100%;">
    <tr>
      <td style="white-space: nowrap;" class="narrow">
        <a class="button new" href="#1" onclick="Urgences.pecInf()">
          {{tr}}CRPU-title-create{{/tr}}
        </a>
        {{if "myHug"|module_active}}{{mb_include module="myHug" template="inc_button_set_number_doctor"}}{{/if}}
      </td>

      <td style="text-align: left; padding-left: 2em;">
        {{mb_include template=inc_hide_missing_rpus}}
        {{mb_include template=inc_hide_previous_rpus}}
      </td>

      <td class="me-padding-top-8" style="text-align: right">
        <form name="selView" method="get" action="?g={{$g}}" onsubmit="onSubmitFormAjax(this, {useFormAction: true}, 'main_courante')">
          <input type="hidden" name="m" value="urgences" />
          <input type="hidden" name="a" value="httpreq_vw_main_courante" />

          <div style="display: inline-block">
            {{me_form_field nb_cells=0 label=CService}}
              <select name="service_id" onchange="this.form.onsubmit()" style="width: 15em;">
                <option value="" {{if !$service_id}}selected{{/if}}>&mdash; {{tr}}CService.all{{/tr}}</option>
                {{foreach from=$services item=_service}}
                  <option value="{{$_service->_id}}" {{if $_service->_id == $service_id}}selected{{/if}}>{{$_service}}</option>
                {{/foreach}}
              </select>
            {{/me_form_field}}
          </div>

          <div style="display: inline-block">
            {{me_form_field label=Display}}
              <select name="selAffichage" onchange="this.form.onsubmit();" style="width: 15em;">
                <option value="tous"               {{if $selAffichage == "tous"              }}selected{{/if}}>{{tr}}All{{/tr}}</option>
                <option value="presents"           {{if $selAffichage == "presents"          }}selected{{/if}}>{{tr}}common-Present|pl{{/tr}}</option>
                <option value="prendre_en_charge"  {{if $selAffichage == "prendre_en_charge" }}selected{{/if}}>{{tr}}CRPU-prendre_en_charge{{/tr}}</option>
                <option value="pec"                {{if $selAffichage == "pec" }}selected{{/if}}>{{tr}}CRPU-pec{{/tr}}</option>
                <option value="sortant"            {{if $selAffichage == "sortant" }}selected{{/if}}>{{tr}}CRPU-sortant{{/tr}}</option>
                <option value="sortis"             {{if $selAffichage == "sortis" }}selected{{/if}}>{{tr}}CRPU-sortis{{/tr}}</option>
                <option value="annule_hospitalise" {{if $selAffichage == "annule_hospitalise"}}selected{{/if}}>{{tr}}CRPU-annule_hospitalise{{/tr}}</option>
              </select>
            {{/me_form_field}}
          </div>

          <div style="display: inline-block">
            {{me_form_field label=CRPU.urgentiste}}
              <select name="urgentiste_id" onchange="this.form.onsubmit();" style="width: 15em;">
                <option value="">&mdash; {{tr}}CRPU.all_urgentistes{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_mediuser list=$urgentistes selected=$urgentiste_id}}
              </select>
            {{/me_form_field}}
          </div>

          <div style="display: inline-block">
            {{if "dPurgences CRPU french_triage"|gconf}}
                {{me_form_field mb_class=CRPU mb_field=french_triage}}
                    {{mb_field class=CRPU field=french_triage onchange="this.form.onsubmit()" value=$ccmu emptyLabel="All" style="width: 15em;"}}
                {{/me_form_field}}
            {{elseif !"dPurgences Display display_cimu"|gconf}}
              {{me_form_field mb_class=CRPU mb_field=ccmu}}
                {{mb_field class=CRPU field=ccmu onchange="this.form.onsubmit()" value=$ccmu emptyLabel="All" style="width: 15em;"}}
              {{/me_form_field}}
            {{else}}
              {{me_form_field mb_class=CRPU mb_field=cimu}}
                {{mb_field class=CRPU field=cimu onchange="this.form.onsubmit()" value=$ccmu emptyLabel="All" style="width: 25em;"}}
              {{/me_form_field}}
            {{/if}}
          </div>
        </form>
      </td>
      <td style="text-align: right;">
        <a href="#" onclick="MainCourante.export(getForm('selView'));" class="button upload me-tertiary">{{tr}}Export{{/tr}}</a>
        <a href="#" onclick="MainCourante.print('{{$date}}')" class="button print me-tertiary">{{tr}}Main_courante{{/tr}}</a>
        <a href="#" onclick="MainCourante.legend()" class="button search me-tertiary">{{tr}}Legend{{/tr}}</a>
      </td>
    </tr>
  </table>

  <div id="main_courante"></div>
</div>
{{if $imagerie_etendue}}
  <div id="holder_imagerie" style="display:none" class="me-padding-0">
    <div id="imagerie">
      <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
    </div>
  </div>
{{/if}}

<div id="holder_uhcd" style="display:none" class="me-padding-0">
  <table class="main">
    <tr>
      <td style="text-align: right">
        Affichage
        <form name="UHCD-view" action="" method="post">
          <select name="uhcd_affichage" onChange="UHCD.refreshUHCD()">
            <option value="tous"              {{if $uhcd_affichage == "tous"             }} selected = "selected" {{/if}}>Tous</option>
            <option value="presents"          {{if $uhcd_affichage == "presents"         }} selected = "selected" {{/if}}>Présents</option>
            <option value="prendre_en_charge" {{if $uhcd_affichage == "prendre_en_charge"}} selected = "selected" {{/if}}>A PeC</option>
            <option value="annule"            {{if $uhcd_affichage == "annule"           }} selected = "selected" {{/if}}>Annulé</option>
          </select>
        </form>
        <a href="#" onclick="MainCourante.legend()" class="button search">Légende</a>
      </td>
    </tr>
  </table>
  <div id="uhcd">
    <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
  </div>
</div>

{{if $conf.dPurgences.gerer_reconvoc == "1" && "dPurgences CRPU type_sejour"|gconf !== "urg_consult"}}
<div id="consultations" style="display:none" class="me-padding-0">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>
{{/if}}

<div id="identito_vigilance" style="margin: 0 5px; display:none" class="me-padding-0">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>

<div id="information_service" style="display:none" class="me-padding-0">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>

{{if "maternite"|module_active}}
  <div id="avis_maternite" style="display:none" class="me-padding-0">
    <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
  </div>
{{/if}}
