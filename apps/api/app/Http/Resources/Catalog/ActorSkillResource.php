<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Actor\AbstractSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует навык или классовую способность в JSON.
 *
 * @mixin AbstractSkill
 */
final class ActorSkillResource extends JsonResource
{
	/**
	 * @return array{code:string,name:string,description:string,rollDice:?string,targetType:?string,radiusCells:?int}
	 */
	public function toArray(Request $request): array
	{
		/** @var AbstractSkill $skill */
		$skill = $this->resource;

		return [
			'code' => $skill->getCode(),
			'name' => $skill->getName(),
			'description' => $skill->getDescription(),
			'rollDice' => $skill->getRollDice() !== null ? 'd' . $skill->getRollDice()?->value : null,
			'targetType' => $skill->getTargetType()?->value,
			'radiusCells' => $skill->getRadiusCells(),
		];
	}
}
