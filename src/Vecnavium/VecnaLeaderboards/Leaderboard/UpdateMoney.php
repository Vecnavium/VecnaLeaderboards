<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use onebone\economyapi\EconomyAPI;
use onebone\economyapi\event\money\MoneyChangedEvent;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Main;

class UpdateMoney implements Listener {
    public function onMoneyUpdate(MoneyChangedEvent $event) {
        $name = $event->getUsername();
        $config = new Config(Main::getInstance()->getDataFolder() . "data/$name.yml");
        if($config->get("money") !== EconomyAPI::getInstance()->myMoney($name)) {
            $config->set("money", EconomyAPI::getInstance()->myMoney($name));
            $config->save();
        } else {
            if($config->get("money") === null) {
                $config->set("money", EconomyAPI::getInstance()->myMoney($name));
                $config->save();
            }
        }
    }
}