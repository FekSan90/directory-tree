<?php

namespace DocumentManager\Controller;

use DocumentManager\Model\Document;
use DocumentManager\Module;
use Zend\Mvc\Controller\AbstractActionController;
use DocumentManager\Form\UploadForm;
use Zend\View\Model\JsonModel;

class DocumentManagerController extends AbstractActionController
{
    /**
     * container.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager = null;


    /**
     * Constructor is used for injecting dependencies into the controller.
     */
    public function __construct($container)
    {
        $this->entityManager = $container->get('doctrine.entitymanager.orm_default');
    }

    /**
     * Ez az eljárás a Document Manager menüpontba lépéskor hajtódik végre és az ürlapot dolgozza fel.
     * @return array
     */
    public function indexAction()
    {
        $form = new UploadForm('upload-form');
        $fileErrors = null;
        $fileName = null;
        $idErrors = null;
        $prg = $this->fileprg($form);

        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            return $prg;
        }

        if (is_array($prg)) {
            if ($form->isValid()) {
                $data = $form->getData();
                $localPath = explode('/', $data['document-file']['tmp_name']);
                $storageName = $localPath[count($localPath) - 1];
                $displayName = $data['document-file']['name'];
                $this->addFileToDb($displayName, $storageName, $data['hidden']);
                $this->moveFile($storageName, $data['hidden']);
            } else {
                $data = $form->getData();
                if (!empty($data['document-file']['tmp_name']))
                    unlink($data['document-file']['tmp_name']);
            }
            $fileErrors = $form->get('document-file')->getMessages();
            $idErrors = $form->get('hidden')->getMessages();

            if (empty($fileErrors)) {
                $fileName = $form->get('document-file')->getValue()['name'];
            }
        }

