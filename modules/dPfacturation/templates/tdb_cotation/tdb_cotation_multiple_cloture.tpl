{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(
    function() {
      Facture.TdbCotation.controlCount();
    }
  );
</script>
<button id="tdb_cotation_multiple_cloture_button" type="button" class="submit" onclick="Facture.TdbCotation.clotureCotation()">
</button>