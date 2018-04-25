<?php

namespace Micayael\Keycloak\ClientBundle\Controller;

use Micayael\Keycloak\ClientBundle\Form\LoginForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $authUtils = $this->get('security.authentication_utils');

        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render('@KeycloakClient/security/login.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');
    }
}
