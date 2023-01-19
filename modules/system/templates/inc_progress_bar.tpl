{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* The two values to get percentage from *}}
{{mb_default var=numerator value=0}}
{{mb_default var=denominator value=0}}
{{mb_default var=percentage value=false}}

{{* Use this to create new themes *}}
{{mb_default var=theme value="default"}}

{{* Display the unit as texte (time units only) *}}
{{mb_default var=unit value=false}}

{{* Sets the number of decimals to display *}}
{{mb_default var=precision value=0}}

{{mb_default var=status value="empty"}}

{{* Use this as a charge bar (criticity grows as percentage) *}}
{{mb_default var=charge value=false}}

{{* Displays text (percentage and/or values) inside progress bar, if not a title attribute will hold the text *}}
{{mb_default var=text value=true}}

{{* In modern theme only, if true, displays values over percentage (available when mouse over bar) *}}
{{mb_default var=text_alt value=false}}

{{* In modern theme only, if true, displays values over percentage (available when mouse over bar) *}}
{{mb_default var=text_center value=false}}

{{* Thresholds *}}
{{mb_default var=threshold1 value=60}}
{{mb_default var=threshold2 value=80}}

{{if !$percentage}}
  {{* Calculate percentage from numerator and denominator *}}
  {{mb_default var=pct value=0}}
  {{if $denominator != 0}}
    {{math equation=(x/y)*100 x=$numerator y=$denominator assign=pct}}
  {{/if}}

  {{* Some unit options are available to display human readable values *}}
  {{if $unit == "h" || $unit == "min"}}
    {{assign var=numerator   value='Ox\Core\CMbDT::convertToHours'|static_call:$numerator:$unit}}
    {{assign var=denominator value='Ox\Core\CMbDT::convertToHours'|static_call:$denominator:$unit}}
  {{elseif $unit == "o"}}
    {{assign var=numerator   value='Ox\Core\CMbString::toDecaSI'|static_call:$numerator}}
    {{assign var=denominator value='Ox\Core\CMbString::toDecaSI'|static_call:$denominator}}
  {{elseif $unit == "B"}}
    {{assign var=numerator   value='Ox\Core\CMbString::toDecaBinary'|static_call:$numerator}}
    {{assign var=denominator value='Ox\Core\CMbString::toDecaBinary'|static_call:$denominator}}
  {{/if}}
{{else}}
  {{* Use percentage is provided directly *}}
  {{mb_default var=pct value=$percentage}}
{{/if}}

{{assign var=width value=$pct}}

{{if $theme == "modern"}}
  {{if $charge}}
    {{if $pct lte 0}}
      {{assign var=status value="empty"}}
    {{elseif $pct lt $threshold1}}
      {{assign var=status value="ok"}}
    {{elseif $pct lt $threshold2}}
      {{assign var=status value="warning"}}
    {{elseif $pct lt 100}}
      {{assign var=status value="error"}}
    {{elseif $pct gte 100}}
      {{assign var=width value=100}}
      {{assign var=status value="error"}}
    {{/if}}
  {{else}}
    {{if $pct lte 0}}
      {{assign var=status value="empty"}}
    {{elseif $pct lte $threshold1}}
      {{assign var=status value="error"}}
    {{elseif $pct lte $threshold2}}
      {{assign var=status value="warning"}}
    {{elseif $pct lt 100}}
      {{assign var=status value="ok"}}
    {{elseif $pct gte 100}}
      {{assign var=width value=100}}
      {{assign var=status value="ok"}}
    {{/if}}
  {{/if}}
{{else}}
  {{if $pct lt 50}}
    {{assign var=status value="full"}}
  {{elseif $pct lt 75}}
    {{assign var=status value="booked"}}
  {{elseif $pct lt 100}}
    {{assign var=status value="normal"}}
  {{elseif $pct gte 100}}
    {{assign var=width value=100}}
    {{assign var=status value="normal"}}
  {{/if}}
{{/if}}

{{if $precision >= 0}}
  {{assign var=format value="%."|cat:$precision|cat:"f"}}
  {{assign var=pct    value=$pct|string_format:$format}}
  {{assign var=width  value=$width|string_format:$format}}
{{/if}}

{{if $theme == "modern"}}
  <div class="progressBarModern{{if $text && $text_alt}} alternate{{/if}}"{{if !$text}} title="{{$numerator}} / {{$denominator}} ({{$pct}}%)"{{/if}}>
    <div class="bar bar-{{$status}}" style="width:{{$width}}%;">
      {{if $text}}
        <div class="progress">{{$pct}}%</div>
        <div class="values">
          {{if !$percentage}}
            {{if $numerator != $denominator}}
              {{$numerator}}&nbsp;/&nbsp;{{$denominator}}
            {{else}}
              {{$numerator}}
            {{/if}}
          {{else}}
            {{$pct}}%
          {{/if}}
        </div>
      {{/if}}
    </div>
  </div>
{{else}}
  <div class="progressBar" {{if !$text}}title="{{$numerator}} / {{$denominator}} ({{$pct}}%)"{{/if}}>
    <div class="bar {{$status}}" style="width:{{$width}}%;"></div>
    {{if $text}}
      <div class="text">
        {{if $numerator != $denominator}}
          {{$numerator}}&nbsp;/&nbsp;{{$denominator}}
        {{else}}
          {{$numerator}}
        {{/if}}
      </div>
    {{/if}}
  </div>
{{/if}}
