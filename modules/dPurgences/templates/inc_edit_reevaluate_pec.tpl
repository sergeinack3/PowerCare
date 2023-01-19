{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editRPUReevalPEC" action="" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close); ">
  {{mb_key   object=$rpu_reeval_pec}}
  {{mb_class object=$rpu_reeval_pec}}
  {{mb_field object=$rpu_reeval_pec field=rpu_id hidden=true}}
  {{mb_field object=$rpu_reeval_pec field=user_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$rpu_reeval_pec}}

    <tr>
      <th>{{mb_label object=$rpu_reeval_pec field=datetime}}</th>
      <td>{{mb_field object=$rpu_reeval_pec field=datetime form="editRPUReevalPEC" register=true}}</td>
    </tr>
    {{assign var=notnull value=""}}

    {{if $rpu_reeval_pec->rpu_id && "dPurgences CRPU gestion_motif_sfmu"|gconf}}
      {{assign var=notnull value="notNull"}}
    {{/if}}

    {{if "dPurgences CRPU french_triage"|gconf}}
      <tr>
        <th>{{mb_label object=$rpu_reeval_pec field=french_triage}}</th>
        <td>{{mb_field object=$rpu_reeval_pec field=french_triage emptyLabel="Choose"}}</td>
      </tr>
    {{/if}}

    <tr>
      <th>{{mb_label object=$rpu_reeval_pec field=ccmu}}</th>
      <td>{{mb_field object=$rpu_reeval_pec field=ccmu emptyLabel="Choose" class=$notnull}}</td>
    </tr>

    {{if "dPurgences Display display_cimu"|gconf && !"dPurgences CRPU french_triage"|gconf}}
      {{assign var=notnull value=""}}
      {{if "dPurgences CRPU cimu_accueil"|gconf}}
        {{assign var=notnull value="notNull"}}
      {{/if}}
      <tr>
        <th>{{mb_label object=$rpu_reeval_pec field=cimu}}</th>
        <td>{{mb_field object=$rpu_reeval_pec field=cimu emptyLabel="Choose" class=$notnull}}</td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$rpu_reeval_pec field=commentaire}}</th>
      <td>{{mb_field object=$rpu_reeval_pec field=commentaire}}</td>
    </tr>

    {{assign var=rpu_reeval_pec_view value=$rpu_reeval_pec->_view|smarty:nodefaults|JSAttribute}}
    {{mb_include module=system template=inc_form_table_footer object=$rpu_reeval_pec options="{typeName: '', objName: '`$rpu_reeval_pec_view`'}" options_ajax="Control.Modal.close"}}
  </table>
</form>
