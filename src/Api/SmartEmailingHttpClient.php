<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use function json_decode;

/**
 * SmartEmailingHttpClient
 *
 * Zajišťuje komunikaci se SmartEmailing API.
 */
final class SmartEmailingHttpClient
{
    private readonly SmartEmailingAuth $auth;

    public function __construct(SmartEmailingAuth $auth)
    {
        $this->auth = $auth;
    }

    public function getAuth(): SmartEmailingAuth
    {
        return $this->auth;
    }

    /**
     * Provádí HTTP požadavek na SmartEmailing API.
     * Zachovává původní struktury a chování.
     *
     * @throws SmartEmailingException
     */
    public function sendRequest(string $method, string $action, mixed $request = null): array
    {
        try {
            $options = [
                'auth' => [
                    $this->auth->getUser(),
                    $this->auth->getToken(),
                ]
            ];

            if (is_array($request)) {
                $options['json'] = $request;
            }

            $client = new Client([
                'base_uri' => SmartEmailingSchema::APP_BASE_URI,
                'timeout'  => 10,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ],
            ]);

            $response = $client->request(
                $method,
                SmartEmailingSchema::parseUrl($action),
                $options
            );

            return $this->handleSuccess($response);

        } catch (ClientException $e) {
            return $this->handleClientError($e);

        } catch (\Exception $e) {
            throw new SmartEmailingException($e->getMessage(), (int) $e->getCode());
        }
    }

    // ======================================================================
    // INTERNAL HANDLERS
    // ======================================================================

    private function handleSuccess(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();

        // Původní chování: 200 / 201 / 204 = success
        if ($status === 200 || $status === 201 || $status === 204) {
            return $this->parseSuccessResponse($response);
        }

        return [
            'message' => 'Unknown success status',
            'data'    => []
        ];
    }

    private function parseSuccessResponse(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();

        if ($body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // původní fallback: prázdný response = status ok
        return ['status' => 'ok'];
    }

    private function handleClientError(ClientException $e): array
    {
        $code = $e->getCode();

        if (in_array($code, [400, 401, 404, 422], true)) {
            return $this->parseErrorResponse($e->getResponse());
        }

        return [
            'message' => 'Unknown error',
            'data'    => []
        ];
    }

    private function parseErrorResponse(?ResponseInterface $response): array
    {
        if ($response === null) {
            return [
                'message' => 'Error',
                'data'    => []
            ];
        }

        $body = $response->getBody()->getContents();

        if ($body === '') {
            return [
                'message' => 'Error',
                'data'    => []
            ];
        }

        $decoded = json_decode($body, true);

        return [
            'message' => (string)($decoded['message'] ?? 'Error'),
            'data'    => []
        ];
    }
}
