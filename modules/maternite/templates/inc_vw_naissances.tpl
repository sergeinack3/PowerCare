{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  onMergeComplete = Naissance.refreshGrossesse.curry('{{$operation->_id}}');
</script>

{{if $doublons|@count && $can->admin}}
  <div class="small-warning">
    Des doublons de naissances ont été détectés :
    {{foreach from=$doublons item=_doublons_by_sejour_enfant key=key_doublon}}
      <div>
        <form name="mergeNaissances{{$key_doublon}}" method="get">
          <button type="button" class="merge notext" onclick="Naissance.doMerge(this.form);"></button>
          {{foreach from=$_doublons_by_sejour_enfant item=_naissance name=doublon}}
            <input type="hidden" name="objects_id[]" value="{{$_naissance->_id}}" />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_ref_sejour_enfant->_guid}}');">
          {{$_naissance->_ref_sejour_enfant->_ref_patient}}
        </span>
            {{if !$smarty.foreach.doublon.last}} &mdash; {{/if}}
          {{/foreach}}
        </form>
      </div>
    {{/foreach}}
  </div>
{{/if}}

<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="title me-text-align-center me-no-bg me-no-title" colspan="5">
      <button type="button" class="add" style="float: left;" {{if $grossesse->_id && !$grossesse->active}}disabled{{/if}}
              onclick="Naissance.edit(0, '{{$operation->_id}}', '{{$sejour->_id}}');">Naissance
      </button>
      {{if "maternite CGrossesse manage_provisoire"|gconf}}
        <button type="button" class="add me-tertiary me-dark" style="float: left;" {{if $grossesse->_id && !$grossesse->active}}disabled{{/if}}
                onclick="Naissance.edit(0, '{{$operation->_id}}', '{{$sejour->_id}}', 1);">Dossier provisoire
        </button>
      {{/if}}
      {{if $grossesse->_id}}
        <form name="closeGrossesse" method="post"
              onsubmit="return onSubmitFormAjax(this, Naissance.refreshGrossesse.curry('{{$operation->_id}}'));">
          {{mb_class object=$grossesse}}
          {{mb_key   object=$grossesse}}
          {{if $grossesse->active}}
            <input type="hidden" name="active" value="0" />
            <button type="button" class="tick" onclick="Modal.open('date_cloture', {width: 300, showClose: true} );"
                    style="float: right;">{{tr}}CGrossesse-stop_grossesse{{/tr}}</button>

            <div id="date_cloture" style="display: none;">
              <table class="form">
                <tr>
                  <th class="title">{{mb_title object=$grossesse field=datetime_cloture}}</th>
                </tr>
                <tr>
                  <td class="button">{{mb_field object=$grossesse field=datetime_cloture register=true
                    form="closeGrossesse" style="width: 10em;" class="notNull"}}</td>
                </tr>
                <tr>
                  <td class="button">
                    <button type="button" class="edit" onclick="Control.Modal.close(); this.form.onsubmit();">{{tr}}Edit{{/tr}}</button>
                  </td>
                </tr>
              </table>
            </div>
          {{else}}
            <input type="hidden" name="active" value="1" />
            <input type="hidden" name="datetime_cloture" value="" />
            <button type="button" class="cancel"
            title="{{tr}}CGrossesse-datetime_cloture{{/tr}} {{tr var1=$grossesse->datetime_cloture|date_format:$conf.date var2=$grossesse->datetime_cloture|date_format:$conf.time}}common-the %s at %s{{/tr}}"
            onclick="this.form.onsubmit()"
                    style="float: right;">{{tr}}CGrossesse-reactive_grossesse{{/tr}}</button>
          {{/if}}
        </form>
      {{/if}}
      Naissances
    </th>
  </tr>
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{mb_label class=CNaissance field=rang}} / {{mb_label class=CNaissance field=date_time}}</th>
    <th class="category">{{tr}}CPatient{{/tr}}</th>
    <th class="category">{{tr}}CSejour{{/tr}}</th>
  </tr>
  {{foreach from=$naissances item=_naissance}}
    {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
    {{assign var=enfant value=$sejour_enfant->_ref_patient}}
    <tr>
      <td>
        <button type="button" class="edit notext"
                onclick="Naissance.edit('{{$_naissance->_id}}', '{{$operation->_id}}', '{{$sejour->_id}}')">{{tr}}Edit{{/tr}}</button>
        <button type="button" class="trash notext not-printable me-tertiary me-dark"
                onclick="Naissance.cancelNaissance('{{$_naissance->_id}}', '{{$operation->_id}}', '{{$sejour->_id}}');">{{tr}}Delete{{/tr}}</button>
      </td>
      <td>
        {{if $_naissance->date_time}}
          Le {{$_naissance->date_time|date_format:$conf.date}} à {{$_naissance->date_time|date_format:$conf.time}}
        {{else}}
          {{$_naissance}}
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$enfant->_guid}}')">{{$enfant}}</span>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour_enfant->_guid}}')">{{$sejour_enfant->_shortview}}</span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CNaissance.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
