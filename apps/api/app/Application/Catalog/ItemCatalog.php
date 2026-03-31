<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Catalog\Item;

use App\Domain\Catalog\Items\ClubItem;
use App\Domain\Catalog\Items\DaggerItem;
use App\Domain\Catalog\Items\GreatclubItem;
use App\Domain\Catalog\Items\HandaxeItem;
use App\Domain\Catalog\Items\JavelinItem;
use App\Domain\Catalog\Items\LightHammerItem;
use App\Domain\Catalog\Items\MaceItem;
use App\Domain\Catalog\Items\QuarterstaffItem;
use App\Domain\Catalog\Items\SickleItem;
use App\Domain\Catalog\Items\SpearItem;
use App\Domain\Catalog\Items\LightCrossbowItem;
use App\Domain\Catalog\Items\DartsItem;
use App\Domain\Catalog\Items\ShortbowItem;
use App\Domain\Catalog\Items\SlingshotItem;
use App\Domain\Catalog\Items\BattleaxeItem;
use App\Domain\Catalog\Items\FlailItem;
use App\Domain\Catalog\Items\GlaiveItem;
use App\Domain\Catalog\Items\GreataxeItem;
use App\Domain\Catalog\Items\GreatswordItem;
use App\Domain\Catalog\Items\HalberdItem;
use App\Domain\Catalog\Items\LanceItem;
use App\Domain\Catalog\Items\LongswordItem;
use App\Domain\Catalog\Items\MorningstarItem;
use App\Domain\Catalog\Items\PikeItem;
use App\Domain\Catalog\Items\RapierItem;
use App\Domain\Catalog\Items\ScimitarItem;
use App\Domain\Catalog\Items\ShortswordItem;
use App\Domain\Catalog\Items\TridentItem;
use App\Domain\Catalog\Items\WarhammerItem;
use App\Domain\Catalog\Items\WarPickItem;
use App\Domain\Catalog\Items\WhipItem;
use App\Domain\Catalog\Items\HandCrossbowItem;
use App\Domain\Catalog\Items\HeavyCrossbowItem;
use App\Domain\Catalog\Items\LongbowItem;
use App\Domain\Catalog\Items\NetItem;
use App\Domain\Catalog\Items\PaddedArmorItem;
use App\Domain\Catalog\Items\LeatherArmorItem;
use App\Domain\Catalog\Items\StuddedLeatherArmorItem;
use App\Domain\Catalog\Items\HideArmorItem;
use App\Domain\Catalog\Items\ChainShirtItem;
use App\Domain\Catalog\Items\ScaleMailItem;
use App\Domain\Catalog\Items\BreastplateItem;
use App\Domain\Catalog\Items\HalfPlateItem;
use App\Domain\Catalog\Items\RingMailItem;
use App\Domain\Catalog\Items\ChainMailItem;
use App\Domain\Catalog\Items\SplintArmorItem;
use App\Domain\Catalog\Items\PlateArmorItem;
use App\Domain\Catalog\Items\ShieldItem;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\BedrollItem;
use App\Domain\Catalog\Items\MessKitItem;
use App\Domain\Catalog\Items\WaterskinItem;
use App\Domain\Catalog\Items\RopeItem;
use App\Domain\Catalog\Items\TorchesItem;
use App\Domain\Catalog\Items\TinderboxItem;
use App\Domain\Catalog\Items\RationsItem;
use App\Domain\Catalog\Items\TentItem;
use App\Domain\Catalog\Items\BlanketItem;
use App\Domain\Catalog\Items\CandlesItem;
use App\Domain\Catalog\Items\ChalkItem;
use App\Domain\Catalog\Items\SoapItem;
use App\Domain\Catalog\Items\InkItem;
use App\Domain\Catalog\Items\QuillItem;
use App\Domain\Catalog\Items\PaperParchmentItem;
use App\Domain\Catalog\Items\SealAndWaxItem;
use App\Domain\Catalog\Items\HourglassItem;
use App\Domain\Catalog\Items\BellItem;
use App\Domain\Catalog\Items\ChainItem;
use App\Domain\Catalog\Items\LockItem;
use App\Domain\Catalog\Items\CrowbarItem;
use App\Domain\Catalog\Items\HammerItem;
use App\Domain\Catalog\Items\IronNailsItem;
use App\Domain\Catalog\Items\ShovelItem;
use App\Domain\Catalog\Items\PickaxeItem;
use App\Domain\Catalog\Items\ManaclesItem;
use App\Domain\Catalog\Items\LanternItem;
use App\Domain\Catalog\Items\HoodedLanternItem;
use App\Domain\Catalog\Items\OilItem;
use App\Domain\Catalog\Items\SpyglassItem;
use App\Domain\Catalog\Items\SteelMirrorItem;
use App\Domain\Catalog\Items\HealersKitItem;
use App\Domain\Catalog\Items\PotionOfHealingItem;
use App\Domain\Catalog\Items\VialsItem;
use App\Domain\Catalog\Items\FlasksItem;
use App\Domain\Catalog\Items\AlchemyBottleItem;
use App\Domain\Catalog\Items\MortarAndPestleItem;
use App\Domain\Catalog\Items\BagItem;
use App\Domain\Catalog\Items\ChestItem;
use App\Domain\Catalog\Items\BasketItem;
use App\Domain\Catalog\Items\BarrelItem;
use App\Domain\Catalog\Items\StorageBottleItem;
use App\Domain\Catalog\Items\MapOrScrollCaseItem;
use App\Domain\Catalog\Items\ArrowsItem;
use App\Domain\Catalog\Items\CrossbowBoltsItem;
use App\Domain\Catalog\Items\SlingBulletsItem;
use App\Domain\Catalog\Items\ThievesToolsItem;
use App\Domain\Catalog\Items\ArtisansToolsItem;
use App\Domain\Catalog\Items\HerbalismKitItem;
use App\Domain\Catalog\Items\MusicalInstrumentItem;
use App\Domain\Catalog\Items\DiceOrCardsItem;
use App\Domain\Catalog\Items\TravelerPackItem;
use App\Domain\Catalog\Items\DungeoneerPackItem;
use App\Domain\Catalog\Items\DiplomatPackItem;
use App\Domain\Catalog\Items\PriestPackItem;
use App\Domain\Catalog\Items\HolySymbolItem;
use App\Domain\Catalog\Items\WoodenShieldItem;
use App\Domain\Catalog\Items\DruidicFocusItem;
use App\Domain\Catalog\Items\ArcaneFocusItem;
use App\Domain\Catalog\Items\SpellbookItem;
use App\Domain\Catalog\Items\CloakItem;
use App\Domain\Catalog\Items\NoArmorItem;
use App\Domain\Catalog\Items\PaperAndQuillItem;
use App\Domain\Catalog\Items\InkAndQuillItem;
use App\Domain\Catalog\Items\HerbsOrHerbalismKitItem;

