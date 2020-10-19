<?php


namespace skh6075\attendancecheck;

use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;

use skh6075\attendancecheck\command\AttendanceCheckCommand;
use skh6075\attendancecheck\listener\EventListener;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function json_encode;
use function json_decode;
use function file_get_contents;
use function file_put_contents;
use function time;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class AttendanceCheck extends PluginBase{

    public static $prefix = "§l§b[출석체크]§r§7 ";
    
    /** @var AttendanceCheck */
    private static $instance;
    
    /** @var string[] */
    private const DEFAULT_SETTING = [
        "출석체크",
        "출석체크 명령어 입니다.",
        "attendance.check.permission"
    ];
    
    /** @var array */
    protected $rewards = [], $players = [];
    
    
    public static function getInstance (): ?AttendanceCheck{
        return self::$instance;
    }
    
    public function onLoad (): void{
        if (self::$instance === null) {
            self::$instance = $this;
        }
        if (date_default_timezone_get () !== "Asia/Seoul") {
            date_default_timezone_set ("Asia/Seoul");
        }
    }
    
    public function onEnable (): void{
        $this->saveResource ("rewards.json");
        $this->saveResource ("players.json");
        $this->rewards = json_decode (file_get_contents ($this->getDataFolder () . "rewards.json"), true);
        $this->players = json_decode (file_get_contents ($this->getDataFolder () . "players.json"), true);
        
        $this->getServer ()->getCommandMap ()->register ("skh6075", new AttendanceCheckCommand (...self::DEFAULT_SETTING));
        $this->getServer ()->getPluginManager ()->registerEvents (new EventListener ($this), $this);
    }
    
    public function onDisable (): void{
        file_put_contents ($this->getDataFolder () . "rewards.json", json_encode ($this->rewards, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents ($this->getDataFolder () . "players.json", json_encode ($this->players, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    public function addRewardItem (int $day, Item $item): void{
        $this->rewards [$day] [] = $item->jsonSerialize ();
    }
    
    public function isAttendanceDayRewardByIndex (int $day, int $index): bool{
        return isset ($this->rewards [$day]) and isset ($this->rewards [$day] [$index]);
    }
    
    public function deleteAttendanceDayRewardByIndex (int $day, int $index): void{
        unset ($this->rewards [$day] [$index]);
    }
    
    public function isAttendanceDayReward (int $day): bool{
        return isset ($this->rewards [$day]);
    }
    
    public function getAttendanceDayRewards (int $day): array{
        return $this->rewards [$day] ?? [];
    }
    
    public function isPlayerData ($name): bool{
        return isset ($this->players [$name]);
    }
    
    public function addPlayerData ($name): void{
        $this->players [$name] = [
                "count" => 0,
                "lastTime" => time ()
        ];
    }
    
    public function getPlayerLastTime ($name): int{
        return $this->players [$name] ["lastTime"];
    }
    
    public function setPlayerLastTime ($name, int $time): void{
        $this->players [$name] ["lastTime"] = $time;
    }
    
    public function setPlayerAttendanceCount (string $name, int $count): void{
        $this->players [$name] ["count"] = $count;
    }
    
    public function getPlayerAttendanceCount (string $name): int{
        return $this->players [$name] ["count"];
    }
}
