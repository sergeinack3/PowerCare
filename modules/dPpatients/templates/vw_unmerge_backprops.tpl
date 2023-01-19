{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=patient_unmerge ajax=true}}

<script>
  Main.add(function () {
    // RAZ des onglets vus
    PatientUnmerge._init('{{$counts|@count}}', '{{$nb_backrefs}}', '{{$old_patient->_id}}', '{{$new_patient->_id}}');

    Control.Tabs.create("backprops-table", false, {
      afterChange: function (container) {
        PatientUnmerge.loadTab(container);
      }
    });

    {{foreach from=$counts key=_name item=_count}}
    Control.Tabs.setTabCount("tab-backprop-{{$_name}}", {{$_count}});
    {{/foreach}}
    {{foreach from=$empty key=_name item=_count}}
    Control.Tabs.setTabCount("tab-backprop-{{$_name}}", 0);
    {{/foreach}}
  });
</script>

{{assign var=all_backs_names value=$counts|@array_keys}}
<form name="load-all-backprops" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="dosql" value="do_bind_all_backprops" />
  <input type="hidden" name="old_patient_id" value="{{$old_patient->_id}}" />
  <input type="hidden" name="new_patient_id" value="{{$new_patient->_id}}" />
  <input type="hidden" name="all_backs_names" value="{{'|'|implode:$all_backs_names}}" />
  <input type="hidden" name="abort" value="" />

  <table class="main layout">
    <col style="width: 10%" />

    <tr>
      <td class="narrow">
        <ul class="control_tabs_vertical" id="backprops-table">
          {{foreach from=$counts key=_name item=_count}}
            {{if array_key_exists($_name, $old_patient->_backSpecs)}}
              {{assign var=initiator value=$old_patient->_backSpecs[$_name]->_initiator}}
            {{else}}
              {{assign var=initiator value='CPatient'}}
            {{/if}}
            <li><a href="#tab-backprop-{{$_name}}">{{tr}}{{$initiator}}-back-{{$_name}}{{/tr}}</a></li>
          {{/foreach}}
          {{foreach from=$empty key=_name item=_count}}
            {{if array_key_exists($_name, $old_patient->_backSpecs)}}
              {{assign var=initiator value=$old_patient->_backSpecs[$_name]->_initiator}}
            {{else}}
              {{assign var=initiator value='CPatient'}}
            {{/if}}
            <li id="{{$_name}}-line-empty" style="display: none" class="empty-backprop-line">
              <a href="#tab-backprop-{{$_name}}">{{tr}}{{$initiator}}-back-{{$_name}}{{/tr}}</a>
            </li>
          {{/foreach}}
        </ul>
      </td>

      <td colspan="3">
        {{foreach from=$counts key=_name item=_count}}
          <div id="tab-backprop-{{$_name}}" style="display:none;"></div>
        {{/foreach}}
        {{foreach from=$empty key=_name item=_count}}
          <div id="tab-backprop-{{$_name}}" style="display: none"></div>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="4">
        <button class="search notext" type="button"
                onclick="PatientUnmerge.showEmptyBackprops()">{{tr}}mod-dPpatients-show-empty-collections{{/tr}}</button>
        <button class="save" type="button" onclick="PatientUnmerge.confirmPatientUnmerge(this.form)" id="save_all_backprops"
                disabled>{{tr}}mod-dPpatients-save-all{{/tr}}</button>
        <button class="cancel" type="button"
                onclick="PatientUnmerge.abortUnmerge(this.form)">{{tr}}mod-dPpatients-abord-unmerge{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>