{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  oResponse = {
    "oAffectations": {{$aAffectation|@json}},
    "oSejours": {{$aSejours|@json}},
    "oPatients": {{$aPatients|@json}},
    "oListTypeRepas": {{$listTypeRepas|@json}},
    "oMenus": {{$aMenus|@json}},
    "oRepas": {{$aRepas|@json}},
    "oPlats": {{$aPlats|@json}},
    "oPlanningRepas": {{$planningRepas|@json}},
    "config": {{$dPrepas|@json}}
  };
  AjaxResponse.putdPrepasData("dPrepas", oResponse);
</script>