{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{me_form_field mb_object=$materiel_op mb_field=dm_id}}
  {{mb_field object=$materiel_op field=dm_id hidden=true
  onchange="
             \$V(this.form.code_cip, '', false);
             \$V(this.form.bdm, '', false);
             \$V(this.form.produit, '', false);"}}
  <input type="text" name="_product_keywords" style="width: 250px;" {{if $materiel_op->dm_id}}value="{{$materiel_op->_view}}"{{/if}} />
{{/me_form_field}}