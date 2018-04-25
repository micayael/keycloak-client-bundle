<?php

namespace Micayael\Keycloak\ClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('keycloak_client');

        $rootNode
            ->children()
                ->scalarNode('keycloak_host')
                    ->info('Servidor de keycloak')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('keycloak_user')
                    ->info('Usuario de acceso al keycloak')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('keycloak_secret')
                    ->info('Contraseña de acceso al keycloak')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('keycloak_token_uri')
                    ->info('URI del servicio para obtener el access_token')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('keycloak_rpt_uri')
                    ->info('URI del servicio para obtener el rpt_token')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('keycloak_permissions_uri')
                    ->info('URI del servicio para obtener los permisos')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('change_password_url')
                    ->info('indica la url para que el usuario cambie su contraseña')
                    ->isRequired()
                ->end()

                ->scalarNode('default_target_route')
                    ->info('Ruta predeterminada para ingresar después de un login si no existe referrer')
                    ->defaultValue('admin')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
