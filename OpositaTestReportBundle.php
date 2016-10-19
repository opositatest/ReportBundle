<?php

namespace OpositaTest\Bundle\ReportBundle;

use Sylius\Bundle\ReportBundle\DependencyInjection\Compiler\RegisterDataFetcherPass;
use Sylius\Bundle\ReportBundle\DependencyInjection\Compiler\RegisterRenderersPass;
use Sylius\Bundle\ResourceBundle\AbstractResourceBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Report component for Symfony2 applications.
 * It is used as a base for report management system inside Sylius.
 *
 * It is fully decoupled, so you can integrate it into your existing project.
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class OpositaTestReportBundle extends AbstractResourceBundle
{
    /**
     * {@inheritdoc}
     */
    public function getSupportedDrivers()
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelNamespace()
    {
        return 'OpositaTest\Bundle\ReportBundle\Model';
    }
}
