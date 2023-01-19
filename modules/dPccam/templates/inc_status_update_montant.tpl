{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $current >= $total}}
  <script type="text/javascript">
    Main.add(function() {
      stopUpdateMontant();
    });
  </script>

  <div class="small-info">Mise � jour des montants termin�e : {{$total}} actes trait�s</div>
{{else}}
  <div class="small-info">Nombre d'actes � traiter : {{$total}}</div>
  <div class="small-info">Nombre d'actes trait�s : {{$current}}</div>
{{/if}}