{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function storeTemps(action) {
    new Url('stats', action)
      .addParam('intervalle', $V(document.storeTempsOp.intervalle))
      .requestUpdate('resultMsg');
  }
</script>

<form name="storeTempsOp" method="get" action="?m={{$m}}">
  <table class="main me-align-auto">
    <tr>
      <th colspan="2" class="title">
        Mémorisation des stats des temps opératoires
      </th>
    </tr>
    <tr>
      <td class="halfPane">
        <table class="form">
          <tr>
            <th colspan="2" class="category">Intervalle choisi</th>
          </tr>
          <tr>
            <td>
              <input type="radio" name="intervalle" value="month" />
              <label for="intervalle_month" title="Prise en compte du dernier mois">Dernier mois</label>
            </td>
            <td>
              <input type="radio" name="intervalle" value="6month" />
              <label for="intervalle_6month" title="Prise en compte des 6 derniers mois">6 derniers mois</label>
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="intervalle" value="year" />
              <label for="intervalle_year"
                     title="Prise en compte de la dernière année">Dernière
                année</label>
            </td>
            <td>
              <input type="radio" name="intervalle" value="none" checked="checked" />
              <label for="intervalle_none"
                     title="Prise en compte sans intervalle">Pas
                d'intervalle</label>
            </td>
          </tr>
        </table>
      </td>
      <td class="halfPane">
        <table class="form">
          <tr>
            <th class="category">Lancer les mémorisations</th>
          </tr>
          <tr>
            <td class="button">
              <button type="button" class="submit" onclick="storeTemps('httpreq_temps_op')">
                Mémoriser les temps opératoires Old
              </button>
            </td>
          </tr>
          <tr>
            <td class="button">
              <button type="button" class="submit" onclick="storeTemps('httpreq_temps_op_new')">
                Mémoriser les temps opératoires new
              </button>
            </td>
          </tr>
          <tr>
            <td class="button">
              <button type="button" class="submit" onclick="storeTemps('httpreq_temps_prepa')">
                Mémoriser les temps de préparation
              </button>
            </td>
          </tr>
          <tr>
            <td class="button">
              <button type="button" class="submit" onclick="storeTemps('httpreq_temps_hospi')">
                Mémoriser les temps d'hospitalisation
              </button>
            </td>
          </tr>
          <tr>
            <td id="resultMsg"></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
