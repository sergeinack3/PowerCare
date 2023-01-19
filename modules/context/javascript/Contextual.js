/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Contextual = {
  /**
   * Disabled buttons
   */
  disabledButton: function (classname) {
    $$('button.' + classname).each(function (button) {
      button.disable();
    })
  }
};

Context = {
  auth:      "basic",
  callurl:   null,
  tokenurl:  null,
  apiurl: null,
  followurl: null,

  refresh: function (context) {
    // Clear and disable inputs
    $(context).select('input').each(function (input) {
      input.clear().disable();
    })

    // Disable view buttons
    $(context).select('button').each(function (button) {
      button.disable();
    })

    // Enable radios and inner inputs and buttons
    $$('input[type=radio]').each(function (radio) {
      radio.enable();
      if (radio.checked) {
        $(context).select('button').each(function (button) {
          button.enable();
        })

        radio.up('div').select('div input').each(function (input) {
          input.enable();
        })
      }
    })
  },

  show: function (form, view) {
    var params = {
      m:    'context',
      a:    'call',
      view: view
    }

    let api_params = {
      view: view
    }

    var form = getForm(form);
    $A(form.elements).each(function (input) {
      if (input.type == 'text' && input.value) {
        params[input.name] = input.value;
        api_params[input.name] = input.value;
      }
   })

    params['token'] = '<token>';

    // Call params and URL
    var call_params = '';
    $H(params).each(function (param) {
      call_params += param.key + ' = ' + param.value + '\n';
    })
    $V('call-params', call_params);

    this.callurl = new Url();
    this.callurl.mergeParams(params);
    $V('call-url', decodeURIComponent(this.callurl.makeAbsolute()));

    delete this.callurl.oParams['token'];

    // Token params and URL
    delete params['a'];
    params['raw'] = 'tokenize';
    params['token_username'] = '<token_username>';
    api_params['token_username'] = '<token_username>';
    var token_params = '';
    $H(params).each(function (param) {
      token_params += param.key + ' = ' + param.value + '\n';
    })
    $V('token-params', token_params);

    this.tokenurl = new Url();
    this.tokenurl.mergeParams(params);
    $V('token-url', decodeURIComponent(this.tokenurl.makeAbsolute()));

    this.apiurl = new Url();
    this.apiurl.mergeParams(api_params);
    $V('api-url', decodeURIComponent(this.apiurl.makeAbsoluteApi('/api/context/tokenize')));

    delete this.tokenurl.oParams['token'];

    Modal.open('show-context', {showClose: true, title: 'Appel contextuel'});
  },

  callModal: function () {
    this.callurl.modal();
  },

  callOpen: function () {
    this.callurl.open();
  },

  tokenize: function () {
    if (this.auth === 'token') {
      this.tokenurl.addElement($('token-username'));
      this.tokenurl.requestJSON(function (response) {
        $V('token-response', JSON.stringify(response));
        Context.followurl = new Url();
        Context.followurl.addParam('token', response.token);
        $V('follow-url', response.code ? decodeURIComponent(Context.followurl.makeAbsolute()) : null);
        $('follow-button').disabled = !response.code
      });
    } else {
      this.apiurl.addElement($('token-username'));

      fetch(this.apiurl.makeAbsoluteApi('/api/context/tokenize')).then(function (response) {
        return response.json();
      }).then(function (json_response) {
        $V('token-response', JSON.stringify(json_response.data ? json_response.data.attributes : json_response.errors));

        Context.followurl = new Url();
        Context.followurl.addParam('token', json_response?.data?.attributes?.hash);

        $V('follow-url', json_response?.data?.links ? json_response.data.links.url_token : null);
        $('follow-button').disabled = json_response.errors;
      });
    }
  },

  follow: function () {
    this.followurl.redirect();
  },

  updateAuth: function (input) {
    let api_textarea = $('api-url');
    let token_textarea = $('token-url');

    if ($V(input) === 'basic') {
      this.auth = 'basic';
      api_textarea.show();
      token_textarea.hide();
    } else {
      this.auth = 'token';
      api_textarea.hide();
      token_textarea.show();
    }
  }
}
