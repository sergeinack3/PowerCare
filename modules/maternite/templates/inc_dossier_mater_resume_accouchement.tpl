{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CGrossesse-back-naissances{{/tr}}</legend>
  <table class="tbl me-no-align me-no-box-shadow">
    <tr>
      <th class="category narrow">{{mb_label class=CNaissance field=rang}}</th>
      <th class="category">{{mb_label class=CNaissance field=date_time}}</th>
      <th class="category">{{tr}}CPatient{{/tr}}</th>
      <th class="category">{{tr}}CSejour{{/tr}}</th>
    </tr>
    {{foreach from=$grossesse->_ref_naissances item=_naissance}}
      {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
      {{assign var=enfant value=$sejour_enfant->_ref_patient}}
      <tr>
        <td class="button">{{mb_value object=$_naissance field=rang}}</td>
        <td>
          {{if $_naissance->date_time}}
            {{tr}}The{{/tr}} {{$_naissance->date_time|date_format:$conf.date}}
            {{tr}}to{{/tr}} {{$_naissance->date_time|date_format:$conf.time}}
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
        <td class="empty" colspan="4">{{tr}}CNaissance.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</fieldset>

<script>
  Main.add(function () {
    Control.Tabs.create('tab-accouchement', true);
  });
</script>

<ul id="tab-accouchement" class="control_tabs">
  {{foreach from=$dossier->_ref_accouchements item=_accouchement}}
    <li>
      <a href="#{{$_accouchement->_guid}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_accouchement->_guid}}')">
          {{$_accouchement->_view}}
        </span>
      </a>
    </li>
    {{foreachelse}}
    <li><a href="#CAccouchement-new">{{tr}}CAccouchement-title-create{{/tr}}</a></li>
  {{/foreach}}

  {{if $dossier->_ref_accouchements|@count}}
    <form name="Resume-accouchement-accouchement-CAccouchement-none" method="post" onsubmit="return onSubmitFormAjax(this);">
      <input type="hidden" name="@class" value="CAccouchement" />
      <input type="hidden" name="accouchement_id" value="" />
      <input type="hidden" name="_count_changes" value="1" />
      <input type="hidden" name="dossier_perinat_id" value="{{$dossier->_id}}" />
      <button type="button" class="add" style="float: right;" onclick="DossierMater.onSubmitAccouchement(this.form);">
        {{tr}}CAccouchement{{/tr}}
      </button>
    </form>
  {{/if}}
</ul>

{{foreach from=$dossier->_ref_accouchements item=accouchement}}
  <div id="{{$accouchement->_guid}}" class="me-padding-2" style="display: none;">
    {{mb_include module=maternite template=vw_edit_accouchement}}
  </div>
  {{foreachelse}}
  <div id="CAccouchement-new" class="me-padding-2" style="display: none;">
    {{mb_include module=maternite template=vw_edit_accouchement accouchement='Ox\Mediboard\Maternite\CDossierPerinat::emptyAccouchement'|static_call:""}}
  </div>
{{/foreach}}