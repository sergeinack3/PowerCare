{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var Action = {
    execute: function (type) {
      if (!confirm("Voulez-vous réelement éxecuter cette action ?")) {
        return;
      }

      var url = new Url("forms", "do_batch_action");
      url.addParam("action", type);
      url.requestUpdate("action-" + type);
    }
  };
</script>

<form name="editConfigForms" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">

    {{assign var=class value=CExClassField}}
    <tr>
      <th colspan="2" class="title">{{tr}}{{$class}}{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_bool var=force_concept}}
    {{mb_include module=system template=inc_config_bool var=doc_template_integration}}

    {{assign var=class value=CExConcept}}
    <tr>
      <th colspan="2" class="title">{{tr}}{{$class}}{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_bool var=force_list}}
    {{mb_include module=system template=inc_config_bool var=native_field}}

    {{assign var=class value=CExClass}}
      <tr>
        <th colspan="2" class="title">{{tr}}{{$class}}{{/tr}}</th>
      </tr>
    {{mb_include module=system template=inc_config_bool var=pixel_positionning}}
    {{mb_include module=system template=inc_config_bool var=pixel_layout_delimiter}}
    {{mb_include module=system template=inc_config_bool var=show_color_score_form}}
    {{mb_include module=system template=inc_config_bool var=check_modification_before_close}}
    {{mb_include module=system template=inc_config_bool var=display_list_readonly}}

    {{mb_include module=system template=inc_config_bool var=allowing_additional_columns}}

    <tr>
      <td class="button" colspan="10">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{*
<div class="small-error">
  Ces actions agissent sur l'ensemble des données des formulaires, elles sont à manipuler avec précaution
</div>

<table class="tbl" style="table-layout: fixed;">
  <tr>
    <th class="title" colspan="2">Actions par lot</th>
  </tr>
  
  <tr>
    <td>
      <button class="tick" onclick="Action.execute('bool_defaul_reset')">Remettre les champs et concepts booléens en "Indéfini"</button>
    </td>
    <td id="action-bool_defaul_reset"></td>
  </tr>
  
  <tr>
    <td>
      <button class="tick" onclick="Action.execute('str_to_text')">Passer les champs et concepts de type "Texte court" en "Texte long"</button>
    </td>
    <td id="action-str_to_text"></td>
  </tr>
</table>
*}}