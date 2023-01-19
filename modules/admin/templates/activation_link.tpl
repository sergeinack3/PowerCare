<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html charset=UTF-8">
</head>
<body style="font-family: sans-serif;padding:0;margin:0;background:#F4F5F7;background-color:#F4F5F7;">
<table role="presentation" style="width:100%" cellspacing="0" cellpadding="0" border="0" align="center">
  <tbody>
  <tr>
    <td>
      <table role="presentation" style="width: 546px;margin-top: 40px;" cellspacing="0" cellpadding="0" border="0"
             align="center">
        <tbody>
        <tr>
          <td>
            <div
              style="text-align: center;background-color: #FFFFFF; border-radius: 4px 4px 0 0;border: 1px solid #E5E6E7;border-bottom: none;padding: 24px 97px;">
              {{if 'Ox\Core\CAppUI::isGroup'|static_call:null || 'Ox\Core\CAppUI::isCabinet'|static_call:null}}
                <img src="https://openxtrem_public.gitlab.io/assets/logo_tamm_small.png" alt="Logo" width="150px" height="auto" />
              {{else}}
                <img src="https://openxtrem_public.gitlab.io/assets/logo_mediboard_small.png" alt="Logo" width="150px" height="auto" />
              {{/if}}

              <p
                style="text-align: left;margin-top: 24px; font-style: normal;font-weight: normal;font-size: 16px;line-height: 24px;color: #212121">
                Bonjour {{$user->_view}},
              </p>
              <p
                style="text-align: left;margin: 16px 0 0 0; font-style: normal;font-weight: normal;font-size: 14px;line-height: 20px;letter-spacing: 0.25px; color: #666666">
                Le compte <strong>{{$user->user_username}}</strong> associé à votre adresse email [<span
                  style="color: #1976D2">{{$email}}</span>] a été créé sur {{$product}}.
              </p>
              <p
                style="text-align: left;margin: 12px 0 0 0;font-style: normal;font-weight: normal;font-size: 14px;line-height: 20px;letter-spacing: 0.25px; color: #666666">
                Merci de cliquer sur le lien ci-dessous afin d'activer votre compte et de choisir votre mot de passe.
              </p>
              <a href="{{$token}}" role="button"
                 style="display: block; margin: 24px 0;background: #3F51B5;background-color: #3F51B5;padding: 10px 0;border-radius: 20px;font-style: normal;font-weight: 500;font-size: 14px;line-height: 16px;letter-spacing: 0.75px;color: #FFFFFF;text-decoration: none; width: 100%;">Activer
                mon compte</a>
              <p
                style="text-align: left;margin: 12px 0 0 0;font-style: normal;font-weight: normal;font-size: 14px;line-height: 20px;letter-spacing: 0.25px; color: #666666">
                Si vous n'avez pas sollicité cette création de compte ou si vous rencontrez un problème, merci de
                répondre à cet email.
              </p>
              <p
                style="text-align: left;margin: 12px 0 0 0;font-style: normal;font-weight: normal;font-size: 14px;line-height: 20px;letter-spacing: 0.25px; color: #666666">
                Cordialement,
              </p>
            </div>
          </td>
        </tr>
        <tr>
          <td
            style="text-align: left;background: #37474F; background-color: #37474F; padding: 16px 97px; border-bottom-right-radius: 4px;border-bottom-left-radius: 4px;">
            <img src="https://openxtrem_public.gitlab.io/assets/logo_ox_full_white_small.png"
                 alt="Openxtrem">
          </td>
        </tr>
        </tbody>
      </table>
    </td>
  </tr>
  </tbody>
</table>
</body>
</html>