        return [
            'dirs' => !is_null($this->entityManager) ? $this->listDirs() : '',
            'form' => $form,
            'fileName' => $fileName,
            'iderror' => !empty($idErrors) ? "A gyökér mappába nem lehet feltölteni " : null,
        ];
    }

    /**
     * A feltöltött fájlokat regisztrálja az adatbázisba
     * @param $displayName
     * @param $storageName
     * @param $pid
     */
    public function addFileToDb($displayName, $storageName, $pid)
    {
        $doc = new Document();
        $data = ['displayName' => htmlspecialchars($displayName),
            'storageName' => htmlspecialchars($storageName),
            'isDIR' => '0',
            'version' => $this->getNextVersion(htmlspecialchars($displayName), $pid),
            'parentId' => $pid,
            'ownerId' => '1',
        ];

        $doc->exchangeArray($data);
        $this->entityManager->persist($doc);
        $this->entityManager->flush();
    }

    /**
     * Ajax törlési kérésre törli a könyvtárakat az adatbázisban és a mapparendszerben ezután választ generál.
     * @return JsonModel
     */
    public function deleteDirAction()
    {
        $depth = array();
        $doc = $this->entityManager->getRepository(Document::class)->findOneById($_POST['id']);;
        // ha létezik akkor bejárja a könyvtár fát és mindent töröl
        if (!is_null($doc)) {
            $depth[] = $this->entityManager->getRepository(Document::class)->findByParentId($doc->getId());
            for ($j = 0; $j < count($depth); $j++) {
                for ($i = 0; $i < count($depth[$j]); $i++) {
                    $depth[] = $this->entityManager->getRepository(Document::class)->findByParentId($depth[$j][$i]->getId());
                }
            }
        }

        $this->entityManager->remove($doc);
        $this->entityManager->flush();

        foreach ($depth as $dir) {
            foreach ($dir as $node) {
                $this->entityManager->remove($node);
                $this->entityManager->flush();
            }
        }
        // ugyan ez fizikálisan
        $dirPath = Module::UPLOAD_PATH . $this->makePath($doc->getParentId()) . $doc->getdisplayName();
        $this->deleteDir($dirPath);

        $view = new JsonModel([
            'status' => 'SUCCESS']);
        return $view;
    }

    /**
     * Ajax létrehozás kérésre új bejegyzést hoz létre az adatbáziésban és fizikálisan.
     * Válaszként elküldi az adatbázisban szereplő azonosítóját
     *
     * @return JsonModel
     */
    public function createDirAction()
    {
        $doc = new Document();
        $pid = null;
        // A js tree j1_1 alakú azanosítót generál
        if (strpos($_POST['parentId'], '_') === false) {
            $pid = $_POST['parentId'];
        }

        $data = ['displayName' => $_POST['name'],
            'storageName' => $_POST['name'],
            'isDIR' => "1",
            'parentId' => $pid,
            'ownerId' => '1',
        ];

        $doc->exchangeArray($data);
        $to = Module::UPLOAD_PATH .
            $this->makePath($doc->getParentId()) .
            $doc->getDisplayName();

        if (!file_exists($to)) {
            mkdir($to, 0777, true);
        }

        $this->entityManager->persist($doc);
        $this->entityManager->flush();
        $lastId = $this->entityManager->getRepository(Document::class)->findBy(
            ['isDIR' => "1"],
            ['id' => 'DESC'],
            1);
        $view = new JsonModel([
            'id' => $lastId[0]->getId(),
            'status' => 'SUCCESS',
        ]);
        return $view;
    }

    /**
     * Átnevezés kérésre átnevezi az adatbázisban és fizikálisan is.
     * @return JsonModel
     */
    public function renameDirAction()
    {
        $id = null;
        if (strpos($_POST['parentId'], '_') === false) {
            $id = $_POST['id'];
        }

        $doc = $this->entityManager->getRepository(Document::class)->findOneById($id);;
        rename(Module::UPLOAD_PATH . $doc->getdisplayName(), Module::UPLOAD_PATH . $_POST['newname']);
        $doc->setdisplayName($_POST['newname']);
        $doc->setstorageName($_POST['newname']);
        $this->entityManager->persist($doc);
        $this->entityManager->flush();

        $view = new JsonModel([
            'status' => 'SUCCESS']);
        return $view;
    }

    /**
     * Tömbe szedi az összes mappát a JS tree számára.
     * @return array|null
     */
    public function listDirs()
    {
        $dirs = null;

        $dirs = $this->entityManager->getRepository(Document::class)->findByisDIR("1");

        return $dirs;
    }

    /**
     * Listába szedi a kijelölt mappában található fájlokat JSON adatként elküldi
     * @return JsonModel
     */
    public function listFilesAction()
    {
        // A js tree j1_1 alakú azanosítót generál
        if (strpos($_POST['parentId'], '_') === false) {
            $list = null;
            $criteria = ['isDIR' => "0",
                'parentId' => $_POST['parentId']];
            $repository = $this->entityManager->getRepository(Document::class)->findby($criteria);

            foreach ($repository as $doc) {
                $list[] = [
                    'name' => $doc->getdisplayName(),
                    'filename' => $doc->getstorageName(),
                    'date' => $doc->getDate()->format('Y-m-d H:i:s'),
                    'version' => $doc->getVersion(),
                ];
            }
        }
        $list[] = ['status' => 'SUCCESS'];
        $view = new JsonModel($list);
        return $view;
    }

    /**
     * Egy mappa azonosítóból útvonalat generál a gyökérig.
     * @param $startid
     * @return string|null
     */
    public function makePath($startid)
    {
        $path = null;
        if ($startid != 0) {
            $doc = $this->entityManager->getRepository(Document::class)->findOneById($startid);
            if (!is_null($doc)) {
                $pathArray[] = $doc->getdisplayName();
                while ($doc->getParentId() != 0) {
                    $doc = $this->entityManager->getRepository(Document::class)->findOneById($doc->getParentId());
                    $pathArray[] = $doc->getdisplayName();
                }
                $path = "";
                foreach (array_reverse($pathArray) as $dir) {
                    $path .= $dir . "/";
                }
            }
        }
        return $path;
    }

    /**
     * Fájlt helyez át a feltöltési mappán belül.
     * @param $name
     * @param $pathId
     */
    private function moveFile($name, $pathId)
    {
        $to = Module::UPLOAD_PATH . $this->makePath($pathId);
        rename(Module::UPLOAD_PATH . $name, $to . $name);
    }

    /**
     * Rekurzívan kitörli az almappákat és fájlokat
     * @param $dirPath
     */
    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Megkeresi az adatbázisban, hogy létezik-e már az adott fájl
     * ha igen akkor növelt verzió számot adja vissza, különben 1.
     * @param $name
     * @param $id
     * @return int
     */
    private function getNextVersion($name, $id)
    {
        $lastVersionDoc = $this->entityManager->getRepository(Document::class)->findBy(
            ['isDIR' => "0", 'parentId' => $id, 'displayName' => $name],
            ['version' => 'DESC'],
            1);

        empty($lastVersionDoc) ? $lastVersion = 0 : $lastVersion = $lastVersionDoc[0]->getVersion();
        return ++$lastVersion;
    }
}