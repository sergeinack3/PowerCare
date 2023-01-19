{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<html>
  <head>
    <style type="text/css">
      {{$style|smarty:nodefaults}}
      table div {
        font-size:12px;
      }
      td.planning-data>div:first-child{
        border-width: 0px;
      }
      td.planning-data>div {
        border-top: 1px dotted #efefef;
        margin-top: 2px;
        padding-top: 2px;
        padding-bottom: 2px;
        margin-bottom: 2px;
      }
      td.planning-data strong,
      td.planning-data div.desc,
      td.planning-data div.location>span {
        font-size: 16px;
      }
    </style>
  </head>
  <body>
    <table style="width:100%;">
      <tr>
        <td style="width: 40%;">
          {{$group->_view}}<br/>
          <br/>
          {{tr}}Week{{/tr}} {{$num_week}} (du {{$monday|date_format:$conf.date}} au {{$sunday|date_format:$conf.date}})
        </td>
        <td style="text-align: center;">
          <strong>Planning</strong>
        </td>
        <td style="text-align: right;width: 40%;">
          {{$sejour->_ref_patient->_view}}<br/>
          {{tr}}CSejour-entree{{/tr}} {{mb_value object=$sejour field=entree}}<br/>
          {{$sejour->_ref_curr_affectation->_view}}
        </td>
      </tr>
    </table>

    <table class="main tbl">
      <tr>
        <td style="max-width:15px;" class="narrow"></td>
        {{if !$current_day}}
          {{assign var=day_monday value=$monday|date_format:"%d"}}
          <th style="width: 140px;">{{tr}}Monday{{/tr}} {{$monday|date_format:"%d"}}</th>
          <th style="width: 140px;">{{tr}}Tuesday{{/tr}} {{'Ox\Core\CMbDT::date'|static_call:"+1 day":$monday|date_format:"%d"}}</th>
          <th style="width: 140px;">{{tr}}Wednesday{{/tr}} {{'Ox\Core\CMbDT::date'|static_call:"+2 day":$monday|date_format:"%d"}}</th>
          <th style="width: 140px;">{{tr}}Thursday{{/tr}} {{'Ox\Core\CMbDT::date'|static_call:"+3 day":$monday|date_format:"%d"}}</th>
          <th style="width: 140px;">{{tr}}Friday{{/tr}} {{'Ox\Core\CMbDT::date'|static_call:"+4 day":$monday|date_format:"%d"}}</th>
          <th style="width: 140px;">{{tr}}Saturday{{/tr}} {{'Ox\Core\CMbDT::date'|static_call:"+5 day":$monday|date_format:"%d"}}</th>
          <th style="width: 140px;">{{tr}}Sunday{{/tr}} {{$sunday|date_format:"%d"}}</th>
        {{else}}
          <th>{{$monday|date_format:$conf.date}}</th>
        {{/if}}
      </tr>
      {{foreach from=$evenements item=_evts_by_type key=type}}
        {{if $type == "Apres-midi"}}
          <tr>
            <th colspan="{{if !$current_day}}8{{else}}2{{/if}}">{{tr}}SSR-Bon_appetit{{/tr}}</th>
          </tr>
        {{/if}}
        <tr>
          <td style="height:40%;text-align: center;">
            {{if $type == "Matin"}}
              M<br/>A<br/>T<br/>I<br/>N
            {{else}}
              A<br/>P<br/>R<br/>E<br/>S<br/>-<br/>M<br/>I<br/>D<br/>I
            {{/if}}
          </td>
          {{foreach from=$_evts_by_type item=_evts_by_day}}
            <td class="planning-data text" style="width:14%;vertical-align: top;">
              {{foreach from=$_evts_by_day item=_evt}}
                <div>
                  <strong>{{$_evt->debut|date_format:$conf.time}} à {{$_evt->_heure_fin|date_format:$conf.time}}</strong>
                  <div class="desc">{{$_evt->_ref_prescription_line_element->_view}}</div>
                  {{if $_evt->_ref_equipement->_id}}
                    <div class="compact location">{{tr}}common-Location{{/tr}}: <span>{{$_evt->_ref_equipement->_view}}</span></div>
                  {{/if}}
                </div>
              {{/foreach}}
            </td>
          {{/foreach}}
        </tr>
      {{/foreach}}
    </table>
  </body>
</html>