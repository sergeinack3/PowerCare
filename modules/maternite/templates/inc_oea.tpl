{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $oea_exam && ($oea_exam|@count > 0)}}
  <span onmouseover="ObjectTooltip.createDOM(this, 'oea_{{$object->_id}}');">
    {{if $oea_exam.last->oreille_droite === 'positif' && $oea_exam.last->oreille_gauche === 'positif'}}
      <i class="fa fa-check" style="color: #078227"></i>
    {{elseif $oea_exam.last->oreille_droite === 'negatif' && $oea_exam.last->oreille_gauche === 'negatif'}}
      <i class="fa fa-times" style="color: #820001"></i>
    {{else}}
      <i class="fa fa-exclamation-triangle" style="color: #ffc107"></i>
    {{/if}}
    {{if $object->_class != "CPrescriptionLineElement"}}
        {{tr}}Yes{{/tr}}
    {{/if}}
  </span>
  <div id="oea_{{$object->_id}}" style="display: none;">
    <table class="tbl">
      <tr>
        <th colspan="6">
          <span title="{{tr}}CNaissance-oea-desc{{/tr}}">
              {{tr}}CNaissance-oea{{/tr}}
          </span>
        </th>
      </tr>
      <tr>
        <th>{{tr}}common-Date{{/tr}}</th>
        <th>{{tr}}Who{{/tr}}</th>
        <th>{{tr}}OD{{/tr}}</th>
        <th>{{tr}}OG{{/tr}}</th>
        <th>{{tr}}Result{{/tr}}</th>
        <th>{{tr}}CNaissance-ENT appointment{{/tr}}</th>
      </tr>
      {{foreach from=$oea_exam key=key item=_oea}}
        {{if $key != "last"}}
          {{assign var=administrateur_oea value='Ox\Mediboard\Mediusers\CMediusers::get'|static_call:$_oea->examinateur_id}}
          <tr>
            <td>
              {{$_oea->date|date_format:$conf.date}}
            </td>
            <td>
            <span onmouseover="ObjectTooltip.createEx(
              this,
              '{{$administrateur_oea->_guid}}');">
                {{$administrateur_oea}}
            </span>
            </td>
            <td>
              {{tr}}CExamenNouveauNe.oreille_droite.{{$_oea->oreille_droite}}{{/tr}}
            </td>
            <td>
              {{tr}}CExamenNouveauNe.oreille_gauche.{{$_oea->oreille_gauche}}{{/tr}}
            </td>
            <td class="me-text-align-center">
              {{if $_oea->oreille_droite === 'positif'
              && $_oea->oreille_gauche === 'positif'}}
                <i class="fa fa-check" style="color: #078227"></i>
              {{elseif $_oea->oreille_droite === 'negatif'
              && $_oea->oreille_gauche === 'negatif'}}
                <i class="fa fa-times" style="color: #820001"></i>
              {{else}}
                <i class="fa fa-exclamation-triangle" style="color: #ffc107"></i>
              {{/if}}
            </td>
            <td>{{mb_value object=$_oea field=rdv_orl}}</td>
          </tr>
        {{/if}}
      {{/foreach}}
    </table>
  </div>
{{else}}
    {{if $object->_class != "CPrescriptionLineElement"}}
        {{tr}}No{{/tr}}
    {{/if}}
{{/if}}
