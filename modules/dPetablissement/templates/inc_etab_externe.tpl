{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete ajax=1}}

<script>
  Main.add(function () {
    InseeFields.initCPVille('etabExterne', 'cp', 'ville', null, null, 'tel');

    var row = $('{{$etab_externe->_guid}}-row');
    if (row) {
      row.addUniqueClassName('selected');
    }
  });
</script>

<form name="etabExterne" method="post" action="?" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {
    Control.Modal.close();
  {{if $etab_externe->_id}}
    Group.reloadEtabExterneLine('{{$etab_externe->_guid}}', '{{$selected}}');
  {{else}}
    Group.reloadListEtabExternes('{{$selected}}');
  {{/if}}

  }});">
  {{mb_class object=$etab_externe}}
  {{mb_key   object=$etab_externe}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$etab_externe}}

    <tr>
      <th>{{mb_label object=$etab_externe field="nom"}}</th>
      <td>{{mb_field object=$etab_externe field="nom" tabindex="1" size=40}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field="raison_sociale"}}</th>
      <td>{{mb_field object=$etab_externe field="raison_sociale" tabindex="2" size=40}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field="adresse"}}</th>
      <td>{{mb_field object=$etab_externe field="adresse" tabindex="3"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$etab_externe field="cp"}}</th>
      <td>{{mb_field object=$etab_externe field="cp" tabindex="4"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$etab_externe field="ville"}}</th>
      <td>{{mb_field object=$etab_externe field="ville" tabindex="5"}}</td>
    </tr>
    
    
    <tr>
      <th>{{mb_label object=$etab_externe field="tel"}}</th>
      <td>{{mb_field object=$etab_externe field="tel" tabindex="6"}}</td>
    </tr>
    <tr>
       <th>{{mb_label object=$etab_externe field="fax"}}</th>
       <td>{{mb_field object=$etab_externe field="fax" tabindex="7"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field="finess"}}</th>
      <td>{{mb_field object=$etab_externe field="finess" tabindex="8"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field="siret"}}</th>
      <td>{{mb_field object=$etab_externe field="siret"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field="ape"}}</th>
      <td>{{mb_field object=$etab_externe field="ape"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field=provenance}}</th>
      <td>{{mb_field object=$etab_externe field=provenance emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field=destination}}</th>
      <td>{{mb_field object=$etab_externe field=destination emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$etab_externe field=priority}}</th>
      <td>{{mb_field object=$etab_externe field=priority}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
      {{if $etab_externe->_id}}
        <button class="modify" type="button" onclick="this.form.onsubmit();">
          {{tr}}Save{{/tr}}
        </button>
        <button class="trash" type="button" name="delete"
                onclick="confirmDeletion(this.form, {
                  typeName: 'l\'établissement',
                  objName:'{{$etab_externe->nom|smarty:nodefaults|JSAttribute}}',
                  ajax:true}, {onComplete:
                  function() {
                    Control.Modal.close();
                    Group.reloadListEtabExternes();
                  }});">
          {{tr}}Delete{{/tr}}
        </button>
      {{else}}
        <button class="new" type="button" onclick="this.form.onsubmit();">
          {{tr}}Create{{/tr}}
        </button>
      {{/if}}
      </td>
    </tr>
  </table>
</form>
