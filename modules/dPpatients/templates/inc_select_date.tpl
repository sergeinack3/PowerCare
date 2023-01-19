{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=day_index   value=$tabindex}}
{{assign var=month_index value=$tabindex+1}}
{{assign var=year_index  value=$tabindex+2}}

{{assign var=date_naissance  value=$date|replace:'%':''|default:'0000-00-00'}}

{{if @$app->user_prefs.new_date_naissance_selector}}
  {{unique_id var=form_uid}}
  <script>
    Main.add(function () {
      function focusNext(event) {
        var key = Event.key(event);
        var input = Event.element(event);

        var value = input.value;

        if (value && value.charAt(input.maxLength - 1) != "_") {
          this.select();
          return;
        }

        switch (key) {
          case 32:  // space
          case 111: // slash
            this.select();
            break;
        }
      }

      function resetInput(event) {
        var element = Event.element(event);
        var input = element.previous();
        $V(input, '');
        input.tryFocus();
      }

      var form = $("date-selector-{{$form_uid}}").down('input').form;
      var day = form.Date_Day;
      var month = form.Date_Month;
      var year = form.Date_Year;

      day.mask("99");
      month.mask("99");
      year.mask("9999");

      day.observe("keyup", focusNext.bindAsEventListener(month));
      month.observe("keyup", focusNext.bindAsEventListener(year));

      day.next('span').observe("click", resetInput);
      month.next('span').observe("click", resetInput);
      year.next('span').observe("click", resetInput);
    });
  </script>
  {{assign var=dn_parts    value="-"|explode:$date_naissance}}
  {{assign var=day_value   value=$dn_parts.2}}
  {{assign var=month_value value=$dn_parts.1}}
  {{assign var=year_value  value=$dn_parts.0}}
  <div class="date-naissance-input" id="date-selector-{{$form_uid}}">
    <input type="text" name="Date_Day" tabindex="{{$day_index}}" value="{{$day_value}}" size="2" maxlength="2" placeholder="JJ" />
    <span>x</span>
  </div>
  <div class="date-naissance-input">
    <input type="text" name="Date_Month" tabindex="{{$month_index}}" value="{{$month_value}}" size="2" maxlength="2"
           placeholder="MM" />
    <span>x</span>
  </div>
  <div class="date-naissance-input">
    <input type="text" name="Date_Year" tabindex="{{$year_index}}" value="{{$year_value}}" size="4" maxlength="4" placeholder="AAAA" />
    <span>x</span>
  </div>
{{else}}
  
  {{html_select_date
  time=$date_naissance
  start_year=1900
  day_value_format="%02d"
  month_format="%m &mdash; %B"
  field_order=DMY
  day_empty="--"
  month_empty="--"
  year_empty="----"
  day_extra="tabindex='$day_index'"
  month_extra="tabindex='$month_index' style='width: 4em;'"
  year_extra="tabindex='$year_index'"
  }}

{{/if}}

