{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=allowed_modify value=$app->user_prefs.allowed_modify_identity_status}}

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=mode_obtention}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=identity_proof_type_id}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=nom_naissance}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=prenom_naissance}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=prenoms}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=date_naissance}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=sexe}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=pays_naissance_insee}}
    </th>
    <th>
        {{mb_title class=Ox\Mediboard\Patients\CSourceIdentite field=commune_naissance_insee}}
    </th>
    <th class="narrow"></th>
    <th class="narrow"></th>
  </tr>

  <tr>
    <td colspan="3" class="me-text-align-center">
      <strong>{{tr}}CPatient{{/tr}}
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=nom}}
      </strong>
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=prenom}}
      </strong>
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=prenoms}}
      </strong>
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=naissance}}
      </strong>
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=sexe}}
      </strong>
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=_pays_naissance_insee}}
      </strong>
    </td>
    <td>
      <strong>
          {{mb_value object=$patient field=lieu_naissance}}
      </strong>
    </td>
    <td colspan="2"></td>
  </tr>

    {{foreach from=$patient->_ref_sources_identite item=_source_identite}}
        {{assign var=justificatif value=$_source_identite->_ref_justificatif}}
      <tr {{if !$_source_identite->active}}class="hatching opacity-60"{{/if}}>
        <td class="button">
            {{if $_source_identite->_id == $patient->source_identite_id}}
              <i class="fas fa-check" title="{{tr}}CSourceIdentite-selected{{/tr}}"></i>
            {{/if}}

            {{if $_source_identite->active && $allowed_modify}}
                {{if $_source_identite->_id != $patient->source_identite_id}}
                  {{if ($_source_identite->mode_obtention !== 'manuel' || $_source_identite->identity_proof_type_id)}}
                    <button
                      onclick="SourceIdentite.disableSource('{{$_source_identite->_id}}', '{{$_source_identite->mode_obtention}}');"
                      class="cancel notext">{{tr}}Cancel{{/tr}}</button>
                  {{/if}}
                {{/if}}
            {{/if}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=mode_obtention}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=identity_proof_type_id}}

            {{if $justificatif && $justificatif->_id}}
              <div>
                  {{thumbnail document=$justificatif profile=small style="width: 50px; cursor: help;"
                  onmouseover="ObjectTooltip.createDOM(this, 'justificatif_`$justificatif->_id`');"
                  onclick="File.popup('`$_source_identite->_class`','`$_source_identite->_id`','`$justificatif->_class`','`$justificatif->_id`');"}}

                <div id="justificatif_{{$justificatif->_id}}" style="display: none;">
                    {{thumbnail document=$justificatif profile=large style="max-height: 800px;"}}
                </div>
              </div>
            {{/if}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=nom_naissance}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=prenom_naissance}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=prenoms}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=date_naissance}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=sexe}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=_pays_naissance_insee}}
        </td>
        <td>
            {{mb_value object=$_source_identite field=_lieu_naissance}}
        </td>
        <td>
            {{if $_source_identite->mode_obtention === 'insi' && $app->user_prefs.allow_use_insi_tlsi}}
                {{if $_source_identite->active}}
                  <div class="me-margin-bottom-8">
                      {{mb_include module=ameli template=services/inc_insiidir_button}}
                  </div>
                {{/if}}
              <button type="button" class="history"
                      onclick="SourceIdentite.showLogINSi('{{$patient->_id}}');">
                  {{tr}}CINSiLog history call{{/tr}}
              </button>
            {{/if}}
        </td>
        <td>
            {{mb_include module=system template=inc_object_history object=$_source_identite}}
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td colspan="11" class="empty">
            {{tr}}CSourceIdentite.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
</table>
