{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=widget_correspondants ajax=$ajax}}

<script>
  Main.add(function () {
    new Correspondants('{{$patient->_id}}', {container: $('medecins')});
  });
</script>