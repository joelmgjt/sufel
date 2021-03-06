<?php
/**
 * Created by PhpStorm.
 * User: Administrador
 * Date: 28/11/2017
 * Time: 12:47 PM.
 */

namespace Sufel\App\Service;

/**
 * Class LinkGenerator.
 */
class LinkGenerator
{
    /**
     * @var RouterBuilderInterface
     */
    private $router;
    /**
     * @var CryptoService
     */
    private $crypto;

    /**
     * LinkGenerator constructor.
     *
     * @param RouterBuilderInterface $builder
     * @param CryptoService            $crypto
     */
    public function __construct(
        RouterBuilderInterface $builder,
        CryptoService $crypto
    ) {
        $this->router = $builder;
        $this->crypto = $crypto;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function getLinks(array $data)
    {
        $hash = urlencode($this->crypto->encrypt(json_encode($data)));

        $xmlLink = $this->router->getFullPath('file_download', ['hash' => $hash, 'type' => 'xml']);
        $pdfLink = $this->router->getFullPath('file_download', ['hash' => $hash, 'type' => 'pdf']);

        return [
          'xml' => $xmlLink,
          'pdf' => $pdfLink,
        ];
    }
}
