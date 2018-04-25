<?php

namespace Micayael\Keycloak\ClientBundle\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Micayael\Keycloak\ClientBundle\Form\LoginForm;
use Micayael\Keycloak\ClientBundle\Security\User\AuthenticatorUser;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $formFactory;
    private $router;
    private $configs;

    public function __construct(FormFactoryInterface $formFactory, RouterInterface $router, $configs)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->configs = $configs;
    }

    public function getCredentials(Request $request)
    {
        $loginPath = $this->getLoginUrl();

        $isLoginSubmit = $request->getPathInfo() == $loginPath && $request->isMethod('POST');

        if (!$isLoginSubmit) {
            return;
        }

        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);
        $data = $form->getData();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );

        return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $token_uri = $this->configs['keycloak_host'].$this->configs['keycloak_token_uri'];
            $rpt_uri = $this->configs['keycloak_host'].$this->configs['keycloak_rpt_uri'];
            $permission_uri = $this->configs['keycloak_host'].$this->configs['keycloak_permissions_uri'];

            $client = new Client();

            //----------------------------------------------------------------------------------------------------------

            $tokenResponse = $client->request('post', $token_uri, [
                'auth' => [$this->configs['keycloak_user'], $this->configs['keycloak_secret']],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'username' => $credentials['_username'],
                    'password' => $credentials['_password'],
                    'grant_type' => 'password',
                ],
            ]);

            $data = json_decode($tokenResponse->getBody(), true);

            $accessToken = $data['access_token'];

            //----------------------------------------------------------------------------------------------------------

            $rptResponse = $client->request('get', $rpt_uri, [
                'headers' => [
                    'Authorization' => 'Bearer '.$data['access_token'],
                ],
            ]);

            $data = json_decode($rptResponse->getBody(), true);

            //----------------------------------------------------------------------------------------------------------

            $rptResponse = $client->request('post', $permission_uri, [
                'auth' => [$this->configs['keycloak_user'], $this->configs['keycloak_secret']],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'token_type_hint' => 'requesting_party_token',
                    'token' => $data['rpt'],
                ],
            ]);

            $data = json_decode($rptResponse->getBody(), true);

            //----------------------------------------------------------------------------------------------------------

            $roles = ['ROLE_USER'];

            foreach ($data['permissions'] as $permiso) {
                $roles[] = 'ROLE_'.strtoupper($permiso['resource_set_name']);
            }

            return new AuthenticatorUser($credentials['_username'], $roles);
        } catch (ConnectException $e) {
            throw new AuthenticationException('No fue posible autenticar al usuario: '.$e->getMessage());
        } catch (RequestException $e) {
            throw new AuthenticationException('Error al intentar obtener los datos del keycloak: '.$e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if (!$targetPath) {
            $targetPath = $this->router->generate($this->configs['default_target_route']);
        }

        return new RedirectResponse($targetPath);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Retorna siempre true porque con el método getUser ya se valida la autenticación del usuario
        // contra el servicio
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('keycloak_security_login');
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function supports(Request $request)
    {
        return $request->request->has('login_form');
    }
}
