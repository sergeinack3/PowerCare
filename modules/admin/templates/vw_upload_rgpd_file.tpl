{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=form}}

<script>
  Main.add(function () {
    var form = getForm('{{$form}}');

    form.select('div.consent-choice').each(function (elt) {
      elt.down("input[type='radio']").on('change', function (e) {
        this.up('div').addUniqueClassName('selected', 'table');
      });
    });
  });

  checkRGPDForm = function(form) {
    var status    = $V(form.elements.status);
    var file_here = (form.elements['formfile[]'].length > 0);

    if (!status) {
      alert($T('CRGPDConsent-error-You have to select a status of consent.'));
      return false;
    }

    if (!file_here) {
      alert($T('CRGPDConsent-error-You have to attach a proof file to this ask for consent.'));
      return false;
    }

    return onSubmitFormAjax(form, {onComplete: Control.Modal.close});
  }
</script>

<h2>{{tr}}{{$consent->_ref_object->_class}}{{/tr}} &mdash; {{$consent->_ref_object}}</h2>

<form name="{{$form}}" method="post" enctype="multipart/form-data" onsubmit="return checkRGPDForm(this);">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_upload_rgpd_file" />
  {{mb_key object=$consent}}

  <table class="main form">
    <tr>
      <td style="text-align: center; width: 50%;">
        <div class="consent-choice" style="border: solid 2px lightgrey; border-radius: 3px; margin: 5px;">
          <label>
            <input type="radio" name="status" value="accepted" />

            <div>
              {{mb_include module=system template=inc_vw_bool_icon value=true size=2}}

              <h4>{{tr}}CRGPDConsent-label-Consent is accepted{{/tr}}</h4>
            </div>
          </label>
        </div>
      </td>

      <td style="text-align: center;">
        <div class="consent-choice" style="border: solid 2px lightgrey; border-radius: 3px; margin: 5px;">
          <label>
            <input type="radio" name="status" value="refused" />

            <div>
              {{mb_include module=system template=inc_vw_bool_icon value=false size=2}}

              <h4>{{tr}}CRGPDConsent-label-Consent is refused{{/tr}}</h4>
            </div>
          </label>
        </div>
      </td>
    </tr>
  </table>

  {{mb_include module=system template=inc_inline_upload multi=false}}

  <div style="text-align: center;">
    <button type="submit" class="save">
      {{tr}}common-action-Save{{/tr}}
    </button>
  </div>
</form>