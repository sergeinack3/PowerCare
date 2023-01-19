{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}

<script type="text/javascript">
  editId400 = function (idex_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url('sante400', 'ajax_edit_identifiant')
      .addParam('idex_id', idex_id)
      .addParam('object_class', '{{$filter->object_class}}')
      .addParam('object_id', '{{$filter->object_id}}')
      .addParam('tag', '{{$filter->tag}}')
      .addParam('id400', '{{$filter->id400}}')
      .addParam('dialog', 1)
      .requestModal(400, 400);
  }

  refreshListId400 = function (idex_id) {
    {{if !$dialog}}
    refreshListFilter();
    {{else}}
    new Url('sante400', "ajax_list_identifiants")
      .addParam('object_class', '{{$filter->object_class}}')
      .addParam('object_id', '{{$filter->object_id}}')
      .addParam('tag', '{{$filter->tag}}')
      .addParam('id400', '{{$filter->id400}}')
      .addParam('idex_id', idex_id)
      .addParam("dialog", '{{$dialog}}')
      .requestUpdate("list_identifiants");
    {{/if}}

  }

  refreshListFilter = function () {
    var form = getForm('filterFrm');
    new Url('sante400', 'ajax_list_identifiants')
      .addElement(form.object_class)
      .addElement(form.object_id)
      .addElement(form.tag)
      .addElement(form.id400)
      .addElement('dialog', '{{$dialog}}')
      .requestUpdate("list_identifiants");

    return false;
  }

  reloadId400 = function (idex_id) {
    refreshListId400(idex_id);
  }

  changePage = function (page) {
    $V(getForm('filterFrm').page, page);
  }

  Main.add(refreshListId400);
</script>

{{if $canSante400->edit}}
  <a class="button new me-float-left me-margin-left-16" onclick="editId400(0);">
    {{tr}}CIdSante400-title-create{{/tr}}
  </a>
{{/if}}

{{if !$dialog}}
  {{mb_include template=inc_filter_identifiants}}
{{/if}}

<div id="list_identifiants" class="me-padding-0"></div>
