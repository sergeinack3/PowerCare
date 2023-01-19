{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=readonly value=true}}

<div style="position: absolute; width: 100%; height: 44px;">
  <table class="tbl">
    <tr>
      <th class="title" colspan="5">
        <span style="float: right;">
          <button class="add" type="button"
                  onclick="
                    Control.Modal.close();

                  {{if $macrocible}}
                    Soins.addMacrocible('{{$transmission->sejour_id}}', '{{$transmission->object_id}}', '{{$transmission->cible_id}}');
                  {{else}}
                    Soins.addTransmission('{{$transmission->sejour_id}}', '{{$app->user_id}}', null, null, null, null, '{{$transmission->cible_id}}');
                  {{/if}}
                    ">
            {{if $from_compact}}
              Ajouter une transmission
            {{else}}
              Réouvrir la cible
            {{/if}}
          </button>
        </span>
        Cible :
        {{if $transmission->object_id && $transmission->object_class}}
          {{assign var=classes value=' '|explode:"CPrescriptionLineMedicament CPrescriptionLineElement CAdministration CPrescriptionLineMix"}}
          {{if in_array($transmission->object_class, $classes)}}
            <span
              title="{{$transmission->_ref_object->_view}} {{if $transmission->_ref_object|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement' && $transmission->_ref_object->commentaire}}({{$transmission->_ref_object->commentaire}}){{/if}}"
              style="border: 2px solid #800; padding-left: 2px; padding-right: 2px; margin-right: 3px;">
            </span>
          {{/if}}

          {{if !in_array($transmission->object_class, $classes)}}
            {{$transmission->_ref_object->_view}}
          {{/if}}
          {{if $transmission->object_class == "CPrescriptionLineMedicament"}}
            [{{$transmission->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
          {{/if}}

          {{if $transmission->object_class == "CPrescriptionLineMix"}}
            [{{mb_value object=$transmission->_ref_object field=type_line}}]
          {{/if}}

          {{if $transmission->object_class == "CPrescriptionLineElement"}}
            [{{$transmission->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
          {{/if}}

          {{if $transmission->object_class == "CAdministration"}}
            {{if $transmission->_ref_object->object_class == "CPrescriptionLineMedicament"}}
              [{{$transmission->_ref_object->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
            {{/if}}

            {{if $transmission->_ref_object->object_class == "CPrescriptionLineElement"}}
              [{{$transmission->_ref_object->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
            {{/if}}
          {{/if}}
        {{/if}}
        {{if $transmission->libelle_ATC}}
          {{$transmission->libelle_ATC}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <th rowspan="2" style="width: 10%">{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
      <th colspan="3">{{mb_title class=CTransmissionMedicale field=text}}</th>
    </tr>
    <tr>
      <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.data{{/tr}}</th>
      <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.action{{/tr}}</th>
      <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.result{{/tr}}</th>
    </tr>
  </table>
</div>
<div style="overflow-x: auto; margin-top: 97px; max-height: 400px;">
  <table class="tbl">
    {{foreach from=$transmissions item=_suivi}}
      <tr>
        {{mb_include module=hospi template=inc_line_suivi show_patient=false show_link=false force_new=true show_target=false show_type=false}}
      </tr>
    {{/foreach}}
  </table>
</div>
