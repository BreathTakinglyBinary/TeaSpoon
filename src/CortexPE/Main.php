<?php

/**
 *
 * MMP""MM""YMM               .M"""bgd
 * P'   MM   `7              ,MI    "Y
 *      MM  .gP"Ya   ,6"Yb.  `MMb.   `7MMpdMAo.  ,pW"Wq.   ,pW"Wq.`7MMpMMMb.
 *      MM ,M'   Yb 8)   MM    `YMMNq. MM   `Wb 6W'   `Wb 6W'   `Wb MM    MM
 *      MM 8M""""""  ,pm9MM  .     `MM MM    M8 8M     M8 8M     M8 MM    MM
 *      MM YM.    , 8M   MM  Mb     dM MM   ,AP YA.   ,A9 YA.   ,A9 MM    MM
 *    .JMML.`Mbmmd' `Moo9^Yo.P"Ybmmd"  MMbmmd'   `Ybmd9'   `Ybmd9'.JMML  JMML.
 *                                     MM
 *                                   .JMML.
 * This file is part of TeaSpoon.
 *
 * TeaSpoon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TeaSpoon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with TeaSpoon.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author CortexPE
 * @link https://CortexPE.xyz
 *
 */

declare(strict_types = 1);

namespace CortexPE;

use CortexPE\block\BlockManager;
use CortexPE\commands\CommandManager;
use CortexPE\entity\EntityManager;
use CortexPE\handlers\{
	EnchantHandler, PacketHandler
};
use CortexPE\inventory\BrewingManager;
use CortexPE\item\{
	enchantment\Enchantment, ItemManager
};
use CortexPE\level\weather\Weather;
use CortexPE\network\PacketManager;
use CortexPE\task\TickLevelsTask;
use CortexPE\tile\Tile;
use CortexPE\utils\FishingLootTable;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {

	// self explanatory constants
	public const CONFIG_VERSION = 32;

	/** @var string */
	public const
		BASE_POCKETMINE_VERSION = "3.0.0",
		TESTED_MIN_POCKETMINE_VERSION = "3.0.0",
		TESTED_MAX_POCKETMINE_VERSION = "4.0.0";

	///////////////////////////////// START OF INSTANCE VARIABLES /////////////////////////////////
	/** @var Config */
	public static $config;
	/** @var Config */
	public static $cacheFile;
	/** @var int[] */
	public static $onPortal = [];
	/** @var string */
	public static $netherName = "nether";
	/** @var Level */
	public static $netherLevel;
	/** @var string */
	public static $endName = "ender";
	/** @var Level */
	public static $endLevel;
	/** @var bool */
	public static $lightningFire = false;
	/** @var Session[] */
	private $sessions = [];
	/** @var bool */
	private $disable = false;
	/** @var BrewingManager */
	private $brewingManager = null;
	/** @var Weather[] */
	public static $weatherData = [];
	/** @var Main */
	private static $instance;
	/** @var string */
	private static $sixCharCommitHash = "";
	////////////////////////////////// END OF INSTANCE VARIABLES //////////////////////////////////

	///////////////////////////////// START OF CONFIGS VARIABLES /////////////////////////////////
	/** @var bool */
	public static $registerVanillaEntities = true;
	/** @var bool */
	public static $registerVanillaEnchantments = true;
	/** @var bool */
	public static $registerDimensions = true;
	/** @var bool */
	public static $weatherEnabled = true;
	/** @var int */
	public static $weatherMinTime = 6000;
	/** @var int */
	public static $weatherMaxTime = 12000;
	/** @var bool */
	public static $enableWeatherLightning = true;
	/** @var bool */
	public static $limitedCreative = false;
	/** @var bool */
	public static $randomFishingLootTables = false;
	/** @var bool */
	public static $vanillaNetherTransfer = false;
	/** @var string */
	public static $overworldLevelName = "";
	/** @var Level */
	public static $overworldLevel = null;
	/** @var bool */
	public static $instantArmorReplace = false;
	/** @var bool */
	public static $elytraEnabled = true;
	/** @var bool */
	public static $elytraBoostEnabled = true;
	/** @var bool */
	public static $silkSpawners = false;
	/** @var bool */
	public static $fireworksEnabled = true;
	/** @var bool */
	public static $ePearlEnabled = true;
	/** @var bool */
	public static $chorusFruitEnabled = true;
	/** @var bool */
	public static $instantArmorEnabled = true;
	/** @var bool */
	public static $dropMobExperience = true;
	/** @var bool */
	public static $fishingEnabled = true;
	/** @var bool */
	public static $clearInventoryOnGMChange = false;
	/** @var bool */
	public static $mobSpawnerEnable = true;
	/** @var bool */
	public static $hoppersEnabled = true;
	/** @var bool */
	public static $beaconEnabled = true;
	/** @var bool */
	public static $beaconEffectsEnabled = true;
	/** @var bool */
	public static $shulkerBoxEnabled = true;
	/** @var bool */
	public static $elytraBoostParticles = true;
	/** @var bool */
	public static $endCrystalExplode = true;
	/** @var bool */
	public static $EnchantingTableEnabled = true;
	/** @var bool */
	public static $AnvilEnabled = true;
	/** @var bool */
	public static $dragonEggTeleport = true;
	/** @var float */
	public static $endCrystalPower = 6;
	/** @var string */
	public static $cars = "";
	/** @var bool */
	public static $creepersExplodes = false;
	/** @var bool */
	public static $chargedCreepers = true;
	/** @var bool */
	public static $ignitableCreepers = true;
	/** @var bool */
	public static $lightningRods = false;
	/** @var bool */
	public static $enableIronGolemStructures = true;
	/** @var bool */
	public static $enableSnowGolemStructures = true;
	/** @var bool */
	public static $shearableSnowGolem = true;
	/** @var bool */
	public static $snowGolemSnowTrails = false;
	/** @var bool */
	public static $snowGolemMelts = true;
	/** @var bool */
	public static $snowLayerMelts = true;
	/** @var bool */
	public static $brewingStandsEnabled = true;
	/** @var bool */
	public static $cauldronsEnabled = true;

	////////////////////////////////// END OF CONFIGS VARIABLES //////////////////////////////////

	public static function getInstance(): Main{
		return self::$instance;
	}

	public static function sendVersion(CommandSender $sender, bool $separator = true){
		$logo = TextFormat::DARK_GREEN . "Tea" . TextFormat::GREEN . "Spon";
		if(Splash::isValentines()){
			$logo = TextFormat::RED . "Dessert" . TextFormat::DARK_RED . "Spoon";
		}elseif(Splash::isChristmastide()){
			$logo = TextFormat::RED . "Tea" . TextFormat::GREEN . "Spoon";
		}
		$sender->sendMessage("This server is running" . $logo . TextFormat::WHITE . " v" . self::$instance->getDescription()->getVersion() . (Utils::isPhared() ? "" : "-dev") . "for PocketMine-MP " . Server::getInstance()->getApiVersion());

		if(self::$sixCharCommitHash != ""){
			$sender->sendMessage("Commit: " . self::$sixCharCommitHash);
		}
		$sender->sendMessage("Repository: https://github.com/CortexPE/TeaSpoon");
		$sender->sendMessage("Website: https://CortexPE.xyz");
		if($separator){
			$sender->sendMessage("--- + --------------- + ---");
		}
	}

	public static function getPluginLogger(): PluginLogger{ // 2 lazy (#blameLarry)
		return self::$instance->getLogger();
	}

	public function onLoad(){
		if(Utils::checkSpoon()){
			$this->getLogger()->error("This plugin is for PMMP only. It is meant to extend PMMP's functionality.");
			$this->getLogger()->error("The plugin will disable itself after being later enabled by the server to prevent any interference with the existing Spoon features.");
			$this->disable = true;
		}
		$this->getLogger()->info("Loading Resources...");

		// Load Resources //
		if(!file_exists($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		$this->saveDefaultConfig();
		self::$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		self::$cacheFile = new Config($this->getDataFolder() . "cache.json", Config::JSON);

		// Load Configuration //
		self::$netherName = self::$config->getNested("dimensions.nether.levelName", self::$netherName);
		self::$endName = self::$config->getNested("dimensions.end.levelName", self::$endName);
		self::$lightningFire = self::$config->getNested("entities.lightningFire", self::$lightningFire);
		self::$registerVanillaEntities = self::$config->getNested("entities.register", self::$registerVanillaEntities);
		self::$registerVanillaEnchantments = self::$config->getNested("enchantments.register", self::$registerVanillaEnchantments);
		self::$registerDimensions = self::$config->getNested("dimensions.enable", self::$registerDimensions);
		self::$weatherEnabled = self::$config->getNested("weather.enable", self::$weatherEnabled);
		self::$weatherMinTime = self::$config->getNested("weather.minDuration", self::$weatherMinTime);
		self::$weatherMaxTime = self::$config->getNested("weather.maxDuration", self::$weatherMaxTime);
		self::$enableWeatherLightning = self::$config->getNested("weather.lightning", self::$enableWeatherLightning);
		self::$limitedCreative = self::$config->getNested("player.limitedCreative", self::$limitedCreative);
		self::$randomFishingLootTables = self::$config->getNested("misc.randomFishingLootTables", self::$randomFishingLootTables);
		self::$vanillaNetherTransfer = self::$config->getNested("dimensions.nether.vanillaNetherTranfer", self::$vanillaNetherTransfer);
		self::$overworldLevelName = self::$config->getNested("dimensions.overrideOverworldLevel", self::$overworldLevelName);
		self::$instantArmorReplace = self::$config->getNested("player.instantArmor.replace", self::$instantArmorReplace);
		self::$elytraEnabled = self::$config->getNested("player.elytra.enable", self::$elytraEnabled);
		self::$elytraBoostEnabled = self::$config->getNested("player.elytra.enableElytraBoost", self::$elytraBoostEnabled);
		self::$silkSpawners = self::$config->getNested("mobSpawner.silkTouchSpawners", self::$silkSpawners);
		self::$fireworksEnabled = self::$config->getNested("fireworks.enable", self::$fireworksEnabled);
		self::$ePearlEnabled = self::$config->getNested("enderPearl.enable", self::$ePearlEnabled);
		self::$chorusFruitEnabled = self::$config->getNested("chorusFruit.enable", self::$chorusFruitEnabled);
		self::$instantArmorEnabled = self::$config->getNested("player.instantArmor.enable", self::$instantArmorEnabled);
		self::$dropMobExperience = self::$config->getNested("Xp.dropMobExperience", self::$dropMobExperience);
		self::$fishingEnabled = self::$config->getNested("player.fishing", self::$fishingEnabled);
		self::$clearInventoryOnGMChange = self::$config->getNested("player.clearInventoryOnGameModeChange", self::$clearInventoryOnGMChange);
		self::$mobSpawnerEnable = self::$config->getNested("mobSpawner.enable", self::$mobSpawnerEnable);
		self::$hoppersEnabled = self::$config->getNested("hopper.enable", self::$hoppersEnabled);
		self::$beaconEnabled = self::$config->getNested("beacon.enable", self::$beaconEnabled);
		self::$beaconEffectsEnabled = self::$config->getNested("beacon.effectsEnabled", self::$beaconEffectsEnabled);
		self::$shulkerBoxEnabled = self::$config->getNested("shulkerBox.enable", self::$shulkerBoxEnabled);
		self::$elytraBoostParticles = self::$config->getNested("player.elytra.elytraBoostParticles", self::$elytraBoostParticles);
		self::$endCrystalExplode = self::$config->getNested("entities.endCrystalExplosion", self::$endCrystalExplode);
		self::$EnchantingTableEnabled = self::$config->getNested("enchantments.enchantingTableEnabled", self::$EnchantingTableEnabled);
		self::$AnvilEnabled = self::$config->getNested("anvil.enable", self::$AnvilEnabled);
		self::$dragonEggTeleport = self::$config->getNested("blocks.dragonEggTeleport", self::$dragonEggTeleport);
		self::$endCrystalPower = self::$config->getNested("entities.endCrystalPower", self::$endCrystalPower);
		self::$cars = self::$config->getNested("misc.vanilla-minecarts", self::$cars);
		self::$creepersExplodes = self::$config->getNested("entities.creeper.enableExplosions", self::$creepersExplodes);
		self::$ignitableCreepers = self::$config->getNested("entities.creeper.enableIgnitedCreepers", self::$ignitableCreepers);
		self::$chargedCreepers = self::$config->getNested("entities.creeper.enableChargedCreepers", self::$chargedCreepers);
		self::$lightningRods = self::$config->getNested("misc.lightningRods", self::$lightningRods);
		self::$enableIronGolemStructures = self::$config->getNested("blocks.enableIronGolem", self::$enableIronGolemStructures);
		self::$enableSnowGolemStructures = self::$config->getNested("blocks.enableSnowGolem", self::$enableSnowGolemStructures);
		self::$shearableSnowGolem = self::$config->getNested("entities.snowGolem.shearable", self::$shearableSnowGolem);
		self::$snowGolemSnowTrails = self::$config->getNested("entities.snowGolem.generatesSnow", self::$snowGolemSnowTrails);
		self::$snowGolemMelts = self::$config->getNested("entities.snowGolem.melting", self::$snowGolemMelts);
		self::$snowLayerMelts = self::$config->getNested("blocks.snowLayerMelts", self::$snowLayerMelts);
		self::$brewingStandsEnabled = self::$config->getNested("blocks.brewingStands", self::$brewingStandsEnabled);
		self::$cauldronsEnabled = self::$config->getNested("cauldron.enable", self::$brewingStandsEnabled);

		// Pre-Enable Checks //
		if(Utils::isPhared()){ // unphared = dev
			$meta = (new \Phar(\Phar::running(false)))->getMetadata(); // https://github.com/poggit/poggit/blob/beta/src/poggit/ci/builder/ProjectBuilder.php#L227-L236
			if(!isset($meta["builderName"]) || !is_array($meta)){
				$this->getLogger()->warning("You're using a developer's build of TeaSpoon. For better performance and stability, please get a pre-packaged version.");
				self::$sixCharCommitHash = "DEVELOPER_VERSION";
			}
			self::$sixCharCommitHash = substr($meta["fromCommit"], 0, 6);
		}else{
			$this->getLogger()->warning("You're using a developer's build of TeaSpoon. For better performance and stability, please get a pre-packaged version.");
		}

		self::$instance = $this;
	}

	public function onEnable(){
		$yr = 2017 . ((2017 != date('Y')) ? '-' . date('Y') : '');
		$stms = TextFormat::DARK_GREEN . "\n\nMMP\"\"MM\"\"YMM              " . TextFormat::GREEN . " .M\"\"\"bgd                                        " . TextFormat::DARK_GREEN . "\nP'   MM   `7             " . TextFormat::GREEN . " ,MI    \"Y                                        " . TextFormat::DARK_GREEN . "\n     MM  .gP\"Ya   ,6\"Yb.  " . TextFormat::GREEN . "`MMb.   `7MMpdMAo.  ,pW\"Wq.   ,pW\"Wq.`7MMpMMMb.  " . TextFormat::DARK_GREEN . "\n     MM ,M'   Yb 8)   MM" . TextFormat::GREEN . "    `YMMNq. MM   `Wb 6W'   `Wb 6W'   `Wb MM    MM  " . TextFormat::DARK_GREEN . "\n     MM 8M\"\"\"\"\"\"  ,pm9MM " . TextFormat::GREEN . " .     `MM MM    M8 8M     M8 8M     M8 MM    MM  " . TextFormat::DARK_GREEN . "\n     MM YM.    , 8M   MM  " . TextFormat::GREEN . "Mb     dM MM   ,AP YA.   ,A9 YA.   ,A9 MM    MM  " . TextFormat::DARK_GREEN . "\n   .JMML.`Mbmmd' `Moo9^Yo." . TextFormat::GREEN . "P\"Ybmmd\"  MMbmmd'   `Ybmd9'   `Ybmd9'.JMML  JMML." . TextFormat::GREEN . "\n                                    MM                                     \n                                  .JMML.  " . TextFormat::YELLOW . Splash::getRandomSplash() . TextFormat::RESET . "\nCopyright (C) CortexPE " . $yr . "\n";
		switch(true){ // todo: add more events?
			case (Splash::isValentines()):
				$stms = TextFormat::RED . "\n\n   .-.                                        " . TextFormat::DARK_RED . "       .-.                    " . TextFormat::RESET . "\n" . TextFormat::RED . "  (_) )-.                                  /  " . TextFormat::DARK_RED . " .--.-'                       " . TextFormat::RESET . "\n" . TextFormat::RED . "     /   \    .-.  .    .    .-.  ).--.---/---" . TextFormat::DARK_RED . "(  (_).-.  .-._..-._..  .-.   " . TextFormat::RESET . "\n" . TextFormat::RED . "    /     \ ./.-'_/ \  / \ ./.-'_/       /    " . TextFormat::DARK_RED . " `-.  /  )(   )(   )  )/   )  " . TextFormat::RESET . "\n" . TextFormat::RED . " .-/.      )(__.'/ ._)/ ._)(__.'/       /    " . TextFormat::DARK_RED . "_    )/`-'  `-'  `-'  '/   (   " . TextFormat::RESET . "\n" . TextFormat::RED . "(_/  `----'     /    /                      " . TextFormat::DARK_RED . "(_.--'/                      `- " . TextFormat::RESET . "\n                                              " . TextFormat::YELLOW . Splash::getRandomSplash() . TextFormat::RESET . "\nCopyright (C) CortexPE " . $yr . "\n";
				break;
			case (Splash::isChristmastide()):
				$stms = TextFormat::RED . "\n\nMMP\"\"MM\"\"YMM              " . TextFormat::GREEN . " .M\"\"\"bgd                                        " . TextFormat::RED . "\nP'   MM   `7             " . TextFormat::GREEN . " ,MI    \"Y                                        " . TextFormat::RED . "\n     MM  .gP\"Ya   ,6\"Yb.  " . TextFormat::GREEN . "`MMb.   `7MMpdMAo.  ,pW\"Wq.   ,pW\"Wq.`7MMpMMMb.  " . TextFormat::RED . "\n     MM ,M'   Yb 8)   MM" . TextFormat::GREEN . "    `YMMNq. MM   `Wb 6W'   `Wb 6W'   `Wb MM    MM  " . TextFormat::RED . "\n     MM 8M\"\"\"\"\"\"  ,pm9MM " . TextFormat::GREEN . " .     `MM MM    M8 8M     M8 8M     M8 MM    MM  " . TextFormat::RED . "\n     MM YM.    , 8M   MM  " . TextFormat::GREEN . "Mb     dM MM   ,AP YA.   ,A9 YA.   ,A9 MM    MM  " . TextFormat::RED . "\n   .JMML.`Mbmmd' `Moo9^Yo." . TextFormat::GREEN . "P\"Ybmmd\"  MMbmmd'   `Ybmd9'   `Ybmd9'.JMML  JMML." . TextFormat::GREEN . "\n                                    MM                                     \n                                  .JMML.  " . TextFormat::YELLOW . Splash::getRandomSplash() . TextFormat::RESET . "\nCopyright (C) CortexPE " . $yr . "\n";
				break;
		}
		$this->getLogger()->info("Loading..." . $stms);

		$this->loadEverythingElseThatMakesThisPluginFunctionalAndNotBrokLMAO();
		$this->getLogger()->info("TeaSpoon is distributed under the AGPL License");
		$this->checkConfigVersion();
	}

	private function loadEverythingElseThatMakesThisPluginFunctionalAndNotBrokLMAO(){
		// Initialize ze managars //
		CommandManager::init();
		Enchantment::init();
		BlockManager::init();
		ItemManager::init();
		EntityManager::init();
		// LevelManager::init(); EXECUTED VIA EventListener
		Tile::init();
		FishingLootTable::init();
		PacketManager::init();
		$this->brewingManager = new BrewingManager();
		$this->brewingManager->init();

		// Register Listeners
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new PacketHandler($this), $this);
		if(self::$registerVanillaEnchantments){
			$this->getServer()->getPluginManager()->registerEvents(new EnchantHandler($this), $this);
		}

		// Task(s)
		if(self::$weatherEnabled){
			$this->getScheduler()->scheduleRepeatingTask(new TickLevelsTask(), 1);
		}
	}

	private function checkConfigVersion(){
		if(Utils::isPhared()){
			$ver = self::$config->get("version");
			if($ver === null || $ver === false || $ver < self::CONFIG_VERSION){
				$this->getLogger()->critical("Your configuration file is Outdated! Keep a backup of it and delete the outdated file.");
			}elseif($ver > self::CONFIG_VERSION){
				$this->getLogger()->critical("Your configuration file is from a higher version of TeaSpoon! Please update the plugin from https://github.com/CortexPE/TeaSpoon");
			}
		}

		if(self::$cacheFile->get("date", "") != strval(date("d-m-y"))){
			self::$cacheFile->set("date", strval(date("d-m-y")));
		}
	}

	public function onDisable(){
		self::$cacheFile->save();
	}

	public function createSession(Player $player): bool{
		if(!isset($this->sessions[$player->getId()])){
			$this->sessions[$player->getId()] = new Session($player);
			$this->getLogger()->debug("Created " . $player->getName() . "'s Session");

			return true;
		}

		return false;
	}

	public function destroySession(Player $player): bool{
		if(isset($this->sessions[$player->getId()])){
			unset($this->sessions[$player->getId()]);
			$this->getLogger()->debug("Destroyed " . $player->getName() . "'s Session");

			return true;
		}

		return false;
	}

	public function getSessionById(int $id){
		if(isset($this->sessions[$id])){
			return $this->sessions[$id];
		}else{
			return null;
		}
	}

	public function getSessionByName(string $name){ // why nawt?
		foreach($this->sessions as $session){
			if($session->getPlayer()->getName() == $name){
				return $session;
			}
		}

		return null;
	}

	public function getBrewingManager(): BrewingManager{
		return $this->brewingManager;
	}
}
