<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Catalog\AbstractRace;
use App\Domain\Catalog\Races\DwarfRace;
use App\Domain\Catalog\Races\ElfRace;
use App\Domain\Catalog\Races\HumanRace;

/**
 * Хранит кодовый справочник рас и подрас.
 */
final class RaceCatalog
{
    /**
     * Возвращает все активные расы справочника.
     *
     * @return list<AbstractRace>
     */
    public function getActiveRaces(): array
    {
        return array_values(array_filter(
            $this->getAllRaces(),
            static fn (AbstractRace $race): bool => $race->isActive(),
        ));
    }

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
        ];
    }
}
