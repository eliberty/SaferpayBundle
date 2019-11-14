<?php

namespace Payment\Bundle\SaferpayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \RuntimeException
     */
    public function process(ContainerBuilder $container)
    {
        $saferpayServiceId = 'payment.saferpay';
        $payInitParameterFactoryServiceId = 'payment.saferpay.payinitparameter.factory';

        if(
            !$container->hasDefinition($saferpayServiceId) OR
            !$container->hasDefinition($payInitParameterFactoryServiceId)
        ){
            return;
        }

        $payInitParameterFactoryDefinition = $container->getDefinition($payInitParameterFactoryServiceId);
        $payInitParameterFactoryDefinition->addArgument(
            new Reference($container->getParameter('payment.saferpay.payinitparameter.serviceid'))
        );
        $payInitParameterFactoryDefinition->addArgument(
            $container->getParameter('payment.saferpay.payinitparameter.defaults')
        );

        $loggerServiceId = $container->getParameter('payment.saferpay.logger.serviceid');
        if($container->hasAlias($loggerServiceId)){
            $loggerServiceId = $container->getAlias($loggerServiceId);
        }

        if(!$container->hasDefinition($loggerServiceId)){
            return;
        }

        $httplugServiceId= $container->getParameter('payment.saferpay.httpclient.serviceid');

        $saferpayDefinition = $container->getDefinition($saferpayServiceId);

        $saferpayDefinition->addArgument(
            new Reference('httplug.message_factory')
        );

        $saferpayDefinition->addMethodCall('setLogger', [
                new Reference($loggerServiceId)
            ]
        );

        $saferpayDefinition->addMethodCall('setHttpClient', [
            new Reference($httplugServiceId)
        ]);
    }
}