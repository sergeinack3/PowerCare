{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}
{{mb_default var=other_view      value=false}}
{{mb_default var=is_plage_groupe value=false}}

{{mb_include module=system template=CMbObject_view}}

{{assign var=elements value=$object->_ref_elements_prescription}}

<table class="tbl tooltip">
 {{if !$other_view}}
  <tr>
    <td class="text">
      <strong>{{tr}}CPlageGroupePatient-Associated prescription element{{/tr}}:</strong>
      <ul>
        {{foreach from=$elements item=_element}}
          <li>{{mb_include module=system template=inc_vw_mbobject object=$_element}}</li>
        {{foreachelse}}
          <li class="empty">{{tr}}CElementPrescription.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>

    <tr>
      <td class="text">
        <strong>{{tr}}CPatient|pl{{/tr}}:</strong>
        <ul>
          {{foreach from=$object->_ref_sejours_associes item=_sejour}}
            <li>{{mb_include module=system template=inc_vw_mbobject object=$_sejour}}</li>
          {{foreachelse}}
            <li class="empty">{{tr}}CPatient.none{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="edit" onclick="GroupePatient.editGroupPlage('{{$object->_id}}', '{{$object->categorie_groupe_patient_id}}');">
            {{tr}}CCategorieGroupePatient-action-Modify a group range{{/tr}}
        </button>
        {{if $object->actif}}
          <button type="button" class="fas fa-users" onclick="GroupePatient.managePatients('{{$object->_id}}', '{{$object->categorie_groupe_patient_id}}', null, null, '{{$is_plage_groupe}}', '{{$object->_date}}');">
            {{tr}}CPlageGroupePatient-action-Patients management{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  {{else}}
   <tr>
      <td class="text">
        <strong>{{tr}}CEvenementSSR|pl{{/tr}}:</strong>
        <ul>
          {{foreach from=$object->_ref_evenements_ssr item=_evenement}}
            {{assign var=line_element value=$_evenement->_ref_prescription_line_element}}
            <li onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_guid}}');">
              {{$line_element->_ref_element_prescription->libelle}}
              &mdash;
              {{foreach from=$_evenement->_ref_actes_csarr item=_acte name=actes}}
                {{$_acte}} {{if !$smarty.foreach.actes.last}}, {{/if}}
              {{/foreach}}
              ({{$_evenement->duree}} min)
            </li>
          {{foreachelse}}
            <li class="empty">{{tr}}CEvenementSSR.none{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
  {{/if}}
</table>
