<?php

/**
 * @package Mediboard\Fhir\Controllers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Controllers;

use Exception;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\Api\Request\CRequestFHIR;
use Ox\Interop\Fhir\Api\Request\CRequestFormats;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\OperationOutcome\CFHIRDataTypeOperationOutcomeIssue;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInformational;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\R4\OperationOutcome\CFHIRResourceOperationOutcome;
use Ox\Interop\Fhir\ValueSet\CFHIRIssueType;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CAppFineController
 * @package Ox\Interop\Fhir\Controllers
 */
class CFHIRAppFineController extends CFHIRController
{
    /**
     * Search route
     *
     * @param String       $resource Resource name
     * @param CRequestFHIR $request  Request
     *
     * @return Response
     * @throws Exception
     *
     * @api
     */
    public function searchAppFine(CRequestFHIR $request): Response
    {
        $resource = $request->getResource();
        $interaction = $request->getInteraction();
        $resource->setInteraction($interaction);
        $resourceResponse = $resource->interactionSearchAppFine();

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Create patient user for AppFine
     *
     * @param CRequestFHIR $request Request
     *
     * @return Response
     * @throws Exception
     *
     * @api
     */
    public function patient_user_appFine(CRequestFHIR $request): Response
    {
        $request_format = new CRequestFormats($request->getRequest());

        $format = $request_format->getFormat();
        $data   = $request->getContent(true);

        $sender_fhir = new CSenderFHIR();
        $sender_fhir->loadFromUser(CUser::get());

        $sender = $sender_fhir->getSender();
        if (!$sender || !$sender->_id) {
            throw new CFHIRExceptionInformational(CAppUI::tr("CSenderHTTP.none"));
        }

        $code              = CMbArray::get($data, "code");
        $patient_id        = CMbArray::get($data, "patient_id");
        $patient_id_client = CMbArray::get($data, "patient_id_client");

        $diag = CAppFineServer::createPatientUserAPI($sender_fhir->getSender(), $patient_id, $patient_id_client, $code);
        $resource          = new CFHIRResourceOperationOutcome();
        $resource->addIssue(
            CFHIRDataTypeOperationOutcomeIssue::build(
                [
                    "code"        => CFHIRIssueType::TYPE_INFORMATIONAL,
                    "diagnostics" => $diag,
                ]
            )
        );
        $interaction = new CFHIRInteraction();
        $interaction->format = $format;
        $interaction->setResource($resource);

        return $this->renderFHIRResponse(new CFHIRResponse($interaction, $format));
    }

    /**
     * View form in AppFine
     *
     * @param RequestApi $request_api
     *
     * @return string
     * @throws CFHIRException
     * @api
     */
    public function preview_form_appFine(RequestApi $request): Response
    {
        $data   = $request->getContent();
        $json   = json_decode(CMbArray::get($data, "data"), true);
        if (!$form_guid = CMbArray::get($json, "object_guid")) {
            throw new CFHIRException("Invalid argument 'ex_class_id'");
        }

        if (!$content_xml = CMbArray::get($json, "form_xml")) {
            throw new CFHIRException("Invalid argument 'form_xml'");
        }

        if (!$file_name = CMbArray::get($json, "file_name")) {
            throw new CFHIRException("Invalid argument 'file_name'");
        }

        $group     = CGroups::loadCurrent();
        $user      = CUser::get();
        $ext       = "xml";
        $file_type = "application/mbForm";
        $form_xml  = base64_decode($content_xml);
        $tag       = CAppFineServer::getObjectTagAppFineViewForm($group->_id);

        // Création du fichier pour le formulaire
        $file = new CFile();
        $file->setObject($user);
        $file->file_name = "$file_name.$ext";
        $file->file_type = $file_type;
        $file->file_date = CMbDT::dateTime();
        $file->fillFields();
        $file->updateFormFields();
        $file->setContent($form_xml);
        if ($msg = $file->store()) {
            throw new CFHIRException($msg);
        }

        // store de l'id sante 400 pour suppression a posteriori avec le tag
        $id_sante_400               = new CIdSante400();
        $id_sante_400->object_id    = $file->_id;
        $id_sante_400->object_class = $file->_class;
        $id_sante_400->id400        = $form_guid;
        $id_sante_400->tag          = $tag;
        if ($msg = $id_sante_400->store()) {
            $file->delete();
            throw new CFHIRException($msg);
        }

        $content_response = [
            "file_id" => $file->_id,
        ];

        return $this->renderJsonResponse(json_encode($content_response));
    }
}
