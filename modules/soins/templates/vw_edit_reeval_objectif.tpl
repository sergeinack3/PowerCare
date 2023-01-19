{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<form name="editReevaluation" method="post"
      onsubmit="Control.Modal.close(); return onSubmitFormAjax(this, Soins.updateReevals.curry('{{$reevaluation->objectif_soin_id}}'));">
  {{mb_class object=$reevaluation}}
  {{mb_key object=$reevaluation}}
  {{mb_field object=$reevaluation field=objectif_soin_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$reevaluation}}
    <tr>
      <th class="narrow">{{mb_label object=$reevaluation field=date}}</th>
      <td>{{mb_field object=$reevaluation field=date register=true form="editReevaluation"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$reevaluation field=commentaire}}</th>
      <td>{{mb_field object=$reevaluation field=commentaire}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="button" class="submit" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        {{if $reevaluation->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form, {
                    ajax:1,
                    objName:'{{$reevaluation->_view|JSAttribute}}'},
                    function() { Control.Modal.close(); Soins.updateReevals('{{$reevaluation->objectif_soin_id}}'); } );">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>