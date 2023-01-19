<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Mutex\CMbMutex;
use Ox\Import\Rpps\Entity\CAbstractExternalRppsObject;
use Ox\Import\Rpps\Entity\CDiplomeAutorisationExercice;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Import\Rpps\Entity\CSavoirFaire;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Import\Rpps\Entity\CMssanteInfos;

/**
 * Description
 */
class CExternalMedecinSync
{
    public const MUTEX_TIMEOUT = 300;

    public const TYPE_ADELI = 'adeli';
    public const TYPE_RPPS  = 'rpps';

    public const ALLOWED_TYPES = [
        self::TYPE_ADELI,
        self::TYPE_RPPS,
    ];

    /** @var CMedecin[] */
    private $medecins = [];

    /** @var CMedecinExercicePlace[] */
    private $medecins_exercices_places = [];

    /** @var CPersonneExercice[] */
    private $personnes_exercices = [];

    /** @var CSavoirFaire[] */
    private $savoir_faire = [];

    /** @var CDiplomeAutorisationExercice[] */
    private $diplomes = [];

    /** @var CMssanteInfos[] */
    private $mssante_address = [];

    /** @var array */
    private $errors = [];

    /** @var array */
    private $updated = [];

    /** @var string */
    private $version;

    /**
     *
     * @throws Exception
     * @throws CImportMedecinException
     */
    public function synchronizeSomeMedecins(int $step = 50, ?string $type = null, array $codes = []): void
    {
        if (!$this->putMutex()) {
            // Mutex already used
            throw new CImportMedecinException('Mutex is already in use');
        }

        // Enforce slave before launching data from std
        CView::enforceSlave();

        $this->medecins = ($type && $codes) ? $this->loadMedecinsToSync(
            $type,
            $codes
        ) : $this->loadRandomMedecinsToSync($step);

        $this->loadExternalDatas($this->medecins);

        // Disable slave before synchronisation
        CView::disableSlave();

        $this->syncDatas();

        $this->releaseMutex();
    }

