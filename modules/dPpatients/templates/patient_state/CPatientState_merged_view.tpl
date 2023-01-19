{{*
 * @package Mediboard\Patients
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

{{assign var=log     value=$object}}
{{assign var=patient value=$log->_ref_object}}

<table class="main tbl">
  <tr>
    <th colspan="2" class="title">
      <span style="float: left;">#{{$_count}}</span>
      {{$object->_ref_object}}
    </th>
  </tr>

  {{if $patient->_back.identifiants || $log->_old_values}}
    <tr>
      <td style="vertical-align: top; width: 40%;">
        <table class="main tbl">
          <tr>
            <th colspan="3" class="section">
              {{tr}}CIdSante400|pl{{/tr}}
            </th>
          </tr>

          <tr>
            <th class="narrow"></th>
            <th>{{mb_title class=CIdSante400 field=tag}}</th>
            <th class="narrow">{{mb_title class=CIdSante400 field=id400}}</th>
          </tr>

          {{foreach from=$patient->_back.identifiants item=_id400}}
            <tr>
              <td>
                {{if $_id400->_type}}
                  <span class="idex-special idex-special-{{$_id400->_type}}">{{$_id400->_type}}</span>
                {{/if}}
              </td>

              <td>{{mb_value object=$_id400 field=tag}}</td>

              <td style="text-align: right;">{{mb_value object=$_id400 field=id400}}</td>
            </tr>
          {{/foreach}}
        </table>
      </td>

      <td style="vertical-align: top;">
        <table class="main tbl">
          <tr>
            <th colspan="3" class="section">
              {{tr}}CUserLog.type.store{{/tr}}
            </th>
          </tr>

          <tr>
            <th>{{tr}}Field{{/tr}}</th>
            <th class="narrow">Valeur avant</th>
            <th class="narrow">Valeur après</th>
          </tr>

          {{foreach from=$log->_old_values item=_field key=_field}}
            <tr>
              <td>
                {{mb_label object=$log->_ref_object field=$_field}}
              </td>

              {{if array_key_exists($_field,$log->_old_values)}}
                <td>
                  {{assign var=old_value value=$log->_old_values.$_field}}
                  {{mb_value object=$log->_ref_object field=$_field value=$old_value tooltip=1}}
                </td>
                <td>
                  {{assign var=log_id value=$log->_id}}
                  {{assign var=new_value value=$log->_ref_object->_history.$log_id.$_field}}

                  <strong>
                    {{mb_value object=$log->_ref_object field=$_field value=$new_value tooltip=1}}
                  </strong>
                </td>
              {{else}}
                <td colspan="2" class="empty">{{tr}}Unavailable information{{/tr}}</td>
              {{/if}}
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>
  {{/if}}
</table>