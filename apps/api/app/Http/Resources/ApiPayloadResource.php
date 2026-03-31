<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Универсальный API-ресурс для DTO, доменных объектов, массивов и Eloquent-моделей.
 */
final class ApiPayloadResource extends JsonResource
{
	/**
	 * Возвращает JSON-ответ для одного ресурса.
	 */
	public static function json(mixed $payload, int $status = JsonResponse::HTTP_OK): JsonResponse
	{
		return self::make($payload)
			->response()
			->setStatusCode($status);
	}

	/**
	 * Возвращает JSON-ответ для коллекции ресурсов.
	 */
	public static function collectionJson(mixed $payload, int $status = JsonResponse::HTTP_OK): JsonResponse
	{
		return self::collection($payload)
			->response()
			->setStatusCode($status);
	}

	/**
	 * Преобразует ресурс в массив ответа.
	 *
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (is_array($this->resource)) {
			return $this->resource;
		}

		if ($this->resource instanceof Arrayable) {
			/** @var array $payload */
			$payload = $this->resource->toArray();

			return $payload;
		}

		if ($this->resource instanceof JsonSerializable) {
			$payload = $this->resource->jsonSerialize();

			return is_array($payload) ? $payload : ['value' => $payload];
		}

		/** @var array $payload */
		$payload = parent::toArray($request);

		return $payload;
	}
}
