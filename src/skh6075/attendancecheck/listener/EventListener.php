<?php


namespace skh6075\attendancecheck\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;

use skh6075\attendancecheck\AttendanceCheck;

use function array_map;

class EventListener implements Listener{

    /** @var AttendanceCheck */
    private $plugin;
    
    
    public function __construct (AttendanceCheck $plugin) {
        $this->plugin = $plugin;
    }
    
    public function handleJoinPlayer (PlayerJoinEvent $event): void{
        $player = $event->getPlayer ();
        $name = $player->getLowerCaseName ();
        
        if (!$this->plugin->isPlayerData ($name)) {
            $this->plugin->addPlayerData ($name);
        }
        if ($this->plugin->getPlayerLastTime ($name) < time ()) {
            $this->plugin->setPlayerLastTime ($name, strtotime ("tomorrow"));
            $this->plugin->setPlayerAttendanceCount ($name, ($day = $this->plugin->getPlayerAttendanceCount ($name) + 1));
            $player->sendMessage ($this->plugin::$prefix . "§f" . $day . "§7일째 출석체크를 하였습니다.");
                
            if ($this->plugin->isAttendanceDayReward ($day)) {
                $player->getInventory ()->addItem (...array_map (function (array $data): Item{
                    return Item::jsonDeserialize ($data);
                }, $this->plugin->getAttendanceDayRewards ($day)));
            }
            $player->sendMessage ($this->plugin::$prefix . "보상 지급이 완료되었습니다.");
        }
    }
}
