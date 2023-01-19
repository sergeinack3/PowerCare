{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  SuiviSallesPresentation = {
    page: 1,
    update: function() {
      var url = new Url("bloc", "ajax_vw_suivi_salle");
      {{foreach from=$blocs_ids item=_bloc_id key=key_bloc}}
      url.addParam('blocs_ids[{{$key_bloc}}', '{{$_bloc_id}}', true);
      {{/foreach}}
      url.addParam('date', '{{$date}}');
      url.addParam('page', SuiviSallesPresentation.page++);
      url.addParam('salle_ids', '{{$salle_ids}}');
      url.addParam('mode_presentation', 1);
      url.requestUpdate("result_suivi");
    },

    startAutoRefresh: function() {
      SuiviSallesPresentation.update();

      {{math assign=period equation="a*1000" a="dPbloc mode_presentation refresh_period"|gconf}}
      {{if $period && $period > 0}}
        SuiviSallesPresentation.timer = setInterval(function() {
          SuiviSallesPresentation.update();
        }, {{$period}});
      {{/if}}
    }
  };

  Main.add(function() {
    SuiviSallesPresentation.startAutoRefresh();
  });
</script>

<button onclick="App.fullscreen();" style="position: absolute; right: 0">Plein écran</button>
<div id="result_suivi"></div>


