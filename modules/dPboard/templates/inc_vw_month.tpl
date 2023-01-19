{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "oxCabinet"|module_active}}
  {{mb_script module=oxCabinet script=TdBTamm ajax=$ajax}}
{{/if}}

<style>
  .modal_month {
    width:600px;
    cursor: pointer;
  }
</style>

{{mb_include module=system template=calendars/vw_month calendar=$calendar}}

<script>
  modal_resume = function(stype, event) {
    var sid = event.get('id');

    if (stype == 'CPlageOp') {
      var url = new Url("bloc", "ajax_vw_plageop");
      url.addParam('plage_id', sid);
      url.requestModal();
    }

    if (stype == 'CPlageconsult') {
      var url = new Url("cabinet", "ajax_vw_plage_consult");
      url.addParam('plage_id', sid);
      url.requestModal();
    }

    if (stype == 'CIntervHorsPlage') {
      var sdate = event.get('date');
      var schir = event.get('chir_id');
      var url = new Url("bloc", "ajax_vw_horsplage");
      url.addParam('date', sdate);
      url.addParam('chir_id', schir);
      url.requestModal(700);
    }
  };

  $$('.event').each(function(event) {
    event.onclick = function() {
      var stype = event.get('type');
      if (stype) {
        modal_resume(stype, event);
      }
    };
  });

  Main.add(function() {
    {{if "oxCabinet"|module_active}}
      TdBTamm.updateVisiteDomicile({{$nb_visite_dom}}, '{{$debut_mois}}', '{{$fin_mois}}');
    {{/if}}
  });
</script>