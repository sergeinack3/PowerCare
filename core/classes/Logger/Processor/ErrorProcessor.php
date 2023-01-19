<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Processor;

use Error;
use ErrorException;
use Monolog\Processor\ProcessorInterface;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\Logger\ErrorTypes;

/**
 * Process error records to add and format data.
 */
class ErrorProcessor implements ProcessorInterface
{
    public ?array $version = null;

    public function __invoke(array $record): array
    {
        if (!isset($record['context']['exception'])) {
            return $record;
        }

        $exception = $record['context']['exception'];

        $file = CMbPath::getRelativePath($exception->getFile());
        $line = $exception->getLine();

        // Stacktrace
        $stacktrace = $exception->getTrace();
        foreach ($stacktrace as &$ctx) {
            unset($ctx['args'], $ctx['object']);
        }

        // ErrorException is sent by errorHandler, we change the type for ui
        $code = "exception";
        if ($exception instanceof Error) {
            $code = ErrorTypes::getCode($exception);
        } elseif ($exception instanceof ErrorException) {
            $code = $exception->getSeverity();
        }

        $type = ErrorTypes::TYPES[$code] ?? null;

        $record['extra']['user_id']      = $this->getCurrentUserId();
        $record['extra']['server_ip']    = $_SERVER["SERVER_ADDR"] ?? null;
        $record['extra']['session_id']   = CMbString::truncate(session_id(), 15);
        $record['extra']['microtime']    = microtime();
        $record['extra']['request_uuid'] = CApp::getRequestUID();
        $record['extra']['type']         = $type;
        $record['extra']['file']         = $file;
        $record['extra']['signature_hash']
                                         = $this->generateSignatureHash($exception->getMessage(), $type, $file, $line);
        $record['extra']['data']         = $this->buildParamDatas($stacktrace);
        $record['extra']['count']        = 1;

        return $record;
    }

    private function getCurrentUserId(): ?int
    {
        return (class_exists(CAppUI::class, false) && CAppUI::$user) ? (int)CAppUI::$user->user_id : null;
    }

    private function buildParamDatas(array $stacktrace): array
    {
        // Might not be ready at the time error is thrown
        $session = $_SESSION ?? [];
        unset($session['AppUI']);
        unset($session['dPcompteRendu']['templateManager']);

        $_all_params = [
            "GET"     => $_GET,
            "POST"    => $_POST,
            "SESSION" => $session,
        ];

        $_all_params = CMbSecurity::filterInput($_all_params);

        return [
            "stacktrace"   => $stacktrace,
            "param_GET"    => $_all_params["GET"],
            "param_POST"   => $_all_params["POST"],
            "session_data" => $_all_params["SESSION"],
        ];
    }

    /**
     * Generate a hash using the data of the exception.
     * Remove the numbers from the text to try to have the saem hashes for similar exceptions.
     */
    private function generateSignatureHash(string $text, string $type, string $file, int $line): string
    {
        // Set the version only once per hit
        if ($this->version === null) {
            $this->version = CApp::getVersion()->toArray();
        }

        $revision             = $this->version['revision'] ?? null;
        $text_without_numbers = preg_replace('/\d+/', '', $text);
        $signature            = [
            'type'     => $type,
            'text'     => mb_convert_encoding($text_without_numbers, 'UTF-8', 'ISO-8859-1'),
            'file'     => $file,
            'line'     => $line,
            'revision' => $revision,
        ];

        return md5(serialize($signature));
    }
}
