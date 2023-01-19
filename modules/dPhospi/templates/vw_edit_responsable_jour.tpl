{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="modale_edit_responsable_jour">
  <script>
    Main.add(function () {
      var form = getForm("addResponsable-{{$service->_id}}-{{$date}}");
      var url = new Url("personnel", "httpreq_do_personnels_autocomplete");
      url.autoComplete(form._view, form._view.id + '_autocomplete', {
        minChars:      0,
        dropdown:      true,
        updateElement: function (element) {
          $V(form.user_id, element.id.split('-')[1]);
          $V(form._view, element.select(".view")[0].innerHTML.stripTags());
        },
        callback:      function (input, queryString) {
          return queryString + "&service_id=" + $V(form.service_id) + "&use_personnel_affecte=" + (form.use_personnel_affecte.checked ? 1 : 0);
        }
      });
    });
  </script>
  <table class="main tbl">
    <tr>
      <th>
        Responsable le {{$date|date_format:$conf.date}}
        <br /> pour le service {{$service->_view}}
      </th>
    </tr>
    <tr>
      <td class="button">
        {{if $responsable->_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$responsable->_ref_user}}
          <form name="trash_{{$responsable->_guid}}" action="?" method="post">
            {{mb_key   object=$responsable}}
            {{mb_class object=$responsable}}
            <input type="hidden" name="del" value="1" />
            <button type="button" class="trash notext" style="float: left;" onclick="confirmDeletion(this.form,
              {ajax: true, typeName:'le responsable du jour', objName: ''},
              {onComplete: function() {Soins.refreshModalReponsableJour('{{$date}}', '{{$service->_id}}');}});"
                    title="{{tr}}CAffectationUserService-title-delete{{/tr}}">
            </button>
          </form>
        {{else}}
          <form name="addResponsable-{{$service->_id}}-{{$date}}" action="?" method="post" style="text-align: left;">
            {{mb_key   object=$responsable}}
            {{mb_class object=$responsable}}
            {{mb_field object=$responsable field=service_id hidden=hidden}}
            {{mb_field object=$responsable field=date hidden=hidden}}
            {{mb_field object=$responsable field=user_id hidden=hidden onchange="Soins.addReponsableJour(this.form);"}}
            <input type="text" name="_view" value="" style="width: 200px;" class="autocomplete"
                   placeholder="Choisir le responsable du jour" />
            <input type="checkbox" name="use_personnel_affecte" value="1" checked />
          </form>
        {{/if}}
      </td>
    </tr>
  </table>
</div>
