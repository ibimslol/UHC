<?php
# plugin hecho por KaitoDoDo
namespace KaitoDoDo\UHC;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as TE;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Effect;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\item\Item;
use onebone\economyapi\EconomyAPI;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\block\Fire;

class UHC extends PluginBase implements Listener {
    
    public $prefix = TE::WHITE . "[" . TE::GOLD . TE::BOLD . "UHC" . TE::RESET . TE::WHITE . "]";
	public $mode = 0;
	public $arenas = array();
	public $currentLevel = "";
        public $op = array();
	
	public function onEnable()
	{
		$this->getLogger()->info(TE::GOLD . "UHC by KaitoDoDo");
                $this->getServer()->getPluginManager()->registerEvents($this ,$this);
                $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
                $this->puri = $this->getServer()->getPluginManager()->getPlugin("PureEntities");
                if(!empty($this->economy))
                {
                $this->api = EconomyAPI::getInstance();
                }
		@mkdir($this->getDataFolder());
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		if($config->get("arenas")!=null)
		{
			$this->arenas = $config->get("arenas");
		}
                foreach($this->arenas as $lev)
		{
			$this->getServer()->loadLevel($lev);
		}
                $mobs = array("Chicken","Cow","Mooshroom","Ocelot","Pig","Rabbit","Sheep","CaveSpider","Creeper","Enderman","PigZombie","Skeleton","Spider","Wolf","Zombie","ZombieVillager");
		if($config->get("mobs")==null)
		{
			$config->set("mobs",$mobs);
		}
		$config->save();
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                $slots->save();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshSigns($this), 10);
        }
        
        public function onDisable() {
            $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
            $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
            if($config->get("arenas")!=null)
            {
                    $this->arenas = $config->get("arenas");
            }
            foreach($this->arenas as $arena)
            {
                    $slots->set("slot1".$arena, 0);
                    $slots->set("slot2".$arena, 0);
                    $slots->set("slot3".$arena, 0);
                    $slots->set("slot4".$arena, 0);
                    $slots->set("slot5".$arena, 0);
                    $slots->set("slot6".$arena, 0);
                    $slots->set("slot7".$arena, 0);
                    $slots->set("slot8".$arena, 0);
                    $slots->set("slot9".$arena, 0);
                    $slots->set("slot10".$arena, 0);
                    $slots->set("slot11".$arena, 0);
                    $slots->set("slot12".$arena, 0);
                    $slots->set("slot13".$arena, 0);
                    $slots->set("slot14".$arena, 0);
                    $slots->set("slot15".$arena, 0);
                    $slots->set("slot16".$arena, 0);
                    $slots->set("slot17".$arena, 0);
                    $slots->set("slot18".$arena, 0);
                    $slots->set("slot19".$arena, 0);
                    $slots->set("slot20".$arena, 0);
                    $slots->set("slot21".$arena, 0);
                    $slots->set("slot22".$arena, 0);
                    $slots->set("slot23".$arena, 0);
                    $slots->set("slot24".$arena, 0);
                    $config->set($arena . "inicio", 0);
                    $slots->save();
                    $this->reload($arena);
            }
        }
        
	public function onCommand(CommandSender $player, Command $cmd, $label, array $args): bool {
        switch($cmd->getName()){
			case "uhc":
				if($player->isOp())
				{
					if(!empty($args[0]))
					{
						if($args[0]=="make")
						{
							if(!empty($args[1]))
							{
								if(file_exists($this->getServer()->getDataPath() . "/worlds/" . $args[1]))
								{
									$this->getServer()->loadLevel($args[1]);
									$this->getServer()->getLevelByName($args[1])->loadChunk($this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorZ());
									array_push($this->arenas,$args[1]);
									$this->currentLevel = $args[1];
									$this->mode = 1;
                                                                        array_push($this->op, $player->getName());
									$player->sendMessage($this->prefix . "Toca el bloque de spawn del equipo §cRED!");
									$player->setGamemode(1);
									$player->teleport($this->getServer()->getLevelByName($args[1])->getSafeSpawn(),0,0);
                                                                        $name = $args[1];
                                                                        $this->zip($player, $name);
								}
								else
								{
									$player->sendMessage($this->prefix . "ERROR mundo no encontrado.");
								}
							}
							else
							{
								$player->sendMessage($this->prefix . "Faltan parametros.");
							}
						}
						else
						{
							$player->sendMessage($this->prefix . "§6/uhc <make> : Crear Arena | Salir del Juego");
                                                        $player->sendMessage($this->prefix . "§cAl crear arena, seleccionar los spawns del lobby UHC, los puntos de aparición y DeathMatch");
                                                        $player->sendMessage($this->prefix . "§6/uhcstart : StartUHC");
						}
					}
					else
					{
						$player->sendMessage($this->prefix . "§6/uhc <make> : Crear Arena | Salir del Juego");
                                                $player->sendMessage($this->prefix . "§cAl crear arena, seleccionar los spawns del lobby UHC, los puntos de aparición y DeathMatch");
                                                $player->sendMessage($this->prefix . "§6/uhcstart : Comenzar UHC");
					}
				}
                                else
                                {
                                }
			return true;
                        
                        case "uhcstart":
                            if($player->isOp())
				{
                                if(!empty($args[0]))
					{
                                        $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                        if($config->get($args[0] . "StartTime") != null)
                                        {
                                        $config->set($args[0] . "StartTime", 10);
                                        $config->save();
                                        $player->sendMessage($this->prefix . "§aEmpezando en 10...");
                                        }
                                        }
                                        else
                                        {
                                            $level = $player->getLevel()->getFolderName();
                                            $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                            if($config->get($level . "StartTime") != null)
                                            {
                                            $config->set($level . "StartTime", 10);
                                            $config->save();
                                            $player->sendMessage($this->prefix . "§aEmpezando en 10...");
                                            }
                                        }
                                }
                                return true;
        }
        }
        
        public function onDamage(EntityDamageEvent $event) {
            if ($event instanceof EntityDamageByEntityEvent) {
                if ($event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {
                     $golpeado = $event->getEntity()->getNameTag();
                     $golpeador = $event->getDamager()->getNameTag();
                if ((strpos($golpeado, "§c<") !== false) && (strpos($golpeador, "§c<") !== false)) {
                $event->setCancelled();
                }
                else if ((strpos($golpeado, "§9<") !== false) && (strpos($golpeador, "§9<") !== false)) {
                $event->setCancelled();
                }
                else if ((strpos($golpeado, "§a<") !== false) && (strpos($golpeador, "§a<") !== false)) {
                $event->setCancelled();
                }
                else if ((strpos($golpeado, "§e<") !== false) && (strpos($golpeador, "§e<") !== false)) {
                $event->setCancelled();
                }
                else if ((strpos($golpeado, "§b<") !== false) && (strpos($golpeador, "§b<") !== false)) {
                $event->setCancelled();
                }
                else if ((strpos($golpeado, "§d<") !== false) && (strpos($golpeador, "§d<") !== false)) {
                $event->setCancelled();
                }
                }
            }
        }
            
        public function enDeath(PlayerDeathEvent $event){
        $jugador = $event->getEntity();
        $level = $jugador->getLevel()->getFolderName();
        if(in_array($level,$this->arenas))
		{
        if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent)
        {
        $asassin = $event->getEntity()->getLastDamageCause()->getDamager();
        if($asassin instanceof Player)
        {
	$event->setDeathMessage("");
            foreach($jugador->getLevel()->getPlayers() as $pl)
			{
                                $muerto = $jugador->getNameTag();
                                $asesino = $asassin->getNameTag();
				$pl->sendMessage(TE::WHITE . $muerto . TE::DARK_AQUA . " ha sido asesinado por " . TE::WHITE . $asesino . TE::YELLOW . ".");
			}
                }
                }
                }
	}
        
        public function onEntityRegainHealthEvent(EntityRegainHealthEvent $event) {
		if ($event->getRegainReason() == EntityRegainHealthEvent::CAUSE_EATING)
                {
                    $player = $event->getEntity();
                    $level = $player->getLevel()->getFolderName();
                    if(in_array($level,$this->arenas))
		{
				$event->setCancelled();
		}
                }
                elseif ($event->getRegainReason() == EntityRegainHealthEvent::CAUSE_REGEN)
                {
                    $player = $event->getEntity();
                    $level = $player->getLevel()->getFolderName();
                    if(in_array($level,$this->arenas))
		{
				$event->setCancelled();
		}
                }
	}
        
        public function eat(PlayerItemConsumeEvent $ev){

        $p = $ev->getPlayer();
        $level = $p->getLevel()->getFolderName();
        if(in_array($level,$this->arenas))
		{
        if($ev->getItem()->getId() === 322){
            
                            $p->setHealth($p->getHealth() + 3);
                            $effect = Effect::getEffect(Effect::REGENERATION);
                            $effect->setVisible(true);
                            $effect->setAmplifier(1);
                            $effect->setDuration(140);
                            $p->addEffect($effect);
                            }
                }
        }
        
        public function onLogin(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
                if(in_array($player->getLevel()->getFolderName(),$this->arenas))
		{
		$player->getInventory()->clearAll();
		$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
		$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
		$player->teleport($spawn,0,0);
                }
	}
        
        public function onQuiter(PlayerQuitEvent $event)
        {
            $pl = $event->getPlayer();
            $level = $pl->getLevel()->getFolderName();
            if(in_array($level,$this->arenas))
            {
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                $pl->setNameTag($pl->getName());
                if($slots->get("slot1".$level)==$pl->getName())
                {
                    $slots->set("slot1".$level, 0);
                }
                if($slots->get("slot2".$level)==$pl->getName())
                {
                    $slots->set("slot2".$level, 0);
                }
                if($slots->get("slot3".$level)==$pl->getName())
                {
                    $slots->set("slot3".$level, 0);
                }
                if($slots->get("slot4".$level)==$pl->getName())
                {
                    $slots->set("slot4".$level, 0);
                }
                if($slots->get("slot5".$level)==$pl->getName())
                {
                    $slots->set("slot5".$level, 0);
                }
                if($slots->get("slot6".$level)==$pl->getName())
                {
                    $slots->set("slot6".$level, 0);
                }
                if($slots->get("slot7".$level)==$pl->getName())
                {
                    $slots->set("slot7".$level, 0);
                }
                if($slots->get("slot8".$level)==$pl->getName())
                {
                    $slots->set("slot8".$level, 0);
                }
                if($slots->get("slot9".$level)==$pl->getName())
                {
                    $slots->set("slot9".$level, 0);
                }
                if($slots->get("slot10".$level)==$pl->getName())
                {
                    $slots->set("slot10".$level, 0);
                }
                if($slots->get("slot11".$level)==$pl->getName())
                {
                    $slots->set("slot11".$level, 0);
                }
                if($slots->get("slot12".$level)==$pl->getName())
                {
                    $slots->set("slot12".$level, 0);
                }
                if($slots->get("slot13".$level)==$pl->getName())
                {
                    $slots->set("slot13".$level, 0);
                }
                if($slots->get("slot14".$level)==$pl->getName())
                {
                    $slots->set("slot14".$level, 0);
                }
                if($slots->get("slot15".$level)==$pl->getName())
                {
                    $slots->set("slot15".$level, 0);
                }
                if($slots->get("slot16".$level)==$pl->getName())
                {
                    $slots->set("slot16".$level, 0);
                }
                if($slots->get("slot17".$level)==$pl->getName())
                {
                    $slots->set("slot17".$level, 0);
                }
                if($slots->get("slot18".$level)==$pl->getName())
                {
                    $slots->set("slot18".$level, 0);
                }
                if($slots->get("slot19".$level)==$pl->getName())
                {
                    $slots->set("slot19".$level, 0);
                }
                if($slots->get("slot20".$level)==$pl->getName())
                {
                    $slots->set("slot20".$level, 0);
                }
                if($slots->get("slot21".$level)==$pl->getName())
                {
                    $slots->set("slot21".$level, 0);
                }
                if($slots->get("slot22".$level)==$pl->getName())
                {
                    $slots->set("slot22".$level, 0);
                }
                if($slots->get("slot23".$level)==$pl->getName())
                {
                    $slots->set("slot23".$level, 0);
                }
                if($slots->get("slot24".$level)==$pl->getName())
                {
                    $slots->set("slot24".$level, 0);
                }
                $slots->save();
            }
        }

        public function onBlockBreakingBad(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$sofar = $config->get($level . "StartTime");
			if($sofar > 0)
			{
				$event->setCancelled();
			}
                        else
                        {
                            $event->setCancelled(false);
                            if($event->getBlock()->getId() == 18)
                            {
                                $rand = rand(1,20);
                                if($rand==1)
                                {
                                $drops = array(Item::get(Item::APPLE,0,1));
                                $event->setDrops($drops);
                                }
                            }
                        }
		}
	}
        
        public function onBlockPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$event->setCancelled(false);
		}
	}
        
        public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
                    $message = $event->getMessage();
                        $event->setCancelled();
			$tag = $player->getNameTag();
                        $players = $this->getServer()->getLevelByName($level)->getPlayers();
                        foreach($players as $pl)
                            {
                                if((strpos($tag, "§c<") !== false) && (strpos($pl->getNameTag(), "§c<") !== false))
                                {                            
                                     $pl->sendMessage($tag . ": " . $message);
                                }
                                else if((strpos($tag, "§9<") !== false) && (strpos($pl->getNameTag(), "§9<") !== false))
                                {                            
                                     $pl->sendMessage($tag . ": " . $message);
                                }
                                else if((strpos($tag, "§a<") !== false) && (strpos($pl->getNameTag(), "§a<") !== false))
                                {                            
                                     $pl->sendMessage($tag . ": " . $message);
                                }
                                else if((strpos($tag, "§e<") !== false) && (strpos($pl->getNameTag(), "§e<") !== false))
                                {                            
                                     $pl->sendMessage($tag . ": " . $message);
                                }
                                else if((strpos($tag, "§b<") !== false) && (strpos($pl->getNameTag(), "§b<") !== false))
                                {                            
                                     $pl->sendMessage($tag . ": " . $message);
                                }
                                else if((strpos($tag, "§d<") !== false) && (strpos($pl->getNameTag(), "§d<") !== false))
                                {                            
                                     $pl->sendMessage($tag . ": " . $message);
                                }
                        }
                }
	}
        
        public function onMovinga(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
                    $mob = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                    $rand = mt_rand(1, 200);
                    if($rand==7)
                    {
                    $lel = array_rand($mob->get("mobs"));
                    $lol = $mob->get("mobs")[$lel];
                    $randal = mt_rand(10, 20);
                    $ent = $this->puri->create($lol, new Position($player->x+$randal, $player->getLevel()->getHighestBlockAt($randal,$randal)+2, $player->z+$randal, $player->getLevel()));
                    $ent->spawnToAll();
                    }
                    elseif($rand==8)
                    {
                    $lel = array_rand($mob->get("mobs"));
                    $lol = $mob->get("mobs")[$lel];
                    $randal = mt_rand(10, 20);
                    $ent = $this->puri->create($lol, new Position($player->x+$randal, $player->getLevel()->getHighestBlockAt($randal,-$randal)+2, $player->z-$randal, $player->getLevel()));
                    $ent->spawnToAll();
                    }
                    elseif($rand==9)
                    {
                    $lel = array_rand($mob->get("mobs"));
                    $lol = $mob->get("mobs")[$lel];
                    $randal = mt_rand(10, 20);
                    $ent = $this->puri->create($lol, new Position($player->x-$randal, $player->getLevel()->getHighestBlockAt(-$randal,$randal)+2, $player->z+$randal, $player->getLevel()));
                    $ent->spawnToAll();
                    }
                    elseif($rand==10)
                    {
                    $lel = array_rand($mob->get("mobs"));
                    $lol = $mob->get("mobs")[$lel];
                    $randal = mt_rand(10, 20);
                    $ent = $this->puri->create($lol, new Position($player->x-$randal, $player->getLevel()->getHighestBlockAt(-$randal,-$randal)+2, $player->z-$randal, $player->getLevel()));
                    $ent->spawnToAll();
                    }
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$sofar = $config->get($level . "PlayTime");
                        $x = $player->getPosition()->x;
                        $z = $player->getPosition()->z;
			if($sofar > 1500)
			{
                            if (($x<-450)||($x>450) || ($z<-450)||($z>450))
                            {
				$player->setOnFire(1);
                                $player->getLevel()->setBlock($player->add(0, -1, 0), new Fire());
                            }
			}
                        elseif($sofar > 1200)
			{
                            if (($x<-400)||($x>400) || ($z<-400)||($z>400))
                            {
				$player->setOnFire(1);
                                $player->getLevel()->setBlock($player->add(0, -1, 0), new Fire());
                            }
			}
                        elseif($sofar > 900)
			{
                            if (($x<-350)||($x>350) || ($z<-350)||($z>350))
                            {
				$player->setOnFire(1);
                                $player->getLevel()->setBlock($player->add(0, -1, 0), new Fire());
                            }
			}
                        elseif($sofar > 600)
			{
                            if (($x<-150)||($x>150) || ($z<-150)||($z>150))
                            {
				$player->setOnFire(1);
                                $player->getLevel()->setBlock($player->add(0, -1, 0), new Fire());
                            }
			}
                        elseif($sofar > 300)
			{
                            if (($x<-100)||($x>100) || ($z<-100)||($z>100))
                            {
				$player->setOnFire(1);
                                $player->getLevel()->setBlock($player->add(0, -1, 0), new Fire());
                            }
			}
                        elseif($sofar > 180)
			{
                            if (($x<-50)||($x>50) || ($z<-50)||($z>50))
                            {
				$player->setOnFire(1);
                                $player->getLevel()->setBlock($player->add(0, -1, 0), new Fire());
                            }
			}
		}
	}
        
        public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$tile = $player->getLevel()->getTile($block);
		
		if($tile instanceof Sign) 
		{
			if($this->mode==26 && in_array($player->getName(), $this->op))
			{
				$tile->setText($this->prefix,TE::YELLOW  . "0 / 24","§b" . $this->currentLevel,TE::GREEN . "[Entra]");
				$this->refreshArenas();
				$this->currentLevel = "";
				$this->mode = 0;
				$player->sendMessage($this->prefix . "Arena Registrada!");
			}
			else
			{
				$text = $tile->getText();
				if($text[0] == $this->prefix)
				{
					if($text[3]==TE::GREEN . "[Entra]")
					{
						$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                                                $namemap = str_replace("§b", "", $text[2]);
						$level = $this->getServer()->getLevelByName($namemap);
                                                $name = $player->getName();
                                                if($slots->get("slot1".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot1".$namemap, $name);
                                                        $player->setNameTag("§c<".$name);
                                                }
                                                elseif($slots->get("slot2".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot2".$namemap, $name);
                                                        $player->setNameTag("§c<".$name);
                                                }
                                                elseif($slots->get("slot3".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot3".$namemap, $name);
                                                        $player->setNameTag("§c<".$name);
                                                }
                                                elseif($slots->get("slot4".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot4".$namemap, $name);
                                                        $player->setNameTag("§c<".$name);
                                                }
                                                elseif($slots->get("slot5".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn2");
                                                        $slots->set("slot5".$namemap, $name);
                                                        $player->setNameTag("§9<".$name);
                                                }
                                                elseif($slots->get("slot6".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn2");
                                                        $slots->set("slot6".$namemap, $name);
                                                        $player->setNameTag("§9<".$name);
                                                }
                                                elseif($slots->get("slot7".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn2");
                                                        $slots->set("slot7".$namemap, $name);
                                                        $player->setNameTag("§9<".$name);
                                                }
                                                elseif($slots->get("slot8".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn2");
                                                        $slots->set("slot8".$namemap, $name);
                                                        $player->setNameTag("§9<".$name);
                                                }
                                                elseif($slots->get("slot9".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn3");
                                                        $slots->set("slot9".$namemap, $name);
                                                        $player->setNameTag("§a<".$name);
                                                }
                                                elseif($slots->get("slot10".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn3");
                                                        $slots->set("slot10".$namemap, $name);
                                                        $player->setNameTag("§a<".$name);
                                                }
                                                elseif($slots->get("slot11".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn3");
                                                        $slots->set("slot11".$namemap, $name);
                                                        $player->setNameTag("§a<".$name);
                                                }
                                                elseif($slots->get("slot12".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn3");
                                                        $slots->set("slot12".$namemap, $name);
                                                        $player->setNameTag("§a<".$name);
                                                }
                                                elseif($slots->get("slot13".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn4");
                                                        $slots->set("slot13".$namemap, $name);
                                                        $player->setNameTag("§e<".$name);
                                                }
                                                elseif($slots->get("slot14".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn4");
                                                        $slots->set("slot14".$namemap, $name);
                                                        $player->setNameTag("§e<".$name);
                                                }
                                                elseif($slots->get("slot15".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn4");
                                                        $slots->set("slot15".$namemap, $name);
                                                        $player->setNameTag("§e<".$name);
                                                }
                                                elseif($slots->get("slot16".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn4");
                                                        $slots->set("slot16".$namemap, $name);
                                                        $player->setNameTag("§e<".$name);
                                                }
                                                elseif($slots->get("slot17".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn5");
                                                        $slots->set("slot17".$namemap, $name);
                                                        $player->setNameTag("§b<".$name);
                                                }
                                                elseif($slots->get("slot18".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn5");
                                                        $slots->set("slot18".$namemap, $name);
                                                        $player->setNameTag("§b<".$name);
                                                }
                                                elseif($slots->get("slot19".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn5");
                                                        $slots->set("slot19".$namemap, $name);
                                                        $player->setNameTag("§b<".$name);
                                                }
                                                elseif($slots->get("slot20".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn5");
                                                        $slots->set("slot20".$namemap, $name);
                                                        $player->setNameTag("§b<".$name);
                                                }
                                                elseif($slots->get("slot21".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn6");
                                                        $slots->set("slot21".$namemap, $name);
                                                        $player->setNameTag("§d<".$name);
                                                }
                                                elseif($slots->get("slot22".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn6");
                                                        $slots->set("slot22".$namemap, $name);
                                                        $player->setNameTag("§d<".$name);
                                                }
                                                elseif($slots->get("slot23".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn6");
                                                        $slots->set("slot23".$namemap, $name);
                                                        $player->setNameTag("§d<".$name);
                                                }
                                                elseif($slots->get("slot24".$namemap)==null)
                                                {
                                                        $thespawn = $config->get($namemap . "Spawn6");
                                                        $slots->set("slot24".$namemap, $name);
                                                        $player->setNameTag("§d<".$name);
                                                }
                                                else
                                                {
                                                    $player->sendMessage($this->prefix.TE::RED."No hay lugares");
                                                    goto stuart;
                                                }
                                                $slots->save();
						$spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$level);
						$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
						$player->teleport($spawn,0,0);
                                                $player->removeAllEffects();
                                                $player->getInventory()->clearAll();
                                                $player->setMaxHealth(20);
                                                $player->setFood(20);
                                                $player->setHealth(20);
                                                $player->sendMessage($this->prefix . "Entraste a UHC");
                                                foreach($level->getPlayers() as $playersinarena)
                                                {
                                                $playersinarena->sendMessage($player->getName() .TE::GREEN. " ha entrado a la partida.");
                                                }
                                                stuart:
					}
					else
					{
						$player->sendMessage($this->prefix .TE::RED. "No puedes entrar");
					}
				}
			}
		}
                elseif (in_array($player->getName(), $this->op)) {
		if($this->mode==1)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn Rojo ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque de spawn del equipo §9BLUE!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==2)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn Azul ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque de spawn del equipo §aGREEN!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==3)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn Verde ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque de spawn del equipo §eYELLOW!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==4)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn Amarillo ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque de spawn del equipo §bAQUA!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==5)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn Aqua ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque de spawn del equipo §dPINK!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==6)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn Rosa ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque teleport del equipo §cRED!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==7)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Teleport Rojo ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque teleport del equipo §9BLUE!");
			$this->mode++;
                        $config->save();
		}
                else if($this->mode==8)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Teleport Azul ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque teleport del equipo §aGREEN!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==9)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Teleport Verde ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque teleport del equipo §eYELLOW!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==10)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Teleport Amarillo ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque teleport del equipo §bAQUA!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==11)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Teleport Aqua ha sido registrado!");
                        $player->sendMessage($this->prefix . "Ahora toca el bloque teleport del equipo §dPink!");
			$this->mode++;
			$config->save();
		}
                else if($this->mode==12)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Teleport Rosa ha sido registrado!");
			$this->mode++;
			if($this->mode==13)
			{
				$player->sendMessage($this->prefix . "Ahora toca el spawn del Combate Final.");
			}
			$config->save();
		}
		else if($this->mode==13)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$config->set("arenas",$this->arenas);
			$player->sendMessage($this->prefix . "Toca el cartel para registrar la arena!");
			$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
			$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
			$player->teleport($spawn,0,0);
			$config->save();
			$this->mode=26;
		}
                }
	}
        
        public function refreshArenas()
	{
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		$config->set("arenas",$this->arenas);
		foreach($this->arenas as $arena)
		{
			$config->set($arena . "PlayTime", 1800);
                        $config->set($arena . "StartTime", 60);
                        $config->set($arena . "inicio", 0);
		}
		$config->save();
	}
        
        public function reload($name)
	{
		if ($this->getServer()->isLevelLoaded($name))
                {
                $this->getServer()->unloadLevel($this->getServer()->getLevelByName($name));
                }
		$zip = new \ZipArchive;
		$zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip');
		$zip->extractTo($this->getServer()->getDataPath() . 'worlds');
		$zip->close();
		unset($zip);
		$this->getServer()->loadLevel($name);
		return true;
	}
        
        public function zip($player, $name)
        {
        $path = realpath($player->getServer()->getDataPath() . 'worlds/' . $name);
				$zip = new \ZipArchive;
				@mkdir($this->getDataFolder() . 'arenas/', 0755);
				$zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip', $zip::CREATE | $zip::OVERWRITE);
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($path),
					\RecursiveIteratorIterator::LEAVES_ONLY
				);
                                foreach ($files as $nu => $file) {
					if (!$file->isDir()) {
						$relativePath = $name . '/' . substr($file, strlen($path) + 1);
						$zip->addFile($file, $relativePath);
					}
				}
				$zip->close();
				$player->getServer()->loadLevel($name);
				unset($zip, $path, $files);
        }
}

class RefreshSigns extends PluginTask {
    public $prefix = TE::WHITE . "[" . TE::GOLD . TE::BOLD . "UHC" . TE::RESET . TE::WHITE . "]";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
  
	public function onRun($currentTick)
	{
		$allplayers = $this->plugin->getServer()->getOnlinePlayers();
		$level = $this->plugin->getServer()->getDefaultLevel();
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Sign) {	
				$text = $t->getText();
				if($text[0]==$this->prefix)
				{
					$aop = 0;
                                        $namemap = str_replace("§b", "", $text[2]);
					foreach($allplayers as $player){
                                            if($player->getLevel()->getFolderName()==$namemap)
                                                {
                                                $aop=$aop+1;
                                            }
                                            }
					$ingame = TE::GREEN . "[Entra]";
					$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
					if($config->get($namemap . "PlayTime")!=1800)
					{
						$ingame = TE::DARK_PURPLE . "[En juego]";
					}
					else if($aop>=24)
					{
						$ingame = TE::GOLD . "[Lleno]";
					}
					$t->setText($this->prefix,TE::YELLOW  . $aop . " / 24",$text[2],$ingame);
				}
			}
		}
	}
}

class GameSender extends PluginTask {
    public $prefix = "";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
                $this->prefix = $this->plugin->prefix;
		parent::__construct($plugin);
	}
  
	public function onRun($tick)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$arenas = $config->get("arenas");
		if(!empty($arenas))
		{
			foreach($arenas as $arena)
			{
				$time = $config->get($arena . "PlayTime");
				$timeToStart = $config->get($arena . "StartTime");
				$levelArena = $this->plugin->getServer()->getLevelByName($arena);
				if($levelArena instanceof Level)
				{
					$playersArena = $levelArena->getPlayers();
					if(count($playersArena)==0)
					{
						$config->set($arena . "PlayTime", 1800);
						$config->set($arena . "StartTime", 60);
                                                $config->set($arena . "inicio", 0);
					}
					else
					{
                                                if(count($playersArena)>=8)
                                                {
                                                    $config->set($arena . "inicio", 1);
                                                    $config->save();
                                                }
						if($config->get($arena . "inicio")==1)
						{
							if($timeToStart>0)
							{
								$timeToStart--;
                                                                foreach($playersArena as $pl)
								{
									$pl->sendPopup(TE::WHITE."Comenzando en ".TE::GREEN . $timeToStart . TE::RESET);
                                                                        if($timeToStart<=5)
                                                                        {
                                                                        $levelArena->addSound(new PopSound($pl));
                                                                        }
                                                                        if($timeToStart<=0)
                                                                        {
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                        }
								}
								if($timeToStart<=0)
								{
                                                                        $config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
                                                                        foreach($playersArena as $pl){
                                                                        if(strpos($pl->getNameTag(), "§c<") !== false)
                                                                        {                            
                                                                             $thespawn = $config->get($arena . "Spawn7");
                                                                        }
                                                                        else if(strpos($pl->getNameTag(), "§9<") !== false)
                                                                        {                            
                                                                             $thespawn = $config->get($arena . "Spawn8");
                                                                        }
                                                                        else if(strpos($pl->getNameTag(), "§a<") !== false)
                                                                        {                            
                                                                             $thespawn = $config->get($arena . "Spawn9");
                                                                        }
                                                                        else if(strpos($pl->getNameTag(), "§e<") !== false)
                                                                        {                            
                                                                             $thespawn = $config->get($arena . "Spawn10");
                                                                        }
                                                                        else if(strpos($pl->getNameTag(), "§b<") !== false)
                                                                        {                            
                                                                             $thespawn = $config->get($arena . "Spawn11");
                                                                        }
                                                                        else if(strpos($pl->getNameTag(), "§d<") !== false)
                                                                        {                            
                                                                             $thespawn = $config->get($arena . "Spawn12");
                                                                        }
                                                                        $spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$levelArena);
                                                                        $pl->teleport($spawn,0,0);
                                                                        $pl->getInventory()->setItem(0, Item::get(Item::WOODEN_SWORD, 0, 1));
                                                                        $pl->getInventory()->setItem(1, Item::get(Item::BREAD, 0, 2));
                                                                        $pl->getInventory()->setItem(2, Item::get(Item::TORCH, 0, 3));
                                                                        $pl->getInventory()->setItem(3, Item::get(322, 0, 1));
                                                                        $pl->getInventory()->setItem(4, Item::get(Item::WOODEN_PICKAXE, 0, 1));
                                                                        $pl->getInventory()->setItem(5, Item::get(Item::WOODEN_AXE, 0, 1));
                                                                        $pl->getInventory()->sendArmorContents($pl);
                                                                        $pl->getInventory()->setHotbarSlotIndex(0, 0);
                                                                    }
								}
								$config->set($arena . "StartTime", $timeToStart);
							}
							else
							{
								$aop = count($levelArena->getPlayers());
                                                                $colors = array();
                                                                foreach($playersArena as $pl)
                                                                {
                                                                array_push($colors, $pl->getNameTag());
                                                                }
                                                                $names = implode("-", $colors);
                                                                $reds = substr_count($names, "§c<");
                                                                $blues = substr_count($names, "§9<");
                                                                $greens = substr_count($names, "§a<");
                                                                $yellows = substr_count($names, "§e<");
                                                                $aquas = substr_count($names, "§b<");
                                                                $pinks = substr_count($names, "§d<");
                                                                foreach($playersArena as $pla)
                                                                {
                                                                $x = intval($pla->x);
                                                                $z = intval($pla->z);
                                                                $pla->sendPopup(TE::BOLD.TE::RED."R:" . $reds .TE::BLUE. " B:" . $blues .TE::GREEN. " G:" . $greens .TE::YELLOW. " Y:" . $yellows .TE::AQUA. " A:" . $aquas .TE::LIGHT_PURPLE. " P:" . $pinks.TE::GOLD." X".TE::GREEN.": ".TE::WHITE.$x.TE::GOLD."  Z".TE::GREEN.":".TE::WHITE.$z.TE::RESET);
                                                                }
								if($aop>=1)
								{
                                                                            $winner = null;
                                                                            $winners = array();
                                                                            if($reds!=0 && $blues==0 && $greens==0 && $yellows==0 && $aquas==0 && $pinks==0)
                                                                            {
                                                                                $winner = TE::BOLD.TE::RED."[RED]";
                                                                                $sear = "§c<";
                                                                            }
                                                                            if($reds==0 && $blues!=0 && $greens==0 && $yellows==0 && $aquas==0 && $pinks==0)
                                                                            {
                                                                                $winner = TE::BOLD.TE::BLUE."[BLUE]";
                                                                                $sear = "§9<";
                                                                            }
                                                                            if($reds==0 && $blues==0 && $greens!=0 && $yellows==0 && $aquas==0 && $pinks==0)
                                                                            {
                                                                                $winner = TE::BOLD.TE::GREEN."[GREEN]";
                                                                                $sear = "§a<";
                                                                            }
                                                                            if($reds==0 && $blues==0 && $greens==0 && $yellows!=0 && $aquas==0 && $pinks==0)
                                                                            {
                                                                                $winner = TE::BOLD.TE::YELLOW."[YELLOW]";
                                                                                $sear = "§e<";
                                                                            }
                                                                            if($reds==0 && $blues==0 && $greens==0 && $yellows==0 && $aquas!=0 && $pinks==0)
                                                                            {
                                                                                $winner = TE::BOLD.TE::AQUA."[AQUA]";
                                                                                $sear = "§b<";
                                                                            }
                                                                            if($reds==0 && $blues==0 && $greens==0 && $yellows==0 && $aquas==0 && $pinks!=0)
                                                                            {
                                                                                $winner = TE::BOLD.TE::LIGHT_PURPLE."[PINK]";
                                                                                $sear = "§d<";
                                                                            }
                                                                            if($winner!=null)
                                                                            {
                                                                                foreach($playersArena as $pl)
                                                                                {
                                                                                    $pl->getInventory()->clearAll();
                                                                                    $pl->removeAllEffects();
                                                                                    $pl->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                                                                                    if(strpos($pl->getNameTag(), $sear) !== false)
                                                                                    {
                                                                                        array_push($winners, $pl->getNameTag());
                                                                                        $this->plugin->api->addMoney($pl,2500);
                                                                                    }
                                                                                    $pl->setNameTag($pl->getName());
                                                                                    $this->reload($arena);
                                                                                    $config->set($arena . "PlayTime", 1800);
                                                                                    $config->set($arena . "StartTime", 60);
                                                                                    $config->set($arena . "inicio", 0);
                                                                                    $config->save();
                                                                                }
                                                                                $this->getOwner()->getServer()->broadcastMessage($this->prefix .TE::YELLOW. ">> ".TE::GOLD. "Equipo ".$winner.TE::GOLD." Ganó UHC en la arena ".TE::AQUA.$arena);
                                                                                $namewin = implode(", ", $winners);
                                                                                $this->getOwner()->getServer()->broadcastMessage($this->prefix .TE::YELLOW. ">> ".TE::GOLD."Ganadores:".$namewin);
                                                                            }
								}
								$time--;
								if($time == 1799)
								{
                                                                        $slots = new Config($this->plugin->getDataFolder() . "/slots.yml", Config::YAML);
                                                                        $slots->set("slot1".$arena, 0);
                                                                        $slots->set("slot2".$arena, 0);
                                                                        $slots->set("slot3".$arena, 0);
                                                                        $slots->set("slot4".$arena, 0);
                                                                        $slots->set("slot5".$arena, 0);
                                                                        $slots->set("slot6".$arena, 0);
                                                                        $slots->set("slot7".$arena, 0);
                                                                        $slots->set("slot8".$arena, 0);
                                                                        $slots->set("slot9".$arena, 0);
                                                                        $slots->set("slot10".$arena, 0);
                                                                        $slots->set("slot11".$arena, 0);
                                                                        $slots->set("slot12".$arena, 0);
                                                                        $slots->set("slot13".$arena, 0);
                                                                        $slots->set("slot14".$arena, 0);
                                                                        $slots->set("slot15".$arena, 0);
                                                                        $slots->set("slot16".$arena, 0);
                                                                        $slots->set("slot17".$arena, 0);
                                                                        $slots->set("slot18".$arena, 0);
                                                                        $slots->set("slot19".$arena, 0);
                                                                        $slots->set("slot20".$arena, 0);
                                                                        $slots->set("slot21".$arena, 0);
                                                                        $slots->set("slot22".$arena, 0);
                                                                        $slots->set("slot23".$arena, 0);
                                                                        $slots->set("slot24".$arena, 0);
                                                                        $slots->save();
									foreach($playersArena as $pl)
									{
                                                                            $pl->sendMessage(TE::YELLOW.">--------------------------------");
                                                                            $pl->sendMessage(TE::YELLOW.">".TE::RED."Atención:".TE::GOLD." El juego ha comenzado");
                                                                            $pl->sendMessage(TE::YELLOW.">".TE::WHITE."usando el mapa " .TE::AQUA. $arena);
                                                                            $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Estas seguro en: ".TE::GOLD."-450".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."450");
                                                                            $pl->sendMessage(TE::YELLOW.">--------------------------------");
                                                                            $levelArena->addSound(new AnvilUseSound($pl));
									}
								}
                                                                if($time == 1560)
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Debes ir a los puntos: ".TE::GOLD."-400".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."400");
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::RED."TIENES 1 MINUTO");
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                    }
                                                                }
                                                                if($time == 1260)
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Debes ir a los puntos: ".TE::GOLD."-350".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."350");
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::RED."TIENES 1 MINUTO");
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                    }
                                                                }
                                                                if($time == 1020)
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Debes ir a los puntos: ".TE::GOLD."-150".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."-150");
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::RED."TIENES 2 MINUTOS");
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                    }
                                                                }
                                                                if($time == 960)
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Debes ir a los puntos: ".TE::GOLD."-150".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."-150");
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::RED."TIENES 1 MINUTO");
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                    }
                                                                }
                                                                if($time == 660)
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Debes ir a los puntos: ".TE::GOLD."-100".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."-100");
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::RED."TIENES 1 MINUTO");
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                    }
                                                                }
                                                                if($time == 360)
                                                                {
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::GREEN."Debes ir a los puntos: ".TE::GOLD."-50".TE::DARK_PURPLE." <".TE::AQUA." X,Z ".TE::DARK_PURPLE."< ".TE::GOLD."-50");
                                                                        $pl->sendMessage(TE::YELLOW.">".TE::RED."TIENES 1 MINUTO");
                                                                        $levelArena->addSound(new AnvilUseSound($pl));
                                                                    }
                                                                }
								if($time>=180)
								{
								$time2 = $time - 180;
								$minutes = $time2 / 60;
								if(is_int($minutes) && $minutes>0)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $minutes . " minutos para la DeathMatch");
									}
								}
								else if($time2 == 30 || $time2 == 15 || $time2 == 10 || $time2 ==5 || $time2 ==4 || $time2 ==3 || $time2 ==2 || $time2 ==1)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $time2 . " segundos para la DeathMatch");
                                                                                $levelArena->addSound(new PopSound($pl));
									}
								}
								if($time2 <= 0)
								{
									$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
                                                                        $thespawn = $config->get($arena . "Spawn13");
                                                                        $spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$levelArena);
									foreach($playersArena as $pl)
									{
										$pl->teleport($spawn,0,0);
                                                                                $levelArena->addSound(new AnvilUseSound($pl));
									}
								}
								}
								else
								{
									$minutes = $time / 60;
									if(is_int($minutes) && $minutes>0)
									{
										foreach($playersArena as $pl)
										{
											$pl->sendMessage($this->prefix .TE::YELLOW. $minutes . " " .TE::GREEN."minutos restantes");
										}
									}
									else if($time == 30 || $time == 15 || $time == 10 || $time ==5 || $time ==4 || $time ==3 || $time ==2 || $time ==1)
									{
										foreach($playersArena as $pl)
										{
											$pl->sendMessage($this->prefix .TE::YELLOW. $time . " " .TE::GREEN. "segundos restantes");
										}
									}
									if($time <= 0)
									{
										foreach($playersArena as $pl)
										{
											$pl->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn(),0,0);
											$pl->getInventory()->clearAll();
                                                                                        $pl->removeAllEffects();
                                                                                        $pl->setFood(20);
                                                                                        $pl->setHealth(20);
                                                                                        $pl->setNameTag($pl->getName());
                                                                                        $this->reload($arena);
                                                                                        $config->set($arena . "inicio", 0);
                                                                                        $config->save();
										}
                                                                                $this->getOwner()->getServer()->broadcastMessage($this->prefix .TE::YELLOW. ">> ".TE::GOLD."No gano ningun equipo en ".$arena);
										$time = 1800;
									}
								}
								$config->set($arena . "PlayTime", $time);
							}
						}
						else
						{
                                                    foreach($playersArena as $pl)
                                                    {
                                                            $pl->sendPopup(TE::RED . "Faltan Jugadores" .TE::RESET);
                                                    }
                                                    $config->set($arena . "PlayTime", 1800);
                                                    $config->set($arena . "StartTime", 60);
						}
					}
				}
			}
		}
		$config->save();
	}
        
        public function reload($name)
	{
		if ($this->plugin->getServer()->isLevelLoaded($name))
                {
                $this->plugin->getServer()->unloadLevel($this->plugin->getServer()->getLevelByName($name));
                }
		$zip = new \ZipArchive;
		$zip->open($this->plugin->getDataFolder() . 'arenas/' . $name . '.zip');
		$zip->extractTo($this->plugin->getServer()->getDataPath() . 'worlds');
		$zip->close();
		unset($zip);
		$this->plugin->getServer()->loadLevel($name);
		return true;
	}
}
