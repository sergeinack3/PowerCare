{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  function synchronizeSejours() {
    var url = new Url();
    url.setModuleAction("dPhospi", "httpreq_do_synchronize_sejours");
    url.addElement(document.synchronizeFrm.dateMin);
    url.requestUpdate("synchronize");
  }
</script>

<form name="synchronizeFrm" method="get">
  <table class="form">
    <tr>
      <th colspan="2" class="title">
        Synchronisation des dates de sortie des séjours et des affectations
      </th>
    </tr>
    <tr>
      <td>
        Date minimale de sortie : <input type="text" name="dateMin" value="AAAA-MM-JJ" />
        <br />
        <button type="button" class="tick" onclick="synchronizeSejours()">Synchroniser</button>
      </td>
      <td id="synchronize"></td>
    </tr>
  </table>
</form>