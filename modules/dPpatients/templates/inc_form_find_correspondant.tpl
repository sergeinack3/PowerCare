{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="correspondantFilterForm" method="get" action="?"
      onsubmit="return onSubmitFormAjax(this, null, 'listCorrespondants')">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="a" value="retrieveMatchingCorrespondantFromRPPS"/>
  <input type="hidden" name="start" value="0"/>
  <input type="hidden" name="step" value="20"/>

  <table class="form">
    <div class="small-info">{{tr}}
      mod-dPpatients-tab-openCorrespondantImportFromRPPSModal_find_correspondants_description{{/tr}}
    </div>
    <tr>
      <th colspan="5" class="section">{{tr}}
        mod-dPpatients-tab-openCorrespondantImportFromRPPSModal_find_correspondants_by_fields{{/tr}}</th>
    </tr>

    <tr>
        {{me_form_field nb_cells=2 mb_object=$medecin mb_field="nom"}}
        {{mb_field object=$medecin field=nom prop=str tabindex=11}}
      {{/me_form_field}}

        {{me_form_field nb_cells=2 mb_object=$medecin mb_field=cp}}
        {{mb_field object=$medecin field=cp prop=str tabindex=13}}
      {{/me_form_field}}
    </tr>

    <tr>
        {{me_form_field nb_cells=2 mb_object=$medecin mb_field="prenom"}}
        {{mb_field object=$medecin field=prenom prop=str tabindex=12}}
      {{/me_form_field}}

        {{me_form_field nb_cells=2 mb_object=$medecin mb_field=ville}}
        {{mb_field object=$medecin field=ville prop=str tabindex=14}}
      {{/me_form_field}}
    </tr>
    <tr id="medecin_type">
        {{me_form_field nb_cells=2 mb_object=$medecin mb_field=type}}
        {{mb_field object=$medecin field=type emptyLabel="All" tabindex=15}}
      {{/me_form_field}}
    </tr>
    <tr>
      <th colspan="5" class="section">{{tr}}
        mod-dPpatients-tab-openCorrespondantImportFromRPPSModal_find_correspondants_by_rpps{{/tr}}</th>
    </tr>
    <tr>
        {{me_form_field nb_cells=2 mb_object=$medecin mb_field=rpps}}
        {{mb_field object=$medecin field=rpps prop=str tabindex=16}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button type="submit" class="search" onclick="Correspondant.enableAddButton(false)">
            {{tr}}Search{{/tr}}
        </button>
        <button type="button" name="add_button" class="add" disabled onclick="Correspondant.addCorrespondant()"
                style="display: none;">
            {{tr}}Add{{/tr}}
        </button>
        <button type="button" name="add_out_of_repository_button" class="new" onclick="Medecin.editMedecin('0');"
                style="display: none;">
            {{tr}}mod-dPpatients-tab-openCorrespondantImportFromRPPSModal_add_out_of_repository{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="listCorrespondants"></div>

<br/>
