{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=accueil_presentation}}

<script>
  Main.add(function() {
    {{if !"dPadmissions presentation sound_alert"|gconf}}
      AccueilPresentation.soundEnabled = false;
    {{/if}}
    AccueilPresentation.rafraichissementPages   = {{"dPadmissions presentation rafraichissement_pages"|gconf}};
    AccueilPresentation.tempsDefilementPages    = {{"dPadmissions presentation vitesse_defilement_pages"|gconf}};
    AccueilPresentation.rafraichissementBandeau = {{"dPadmissions presentation rafraichissement_bandeau"|gconf}};
    AccueilPresentation.tempsDefilementBandeau  = {{"dPadmissions presentation vitesse_defilement_bandeau"|gconf}};
    AccueilPresentation.periodicalUpdatePages()
      .periodicalUpdateBandeau();
  });
</script>

<div style="width: 100%; text-align: center">
  <h1>{{tr}}admissions-presentation title{{/tr}}</h1>
</div>
<form action="#" name="admission_presentation_filters" method="post" style="display:none">
  <input type="hidden" name="statut_pec"       value='{{$_statut_pec}}'/>
  <input type="hidden" name="praticien_id"     value='{{$praticien_id}}'/>
  <input type="hidden" name="type_pec"         value='{{$type_pec}}'/>
  <input type="hidden" name="enabled_services" value='{{$enabled_services}}'/>
  <input type="hidden" name="period"           value='{{$period}}'/>
</form>
<table class="tbl" id="admission_presentation_lines"></table>
<table class="tbl">
  <tr>
    <td>
      <div id="bandeau_container"></div>
      <div id="bandeau_container_data" style="display:none"></div>
      <div id="bar_loader"></div>
      <audio id="admission_presentation_alert">
        <source src="./sounds/alert.mp3" type="audio/mpeg"/>
        <source src="./sounds/alert.wav" type="audio/wav"/>
      </audio>
    </td>
  </tr>
</table>