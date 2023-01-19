{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('select_date');
    form.elements.year.addSpinner({min:0, step: 1});

    var date = new Date('{{$date}}');

    var countries = {
      1: [],
      2: [10, 12],
      3: []
    };

    var holidays_by_country = {};
    for (var _country in countries) {
      holidays_by_country[_country] = {
        'holidays': Calendar.getDateHolidays(date, parseInt(_country)),
        'cp_holidays': []
      };

      for (var i = 0, l = countries[_country].length; i < l; i++) {
        var _region = countries[_country][i];
        holidays_by_country[_country]['cp_holidays'][_region] = Calendar.getDateHolidaysCP(date, parseInt(_country), _region);
      }
    }

    {{foreach from=$holidays_by_country key=_country item=_holidays}}
      {{if $_holidays.holidays}}
        {{foreach from=$_holidays.holidays key=_date item=_day}}
          var _td = $('holiday_{{$_country}}_{{$_date}}').down('td').next();
          var _js_date = holidays_by_country[{{$_country}}]['holidays'].indexOf('{{$_date}}');

          if (_js_date != -1) {
            _td.innerHTML = holidays_by_country[{{$_country}}]['holidays'][_js_date];
            _td.setStyle({backgroundColor: 'forestgreen'});
          }
          else {
            _td.setStyle({backgroundColor: 'firebrick'});
          }
        {{/foreach}}
      {{/if}}

      {{if $_holidays.cp_holidays}}
        {{foreach from=$_holidays.cp_holidays key=_region item=_dates}}
          {{foreach from=$_dates key=_date item=_day}}
            var _td = $('cp_holiday_{{$_country}}_{{$_region}}_{{$_date}}').down('td').next();
            var _js_date = holidays_by_country[{{$_country}}]['cp_holidays'][{{$_region}}].indexOf('{{$_date}}');

            if (_js_date != -1) {
              _td.innerHTML = holidays_by_country[{{$_country}}]['cp_holidays'][{{$_region}}][_js_date];
              _td.setStyle({backgroundColor: 'forestgreen'});
            }
            else {
              _td.setStyle({backgroundColor: 'firebrick'});
            }
          {{/foreach}}
        {{/foreach}}
      {{/if}}
    {{/foreach}}
  });
</script>

<table class="main tbl">
  <tr>
    <th class="title">
      <form name="select_date" method="get">
        <input type="hidden" name="m" value="dPdeveloppement" />
        <input type="hidden" name="tab" value="holidays_tester" />

        <input type="text" name="year" value="{{$year}}" size="3" />

        <button type="submit" class="search notext">{{tr}}Search{{/tr}}</button>
      </form>
    </th>

    <th class="title" colspan="2"></th>
  </tr>

  {{foreach from=$holidays_by_country key=_country item=_holidays}}
    <tr>
      <th class="title" colspan="3">{{tr}}config-ref_pays-{{$_country}}{{/tr}}</th>
    </tr>

    {{if $_holidays.holidays}}
      <tr>
        <th class="category" colspan="3">{{tr}}CMbDT-holidays{{/tr}}</th>
      </tr>

      <tr>
        <th></th>
        <th class="section" style="background-color: steelblue;">PHP</th>
        <th class="section" style="background-color: firebrick;">Javascript</th>
      </tr>

      {{foreach from=$_holidays.holidays key=_date item=_day}}
        <tr id="holiday_{{$_country}}_{{$_date}}">
          <th class="narrow">{{$_day}}</th>
          <td>{{$_date}}</td>

          <td></td>
        </tr>
      {{/foreach}}
    {{/if}}

    {{if $_holidays.cp_holidays}}
      <tr>
        <th class="category" colspan="3">{{tr}}CMbDT-cp_holidays{{/tr}}</th>
      </tr>

      <tr>
        <th></th>
        <th class="section" style="background-color: steelblue;">PHP</th>
        <th class="section" style="background-color: firebrick;">Javascript</th>
      </tr>

      {{foreach from=$_holidays.cp_holidays key=_region item=_dates}}
        <tr>
          <th class="section" colspan="3">{{$_region}}</th>
        </tr>

        {{foreach from=$_dates key=_date item=_day}}
          <tr id="cp_holiday_{{$_country}}_{{$_region}}_{{$_date}}">
            <th class="narrow">{{$_day}}</th>
            <td>{{$_date}}</td>

            <td></td>
          </tr>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
  {{/foreach}}
</table>