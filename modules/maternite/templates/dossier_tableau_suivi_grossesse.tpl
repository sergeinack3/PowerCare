{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation}}

{{assign var=patient value=$grossesse->_ref_parturiente}}

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

{{mb_include module=maternite template=inc_gestion_suivi_grossesse}}