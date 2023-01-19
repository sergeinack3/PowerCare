{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

  showListTags = function (oForm) {
    var url = new Url("eai", "ajax_show_list_tags");
    url.addParam("object_class", $V(oForm.object_class));
    url.requestUpdate("show_list_tags");
  }

  Main.add(function () {
    showListTags(getForm('addDomainWithIdex'));
  });

</script>

<form name="addDomainWithIdex" action="?" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
  <input type="hidden" name="dosql" value="do_domain_aed" />
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="derived_from_idex" value="1" />

  <table class="tbl form">
    <tr>
      <th colspan="2" class="title">Création d'un domaine depuis un identifiant externe</th>
    </tr>

    <tr>
      <th style="width: 50%">Classe de l'idex</th>
      <td>
        <select name="object_class" onchange="showListTags(this.form)">
          {{foreach from=$idexs_class item=_idex_class}}
            <option value="{{$_idex_class}}">{{tr}}{{$_idex_class}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th style="width: 50%">Liste des tags de l'idex</th>
      <td id="show_list_tags">
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button class="submit singleclick" type="submit">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>