/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * WebcamImage : Classe gérant l'activation de la webcam, le rendu dans un conteneur ainsi que l'enregistrement
 *  d'une image.
 */
WebcamImage = {
  /** Flux en cours */
  stream: null,
  /** Etat du stream (1 : en cours, 0|null : arrêté) */
  streaming: null,
  /** Conteneur de l'aperçu */
  video: null,
  /** Canvas utilisé pour l'enregistrement de l'image */
  canvas: null,
  /** Dimensions de la vidéo */
  width: null,
  height: null,

  /**
   * Affiche la Modale principale
   *
   * @param objectGuid Object auquel sera lié l'image enregistrée
   * @param options
   */
  show: function(objectGuid, options) {
    options = Object.extend({
      rename:     "Image.jpg",
      size  :     "medium",
    }, options);

    switch (options.size) {
      case 'lg':
      case 'large':
        this.width = "66%";
        break;
      case 'md':
      case 'medium':
        this.width = "50%";
        break;
      case 'sm':
      case 'small':
        this.width = "25%";
        break;
      default:
        this.width = options.size;
        break;
    }

    new Url('files', 'webcam_image')
      .addParam('object_guid', objectGuid)
      .addParam('rename', options.rename)
      .requestModal(this.width, null, {
        showReload: false,
        onClose: function() {
          this.stop();
        }.bind(this),
        onComplete: function() {
          this.initModal(false);
        }.bind(this)
      });
  },

  /**
   * Initialisation du bouton qui affichera la modale
   *
   * @param button     Bouton à afficher / masquer et à préparé
   * @param objectGuid Object auquel sera lié l'image enregistrée
   * @param options
   */
  initButton: function(button, objectGuid, options) {
    if ((navigator && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
      (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia)){
      button.show();
      button.on('click', function() {
        this.show(objectGuid, options);
      }.bind(this));
    }
    else {
      button.hide();
    }
  },

  /**
   * Initialisation de la modale
   *
   * @param forceOutOfDateStreamFun Force l'utilisation des fonctions obsolètes
   */
  initModal: function(forceOutOfDateStreamFun) {
    $('webcam_image_consent').show();

    this.video     = $('webcam_image_video');
    this.canvas    = $('webcam_image_canvas');
    this.width     = this.video.getWidth();
    this.height    = 0;
    this.streaming = false;

    if (navigator && navigator.mediaDevices && navigator.mediaDevices.getUserMedia && !forceOutOfDateStreamFun) {
      navigator.mediaDevices.getUserMedia({video: true})
        .then(function(stream) {
          this.launchStream(stream, false);
        }.bind(this))
        .catch(function(err) {
          console.warn(
            "getUserMedia() is undefined, the browser is probably out of date. Trying to use old functions... More info : " + err
          );
          this.initModal(true);
        }.bind(this));
    }
    else if (navigator) {
      /** Récupération de la fonction adaptée selon le navigateur */
      navigator.getMedia = ( navigator.getUserMedia ||
        navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia ||
        navigator.msGetUserMedia);
      if (navigator.getMedia) {
        navigator.getMedia(
          {video: true},
          function(stream) {
            this.launchStream(stream, true);
          }.bind(this),
          function(err) {
            this.abortStream("An error occured : " + err);
          }.bind(this)
        );
      }
      else {
        this.abortStream("No functions available ");
      }
    }
  },

  /**
   * Affichage du stream dans la contenenur video
   *
   * @param stream            Stream à afficher
   * @param outOfDateFunction Flag : Utilise-t-on une fonction de stream obsolète
   */
  launchStream: function(stream, outOfDateFunction) {
    this.video.up('div.modal').setStyle({top: '0px'});
    if (outOfDateFunction) {
      var vendorURL = window.URL || window.webkitURL;
      this.video.src = vendorURL.createObjectURL(stream);

      this.play();
    }
    else {
      this.video.srcObject = stream;
      this.video.onloadedmetadata = function(e) {
        this.play();
      }.bind(this);
    }
    /** On met de côté le flux. Utile pour mettre le stream sur pause. */
    this.stream = stream.getTracks()[0];

    this.video.addEventListener('canplay', function(ev){
      if (!this.streaming) {
        this.height = this.video.videoHeight / (this.video.videoWidth/this.width);
        this.video.setAttribute('width', this.width);
        this.video.setAttribute('height', this.height);
        this.streaming = true;
      }
    }.bind(this), false);
  },

  /**
   * Mise sur pause de la video, et propose de sauvegarder de l'image ou de relancer le stream
   * Copie le conteneur video dans la canvas (en vue de l'enregistrer)
   */
  takePicture: function() {
    this.video.pause();
    $('webcam_image_take_picture').hide();
    $('webcam_image_validate_picture').show();
    this.canvas.width = this.width;
    this.canvas.height = this.height;
    this.canvas.getContext('2d').drawImage(this.video, 0, 0, this.width, this.height);
  },

  /**
   * Relance la video et propose de mettre le stream sur pause ("prendre la photo")
   */
  play: function() {
    this.video.play();
    $('webcam_image_take_picture').show();
    $('webcam_image_backvideo').show();
    $('webcam_image_validate_picture').hide();
    $('webcam_image_consent').hide();
  },

  /**
   * Arrête le stream et libère le périphérique (photo prise, fermeture de la modale, erreur rencontrée)
   */
  stop: function() {
    if (this.stream && typeof(this.stream.stop) !== 'undefined') {
      this.stream.stop();
    }
  },

  /**
   * Arrête le stream et affiche une erreur
   *
   * @param err
   */
  abortStream: function(err) {
    console.warn(err);
    alert($T('Could not start video source'));
    this.stop();
    Control.Modal.close();
  },

  /**
   * Enregistre l'image en arrêt sur la video (et dans le canvas).
   */
  validatePicture: function() {
    /** toBlob => on store le retour dans un input du formulaire d'upload de fichier, et on submit */
    this.canvas.toBlob(function(blob) {
      /** Store de l'image */
      $$('.inline-upload-files')[0].insert(DOM.input({
        type: "text",
        "data-blob": "blob",
        name: "formfile[]",
        value: "Image.png"
      })
        .store("blob", blob));

      /** Submit du formulaire */
      getForm('uploadFrm').onsubmit();
    });
  }
};
