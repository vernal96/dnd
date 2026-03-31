<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность расы, реализуемая конкретными классами.
 */
abstract class AbstractRace
{
    /**
     * Возвращает код расы.
     */
    abstract public function getCode(): string;

    /**
     * Возвращает название расы.
     */
    abstract public function getName(): string;

    /**
     * Возвращает описание расы.
     */
    abstract public function getDescription(): ?string;

    /**
     * Возвращает подрасы текущей расы.
     *
     * @return list<AbstractSubrace>
     */
    public function getSubraces(): array
    {
        return [];
    }

    /**
     * Возвращает признак активности расы.
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * Возвращает только активные подрасы текущей расы.
     *
     * @return list<AbstractSubrace>
     */
    public function getActiveSubraces(): array
    {
        return array_values(array_filter(
            $this->getSubraces(),
            static fn (AbstractSubrace $subrace): bool => $subrace->isActive(),
        ));
    }

    /**
     * Преобразует расу в ответ API.
     *
     * @return array{
     *     code:string,
     *     name:string,
     *     description:?string,
     *     isActive:bool,
     *     subraces:list<array{code:string,name:string,description:?string,isActive:bool}>
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'isActive' => $this->isActive(),
            'subraces' => array_map(
                static fn (AbstractSubrace $subrace): array => $subrace->toArray(),
                $this->getActiveSubraces(),
            ),
        ];
    }
}
