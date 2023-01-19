{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=oxCabinet script=res_material ajax=$ajax}}

{{if $ressource->_id}}
  <script>
    Main.add(function() {
      File.register('{{$ressource->_id}}','{{$ressource->_class}}', 'files_ressource-{{$ressource->_guid}}');
    });
  </script>
{{/if}}

<form name="editRessource" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {
    Control.Modal.close();
    MaterialRessource.refreshRessources();
  }});" enctype="multipart/form-data">
  {{mb_class object=$ressource}}
  {{mb_key   object=$ressource}}
  {{mb_field object=$ressource field=function_id hidden=true}}
  {{mb_field object=$ressource field=owner_id    hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ressource}}

    <tr>
      <th>{{mb_label object=$ressource field=libelle}}</th>
      <td>{{mb_field object=$ressource field=libelle}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ressource field=description}}</th>
      <td>{{mb_field object=$ressource field=description}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ressource field=in_charge}}</th>
      <td>{{mb_field object=$ressource field=in_charge}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ressource field=color}}</th>
      <td>{{mb_field object=$ressource field=color form=editRessource register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ressource field=actif}}</th>
      <td>{{mb_field object=$ressource field=actif}}</td>
    </tr>

    {{if !$ressource->_id}}
    <tr>
      <th></th>
      <td>
        {{mb_include module=system template=inc_inline_upload class=CRessourceCab}}
      </td>
    </tr>
    {{/if}}

    {{mb_include module=system template=inc_form_table_footer object=$ressource options_ajax="Control.Modal.close"}}
  </table>
</form>

{{if $ressource->_id}}
 <fieldset>
   <legend>
     {{tr}}CMbObject-back-files{{/tr}}
   </legend>
   <div id="files_ressource-{{$ressource->_guid}}"></div>
  </fieldset>
{{/if}}
