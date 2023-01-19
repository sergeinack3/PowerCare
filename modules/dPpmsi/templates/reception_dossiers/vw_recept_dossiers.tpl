{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=reception}}
{{mb_script module=pmsi script=relance}}
{{mb_script module=planningOp script=sejour}}

<script>
  ReceptionPlay = {
    updater : null,
    updater2 : null,

    init: function(frequency) {
      var url1 = new Url("pmsi", "ajax_recept_dossiers_month");
      var url2 = new Url("pmsi", "ajax_recept_dossiers_lines");

      ReceptionPlay.updater = url1.periodicalUpdate('allDossiers', { frequency: frequency } );
      ReceptionPlay.updater2 = url2.periodicalUpdate('listDossiers', { frequency: frequency } );
    },

    start: function(delay, frequency) {
      this.stop();
      this.init.delay(delay, frequency);
    },

    stop: function() {
      if (ReceptionPlay.updater) {
        ReceptionPlay.updater.stop();
      }
      if (ReceptionPlay.updater2) {
        ReceptionPlay.updater2.stop();
      }
    },

    resume: function() {
      if (ReceptionPlay.updater) {
        ReceptionPlay.updater.resume();
      }
      if (ReceptionPlay.updater2) {
        ReceptionPlay.updater2.resume();
      }
    }
  };
  togglePlayPause = function (button) {
    button.toggleClassName("play");
    button.toggleClassName("pause");
    if (button.hasClassName("play")) {
      ReceptionPlay.stop();
    }
    else {
      ReceptionPlay.resume();
    }
  };

  Main.add(function() {
    Reception.form = 'selType';
    ReceptionPlay.start(0, 10);
  });
</script>

<table class="main">
  <tr>
    <td>
      <a href="#legend" onclick="Reception.showLegend()" class="button search me-tertiary me-dark">{{tr}}common-Legend{{/tr}}</a>
    </td>
    <td style="float: right">
      <button type="button" class="pause notext" onclick="togglePlayPause(this);" title="Arrêter / Relancer le rafraîchissement automatique">Rech. auto</button>
      <form action="?" name="selType" method="get">
        <input type="hidden" name="date" value="{{$date}}" />
        <input type="hidden" name="date_end" value="{{$date}}"/>
        <input type="hidden" name="tri_recept" value="{{$tri_recept}}" />
        <input type="hidden" name="tri_complet" value="{{$tri_complet}}" />
        <input type="hidden" name="order_col" value="{{$order_col}}" />
        <input type="hidden" name="order_way" value="{{$order_way}}" />
        <input type="hidden" name="filterFunction" value="{{$filterFunction}}" />
        <select name="period" onchange="Reception.reloadListDossiers();">
          <option value=""      {{if !$period          }}selected{{/if}}>&mdash; Toute la journée</option>
          <option value="matin" {{if $period == "matin"}}selected{{/if}}>Matin</option>
          <option value="soir"  {{if $period == "soir" }}selected{{/if}}>Soir</option>
        </select>
        {{mb_field object=$sejour field="_type_admission" emptyLabel="CSejour.all" onchange="Reception.reloadAllReceptDossiers()"}}
        <select name="service_id" onchange="Reception.reloadAllReceptDossiers();" {{if $sejour->service_id|@count > 1}}size="5" multiple="true"{{/if}}>
          <option value="">&mdash; Tous les services</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if in_array($_service->_id, $sejour->service_id)}}selected{{/if}}>{{$_service}}</option>
          {{/foreach}}
        </select>
        <input type="checkbox" onclick="Reception.toggleMultipleServices(this)" {{if $sejour->service_id|@count > 1}}checked{{/if}}/>

        <label>
          Facturable :
          <select name="facturable" onchange="Reception.reloadAllReceptDossiers();">
            <option value="">Tous</option>
            <option value="1" {{if $sejour->facturable}}selected{{/if}}>Oui</option>
            <option value="0" {{if $sejour->facturable === "0"}}selected{{/if}}>Non</option>
          </select>
        </label>

        <label>
          <input type="checkbox" name="sans_dmh" {{if $sejour->sans_dmh}}checked{{/if}} onchange="Reception.reloadAllReceptDossiers();" /> Sans dossier
        </label>

        <select name="prat_id" onchange="Reception.reloadAllReceptDossiers();">
          <option value="">&mdash; Tous les praticiens</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$sejour->praticien_id}}
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <td id="allDossiers" style="width: 250px">
    </td>
    <td id="listDossiers" style="width: 100%">
    </td>
  </tr>
</table>