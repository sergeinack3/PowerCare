{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=instanceCCAM value=$codage|instanceof:'Ox\Mediboard\Ccam\CCodageCCAM'}}
<script type="text/javascript">
  let form = getForm("form_choose_date");
  let dates = {};

  {{if $instanceCCAM}}
    dates.limit = {
      start: '{{$codage->date|iso_date}}',
      stop:  '{{$codage->_ref_codable->sortie|iso_date}}'
    };
  {{else}}
    dates.limit = {
      start: '{{$codage->execution|iso_date}}',
      stop: '{{$codable->sortie|iso_date}}'
    };
  {{/if}}

  if (form) {
    Calendar.regField(form._date, dates);
    Calendar.regField(form.calendar, dates, {noView: true, inline: true, container: null});
  }
</script>

<form name="form_choose_date">
  <table class="tbl">
    <tr>
      <th class="title" colspan="2">
        <input type="radio" value="one_date" name="type_of_date" checked
               onchange="CCodageCCAM.updateDuplicate(this.value, '{{$codage->_class}}');"/>
        {{tr}}CCodageCCAM-action-duplicate to{{/tr}}
      </th>
    </tr>
    <tbody id="section_duplicate_jusqu_au">
    <tr>
      <td>
        <label for="date_jusqu_au">{{tr}}CCodageCCAM-action-duplicate to{{/tr}}: </label>
        <input type="hidden" disabled name="_date" class="date section_duplicate_jusqu_au" id="date_jusqu_au"
               value="{{if $instanceCCAM}}{{$codage->_ref_codable->sortie|date_format:'%Y-%m-%d'}}{{else}}{{$codable->sortie|date_format:'%Y-%m-%d'}}{{/if}}"
               onchange="{{if $instanceCCAM}}$V(getForm('duplicateCodage').date, this.value); {{else}}$V(getForm('duplicateNGAP').date, this.value);{{/if}}"/>
      </td>
    </tr>
    </tbody>
    <tr>
      <th class="title" colspan="2">
        <input type="radio" value="multiple_date" name="type_of_date"
               onchange="CCodageCCAM.updateDuplicate(this.value, '{{$codage->_class}}');"/>
        {{tr}}CCodageCCAM-action-choose dates for duplication{{/tr}}
      </th>
    </tr>
    <tbody id="choose_date_duplicate" class="opacity-30">
    <tr>
      <td class="not-printable me-bloc-edit-planning-right-layout me-bg-white">
        <input type="hidden" name="calendar" class="date" id="calendar_duplicate_date" disabled onchange="CCodageCCAM.addDate(this);"/>
      </td>
      <td class="me-valign-top">
        <table id="dates_choosen" class="form me-no-box-shadow">
          <tr>
            <th class="title" colspan="12">
              {{tr}}CCodageCCAM-msg-dates selected{{/tr}}
            </th>
          </tr>
          <tr id="class_empty">
            <td class="empty">{{tr}}CCodageCCAM-msg-no date selected{{/tr}}</td>
          </tr>
        </table>
      </td>
    </tr>
    </tbody>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="submit" onclick="CCodageCCAM.submitDuplicationCodage('{{$codage->_class}}')">
            {{tr}}Duplicate{{/tr}}
        </button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">
            {{tr}}Cancel{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
