{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  oResponse = {
    "oEtablissements": {{$etablissements|@json}},
    "oServices": {{$services|@json}}
  };
  AjaxResponse.putServices("services", oResponse);
</script>