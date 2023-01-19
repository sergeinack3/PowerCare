{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dmi"|module_active}}
  {{mb_field object=$materiel_op field=dm_id hidden=true
             onchange="
                       \$V(this.form.code_cip, '', false);
                       \$V(this.form.bdm, '', false);
                       \$V(this.form.produit, '', false);
                       ProtocoleOp.checkButton(this);"}}
  <input type="text" name="_product_keywords" placeholder="{{tr}}CDM-choose{{/tr}}" />

  <div>
    <h2>{{tr}}common-or{{/tr}}</h2>
  </div>
{{/if}}

{{mb_field object=$materiel_op field=bdm hidden=true}}
{{mb_field object=$materiel_op field=code_cip hidden=true
           onchange="
                     \$V(this.form.dm_id, '', false);
                     \$V(this.form._product_keywords, '', false);
                     ProtocoleOp.checkButton(this);"}}
<input type="text" name="produit" placeholder="{{tr}}CPrescription.select_produit{{/tr}}" />