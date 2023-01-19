{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=sejour value=$dossier->_ref_sejour_accouchement}}

{{if $dossier->admission_id != null}}
  {{mb_include module=maternite template=inc_dossier_mater_resume_sejour_mere}}
{{else}}
  {{mb_include module=maternite template=inc_dossier_mater_admission_choix_sejour}}
{{/if}}