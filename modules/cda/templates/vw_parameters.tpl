{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  <div>Pour vous assurer du bon fonctionnement du module. Veuillez paramétrer les différents points ci-dessous :</div>
</div>

<div class="me-padding-6">
    <table class="form">
      <tr>
        <th class="title">
          {{tr}}Group{{/tr}}
        </th>
        <th class="title">
          {{tr}}Association{{/tr}}
        </th>
      </tr>
      <tr>
        <td>
          {{$group->text}}
        </td>
        <td>
          XDS :
          <form name="form_type_code-xds" method="post" onsubmit="return onSubmitFormAjax(this)">
            <input type="hidden" name="m" value="cda" />
            <input type="hidden" name="dosql" value="do_cda_association_aed" />
            <input type="hidden" name="group_id" value="{{$group->_id}}"/>
            <input type="hidden" name="idex_tag" value="xds_association_code"/>

            <select name="group_type" onchange="this.form.onsubmit()">
              <option value="">&mdash; {{tr}}Association.none{{/tr}} &mdash;</option>
              {{foreach from=$xds_healthcareFacilityTypeCode item=_type_group}}
                <option value="{{$_type_group.code}}"
                        {{if $idex_group_xds->id400 === $_type_group.code}}selected{{/if}}>
                  {{$_type_group.code}} - {{$_type_group.displayName}}
                </option>
              {{/foreach}}
            </select>
          </form>

          <br />

          DMP :
          <form name="form_type_code-dmp" method="post" onsubmit="return onSubmitFormAjax(this)">
            <input type="hidden" name="m" value="cda" />
            <input type="hidden" name="dosql" value="do_cda_association_aed" />
            <input type="hidden" name="group_id" value="{{$group->_id}}"/>
            <input type="hidden" name="idex_tag" value="cda_association_code"/>

            <select name="group_type" onchange="this.form.onsubmit()">
              <option value="">&mdash; {{tr}}Association.none{{/tr}} &mdash;</option>
              {{foreach from=$dmp_healthcareFacilityTypeCode item=_type_group}}
                <option value="{{$_type_group.code}}"
                        {{if $idex_group_dmp->id400 === $_type_group.code}}selected{{/if}}>
                  {{$_type_group.code}} - {{$_type_group.displayName}}
                </option>
              {{/foreach}}
            </select>
          </form>
        </td>
      </tr>
    </table>
</div>
