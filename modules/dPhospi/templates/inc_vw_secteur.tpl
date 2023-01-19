{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CSecteur" action="" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.close();}});">
  {{mb_key object=$secteur}}
  {{mb_class object=$secteur}}
  {{mb_field object=$secteur field=group_id hidden=true}}
  {{mb_field object=$secteur field=code hidden=true value=$secteur->nom}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$secteur}}

    <tr>
      <th>{{mb_label object=$secteur field=nom}}</th>
      <td>{{mb_field object=$secteur field=nom onchange="Infrastructure.setValueForm('Edit-CSecteur', 'code', this.value)"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$secteur field=description}}</th>
      <td>{{mb_field object=$secteur field=description}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $secteur->_id}}
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form,{typeName:'le secteur',objName: $V(this.form.nom)}, Control.Modal.close)">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $secteur->_id}}
  <script type="text/javascript">
    Main.add(function () {
      Infrastructure.initSecteurEditForm('{{$secteur->group_id}}');
    });


  </script>
  <div class="me-align-auto">
    <form name="addService" method="post"
          onsubmit="return onSubmitFormAjax(this, Infrastructure.reloadSecteurServices.curry('{{$secteur->_id}}'))">
      <input type="hidden" name="m" value="dPhospi" />
      <input type="hidden" name="dosql" value="do_service_aed" />
      <input type="hidden" name="service_id" value="" />
      <input type="hidden" name="secteur_id" value="{{$secteur->_id}}" />
      <input type="text" name="_service_autocomplete" />
    </form>
  </div>

  <form name="delService" method="post"
        onsubmit="return onSubmitFormAjax(this, Infrastructure.reloadSecteurServices.curry('{{$secteur->_id}}'))">
    <input type="hidden" name="m" value="dPhospi" />
    <input type="hidden" name="dosql" value="do_service_aed" />
    <input type="hidden" name="service_id" value="" />
    <input type="hidden" name="secteur_id" value="" />
  </form>
  {{mb_include module=hospi template=inc_services_secteur}}
{{/if}}
