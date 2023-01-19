{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=onchange_mode value='return true;'}}

{{if $mode->_class == 'CModeSortieSejour'}}
  <script>
    Main.add(function() {
      var form = getForm('edit-mode-{{$mode->_class}}');
      ParametrageMode.prepareDestinationEtabExt('{{$mode->_class}}');
    });
  </script>

  {{assign var=onchange_mode
    value='ParametrageMode.changeDestination(this.form).changeOrientation(this.form).toggleEtabExterne(this.form);'}}
{{/if}}

<form name="edit-mode-{{$mode->_class}}" method="post"
      onsubmit="return ParametrageMode.submitSaveForm(this);">
  <input type="hidden" name="m" value="planningOp" />
  {{mb_class object=$mode}}
  {{mb_key object=$mode}}
  {{mb_field object=$mode field=group_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$mode}}

    <tr>
      <th style="width: 40%;">{{mb_label object=$mode field=code}}</th>
      <td>{{mb_field object=$mode field=code}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$mode field=libelle}}</th>
      <td>{{mb_field object=$mode field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$mode field=mode}}</th>
      <td>{{mb_field object=$mode field=mode onchange=$onchange_mode}}</td>
    </tr>
    {{if $mode->_class !== 'CModeSortieSejour'}}
      <tr>
        <th>{{mb_label object=$mode field=provenance}}</th>
        <td>{{mb_field object=$mode field=provenance emptyLabel="Choose" onchange=$onchange_mode}}</td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$mode field=actif}}</th>
      <td>{{mb_field object=$mode field=actif}}</td>
    </tr>

    {{if $mode->_class == 'CModeSortieSejour'}}
      <tr>
        <th>{{mb_label object=$mode field=destination}}</th>
        <td>{{mb_field object=$mode  emptyLabel="Choose" field=destination}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$mode field=orientation}}</th>
        <td>{{mb_field object=$mode  emptyLabel="Choose" field=orientation}}</td>
      </tr>
      <tr class="etab_externe" {{if $mode->mode != "transfert"}}style="display: none;"{{/if}}>
        <th>{{mb_label object=$mode field=etab_externe_id}}</th>
        <td>
          {{mb_field object=$mode field=etab_externe_id hidden=true}}
          <input name="etab_externe_id_view" value="{{$mode->_ref_etab_externe}}"/>
        </td>
      </tr>
    {{/if}}

    <tr>
      <td colspan="2" class="button">
        {{if $mode->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="ParametrageMode.submitRemoveForm(this.form, '{{$mode->_view|smarty:nodefaults|JSAttribute}}');">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
