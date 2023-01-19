{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=plus_de_55_ans value=0}}
{{mb_default var=imc_sup_26 value=0}}

<table class="layout">
  <tr>
    <td>
      {{if $plus_de_55_ans}}
        {{mb_field object=$consult_anesth field=plus_de_55_ans typeEnum=checkbox readonly=1}}
      {{else}}
        {{mb_field object=$consult_anesth field=plus_de_55_ans typeEnum=checkbox onchange="verifIntubDifficileAndSave(this.form);"}}
      {{/if}}
      {{mb_label object=$consult_anesth field=plus_de_55_ans}}
    </td>
    <td>
      {{mb_field object=$consult_anesth field=edentation typeEnum=checkbox onchange="verifIntubDifficileAndSave(this.form);"}}
      {{mb_label object=$consult_anesth field=edentation}}
    </td>
  </tr>
  <tr>
    <td>
      {{mb_field object=$consult_anesth field=barbe typeEnum=checkbox onchange="verifIntubDifficileAndSave(this.form);"}}
      {{mb_label object=$consult_anesth field=barbe}}
    </td>
    <td>
      {{if $imc_sup_26}}
        {{mb_field object=$consult_anesth field=imc_sup_26 typeEnum=checkbox readonly=1}}
      {{else}}
        {{mb_field object=$consult_anesth field=imc_sup_26 typeEnum=checkbox onchange="verifIntubDifficileAndSave(this.form);"}}
      {{/if}}
      {{mb_label object=$consult_anesth field=imc_sup_26}}
    </td>
  </tr>
  <tr>
    <td>
      {{mb_field object=$consult_anesth field=ronflements typeEnum=checkbox onchange="verifIntubDifficileAndSave(this.form);"}}
      {{mb_label object=$consult_anesth field=ronflements}}
    </td>
    <td>
      {{mb_field object=$consult_anesth field=piercing typeEnum=checkbox onchange="verifIntubDifficileAndSave(this.form);"}}
      {{mb_label object=$consult_anesth field=piercing}}
    </td>
  </tr>
</table>