{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $period == "hour"}}

  {{assign var=hour_number value=$_datetime|date_format:"%H"}}
  {{assign var=background value=#ddd}}
  {{if $hour_number == "00"}}
    {{assign var=background value=#aaa}}
  {{/if}}

  <th style="text-align: center; width: 3em; background-color: {{$background}};" colspan="2">
    <strong title="{{$_datetime|date_format:$conf.datetime}}">
      {{$_datetime|date_format:"%Hh"}}
    </strong>
  </th>

{{/if}}

{{if $period == "day"}}

  {{assign var=day_number value=$_datetime|date_format:"%w"}}
  {{assign var=background value="#ddd"}}
  {{if $day_number == '0' || $day_number == '6'}}
    {{assign var=background value="#aaa"}}
  {{elseif in_array('Ox\Core\CMbDT::date'|static_call:$_datetime, $bank_holidays)}}
    {{assign var=background value=#fc0}}
  {{/if}}

  <th style="text-align: center; width: 3em; background-color: {{$background}};" colspan="2">
    <strong title="{{$_datetime|date_format:$conf.longdate}}">
      {{$_datetime|date_format:"%a"|upper|substr:0:1}}
      {{$_datetime|date_format:"%d"}}
    </strong>
  </th>

{{/if}}

{{if $period == "week"}}

  {{assign var=background value=#ddd}}
  {{if $_datetime|week_number_month == 1}}
    {{assign var=background value=#aaa}}
  {{elseif in_array('Ox\Core\CMbDT::date'|static_call:$_datetime, $bank_holidays)}}
    {{assign var=background value=#fc0}}
  {{/if}}

  <th style="text-align: center; width: 3em; background-color: {{$background}};" colspan="2">
    <strong title="{{$_datetime|date_format:$conf.longdate}}">
      {{$_datetime|date_format:"%V"}}
    </strong>
  </th>

{{/if}}

