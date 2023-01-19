{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Nettoyage des correspondants patient</h2>

<div class="small-info">
  Cet outil permet d'effectuer une épuration des doublons de correspondants patients dûs à des importations en supprimant les doublons.
</div>

<form name="cleanup-correspondant-patient" method="post"
      onsubmit="return onSubmitFormAjax(this, {}, 'cleanup-correspondant-patient-log')">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_cleanup_correspondant_patient" />

  <table class="tbl">
    <tr>
      <th class="section">{{tr}}Action{{/tr}}</th>
      <th class="section">{{tr}}Status{{/tr}}</th>
    </tr>

    <tr>
      <td style="width: 40%">
        <table class="layout">
          <tr>
            <td>
              <label>
                Traiter les doublons qui sont plus de <input type="number" name="count_min" value="50" size="5" />
              </label>
            </td>
          </tr>
          <tr>
            <td>
              <label>
                Regrouper aussi les correspondants ayant des dates de début différentes <input type="checkbox" name="merge_dates"
                                                                                               value="1" />
              </label>
            </td>
          </tr>
          <tr>
            <td>
              <label>
                Dry run (n'effectue pas de suppression) <input type="checkbox" name="dry_run" value="1" checked />
              </label>
            </td>
          </tr>
          <tr>
            <td>
              <button type="submit" class="tick">{{tr}}Clean up{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>

      <td id="cleanup-correspondant-patient-log"></td>
    </tr>
  </table>
</form>