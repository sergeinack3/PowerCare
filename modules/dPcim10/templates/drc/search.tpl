{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}Search{{/tr}}</legend>
  <form name="filterConsultationResults" method="post" onsubmit="DRC.search(this);">
    <table class="form">
      <tr>
        <th>
          <label for="result_class_id" title="{{tr}}CDRCConsultationResult-_class-desc{{/tr}}">
            {{tr}}CDRCConsultationResult-_class{{/tr}}
          </label>
        </th>
        <td class="me-text-align-left">
          <select name="result_class_id">
            {{foreach from=$result_classes item=class}}
              <option value="{{$class->class_id}}"{{if $class->class_id == 1}} selected{{/if}}>
                {{$class->libelle}}
              </option>
            {{/foreach}}
          </select>
        </td>
        <th>
          <label for="">{{tr}}Keywords{{/tr}}</label>
        </th>
        <td class="me-text-align-left">
          <input type="text" name="keywords" value="">
        </td>
        <th>
          <label for="sex" title="{{tr}}CDRCConsultationResult-sex-desc{{/tr}}">
            {{tr}}CDRCConsultationResult-sex{{/tr}}
          </label>
        </th>
        <td class="me-text-align-left">
          <select name="sex">
            <option value="">&mdash;{{tr}}Select{{/tr}}</option>
            <option value="1">{{tr}}CDRCConsultationResult-sex-homme{{/tr}}</option>
            <option value="2">{{tr}}CDRCConsultationResult-sex-femme{{/tr}}</option>
            <option value="3">{{tr}}CDRCConsultationResult-sex-mixte{{/tr}}</option>
          </select>
        </td>
        <th>
          <label for="age">{{tr}}CDRCConsultationResult-age{{/tr}}</label>
        </th>
        <td class="me-text-align-left">
          <input type="text" name="age" size="3" value="{{$patient->_annees}}">
        </td>
      </tr>
      <tr>
        <td class="button" colspan="8">
          <button type="button" onclick="this.form.onsubmit();">
            <i class="fa fa-search"></i>
            {{tr}}Search{{/tr}}
          </button>
          <button type="button" class="cancel notext">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
  </fieldset>
