{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_type_radio value=0}}
{{unique_id var=form_attente}}
{{mb_default var=form value="Horodatage-`$attente->_guid`-`$form_attente`"}}
{{mb_default var=show_label value=0}}

<form name="Horodatage-{{$attente->_guid}}-{{$form_attente}}" action="" method="post"
      onsubmit="return Horodatage.onSubmit(this);">
  {{mb_class object=$attente}}
  {{mb_key   object=$attente}}
  {{mb_field object=$attente field="rpu_id" value=$rpu->_id hidden=true}}
  {{mb_field object=$attente field="type_attente" value=$type_attente hidden=true}}

  {{if $see_type_radio && $type_attente == "radio" && $show_type_radio}}
    {{mb_field object=$attente field=type_radio register=true onchange="this.form.onsubmit();" emptyLabel="CRPUAttente-type_radio"}}
  {{/if}}

  {{mb_include template=inc_horodatage_field object=$attente}}
</form>
