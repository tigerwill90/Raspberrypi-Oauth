<?php
  /**
   * SYLVAIN MULLER
   * TB : Securing an IoT Network with Oauth2.0 protocol
   */

    require __DIR__ . '/../vendor/autoload.php';

    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
    $dotenv->load();
    $dotenv->required('DEBUG')->notEmpty()->allowedValues(['true', 'false']);
    $dotenv->required('TIMEZONE')->notEmpty();
    $dotenv->required('CLIENT_AUTHENTICATION')->notEmpty();
    $dotenv->required('OAUTH_SERVER')->notEmpty();

    date_default_timezone_set(getenv('TIMEZONE'));

    $app = new Slim\App([
    'settings' => [
      'displayErrorDetails' => filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
    ]
    ]);

    $app->get('/rain', function(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) : \Psr\Http\Message\ResponseInterface {
        $body = $response->getBody();

        $api = new RestClient([
            'base_url' => getenv('OAUTH_SERVER'),
            'curl_options' => [CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false],
            'headers' => ['Authorization' => 'Basic ' . base64_encode(getenv('CLIENT_AUTHENTICATION'))]
        ]);

        $headers = $request->getHeader('HTTP_AUTHORIZATION');
        if (preg_match('/Bearer\s+(.*)$/i', $headers[0],$matches)) {
            $token =  $matches[1];
            $result = $api->post('introspect', ['token' => $token]);

            try {
                $param = $result->decode_response();
                if ($param->active) {

                    //$gpio = new \PhpGpio\Gpio();
                    //$gpio->setup(17, 'in');

                    $body->write(json_encode(['messages' => '30mm de pluie ']));
                } else {
                    $body->write(json_encode(['error' => 'access denied']));
                    return $response->withBody($body)->withHeader('content-type' ,'application/json')->withStatus(401);
                }
            } catch (Exception $e) {
                $body->write(json_encode(['error' => 'something goes wrong']));
                return $response->withBody($body)->withHeader('content-type' ,'application/json')->withStatus(500);
            }

        } else {
            $body->write(json_encode(['error' => 'access denied']));
            return $response->withBody($body)->withHeader('content-type' ,'application/json')->withStatus(401);
        }

        return $response->withBody($body);
    });

    $app->run();
