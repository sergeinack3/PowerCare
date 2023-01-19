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
  <div class="small-info">R�paration des constantes termin�e : {{$total}} objets constante trait�s</div>
{{else}}
  <div class="small-info">Nombre d'objets constante � traiter : {{$total}}</div>
  <div class="small-info">Nombre d'objets constante trait�s : {{$current}}</div>
{{/if}}