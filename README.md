Keycloak Client Bundle
======================

Conector para keycloak para Symfony4

Instalación del bundle
----------------------

### Agregar al composer.json
~~~
    composer require "micayael/keycloak-client-bundle:~1.0.0"
~~~


### Configuración del bundle

> Para ver la documentación de las configuraciones:
> bin/console config:dump-reference keycloak_client

~~~
keycloak_client:
    keycloak_host: http://sso-sso-app-dev.apps.dncp.gov.py

    keycloak_user: SICP
    keycloak_secret: dc42717b-a354-477d-8822-16ee099aa2a0

    keycloak_token_uri: /auth/realms/SICP/protocol/openid-connect/token
    keycloak_rpt_uri: /auth/realms/SICP/authz/entitlement/SICP
    keycloak_permissions_uri: /auth/realms/SICP/protocol/openid-connect/token/introspect

    change_password_url: http://localhost:9095/cambiar-clave
    default_target_route: pliego.index
~~~

### Publicación de assets

~~~
bin/console assets:install --relative --symlink
~~~

### Importación de rutas en el archivo routes.yaml

~~~
keycloak_client:
    resource: "@KeycloakClientBundle/Resources/config/routing.yml"
    prefix: /
~~~

### Configuración del security.yaml

~~~
    providers:
        keycloak:
            id: 'Micayael\Keycloak\ClientBundle\Security\User\AuthenticatorUserProvider'

    firewalls:
        main:
            anonymous: ~
            logout:
                path: /logout

            guard:
                authenticators:
                    - 'Micayael\Keycloak\ClientBundle\Security\LoginFormAuthenticator'

    access_control:
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin, roles: ROLE_USER }
~~~

Documentación Extra
-------------------

- Para definir tiempo de vida y nombre de la sesión:  https://symfony.com/doc/current/reference/configuration/framework.html#session

Referencias
-----------

- https://knpuniversity.com/screencast/symfony-security