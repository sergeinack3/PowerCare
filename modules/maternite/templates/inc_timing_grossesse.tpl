{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=label              value=true}}
{{mb_default var=isDossierPerinatal value=false}}
{{mb_default var=readonly           value=false}}

{{assign var=callback value="SurveillancePerop.showPartogramme.curry(`$operation->_id`, 0, `$isDossierPerinatal`)"}}

{{if $type == "sspi"}}
  {{assign var=callback value="SurveillancePerop.showPostPartum.curry(`$operation->_id`, `$grossesse->_id`, 0, `$isDossierPerinatal`)"}}
{{/if}}

{{if $isDossierPerinatal}}
  {{assign var=callback value="Control.Modal.refresh"}}
{{/if}}

<form name="edit-grossesse-accouchement-{{$grossesse->_id}}-{{$timing}}-{{$type}}" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$grossesse}}
  {{mb_key   object=$grossesse}}
  <input type="hidden" name="callback" value="{{$callback}}" />

  {{if $grossesse->$timing}}
    {{if $label}}
      {{if $timing === "datetime_debut_travail"}}
      {{tr}}common-Start{{/tr}}
      {{else}}
      {{mb_label object=$grossesse field=$timing}}
      {{/if}}
    {{/if}}

    {{if $readonly}}
      {{mb_value object=$grossesse field=$timing}}
    {{else}}
      {{mb_field object=$grossesse field=$timing register=true
      form="edit-grossesse-accouchement-`$grossesse->_id`-$timing-$type" onchange="this.form.onsubmit()"}}
    {{/if}}
  {{else}}
    <input type="hidden" name="{{$timing}}" value="now" />
    <button type="submit" class="save not-printable notext" {{if $readonly}}disabled{{/if}}>
      {{if $timing === "datetime_debut_travail"}}
        {{tr}}common-Start{{/tr}}
      {{else}}
        {{tr}}CGrossesse-{{$timing}}{{/tr}}
      {{/if}}
    </button>
  {{/if}}
</form>
