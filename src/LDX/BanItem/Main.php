<?php

declare(strict_types=1);

namespace LDX\BanItem;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Filesystem;
use pocketmine\utils\TextFormat as TEXTFORMAT;

final class Main extends PluginBase implements Listener {

    /** @var string[]|null $items */
    private ?array $items;
    /** @var string[] $spys */
    private array $spys = [];

    public function onEnable(): void {
        $this->saveItems();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function getPlayerPosition(Player $sender): string {
        $pos = $sender->getPosition();
        $playerX = $pos->getX();
        $playerY = $pos->getY();
        $playerZ = $pos->getZ();
        $outX = round($playerX, 1);
        $outY = round($playerY, 1);
        $outZ = round($playerZ, 1);
        $worldName = $sender->getWorld()->getDisplayName();
        return ("x:" . $outX . ", y:" . $outY . ", z:" . $outZ . ". On: " . $worldName);
    }

    public function onTap(PlayerItemUseEvent $event): void {
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!$p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass")) {
                $p->sendMessage("[BanItem] That item is banned.");
                $event->cancel();
            }
        }
    }

    public function onTouch(PlayerInteractEvent $event): void {
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!$p->hasPermission("banitem.*") || !$p->hasPermission("banitem.bypass")) {
                $p->sendMessage("[BanItem] That item is banned.");
                $event->cancel();
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if($p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass")) {
                $event->cancel();
            }
        }
    }

    public function onHurt(EntityDamageByEntityEvent $event): void {
        $p = $event->getDamager();
        if($p instanceof Player) {
            if($this->isBanned($p->getInventory()->getItemInHand())) {
                foreach($this->getServer()->getOnlinePlayers() as $player) {
                    if(isset($this->spys[strtolower($player->getName())])) {
                        $player->sendMessage($p->getName() . " tried a Banned Item: " . $p->getInventory()->getItemInHand());
                    }
                }
                if(!$p->hasPermission("banitem.*") || !$p->hasPermission("banitem.bypass")) {
                    $p->sendMessage("[BanItem] That item is banned.");
                    $event->cancel();
                }
            }
        }
    }

    public function onEat(PlayerItemConsumeEvent $event): void {
        $p = $event->getPlayer();
        if($this->isBanned($event->getItem())) {
            $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getItem() . " at " . $this->getPlayerPosition($p));
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                if(isset($this->spys[strtolower($player->getName())])) {
                    $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getItem());
                }
            }
            if(!$p->hasPermission("banitem.*") || !$p->hasPermission("banitem.bypass")) {
                $p->sendMessage("[BanItem] That item is banned.");
                $event->cancel();
            }
        }
    }

    public function onShoot(EntityShootBowEvent $event): void {
        $p = $event->getEntity();
        if($p instanceof Player) {
            if($this->isBanned($event->getBow())) {
                foreach($this->getServer()->getOnlinePlayers() as $player) {
                    if(isset($this->spys[strtolower($player->getName())])) {
                        $player->sendMessage($p->getName() . " tried a Banned Item: " . $event->getBow());
                    }
                }
                $this->getLogger()->info($p->getName() . " tried a Banned Item: " . $event->getBow() . " at " . $this->getPlayerPosition($p));
                if(!$p->hasPermission("banitem.*") || !$p->hasPermission("banitem.bypass")) {
                    $p->sendMessage("[BanItem] That item is banned.");
                    $event->cancel();
                }
            }
        }
    }

    public function onCommand(CommandSender $p, Command $cmd, string $label, array $args) : bool {
        if(!isset($args[0])) {
            return false;
        }

        if($args[0] === "report" && $p instanceof Player) {
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

        $item = StringToItemParser::getInstance()->parse($args[1]) ?? LegacyStringToItemParser::getInstance()->parse($args[1]);
        if($item === null) {
            $p->sendMessage("[BanItem] Please only use an item's ID value, and damage if needed.");
            return true;
        }

        if($args[0] === "ban") {
            $i = $item->getId();
            if($item->getMeta() !== 0) {
                $i = $i . "#" . $item->getMeta();
            }
            if(in_array($i, $this->items)) {
                $p->sendMessage("[BanItem] That item is already banned.");
            } else {
                $this->items[] = $i;
                $this->saveItems();
                $p->sendMessage("[BanItem] " . str_replace("#", ":", $i) . " has been banned");
                $this->getLogger()->info("[BanItem] " . str_replace("#", ":", $i) . " has been banned by " . $p->getName());
            }
        }elseif($args[0] === "unban") {
            $i = $item->getId();
            if($item->getMeta() !== 0) {
                $i = $i . "#" . $item->getMeta();
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

    public function isBanned(Item $i): bool {
        if (in_array(strval($i->getId()), $this->items, true) || in_array(($i->getId() . "#" . $i->getMeta()), $this->items, true)) {
            return true;
        }
        return false;
    }

    public function saveItems(): void {
        if(!isset($this->items)) {
            if(!file_exists($this->getDataFolder() . "items.bin")) {
                Filesystem::safeFilePutContents($this->getDataFolder() . "items.bin", json_encode([]));
            }
            $this->items = json_decode(file_get_contents($this->getDataFolder() . "items.bin"), true);
        }
        file_put_contents($this->getDataFolder() . "items.bin", json_encode($this->items));
    }
}
