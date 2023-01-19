{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{me_form_field mb_object=$materiel_op mb_field=code_cip}}
{{mb_field object=$materiel_op field=bdm hidden=true}}
{{mb_field object=$materiel_op field=code_cip hidden=true
onchange="
                         \$V(this.form.dm_id, '', false);
                         \$V(this.form._product_keywords, '', false);"}}
  <input type="text" name="produit" style="width: 250px;" {{if $materiel_op->code_cip}}value="{{$materiel_op->_view}}"{{/if}} />
{{/me_form_field}}