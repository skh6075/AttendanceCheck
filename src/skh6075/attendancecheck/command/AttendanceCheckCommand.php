<?php


namespace skh6075\attendancecheck\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\item\Item;

use skh6075\attendancecheck\AttendanceCheck;

class AttendanceCheckCommand extends Command{

    /** @var AttendanceCheck */
    private $plugin;
    

    public function __construct (string $name, string $description, string $permission) {
        parent::__construct ($name, $description);
        $this->setPermission ($permission);
        $this->plugin = AttendanceCheck::getInstance ();
    }
    
    public function execute (CommandSender $player, string $label, array $args): bool{
        if ($player instanceof Player) {
            if ($player->hasPermission ($this->getPermission ())) {
                switch (array_shift ($args) ?? 'x') {
                    case "보상추가":
                        $item = $player->getInventory ()->getItemInHand ();
                        if (!$item->isNull ()) {
                            $day = array_shift ($args);
                            $count = array_shift ($args) ?? $item->getCount ();
                            if (isset ($day) and is_numeric ($day) and is_numeric ($count)) {
                                $this->plugin->addRewardItem ($day, clone $item->setCount ($count));
                                $player->sendMessage ($this->plugin::$prefix . "성공적으로 보상을 추가하였습니다.");
                            } else {
                                $player->sendMessage ($this->plugin::$prefix . "/출석체크 보상추가 [일] [수량]");
                            }
                        } else {
                            $player->sendMessage ($this->plugin::$prefix . "공기는 보상으로 추가할 수 없습니다.");
                        }
                        break;
                    case "보상제거":
                        $day = array_shift ($args);
                        $index = array_shift ($args);
                        if (isset ($day) and is_numeric ($day) and isset ($index) and is_numeric ($index)) {
                            if ($this->plugin->isAttendanceDayRewardByIndex ($day, $index)) {
                                $this->plugin->deleteAttendanceDayRewardByIndex ($day, $index);
                                $player->sendMessage ($this->plugin::$prefix . "성공적으로 보상을 삭제하였습니다.");
                            } else {
                                $player->sendMessage ($this->plugin::$prefix . "해당 번호의 보상은 존재하지 않습니다.");
                            }
                        } else {
                            $player->sendMessage ($this->plugin::$prefix . "/출석체크 보상삭제 [일] [번호]");
                        }
                        break;
                    case "보상목록":
                        $day = array_shift ($args);
                        if (isset ($day) and is_numeric ($day)) {
                            if ($this->plugin->isAttendanceDayReward ($day)) {
                                $player->sendMessage ($this->plugin::$prefix . "§f" . $day . "§7일 출석 보상: " . implode (", ", array_map (function (array $data): string{
                                    $item = Item::jsonDeserialize ($data);
                                    $name = $item->hasCustomName () ? $item->getCustomName () . "§r" : $item->getName ();
                                    return "{$name} × {$item->getCount ()}개";
                                }, $this->plugin->getAttendanceDayRewards ($day))));
                            } else {
                                $player->sendMessage ($this->plugin::$prefix . "해당 일의 출석체크 보상은 존재하지 않습니다.");
                            }
                        } else {
                            $player->sendMessage ($this->plugin::$prefix . "/출석체크 보상목록 [일]");
                        }
                        break;
                    default:
                        $player->sendMessage ($this->plugin::$prefix . "/출석체크 보상추가 [일] [수량] - 출석체크 보상을 추가합니다.");
                        $player->sendMessage ($this->plugin::$prefix . "/출석체크 보상제거 [일] [번호] - 출석체크 보상을 삭제합니다.");
                        $player->sendMessage ($this->plugin::$prefix . "/출석체크 보상목록 [일] - 출석체크 보상을 봅니다.");
                        break;
                }
            } else {
                $player->sendMessage ($this->plugin::$prefix . "당신은 이 명령어를 사용할 권한이 없습니다.");
            }
        }
        return true;
    }
}