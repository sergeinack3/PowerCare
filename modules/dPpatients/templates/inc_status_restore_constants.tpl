{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $current >= $total}}
  <script type="text/javascript">
    Main.add(function () {
      stopRestoreConstants();
    });
  </script>
  <div class="small-info">Réparation des constantes terminée : {{$total}} objets constante traités</div>
{{else}}
  <div class="small-info">Nombre d'objets constante à traiter : {{$total}}</div>
  <div class="small-info">Nombre d'objets constante traités : {{$current}}</div>
{{/if}}