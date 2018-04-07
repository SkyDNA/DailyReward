<?php

namespace StuckDexter;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;

class dailyreward extends PluginBase implements Listener{

public $prefix = "§7[§5DailyReward§7]§r ";
public $reg = false;

public function onEnable(){
$this->getServer()->getPluginManager()->registerEvents($this, $this);
@mkdir($this->getDataFolder());
$players = new Config($this->getDataFolder()."players.yml", Config::YAML);
$config = new Config($this->getDataFolder()."config.yml", Config::YAML);
if(empty($config->get("Reward"))){
$config->set("Reward", "say Hi");
$config->set("BlockX", 0);
$config->set("BlockY", 0);
$config->set("BlockZ", 0);
$config->save();
}
$this->getServer()->getScheduler()->scheduleRepeatingTask(new RewardUpdate($this), 1);
}
public function onJoin(PlayerJoinEvent $event){
$name = $event->getPlayer()->getName();
$players = new Config($this->getDataFolder()."players.yml", Config::YAML);
if(empty($players->get($name))){
$players->set($name, false);
$players->save();
}
}
public function onInteract(PlayerInteractEvent $event){
$players = new Config($this->getDataFolder()."players.yml");
$config = new Config($this->getDataFolder()."config.yml", Config::YAML);
$block = $event->getBlock();
$player = $event->getPlayer();
$name = $player->getName();
if($block->getX() == $config->get("BlockX") && $block->getY() == $config->get("BlockY") && $block->getZ() == $config->get("BlockZ")){
if($players->get($name) === false){
$command = str_replace("%p%", $name, $config->get("Reward"));
    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
    $players->set($name, true);
    $players->save();
}else{
$player->sendMessage("§4Du hast deine Tägliche belohnung bereits abgeholt");
}
}
if($this->reg === true){
$config->set("BlockX", $block->getX());
$config->set("BlockY", $block->getY());
$config->set("BlockZ", $block->getZ());
$config->save();
$player->sendMessage($this->prefix."§aDie position wurde gesetzt");
$this->reg = false;
}
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
$config = new Config($this->getDataFolder()."config.yml", Config::YAML);
if(strtolower($cmd->getName()) == "reward"){
if($sender instanceof Player){
if($sender->hasPermission("reward.main")){
$this->reg = true;
$sender->sendMessage($this->prefix."§aTippe nun den Block an!");
}else{
$sender->sendMessage("§4Du darfst das nicht");
}
}else{
$this->getLogger()->info($this->prefix."§4Diesen command kannst du nur ingame nutzen");
}
}
}
}
class RewardUpdate extends PluginTask{

public function __construct(DailyReward $plugin) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun($tick) {
    $players = new Config($this->plugin->getDataFolder()."players.yml", Config::YAML);
    if(date("G") == 24){
    $players->setAll(false);
    $players->save();
    }
    }
    }

?>
