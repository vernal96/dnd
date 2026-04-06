<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Actor\AbstractRace;
use App\Domain\Actor\Races\DragonbornRace;
use App\Domain\Actor\Races\DwarfRace;
use App\Domain\Actor\Races\ElfRace;
use App\Domain\Actor\Races\GnomeRace;
use App\Domain\Actor\Races\HalfElfRace;
use App\Domain\Actor\Races\HalflingRace;
use App\Domain\Actor\Races\HalfOrcRace;
use App\Domain\Actor\Races\HumanRace;
use App\Domain\Actor\Races\TieflingRace;

/**
 * Хранит кодовый справочник рас и подрас.
 */
final class RaceCatalog
{
	/**
	 * Возвращает одну активную расу по коду.
	 */
	public function findActiveRaceByCode(string $code): ?AbstractRace
	{
		foreach ($this->getActiveRaces() as $race) {
			if ($race->getCode() === $code) {
				return $race;
			}
		}

		return null;
	}

	/**
	 * Возвращает одну доступную игроку активную расу по коду.
	 */
	public function findPlayerSelectableRaceByCode(string $code): ?AbstractRace
	{
		foreach ($this->getPlayerSelectableRaces() as $race) {
			if ($race->getCode() === $code) {
				return $race;
			}
		}

		return null;
	}

	/**
	 * Возвращает все активные расы справочника.
	 *
	 * @return list<AbstractRace>
	 */
	public function getActiveRaces(): array
	{
		return array_values(array_filter(
			$this->getAllRaces(),
			static fn(AbstractRace $race): bool => $race->isActive(),
		));
	}

	/**
	 * Возвращает активные расы, доступные для выбора игроком.
	 *
	 * @return list<AbstractRace>
	 */
	public function getPlayerSelectableRaces(): array
	{
		return $this->getActiveRaces();
	}

	/**
	 * Возвращает полный кодовый справочник рас.
	 *
	 * @return list<AbstractRace>
	 */
	private function getAllRaces(): array
	{
		return [
			new HumanRace,
			new ElfRace,
			new DwarfRace,
			new HalflingRace,
			new GnomeRace,
			new DragonbornRace,
			new HalfElfRace,
			new HalfOrcRace,
			new TieflingRace,
		];
	}
}
