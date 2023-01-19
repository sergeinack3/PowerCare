{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span style="float: right;">
  {{if $_line->date_arret}}
    <i class="me-icon cross-circle me-error"
       title="{{tr}}CPrescriptionLineMedicament._arret{{/tr}} : {{mb_value object=$_line field=date_arret}}{{if $_line->time_arret}} {{tr}}to{{/tr}} {{mb_value object=$_line field=time_arret}}{{/if}}"></i>
  {{/if}}
  {{assign var=to value=$_line->date_arret}}
  {{if !$to}}
    {{assign var=to value=$_line->_fin_reelle}}
  {{/if}}
  {{mb_include module=system template=inc_opened_interval_date from=$_line->debut}}
</span>

{{assign var=element value=$_line->_ref_element_prescription}}
{{assign var=category value=$element->_ref_category_prescription}}

{{if !@$only_comment}}
  <strong onmouseover="ObjectTooltip.createDOM(this, 'details-{{$element->_guid}}')">
    <span class="mediuser" style="border-left-color: #{{$element->_color}};">
    {{$element}}
    </span>
  </strong>
{{/if}}

<div id="details-{{$element->_guid}}" style="display: none;">
  <strong>{{mb_label object=$element field=description}}</strong>: 
  {{$element->description|default:'Aucune'|nl2br}}
</div>

{{if $_line->commentaire}}
<div style="{{if @$only_comment}}display: inline; margin-left: 10px;{{else}}margin-left: 25px;{{/if}}" class="text message">{{$_line->commentaire}}</div>
{{/if}}