/**
 * Хранит кодовый справочник предметов и снаряжения.
 */
final class ItemCatalog
{
	/**
	 * Возвращает один активный предмет по коду.
	 */
	public function findActiveItemByCode(string $code): ?Item
	{
		foreach ($this->getActiveItems() as $item) {
			if ($item->getCode() === $code) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Возвращает все активные предметы справочника.
	 *
	 * @return list<Item>
	 */
	public function getActiveItems(): array
	{
		return array_values(array_filter(
			$this->getAllItems(),
			static fn(Item $item): bool => $item->isActive(),
		));
	}

	/**
	 * Возвращает полный кодовый справочник предметов.
	 *
	 * @return list<Item>
	 */
	private function getAllItems(): array
	{
		return [
			new ClubItem,
			new DaggerItem,
			new GreatclubItem,
			new HandaxeItem,
			new JavelinItem,
			new LightHammerItem,
			new MaceItem,
			new QuarterstaffItem,
			new SickleItem,
			new SpearItem,
			new LightCrossbowItem,
			new DartsItem,
			new ShortbowItem,
			new SlingshotItem,
			new BattleaxeItem,
			new FlailItem,
			new GlaiveItem,
			new GreataxeItem,
			new GreatswordItem,
			new HalberdItem,
			new LanceItem,
			new LongswordItem,
			new MorningstarItem,
			new PikeItem,
			new RapierItem,
			new ScimitarItem,
			new ShortswordItem,
			new TridentItem,
			new WarhammerItem,
			new WarPickItem,
			new WhipItem,
			new HandCrossbowItem,
			new HeavyCrossbowItem,
			new LongbowItem,
			new NetItem,
			new PaddedArmorItem,
			new LeatherArmorItem,
			new StuddedLeatherArmorItem,
			new HideArmorItem,
			new ChainShirtItem,
			new ScaleMailItem,
			new BreastplateItem,
			new HalfPlateItem,
			new RingMailItem,
			new ChainMailItem,
			new SplintArmorItem,
			new PlateArmorItem,
			new ShieldItem,
			new BackpackItem,
			new BedrollItem,
			new MessKitItem,
			new WaterskinItem,
			new RopeItem,
			new TorchesItem,
			new TinderboxItem,
			new RationsItem,
			new TentItem,
			new BlanketItem,
			new CandlesItem,
			new ChalkItem,
			new SoapItem,
			new InkItem,
			new QuillItem,
			new PaperParchmentItem,
			new SealAndWaxItem,
			new HourglassItem,
			new BellItem,
			new ChainItem,
			new LockItem,
			new CrowbarItem,
			new HammerItem,
			new IronNailsItem,
			new ShovelItem,
			new PickaxeItem,
			new ManaclesItem,
			new LanternItem,
			new HoodedLanternItem,
			new OilItem,
			new SpyglassItem,
			new SteelMirrorItem,
			new HealersKitItem,
			new PotionOfHealingItem,
			new VialsItem,
			new FlasksItem,
			new AlchemyBottleItem,
			new MortarAndPestleItem,
			new BagItem,
			new ChestItem,
			new BasketItem,
			new BarrelItem,
			new StorageBottleItem,
			new MapOrScrollCaseItem,
			new ArrowsItem,
			new CrossbowBoltsItem,
			new SlingBulletsItem,
			new ThievesToolsItem,
			new ArtisansToolsItem,
			new HerbalismKitItem,
			new MusicalInstrumentItem,
			new DiceOrCardsItem,
			new TravelerPackItem,
			new DungeoneerPackItem,
			new DiplomatPackItem,
			new PriestPackItem,
			new HolySymbolItem,
			new WoodenShieldItem,
			new DruidicFocusItem,
			new ArcaneFocusItem,
			new SpellbookItem,
			new CloakItem,
			new NoArmorItem,
			new PaperAndQuillItem,
			new InkAndQuillItem,
			new HerbsOrHerbalismKitItem,
		];
	}
}
