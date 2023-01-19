{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span
  id="sejour-edit-patient_infos"{{if 'dPfacturation'|module_active && "dPplanningOp CSejour fields_display assurances"|gconf}} style="display: inline-block; width: 50%; vertical-align: top;"{{/if}}>
  <fieldset>
    <legend>Informations bénéficiaire</legend>
    <table class="form">
    <tr>
      <th>
        {{mb_label object=$patient field=c2s}}
      </th>
      <td>
        {{mb_field object=$patient field=c2s onchange="DHE.sejour.syncViewFlag(this); DHE.sejour.syncPatientField(this);"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$patient field=ald}}
      </th>
      <td>
        <input type="radio" name="_ald" id="sejourEdit__ald_1" value="1"{{if $patient->ald == '1'}} checked{{/if}} onchange="DHE.sejour.syncPatientField(this, 'ald');">
        <label for="sejourEdit__ald_1">{{tr}}Yes{{/tr}}</label>
        <input type="radio" name="_ald" id="sejourEdit__ald_0" value="0"{{if $patient->ald == '0'}} checked{{/if}} onchange="DHE.sejour.syncPatientField(this, 'ald');">
        <label for="sejourEdit__ald_0">{{tr}}No{{/tr}}</label>
      </td>
    </tr>
      <tr>
      <th>
        {{mb_label object=$sejour field=ald}}
      </th>
      <td>
        {{mb_field object=$sejour field=ald}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$patient field=acs}}
      </th>
      <td>
        {{mb_field object=$patient field=acs onchange="DHE.sejour.changeACS(); DHE.sejour.syncViewFlag(this, \$T('CPatient-acs-desc')); DHE.sejour.syncPatientField(this);"}}
      </td>
    </tr>

    <tr id="sejour-edit-acs_type"{{if $patient->acs == '0'}} style="display: none;"{{/if}}>
      <th>
        {{mb_label object=$patient field=acs_type}}
      </th>
      <td>
        {{mb_field object=$patient field=acs_type onchange="DHE.sejour.syncViewFlag(this.form.elements['acs'][0], \$T('CPatient-acs-desc') + ' contrat ' + \$T('CPatient.acs_type.' + \$V(this))); DHE.sejour.syncPatientField(this.form.elements['acs'][0]);"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$patient field=tutelle}}
      </th>
      <td>
        {{mb_field object=$patient field=tutelle onchange="DHE.sejour.syncViewFlag(this, \$T('CPatient.tutelle.' + \$V(this)), ['tutelle', 'curatelle']); DHE.sejour.syncPatientField(this);"}}
      </td>
    </tr>
  </table>
  </fieldset>
</span>

{{if 'dPfacturation'|module_active && "dPplanningOp CSejour fields_display assurances"|gconf}}
    <span id="sejour-edit-assurance" style="display: inline-block; vertical-align: top; width: 49%;">
    <fieldset>
      <legend>Assurance</legend>
      <table class="form">
        <tr>
          <th>
            {{mb_label object=$sejour field=_type_sejour}}
          </th>
          <td>
            {{mb_field object=$sejour field=_type_sejour onchange="DHE.sejour.syncView(this);"}}
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$sejour field=_statut_pro}}
          </th>
          <td>
            {{mb_field object=$sejour field=_statut_pro emptyLabel='Choose' onchange="DHE.sejour.syncView(this);"}}
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$sejour field=_dialyse}}
          </th>
          <td>
            {{mb_field object=$sejour field=_dialyse onchange="DHE.sejour.syncViewFlag(this);"}}
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$sejour field=_cession_creance}}
          </th>
          <td>
            {{mb_field object=$sejour field=_cession_creance onchange="DHE.sejour.syncViewFlag(this);"}}
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$sejour field=_assurance_maladie}}
          </th>
          <td>
            <input type="text" name="_assurance_maladie_view"
                   value="{{if $sejour->_ref_factures|@count}}{{$sejour->_ref_facture->_ref_assurance_maladie->nom}}{{/if}}">
            {{mb_field object=$sejour field=_assurance_maladie hidden=true onchange="DHE.sejour.syncView(this);" view="_assurance_maladie_view"}}
            <button type="button" class="cancel notext"
                    onclick="DHE.emptyField(this.form._assurance_maladie);">{{tr}}Empty{{/tr}}</button>
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$sejour field=_rques_assurance_maladie}}
          </th>
          <td>
            {{mb_field object=$sejour field=_rques_assurance_maladie onchange="DHE.sejour.syncView(this);"}}
          </td>
        </tr>
      </table>
    </fieldset>
  </span>
{{/if}}
