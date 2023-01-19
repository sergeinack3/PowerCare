{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if $messages}}
    Main.add(function() {
      window.opener.ProtocoleOp.refreshList();
    });
  {{/if}}
</script>

<h2>
  {{tr}}CProtocoleOperatoire-Import{{/tr}}
</h2>

{{mb_include module=system template=inc_import_csv_info_intro}}
<li><strong><label>{{tr}}CProtocoleOperatoire-Is protocole{{/tr}}</label></strong></li>
<li><strong><label>{{tr}}CProtocoleOperatoire-Is materiel{{/tr}}</label></strong></li>
<li><strong><label>{{tr}}common-Practitioner name{{/tr}}</label></strong></li>
<li><strong><label>{{tr}}common-Practitioner firstname{{/tr}}</label></strong></li>
<li><strong>{{mb_label class=CProtocoleOperatoire field=function_id}}</strong></li>
<li><strong>{{mb_label class=CProtocoleOperatoire field=group_id}}</strong></li>
<li><strong>{{mb_label class=CProtocoleOperatoire field=libelle}}</strong></li>
<li>{{mb_label class=CProtocoleOperatoire field=code}} ({{tr}}CProtocoleOperatoire-code-Used to match existing protocol{{/tr}})</li>
<li>{{mb_label class=CProtocoleOperatoire field=numero_version}}</li>
<li>{{mb_label class=CProtocoleOperatoire field=remarque}}</li>
<li>{{mb_label class=CProtocoleOperatoire field=description_equipement_salle}}</li>
<li>{{mb_label class=CProtocoleOperatoire field=description_installation_patient}}</li>
<li>{{mb_label class=CProtocoleOperatoire field=description_preparation_patient}}</li>
<li>{{mb_label class=CMaterielOperatoire field=dm_id}}</li>
<li>{{mb_label class=CMaterielOperatoire field=code_cip}}</li>
<li>{{mb_label class=CMaterielOperatoire field=bdm}}</li>
<li>{{mb_label class=CMaterielOperatoire field=qte_prevue}}</li>
<li>{{mb_label class=CDM field=_pharma_code}} ({{tr}}CDM-_pharma_code-search{{/tr}})</li>

{{mb_include module=system template=inc_import_csv_info_outro}}

<form name="importProtocolesOps" method="post" enctype="multipart/form-data"
      action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1">
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />
  <button class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{$messages|smarty:nodefaults}}
