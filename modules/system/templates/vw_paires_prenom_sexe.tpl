{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  showPairesLettre = function(lettre) {
    new Url("patients", "ajax_show_paires")
      .addNotNullParam("lettre", lettre)
      .requestUpdate("paires_area");
  };

  Main.add(showPairesLettre);
</script>

<div style="font-size: 1.1em; text-align: center" class="pagination">

<div id="paires_area"></div>