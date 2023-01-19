{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  showPaires = function() {
    new Url("system", "vw_paires_prenom_sexe")
      .requestModal("80%", "80%");
  };

  repairPaires = function() {
    new Url("system", "vw_guess_sexe")
      .requestModal("80%", "80%");
  }
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      Import de la base de données de correspondance Prénoms / sexe
    </th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td style="vertical-align: top">
      <form name="do_firstnames" method="post" onsubmit="return onSubmitFormAjax(this, null, 'action_firstname_db')">
        <input type="hidden" name="m" value="system"/>
        <input type="hidden" name="dosql" value="do_import_firstnames"/>

        <table class="form">
          <tr>
            <td colspan="2">
              <button class="tick">
                Importer la table de prénoms
              </button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    <td id="action_firstname_db"></td>
  </tr>
  <tr>
    <td colspan="2">
      <button type="button" class="search" onclick="showPaires();">Visualiser les paires</button>
    </td>
  </tr>

  <tr>
    <th class="title" colspan="2">
    Réparation des paires prénom - sexe
    </th>
  </tr>
  <tr>
    <td colspan="2">
      <button type="button" class="search" onclick="repairPaires();">Visualiser les paires à traiter</button>
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <a class="button lookup" href="?m=system&tab=vw_firstnames">
        {{tr}}mod-system-tab-vw_firstnames{{/tr}}
      </a>
    </td>
  </tr>
</table>