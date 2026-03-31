<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность класса персонажа, реализуемая конкретными классами.
 */
abstract class AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    abstract public function getCode(): string;

    /**
     * Возвращает название класса персонажа.
     */
    abstract public function getName(): string;

    /**
     * Возвращает описание класса персонажа.
     */
    abstract public function getDescription(): ?string;

    /**
     * Возвращает подклассы текущего класса персонажа.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [];
    }

    /**
     * Возвращает признак активности класса персонажа.
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * Возвращает только активные подклассы текущего класса персонажа.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getActiveSubclasses(): array
    {
        return array_values(array_filter(
            $this->getSubclasses(),
            static fn (AbstractCharacterSubclass $subclass): bool => $subclass->isActive(),
        ));
    }

    /**
     * Преобразует класс персонажа в ответ API.
     *
     * @return array{
     *     code:string,
     *     name:string,
     *     description:?string,
     *     isActive:bool,
     *     subclasses:list<array{code:string,name:string,description:?string,isActive:bool}>
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'isActive' => $this->isActive(),
            'subclasses' => array_map(
                static fn (AbstractCharacterSubclass $subclass): array => $subclass->toArray(),
                $this->getActiveSubclasses(),
            ),
        ];
    }
}
