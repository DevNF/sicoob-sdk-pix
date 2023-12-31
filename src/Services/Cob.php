<?php

namespace NFService\Sicoob\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Sicoob\Formater\RequestMaker;
use NFService\Sicoob\Helpers\QRCodeGenerator;
use NFService\Sicoob\Validators\CobValidator;
use stdClass;

class Cob
{
    private RequestMaker $req;
    private ?string $txid;
    public function __construct(RequestMaker $req, ?string $txid = null)
    {
        $this->req = $req;
        $this->txid = $txid;
    }


    public function criar(array $body): string | GuzzleException | array | stdClass
    {

        $cobValidator = new CobValidator($body);
        $cobValidator->validatePost();

        try {
            return $this->req->requisicao('/cob', 'POST', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function atualizar (array $body): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            throw new Exception('Txid é obrigatório para atualizar cobranças');
        }

        $cobValidator = new CobValidator($body);
        $cobValidator->validatePatch();

        try {
            return $this->req->requisicao("/cob/$this->txid", 'PUT', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function consultar(array $options): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            foreach($options as $key => $value) {
                if(!array_key_exists('inicio', $options) || !array_key_exists('fim', $options)) {
                    throw new Exception('Inicio e fim são obrigatórios para consultar cobranças sem txid');
                }
            }
        }

        $query = "?".http_build_query($options);

        if(!empty($this->txid)){
            $query = "/$this->txid";
        }

        try {
            return $this->req->requisicao("/cob{$query}", 'GET');
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function cancelar(): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            throw new Exception('Txid é obrigatório para cancelar cobranças');
        }

        try {
            return $this->req->requisicao("/cob/{$this->txid}", 'PATCH', ['status' => 'REMOVIDA_PELO_USUARIO_RECEBEDOR']);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function gerarQRCode(): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            throw new Exception('Txid é obrigatório para gerar QRCode');
        }

        try {
            return QRCodeGenerator::generate($this->req->requisicao("/cob/{$this->txid}", 'GET')->brcode);
        } catch (GuzzleException $e) {
            return $e;
        }
    }
}
