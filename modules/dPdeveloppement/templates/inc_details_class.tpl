{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl" name="details_class">
  <tr>
    <th colspan="5">
      {{$data_model->class_select}} ({{tr}}{{$data_model->class_select}}{{/tr}})
    </th>
  </tr>

  {{if $data_model->show_refs}}
    <tr>
      <th colspan="5" class="section">{{tr}}CModelGraph-references{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CModelGraph-property-name{{/tr}}</th>
      <th>{{tr}}CModelGraph-property-name-desc{{/tr}}</th>
      <th>{{tr}}CModelGraph-description{{/tr}}</th>
      <th>{{tr}}CModelGraph-type{{/tr}}</th>
      <th>{{tr}}CModelGraph-type-bdd{{/tr}}</th>
    </tr>
    <tr>
    {{foreach from=$refs key=_key item=_prop}}
      <tr>
        <td>
          {{$_key}}
        </td>
        <td class="text">
          {{tr}}{{$data_model->class_select}}-{{$_key}}{{/tr}}
        </td>
        <td class="text compact">
          {{tr}}{{$data_model->class_select}}-{{$_key}}-desc{{/tr}}
        </td>
        <td class="text">
          {{$_prop|smarty:nodefaults}}
        </td>
        <td class="text">
          {{$db_spec[$_key]}}
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
  {{if $data_model->show_properties}}
    <tr>
      <th colspan="5" class="section">{{tr}}CModelGraph-properties{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CModelGraph-property-name{{/tr}}</th>
      <th>{{tr}}CModelGraph-property-name-desc{{/tr}}</th>
      <th>{{tr}}CModelGraph-description{{/tr}}</th>
      <th>{{tr}}CModelGraph-type{{/tr}}</th>
      <th>{{tr}}CModelGraph-type-bdd{{/tr}}</th>
    </tr>
    <tr>
    {{foreach from=$plainfield key=_key item=_prop}}
      <tr>
        <td>
          {{$_key}}
        </td>
        <td class="text">
          {{tr}}{{$data_model->class_select}}-{{$_key}}{{/tr}}
        </td>
        <td class="text compact">
          {{tr}}{{$data_model->class_select}}-{{$_key}}-desc{{/tr}}
        </td>
        <td class="text">
          {{$_prop|smarty:nodefaults}}
        </td>
        <td class="text">
          {{$db_spec[$_key]}}
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
  {{if $data_model->show_formfields}}
    <tr>
      <th colspan="5" class="section">{{tr}}CModelGraph-form_fields{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CModelGraph-property-name{{/tr}}</th>
      <th>{{tr}}CModelGraph-property-name-desc{{/tr}}</th>
      <th>{{tr}}CModelGraph-description{{/tr}}</th>
      <th colspan="2">{{tr}}CModelGraph-type{{/tr}}</th>
    </tr>
    {{foreach from=$formfield key=_key item=_prop}}
      <tr>
        <td>
          {{$_key}}
        </td>
        <td class="text">
          {{tr}}{{$data_model->class_select}}-{{$_key}}{{/tr}}
        </td>
        <td class="text compact">
          {{tr}}{{$data_model->class_select}}-{{$_key}}-desc{{/tr}}
        </td>
        <td class="text" colspan="2">
          {{$_prop|smarty:nodefaults}}
        </td>

      </tr>
    {{/foreach}}
  {{/if}}
  {{if $data_model->show_heritage}}
    <tr>
      <th colspan="5" class="section">{{tr}}CModelGraph-herited_fields{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CModelGraph-property-name{{/tr}}</th>
      <th>{{tr}}CModelGraph-property-name-desc{{/tr}}</th>
      <th>{{tr}}CModelGraph-description{{/tr}}</th>
      <th colspan="2">{{tr}}CModelGraph-type{{/tr}}</th>
    </tr>
    {{foreach from=$heritage key=_key item=_heritage}}
      <tr>
        <td>
          {{$_key}}
        </td>
        <td class="text">
          {{tr}}{{$data_model->class_select}}-{{$_key}}{{/tr}}
        </td>
        <td class="text compact">
          {{tr}}{{$data_model->class_select}}-{{$_key}}-desc{{/tr}}
        </td>
        <td class="text" colspan="2">
          {{$_heritage|smarty:nodefaults}}
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
  {{if $data_model->show_backs}}
    <tr>
      <th colspan="5" class="section">{{tr}}CModelGrap-collections{{/tr}}</th>
    </tr>
    <tr>
      <th colspan="3">{{tr}}CModelGraph-property-name{{/tr}}</th>
      <th colspan="2">{{tr}}CModelGraph-type{{/tr}}</th>
    </tr>
    {{foreach from=$backprops key=_key_back item=_back}}
      <tr>
        <td colspan="3">
          {{$_key_back}}
        </td>
        <td class="text" colspan="2">
          {{$_back}}
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>