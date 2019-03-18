<?php
/**
 * Created by PhpStorm.
 * User: Giansalex
 * Date: 31/03/2018
 * Time: 22:40.
 */

namespace Sufel\App\Controllers;

use Sufel\App\Models\ApiResult;
use Sufel\App\Models\Document;
use Sufel\App\Repository\DocumentRepositoryInterface;
use Sufel\App\Repository\FileReaderInterface;
use Sufel\App\Service\CryptoService;

/**
 * Class ExternalFileApi.
 */
class ExternalFileApi implements ExternalFileApiInterface
{
    use ResponseTrait;

    /**
     * @var CryptoService
     */
    private $crypto;
    /**
     * @var DocumentRepositoryInterface
     */
    private $documentRepository;
    /**
     * @var FileReaderInterface
     */
    private $fileRepository;

    /**
     * ExternalFileApi constructor.
     *
     * @param CryptoService               $crypto
     * @param DocumentRepositoryInterface $documentRepository
     * @param FileReaderInterface $fileRepository
     */
    public function __construct(
        CryptoService $crypto,
        DocumentRepositoryInterface $documentRepository,
        FileReaderInterface $fileRepository
    ) {
        $this->crypto = $crypto;
        $this->documentRepository = $documentRepository;
        $this->fileRepository = $fileRepository;
    }

    /**
     * Download file from hash.
     *
     * @param string $hash
     * @param string $type xml or pdf
     *
     * @return ApiResult
     */
    public function download($hash, $type)
    {
        if (!in_array($type, ['xml', 'pdf'])) {
            return $this->response(404);
        }

        $res = $this->crypto->decrypt($hash);
        if ($res === false) {
            return $this->response(404);
        }
        $obj = json_decode($res);
        $id = $obj->id;
        $doc = $this->documentRepository->get($id);
        if ($doc === null) {
            return $this->response(404);
        }

        $filter = $this->convertToDocument($doc);
        $storageId = $this->documentRepository->getStorageId($filter);

        $result = [
            'file' => $this->fileRepository->read($storageId, $type),
            'type' => $type === 'xml' ? 'text/xml' : 'application/pdf'
        ];

        $headers = [
            'Content-Type' => $result['type'],
            'Content-Disposition' => "attachment; filename=\"{$doc['filename']}.$type\";",
            'Content-Length' => strlen($result['file']),
        ];

        return $this->response(200, $result, $headers);
    }

    private function convertToDocument(array $data)
    {
        $doc = new Document();
        $doc->setEmisor($data['emisor'])
            ->setTipo($data['tipo'])
            ->setSerie($data['serie'])
            ->setCorrelativo($doc['correlativo']);

        return $doc;
    }
}
