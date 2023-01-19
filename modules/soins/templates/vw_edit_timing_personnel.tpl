{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-{{$timing->_guid}}" action="" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.close();}});">
  {{mb_key   object=$timing}}
  {{mb_class object=$timing}}
  {{mb_field object=$timing field=group_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$timing}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$timing mb_field=name}}
        {{mb_field object=$timing field=name}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$timing mb_field=description}}
        {{mb_field object=$timing field=description}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$timing mb_field=time_debut class="me-large-datetime"}}
        {{mb_field object=$timing field=time_debut form="Edit-`$timing->_guid`"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$timing mb_field=time_fin class="me-large-datetime"}}
        {{mb_field object=$timing field=time_fin form="Edit-`$timing->_guid`"}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $timing->_id}}
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="
                    confirmDeletion(this.form,{typeName:'le timing',objName: $V(this.form.name), ajax: true }, Control.Modal.close)">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
