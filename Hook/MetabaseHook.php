<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Metabase\Hook;

use Metabase\Metabase;
use Metabase\Service\MetabaseService;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;

class MetabaseHook extends BaseHook
{
    private $metabaseService;

    public function __construct(MetabaseService $metabaseService)
    {
        $this->metabaseService = $metabaseService;
    }

    public function metabaseHome(HookRenderEvent $event)
    {
        // The url of the metabase installation
        $metabaseUrl = Metabase::getConfigValue(Metabase::CONFIG_KEY_URL);
        // The secret embed key from the admin settings screen
        $metabaseKey = Metabase::getConfigValue(Metabase::CONFIG_KEY_TOKEN);

        $metabase = new \Metabase\Embed($metabaseUrl, $metabaseKey);

        $apiResult = json_decode($this->metabaseService->getDashboards(), true);

        for ($i = 0; $i < $apiResult[$i]; $i++)
        {
            $dashboards [] = $metabase->dashboardIFrame($apiResult[$i]['id']);
        }

        $event->add(
            $this->render(
                'metabase-module.html',
                ["dashboards" => $dashboards]
            )
        );
    }

    public function metabaseConfig(HookRenderEvent $event)
    {
        if (null !== $params = ModuleConfigQuery::create()->findByModuleId(Metabase::getModuleId())) {
            /** @var ModuleConfig $param */
            foreach ($params as $param) {
                $vars[$param->getName()] = $param->getValue();
            }
        }
        $event->add($this->render('module-configuration.html'));
    }
}
