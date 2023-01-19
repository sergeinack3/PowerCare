{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$object->_ref_patient}}
{{assign var=naissance value=$object->_ref_naissance}}

<table class="tbl tooltip">
  <tr>
    <th class="title text" colspan="3">
      {{mb_include module=system template=inc_object_notes     }}
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history   }}
      {{mb_include module=system template=inc_object_uf}}

      {{if $object->presence_confidentielle}}{{mb_include module=planningOp template=inc_badge_sejour_conf}}{{/if}} {{tr}}CSejour{{/tr}} {{mb_include module=system template=inc_interval_date from=$object->entree to=$object->sortie}}

      {{if $app->_ref_user->isAdmin() && ('admin CBrisDeGlace enable_bris_de_glace'|gconf || 'admin CLogAccessMedicalData enable_log_access'|gconf)}}
        <a href="#" onclick="guid_access_medical('{{$object->_guid}}')" style="float:right;"><img src="images/icons/planning.png"
                                                                                                  alt="" /></a>
      {{/if}}
    </th>
  </tr>

  <tr>
    <td style="width: 1px;">
      {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=50}}
    </td>
    <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
      {{$patient}} {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
    </span>
    </td>
  </tr>

  <tr>
    <td class="button" colspan="3">
      {{if $naissance->_id && !$naissance->date_time}}
        {{assign var=sejour_maman value=$naissance->_ref_sejour_maman}}
        {{assign var=operation value=$sejour_maman->_ref_last_operation}}
        {{if $operation->_id}}
          <button type="button" class="tick"
                  onclick="Naissance.edit('{{$naissance->_id}}', '{{$operation->_id}}', null, null, null, Placement.refreshEtiquette.curry('{{$_sejour->_id}}'));">
            {{tr}}CNaissance-real_dossier{{/tr}}
          </button>
        {{/if}}
      {{/if}}
    </td>
  </tr>
</table>