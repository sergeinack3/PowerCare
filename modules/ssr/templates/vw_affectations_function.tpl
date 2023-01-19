{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=codes_affectation ajax=$ajax}}

<script>
  Main.add(function () {
    var code_input = $('code_input');
    new Url('ssr', 'ajax_presta_ssr_autocomplete')
      .addParam('code', code_input.value)
      .addParam('all', '1')
      .autoComplete('code_input', "code_autocomplete", {
        dropdown:      true,
        minChars:      1,
        method:        "get",
        select:        "value",
        updateElement: function (selected) {
          var code = selected.querySelector('strong').innerHTML;

          // Add the affectation code to the function
          var form = getForm('ad_affectation_code');
          form.code.value = code;
          form.function_id.value = code_input.dataset.functionId;
          form.onsubmit(this);
        }
      });

    CodesAffectation.loadAffectations('{{$function->_id}}');
  });
</script>

{{* Fake form to save and delete data *}}
<form name="ad_affectation_code"
      style="display: none;"
      method="post"
      onsubmit="return onSubmitFormAjax(this, { onComplete: function() {
        CodesAffectation.loadAffectations(getForm(ad_affectation_code).function_id.value);
      }});">
  {{mb_class object=$affectation}}
  {{mb_key object=$affectation}}

  <input type="hidden" name="del" value="0">

  {{mb_field object=$affectation field=code_type value="H+" hidden=true}}
  {{mb_field object=$affectation field=code hidden=true}}
  {{mb_field object=$affectation field=function_id value=$function->_id hidden=true}}
</form>

<table class="tbl main">
  <tr>
    <th class="title" colspan="3">{{tr}}CCodeAffectation-Edit{{/tr}}</th>
  </tr>
  <tr>
    <td>{{mb_include module=mediusers template=inc_vw_function}}</td>
    <td>
      <form name="code_form" method="get">
        <label for="code_input"></label><input type="text" id="code_input" name="code" class="autocomplete" data-function-id="{{$function->_id}}">
        <div style="display: none;" class="autocomplete" id="code_autocomplete"></div>
      </form>
    </td>
    <td></td>
  </tr>

  <tbody id="affectations_codes"></tbody>
</table>
