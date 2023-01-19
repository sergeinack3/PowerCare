{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    // Setting parent because of iframe context (reconnecting reload purpose)
    parent.User = User;
  });
</script>

<div class="small-info">
  Vous êtes bien authentifié sur Mediboard.
</div>