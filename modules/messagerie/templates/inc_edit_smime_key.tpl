{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  checkCertificateType = function(input) {
    var filename = $V(input);
    var extension = filename.split('.')[filename.split('.').length -1];

    /* If the certificate is in the PKCS12 format, we display the input for the pkcs12 passphrase, and set the certificate type to PKCS12 */
    if (extension == 'p12' || extension == 'pfx') {
      $('pkcs12_passphrase').show();
      $V(input.form._certificate_type, 'pkcs12');
    }
    else {
      $('pkcs12_passphrase').hide();
      $V(input.form._certificate_type, 'pem');
    }
  };

  showLoading = function() {
    var systemMsg = $('systemMsg');
    systemMsg.update('\<div class=\'loading\'\>{{tr}}Loading in progress{{/tr}}\</div\>');
    systemMsg.show();
  };
</script>

<fieldset>
  <legend>{{tr}}CSMimeKey{{/tr}}</legend>

  <iframe name="uploadCert" id="uploadCert" style="display: none;"></iframe>

  <form name="setSMIMECert" action="?"enctype="multipart/form-data" method="post" onsubmit="return checkForm(this);" target="uploadCert">
    <input type="hidden" name="m" value="messagerie" />
    <input type="hidden" name="dosql" value="do_aed_smimekey" />
    <input type="hidden" name="ajax" value="1" />
    <input type="hidden" name="suppressHeaders" value="1" />
    <input type="hidden" name="callback" value="callbackEditSMimeKey" />

    {{mb_class object=$smime_key}}
    {{mb_key object=$smime_key}}

    <input type="hidden" name="del" value="0"/>
    {{mb_field object=$smime_key field=source_id hidden=true}}
    {{mb_field object=$smime_key field=cert_path hidden=true}}

    <table class="form">
      <tr>
        <th>{{mb_label object=$smime_key field=_cert_file}}</th>
        <td>
          <input type="file" name="certificate" size="0" onchange="checkCertificateType(this);"/>
          <input type="hidden" name="_certificate_type" value="pem"/>

          {{if $smime_key->_is_cert_set}}
            <i class="fa fa-lg fa-check" style="color: forestgreen" title="Certificat présent sur le serveur"></i>
          {{else}}
            <i class="fa fa-lg fa-times" style="color: firebrick" title="Certificat absent"></i>
          {{/if}}
        </td>
      </tr>
      <tr id="pkcs12_passphrase" style="display: none;">
        <th><label for="_pkcs12_passphrase">{{tr}}CSMimeKey-_pkcs12_passphrase{{/tr}}</label></th>
        <td>
          <input type="password" name="_pkcs12_passphrase" value=""/>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$smime_key field=_passphrase}}</th>
        <td>
          {{mb_field object=$smime_key field=_passphrase}}
          {{if $smime_key->passphrase}}
            <i class="fa fa-lg fa-check" style="color: forestgreen;" title="Renseignée"></i>
          {{else}}
            <i class="fa fa-lg fa-times" style="color: firebrick;" title="Non renseignée"></i>
          {{/if}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2" style="text-align: center;">
          <button type="submit" class="save" onclick="showLoading();">{{tr}}Save{{/tr}}</button>
          {{if $smime_key->_id}}
            <button type="submit" class="trash" onclick="$V(this.form.del, 1);">{{tr}}Delete{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
</fieldset>