    /**
     * @return array
     * @throws Exception
     * @throws CMbException
     */
    private function loadMedecinsToSync(string $type, array $codes): ?array
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new CMbException(
                'CExternalMedecinSync-Error-Type must be in array',
                implode('|', self::ALLOWED_TYPES),
                $type
            );
        }

        $medecin  = new CMedecin();
        $personne = new CPersonneExercice();
        $ds       = $personne->getDS();

        $query = new CRequest();
        $query->addSelect('version');
        $query->addTable($personne->getSpec()->table);
        $query->setLimit(1);

        $this->version = $ds->loadResult($query->makeSelect());

        return $medecin->loadList(
            [
                'actif'               => "= '1'",
                'import_file_version' => $ds->prepare('!= ? OR `import_file_version` IS NULL', $this->version),
                'rpps'                => $ds->prepareIn($codes),
            ]
        );
    }

    /**
     * @return array
     * @throws Exception
     * @throws CMbException
     */
    private function loadRandomMedecinsToSync(int $step): ?array
    {
        $medecin  = new CMedecin();
        $personne = new CPersonneExercice();
        $ds       = $personne->getDS();

        $query = new CRequest();
        $query->addSelect('version');
        $query->addTable($personne->getSpec()->table);
        $query->setLimit(1);

        $this->version = $ds->loadResult($query->makeSelect());

        if (!$this->version) {
            $this->releaseMutex();
            throw new CMbException(
                'Erreur lors de la récupération des infos de la base externe'
            );
        }

        return $medecin->loadList(
            [
                'actif'               => "= '1'",
                'import_file_version' => $ds->prepare('!= ? OR `import_file_version` IS NULL', $this->version),
            ],
            null,
            $step
        );
    }

    /**
     * @throws Exception
     */
    private function loadExternalDatas(array $medecins): void
    {
        $this->medecins_exercices_places = CStoredObject::massLoadBackRefs(
            $medecins,
            'medecins_exercices_places'
        );

        // Get old adelis of medecin and new adelis of medecin_exercice_place
        $filtered_ids = array_merge(
            array_filter(CMbArray::pluck($medecins, 'rpps')),
            array_filter(CMbArray::pluck($medecins, 'adeli')),
            array_filter(CMbArray::pluck($this->medecins_exercices_places, 'adeli'))
        );

        $this->personnes_exercices = $this->loadExternalObjectsFromIds(
            $filtered_ids,
            new CPersonneExercice()
        );

        $this->savoir_faire    = $this->loadExternalObjectsFromIds($filtered_ids, new CSavoirFaire());
        $this->diplomes        = $this->loadExternalObjectsFromIds(
            $filtered_ids,
            new CDiplomeAutorisationExercice()
        );
        $this->mssante_address = $this->loadExternalObjectsFromIds($filtered_ids, new CMssanteInfos());
    }

    /**
     * @throws Exception
     */
    public function loadExternalObjectsFromIds(
        array $filtered_ids,
        CAbstractExternalRppsObject $object
    ): array {
        $ds = $object->getDS();

        $where = ['identifiant' => $ds->prepareIn($filtered_ids)];

        $personnes = $object->loadList($where);

        $indexed_personnes = [];

        foreach ($personnes as $personne) {
            if (!isset($indexed_personnes[$personne->identifiant])) {
                $indexed_personnes[$personne->identifiant] = [];
            }

            $indexed_personnes[$personne->identifiant][] = $personne;
        }

        return $indexed_personnes;
    }

    /**
     * @throws Exception
     */
    private function syncDatas(): void
    {
        foreach ($this->medecins as $medecin) {
            if ($medecin->rpps) {
                $this->syncMedecin($medecin, $medecin->rpps);
                $this->updated[] = $medecin->_id;
                continue;
            }

            /** @var CMedecinExercicePlace $_medecin_exercice_place */
            foreach ($medecin->loadBackRefs('medecins_exercices_places') as $_medecin_exercice_place) {
                if ($_medecin_exercice_place->adeli) {
                    $this->syncMedecin($medecin, $_medecin_exercice_place->adeli);
                    $this->updated[] = $medecin->_id;
                }
            }
        }
    }

    /**
     * @throws CImportMedecinException
     * @throws Exception
     */
    private function syncMedecin(CMedecin $medecin, string $code): void
    {
        if (isset($this->personnes_exercices[$code])) {
            /** @var CPersonneExercice $_personne_exercice */
            foreach ($this->personnes_exercices[$code] as $_personne_exercice) {
                $_personne_exercice->synchronize($medecin);

                $exercice_place = new CExercicePlace();

                if ($_personne_exercice->hashIdentifier()) {
                    $exercice_place->exercice_place_identifier = $_personne_exercice->hashIdentifier();
                    $exercice_place->loadMatchingObjectEsc();
                }

                $_personne_exercice->updateOrCreatePlace($exercice_place);
                $medecin_exercice_place = $_personne_exercice->synchronizeExercicePlace($medecin, $exercice_place);

                if (isset($this->mssante_address[$code])) {
                    $_personne_exercice->addMSSanteAddress(
                        $medecin_exercice_place,
                        $medecin,
                        (array)$this->mssante_address[$code]
                    );

                    foreach ($this->mssante_address[$code] as $mssante_address) {
                        $this->setSynchronized($mssante_address);
                    }
                }
            }

            $this->setSynchronized($_personne_exercice);
        }

        if (isset($this->diplomes[$code])) {
            /** @var CDiplomeAutorisationExercice $diplome */
            foreach ($this->diplomes[$code] as $diplome) {
                $diplome->synchronize($medecin);
                $this->setSynchronized($diplome);
            }
        }

        if (isset($this->savoir_faire[$code])) {
            /** @var CSavoirFaire $savoir_faire */
            foreach ($this->savoir_faire[$code] as $savoir_faire) {
                $savoir_faire->synchronize($medecin);
                $this->setSynchronized($savoir_faire);
            }
        }

        $medecin->import_file_version = $this->version;
        if ($msg = $medecin->store()) {
            $this->errors[] = $msg;
        }

        $manager = new CMedecinExercicePlaceManager();
        $manager->removeBadMatchingMedecinExercicePlace($medecin);
    }

    /**
     * @return bool
     */
    private function putMutex(): bool
    {
        $mutex = $this->getMutex();

        return $mutex->lock(self::MUTEX_TIMEOUT);
    }

    /**
     * @return CMbMutex
     */
    private function getMutex(): CMbMutex
    {
        return new CMbMutex(__CLASS__, __METHOD__);
    }

    /**
     * @return void
     */
    private function releaseMutex(): void
    {
        $mutex = $this->getMutex();
        $mutex->release();
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getUpdated(): array
    {
        return $this->updated;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAvancement(): array
    {
        $not_sync = $this->getCounts(false);
        $sync     = $this->getCounts(true);

        return [
            CPersonneExercice::class            => $this->buildAvancement(
                $sync[CPersonneExercice::class],
                $not_sync[CPersonneExercice::class]
            ),
            CSavoirFaire::class                 => $this->buildAvancement(
                $sync[CSavoirFaire::class],
                $not_sync[CSavoirFaire::class]
            ),
            CDiplomeAutorisationExercice::class => $this->buildAvancement(
                $sync[CDiplomeAutorisationExercice::class],
                $not_sync[CDiplomeAutorisationExercice::class]
            ),
            CMssanteInfos::class                => $this->buildAvancement(
                $sync[CMssanteInfos::class],
                $not_sync[CMssanteInfos::class]
            ),
        ];
    }

    /**
     * @param int $sync_num
     * @param int $not_sync_num
     *
     * @return array
     */
    private function buildAvancement(int $sync_num, int $not_sync_num): array
    {
        $total = $sync_num + $not_sync_num;
        $pct   = ($total > 0) ? (($sync_num / $total) * 100) : 0;

        return [
            'sync'      => number_format($sync_num, 0, ',', ' '),
            'not_sync'  => number_format($not_sync_num, 0, ',', ' '),
            'total'     => number_format($total, 0, ',', ' '),
            'pct'       => number_format($pct, 4, ',', ' '),
            'threshold' => ($pct < 50) ? 'critical' : (($pct < 80) ? 'warning' : 'ok'),
            'width'     => number_format($pct),
        ];
    }


    /**
     * @param bool $sync
     *
     * @return array
     * @throws Exception
     */
    public function getCounts(bool $sync): array
    {
        $person_exercice               = new CPersonneExercice();
        $person_exercice->synchronized = ($sync) ? '1' : '0';
        $person_exercice->error        = '0';

        $savoir_faire               = new CSavoirFaire();
        $savoir_faire->synchronized = ($sync) ? '1' : '0';
        $savoir_faire->error        = '0';

        $diplome               = new CDiplomeAutorisationExercice();
        $diplome->synchronized = ($sync) ? '1' : '0';
        $diplome->error        = '0';

        $mssante               = new CMssanteInfos();
        $mssante->synchronized = ($sync) ? '1' : '0';
        $mssante->error        = '0';

        return [
            CPersonneExercice::class            => $person_exercice->countMatchingList(),
            CSavoirFaire::class                 => $savoir_faire->countMatchingList(),
            CDiplomeAutorisationExercice::class => $diplome->countMatchingList(),
            CMssanteInfos::class                => $mssante->countMatchingList(),
        ];
    }

    /**
     * @param CAbstractExternalRppsObject $object
     *
     * @return void
     * @throws Exception
     *
     */
    private function setSynchronized(CAbstractExternalRppsObject $object): void
    {
        $object->synchronized = '1';
        $object->store();
    }
}
