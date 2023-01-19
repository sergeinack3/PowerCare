{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{me_form_field mb_object=$materiel_op mb_field=qte_prevue}}
  {{mb_field object=$materiel_op field=qte_prevue form=editMaterielOp increment=true}}
{{/me_form_field}}