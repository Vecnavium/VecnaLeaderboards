<?php

namespace Vecnavium\VecnaLeaderboards\Provider;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Main;
use pocketmine\utils\TextFormat as C;

class UserDataSessionProvider
{
	private Player $player;
	private Config $config;
	private int $currentStreak = 0;

	/**
	 * UserDataSessionProvider constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
		$this->config = new Config(Main::getInstance()->getDataFolder() . "data/{$player->getName()}.yml");
	}

	public function getKills(): int
	{
		return (int)$this->config->get('kills', 0);
	}

	public function addKill(): void
	{
		$kills = $this->getKills() + 1;
		$this->config->set('kills', $kills);
		$this->config->save();
		$this->currentStreak++;
		if ($this->currentStreak > 5 && $this->currentStreak > $this->getStreak()) {
			Main::getInstance()->getServer()->broadcastMessage(
				C::GRAY . "" . C::DARK_RED . "KillStreak alert:" .
				C::GRAY . "> " . C::WHITE . $this->player->getName() . " is on a " . $this->currentStreak .
				" killstreak. Go kill them to end their streak! ");
			$this->setStreak($this->currentStreak);
		}
		$playerLevel = $this->getLevel();
		foreach ($this->getPlugin()->getYamlProvider()->getLevels() as $level => $data) {
			if ($kills == $data['kills'] && $playerLevel < $level){
				$this->player->sendPopup(C::DARK_GREEN . "You have successfully Leveled up!");
				$this->levelUp();
				foreach ($level["cmds"] as $command) {
					$cmd = str_replace(["{p}", "{k}", "{s}", "{d}", "{l}"], ["\"" . $this->player->getName() . "\"", $this->getKills(), $this->getStreak(), $this->getDeaths(), $this->getLevel()], $command);
					$this->getPlugin()->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
				}
			}
		}
	}

	public function getDeaths(): int
	{
		return (int)$this->config->get('deaths', 0);
	}

	public function addDeath(?Player $assasin = null): void
	{
		$deaths = $this->getDeaths();
		$this->config->set('deaths', $deaths + 1);
		$this->config->save();
		if ($this->currentStreak > $this->getStreak()) {
			if ($assasin !== null) {
				$this->player->sendMessage(C::GRAY . "" . C::DARK_GREEN . "KillStreak alert:" . C::GRAY . "> " . C::WHITE . "Your " . $this->currentStreak . " killstreak was ended by " . $assasin->getName() . "!");
				$assasin->sendMessage(C::GRAY . "" . C::DARK_RED . "KillStreak alert:" . C::GRAY . "> " . C::WHITE . "You have ended " . $this->player->getName() . "'s " . $this->currentStreak . " killstreak!");
			} else {
				$this->player->sendMessage(C::GRAY . "" . C::DARK_GREEN . "KillStreak alert:" . C::GRAY . "> " . C::WHITE . "Your " . $this->currentStreak . " killstreak was ended!");
			}
		}
		$this->currentStreak = 0;
	}

	public function getStreak(): int
	{
		return (int)$this->config->get('killstreak', 0);
	}

	public function setStreak(int $streak): void
	{
		$this->config->set('killstreak', $streak);
		$this->config->save();
	}

	public function getLevel(): int
	{
		return (int)$this->config->get('level', 0);
	}

	public function setLevel(int $level): void
	{
		$this->config->set('level', $level);
		$this->config->save();
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->player;
	}

	/**
	 * @return int
	 */
	public function getCurrentStreak(): int
	{
		return $this->currentStreak;
	}

	/**
	 * @return Main
	 */
	public function getPlugin(): Main
	{
		return Main::getInstance();
	}

	private function levelUp()
	{
		$level = $this->getLevel() + 1;
		$this->config->set('level', $level);
		$this->config->save();
	}

}