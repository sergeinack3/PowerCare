{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient register=true}}

<script type="text/javascript">
    Main.add(function() {
        MaintenanceConfig.ExpiredIdentityFiles.initializeView();
    });
</script>

{{mb_include module=system template=inc_pagination current=$start total=$total step=20 change_page='MaintenanceConfig.ExpiredIdentityFiles.changePage'}}
<table class="tbl">
    <thead>
        <tr>
            <th colspan="10" style="text-align: center;">
                <form name="setExpirationDate" method="post" action="?"
                      onsubmit="return MaintenanceConfig.ExpiredIdentityFiles.refresh();">
                    <label title="{{tr}}CSourceIdentite-expiration date-desc{{/tr}}">
                        {{tr}}common-Date{{/tr}}
                        <input type="hidden" name="expirationDate" value="{{$expirationDate}}" onchange="this.form.onsubmit();">
                    </label>
                </form>
            </th>
        </tr>
        <tr>
            <th class="narrow">
                {{if $patients|@count}}
                    <input type="checkbox" name="all_patients" onclick="MaintenanceConfig.ExpiredIdentityFiles.toggleCheckboxes();">
                {{/if}}
            </th>
            <th>
                {{mb_title class=CPatient field=nom_jeune_fille}}
            </th>
            <th>
                <label title="{{tr}}CPatient-prenom-desc{{/tr}}">{{tr}}CPatient-prenom{{/tr}}</label>
            </th>
            <th>
                {{mb_title class=CPatient field=naissance}}
            </th>
            <th>
                {{mb_title class=CPatient field=sexe}}
            </th>
            <th>
                {{mb_title class=CPatient field=_pays_naissance_insee}}
            </th>
            <th>
                {{mb_title class=CPatient field=lieu_naissance}}
            </th>
            <th>
                {{mb_title class=CSourceIdentite field=mode_obtention}}
            </th>
            <th>
                {{mb_title class=CSourceIdentite field=identity_proof_type_id}}
            </th>
            <th class="narrow">
                {{if $patients|@count}}
                    <button type="button" onclick="MaintenanceConfig.ExpiredIdentityFiles.deleteFilesForPatients();" class="trash notext">
                        {{tr}}CSourceIdentite-action-delete_expired_identity_files{{/tr}}
                    </button>
                {{/if}}
            </th>
        </tr>
    </thead>
    {{foreach from=$patients item=patient}}
        {{assign var=sources_count value=$patient->_ref_sources_identite|@count}}
        <tr id="{{$patient->_guid}}">
            <td rowspan="{{$sources_count}}" class="narrow">
                <input type="checkbox" name="patients" data-patient_guid="{{$patient->_guid}}">
                <button type="button" class="search notext" onclick="Patient.viewModal('{{$patient->_id}}')">
                    {{tr}}dPpatients-CPatient-Dossier_complet{{/tr}}
                </button>
            </td>
            <td class="last_name" rowspan="{{$sources_count}}">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                    {{mb_value object=$patient field=nom_jeune_fille}}
                </span>
            </td>
            <td class="first_name" rowspan="{{$sources_count}}">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                    {{mb_value object=$patient field=prenom}}
                </span>
            </td>
            <td rowspan="{{$sources_count}}">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                    {{mb_value object=$patient field=naissance}}
                </span>
            </td>
            <td rowspan="{{$sources_count}}">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                    {{mb_value object=$patient field=sexe}}
                </span>
            </td>
            <td rowspan="{{$sources_count}}">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                    {{mb_value object=$patient field=_pays_naissance_insee}}
                </span>
            </td>
            <td rowspan="{{$sources_count}}">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                    {{mb_value object=$patient field=lieu_naissance}}
                </span>
            </td>
            {{foreach from=$patient->_ref_sources_identite item=source name=sources_loop}}
                {{if !$smarty.foreach.sources_loop.first}}
                    <tr>
                {{/if}}
                <td>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$source->_guid}}');">
                        {{mb_value object=$source field=mode_obtention}}
                    </span>
                </td>
                <td>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$source->_guid}}');">
                        {{mb_value object=$source field=identity_proof_type_id}}
                    </span>
                    <div>
                        {{thumbnail document=$source->_ref_justificatif profile=small style="width: 50px; cursor: help;"
                                    onmouseover="ObjectTooltip.createDOM(this, 'justificatif_`$source->_ref_justificatif->_id`');"
                                    onclick="File.popup('`$source->_class`','`$source->_id`','`$source->_ref_justificatif->_class`','`$source->_ref_justificatif->_id`');"}}

                        <div id="justificatif_{{$source->_ref_justificatif->_id}}" style="display: none;">
                          {{thumbnail document=$source->_ref_justificatif profile=large style="max-height: 800px;"}}
                        </div>
                    </div>
                </td>
                {{if $smarty.foreach.sources_loop.first}}
                    <td rowspan="{{$sources_count}}" class="narrow">
                        <button type="button" onclick="MaintenanceConfig.ExpiredIdentityFiles.deleteFilesForPatient('{{$patient->_guid}}');" class="trash notext">
                            {{tr}}CSourceIdentite-action-delete_expired_identity_file{{/tr}}
                        </button>
                    </td>
                {{elseif !$smarty.foreach.sources_loop.last}}
                    </tr>
                {{/if}}
            {{/foreach}}
        </tr>
    {{foreachelse}}
      <tr>
          <td class="empty" colspan="10" style="text-align: center;">
              {{tr}}CSourceIdentite-expired_identity_files.none{{/tr}}
          </td>
      </tr>
    {{/foreach}}
</table>
