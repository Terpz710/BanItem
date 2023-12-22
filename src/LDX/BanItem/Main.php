<?php

namespace LDX\BanItem;

use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Armor;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\utils\TextFormat as TEXTFORMAT;

class Main extends PluginBase implements Listener {

    private $items;

    private $spys = [];

    public function onEnable(): void {
        $this->saveItems();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function getPlayerPosition(Player $sender) {

        $playerX = $sender->getPosition()->getX();
        $playerY = $sender->getPosition()->getY();
        $playerZ = $sender->getPosition()->getZ();
        $outX = round($playerX, 1);
        $outY = round($playerY, 1);
        $outZ = round($playerZ, 1);
        $playerLevel = $sender->getWorld()->getFolderName();
        return ("x:" . $outX . ", y:" . $outY . ", z:" . $outZ . ". On: " . $playerLevel);
    }


    public function onTouch(PlayerInteractEvent $event) {
        if ($event->isCancelled()) return;
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
                $p->sendMessage("[BanItem] That item is banned.");
                $event->cancel();
            }
        }
    }

    public function onTap(PlayerItemUseEvent $event){
        if ($event->isCancelled()) return;
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
                $p->sendMessage("[BanItem] That item is banned.");
                $event->cancel();
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        if ($event->isCancelled()) return;
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
                $event->cancel();
            }
        }
    }

    public function onHurt(EntityDamageEvent $event) {
        if ($event->isCancelled()) return;
        if($event instanceof EntityDamageByEntityEvent && $event->getDamager() instanceof Player) {
            $p = $event->getDamager();
            if($this->isBanned($p->getInventory()->getItemInHand())) {
                foreach($this->getServer()->getOnlinePlayers() as $player) {
                    if(isset($this->spys[strtolower($player->getName())])) {
                        $player->sendMessage($p->getName() . " tried a Banned Item: " . $p->getInventory()->getItemInHand());
                    }
                }
                if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
                    $p->sendMessage("[BanItem] That item is banned.");
                    $event->cancel();
                }
            }
        }
    }

    public function onEat(PlayerItemConsumeEvent $event) {
        if ($event->isCancelled()) return;
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
                $p->sendMessage("[BanItem] That item is banned.");
                $event->cancel();
            }
        }
    }

    public function onShoot(EntityShootBowEvent $event) {
        if ($event->isCancelled()) return;
        if($event->getEntity() instanceof Player) {
            $p = $event->getEntity();
            if($this->isBanned($event->getBow())) {
                foreach($this->getServer()->getOnlinePlayers() as $player) {
                    if(isset($this->spys[strtolower($player->getName())])) {
                        $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getBow());
                    }
                }
                $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getBow() . " at " . $this->getPlayerPosition($p));
                if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
                    $p->sendMessage("[BanItem] That item is banned.");
                    $event->cancel();
                }
            }
        }
    }

    public function onCommand(CommandSender $p, Command $cmd, string $label, array $args) : bool{
        if(!isset($args[0])) {
            return false;
        }

        if($args[0] == "report" && $p instanceof Player) {
            if(isset($this->spys[strtolower($p->getName())])) {
                unset($this->spys[strtolower($p->getName())]);
            } else {
                $this->spys[strtolower($p->getName())] = strtolower($p->getName());
            }
            if(isset($this->spys[strtolower($p->getName())])) {
                $p->sendMessage(TEXTFORMAT::GREEN . "You have turned on banitem reports");
            } else {
                $p->sendMessage(TEXTFORMAT::GREEN . "You have turned off banitem reports");
            }
            return true;
        }

        if(!isset($args[1])) {
            return false;
        }

        $item = explode(":", $args[1]);
        if(!is_numeric($item[0]) || (isset($item[1]) && !is_numeric($item[1]))) {
            $p->sendMessage("[BanItem] Please only use an item's ID value, and damage if needed.");
            return true;
        }
        if($args[0] == "ban") {
            $i = $item[0];
            if(isset($item[1])) {
                $i = $i . "#" . $item[1];
            }
            if(in_array($i, $this->items)) {
                $p->sendMessage("[BanItem] That item is already banned.");
            } else {
                array_push($this->items, $i);
                $this->saveItems();
                $p->sendMessage("[BanItem] " . str_replace("#", ":", $i) . " has been banned");
                $this->getLogger()->info("[BanItem] " . str_replace("#", ":", $i) . " has been banned by " . $p->getName());
            }
        }

        if($args[0] == "unban") {

            $i = $item[0];
            if(isset($item[1])) {
                $i = $i . "#" . $item[1];
            }
            if(!in_array($i, $this->items)) {
                $p->sendMessage("[BanItem] That item wasn't banned.");
            } else {
                array_splice($this->items, array_search($i, $this->items), 1);
                $this->saveItems();
                $p->sendMessage("[BanItem] " . str_replace("#", ":", $i) . " has been unbanned.");
                $this->getLogger()->info($p->getName() . " UnBanned Item: " . str_replace("#", ":", $i));
            }
        }
        return true;
    }

    public function isBanned($i)
    {
        if ($i instanceof Tool || $i instanceof Armor || $i instanceof Sword){
            if (in_array(strval($i->getID()), $this->items, true) || in_array(($i->getID() . "#" . $i->getDamage()), $this->items, true)) {
                return true;
            }
        } else{
            if (in_array(strval($i->getID()), $this->items, true) || in_array(($i->getID() . "#" . $i->getMeta()), $this->items, true)) {
                return true;
            }
        }
        return false;
    }


    public function saveItems() {
        if(!isset($this->items)) {
            if(!file_exists($this->getDataFolder() . "items.bin")) {
                @mkdir($this->getDataFolder());
                file_put_contents($this->getDataFolder() . "items.bin", json_encode([]));
            }
            $this->items = json_decode(file_get_contents($this->getDataFolder() . "items.bin"), true);
        }
        @mkdir($this->getDataFolder());
        file_put_contents($this->getDataFolder() . "items.bin", json_encode($this->items));
    }

}
