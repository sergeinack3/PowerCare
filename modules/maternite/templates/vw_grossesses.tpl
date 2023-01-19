{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    var form = getForm("filterDate");
    Calendar.regField(form.date, null, {noView: true});
  });

  editSejour = function (sejour_id, grossesse_id, parturiente_id) {
    var url = new Url("dPplanningOp", "vw_edit_sejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("grossesse_id", grossesse_id);
    url.addParam("patient_id", parturiente_id);
    url.addParam("dialog", 1);

    url.modal({width: 1000, height: 700});
    url.modalObject.observe("afterClose", function () {
      getForm('filterDate').submit();
    });
  }
</script>

{{mb_script module=admissions script=admissions}}

<form name="filterDate" method="get" action="?">
  <input type="hidden" name="m" value="maternite" />
  <input type="hidden" name="tab" value="vw_grossesses" />
  <input type="hidden" name="show_cancelled" value="{{$show_cancelled}}" />
  <strong>
    <a href="#1" onclick="var form = getForm('filterDate'); $V(form.date, this.get('date')); form.submit();" data-date="{{$date_min}}">&lt;&lt;&lt;</a>
    {{$date|date_format:$conf.longdate}}
    <input type="hidden" name="date" value="{{$date}}" class="notNull" onchange="this.form.submit()" />
    <a href="#1" onclick="var form = getForm('filterDate'); $V(form.date, this.get('date')); form.submit();" data-date="{{$date_max}}"">&gt;&gt;&gt;</a>
  </strong>
  <label>
    <input type="checkbox" {{if $show_cancelled}}checked{{/if}}
           onclick="$V(this.form.show_cancelled, this.checked ? 1 : 0); this.form.submit()" /> Afficher les séjours annulés
  </label>
</form>

<table class="tbl me-no-align" id="admissions">
  <tr>
    <th class="title" colspan="5">
      <button class="new not-printable" onclick="editSejour(0);" style="float: left;">
        {{tr}}CSejour-title-create{{/tr}}
      </button>
      {{$grossesses|@count}} Grossesse(s) arrivant à terme entre le {{$date_min|date_format:$conf.date}} et
      le {{$date_max|date_format:$conf.date}}
    </th>
  </tr>
  <tr>
    <th class="category">
      Terme prévu
    </th>
    <th class="category">
      {{tr}}CPatient{{/tr}}
    </th>
    <th class="category narrow">
      <input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions')" id="filter-patient-name" />
    </th>
    <th class="category">
      {{tr}}CSejour.all{{/tr}}
    </th>
  </tr>
  {{foreach from=$grossesses item=_grossesse}}
    <tr>
      <td style="width: 8%">
        {{$_grossesse->terme_prevu|date_format:$conf.date}}
      </td>
      <td colspan="2" style="width: 15%">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_guid}}')" class="CPatient-view">
          {{$_grossesse->_ref_parturiente}}
        </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_grossesse->_ref_parturiente}}
      </td>
      <td>
        <button class="new notext not-printable" title="{{tr}}CSejour-title-create{{/tr}}"
                onclick="editSejour(0, '{{$_grossesse->_id}}', '{{$_grossesse->parturiente_id}}');"></button>
        {{foreach from=$_grossesse->_ref_sejours item=_sejour}}
          {{if $show_cancelled || !$_sejour->annule}}
            <span class="{{if $_sejour->annule}}cancelled{{elseif $_sejour->sortie_reelle}}hatching{{/if}}">
              <button type="button" class="edit notext not-printable" onclick="editSejour({{$_sejour->_id}})"></button>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                {{$_sejour}}
              </span>
            </span>
          {{/if}}
        {{/foreach}}
        {{assign var=consult value=$_grossesse->_ref_last_consult_anesth}}
        {{if $consult->_id}}
          &mdash;
          <span onmouseover="ObjectTooltip.createEx(this, '{{$consult->_ref_consult_anesth->_guid}}')">
            {{$consult}} du {{$consult->_ref_plageconsult->date|date_format:$conf.date}}
          </span>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">{{tr}}CGrossesse.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
