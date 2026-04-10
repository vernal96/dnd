# Текущее состояние проекта

Документ нужен как короткая точка возврата в контекст после паузы.

## Общая картина

- Проект работает через Docker Compose.
- Backend: Laravel API, server source of truth.
- Frontend: Vue + Vite + SSR.
- Realtime: отдельный websocket-сервис.
- Все игровые действия идут по схеме `intent -> backend validate/apply -> persist -> realtime`.

## Что уже реализовано

### 1. Кабинет ГМа и сцены

- В игре ГМ может создавать и удалять сцены.
- Есть fullscreen-редактор сцены на canvas.
- В редакторе есть:
  - изменение размеров поля
  - редактирование названия и описания
  - поверхности
  - authored-объекты
  - authored-NPC
  - authored-player spawn point
- Поле двигается мышью, поддерживается поворот камеры и zoom.

Основные файлы:

- `apps/frontend/src/pages/GmGamePage.vue`
- `apps/frontend/src/pages/GmSceneEditorPage.vue`
- `apps/api/app/Application/Game/SceneManagementService.php`
- `apps/api/app/Http/Controllers/Api/GameSceneController.php`

### 2. Runtime-сцена

- ГМ может запустить authored-сцену в runtime.
- После запуска создаются runtime actor instances.
- Есть отдельные runtime-страницы:
  - ГМ: `apps/frontend/src/pages/GmSceneRuntimePage.vue`
  - игрок: `apps/frontend/src/pages/PlayerSceneRuntimePage.vue`
- Игроки получают сигнал о запуске сцены по realtime.
- Если игрок был offline, активная игра видна в ЛК игрока, и туда можно зайти вручную.

Основные backend-файлы:

- `apps/api/app/Application/Game/RuntimeSceneManagementService.php`
- `apps/api/app/Application/Realtime/RealtimePublisher.php`
- `apps/api/app/Http/Controllers/Api/GmRuntimeSceneController.php`
- `apps/api/app/Http/Controllers/Api/PlayerRuntimeSceneController.php`

## Runtime: текущее поведение

### Производные показатели акторов

- Уровни персонажей ограничены диапазоном 1-20.
- Переход с 1 на 2 уровень требует 1000 опыта, каждый следующий переход требует еще на 1000 опыта больше.
- Базовые показатели актора:
  - здоровье: 5
  - скорость: 3 клетки
  - класс брони: 10
  - высота прыжка: 1 клетка
- Модификатор характеристики считается как `floor((характеристика - 10) / 2)`.
- Итоговые runtime-показатели считаются на backend:
  - здоровье: `5 + бонус + (уровень - 1) * 4 + модификатор телосложения * уровень + модификатор силы`
  - скорость: `clamp(3 + бонус + модификатор ловкости + floor(модификатор силы / 2), 2, 6)`
  - класс брони: `10 + бонус брони + clamp(модификатор ловкости, 0, 3)`
  - высота прыжка: `clamp(1 + floor(модификатор силы / 2) + max(0, floor(модификатор ловкости / 2)), 1, 4)`
  - бонус к урону: `clamp(floor(модификатор основной характеристики / 2) + floor((уровень - 1) / 4), 0, 4)`

Основные файлы:

- `apps/api/app/Domain/Actor/CharacterLevel.php`
- `apps/api/app/Application/Game/ActorCombatStatsService.php`
- `apps/api/database/migrations/2026_04_10_000100_add_base_combat_stats_to_actors.php`

### Перемещение

- ГМ может перемещать runtime-акторов.
- Игрок может перемещать только своего героя.
- Перемещения анимируются.
- Realtime переведен на delta-события с fallback на полный reload, если потеряна версия или пришла неполная дельта.

### Runtime-действия

- Добавлен первый server-side intent действия: `weapon_attack`.
- Добавлен первый saving throw intent: `trip_attack`.
- Endpoint игрока: `POST /api/player/games/{game}/runtime/actors/{actor}/actions`.
- Endpoint мастера: `POST /api/games/{game}/runtime/actors/{actor}/actions`.
- Payload: `action: "weapon_attack" | "trip_attack"`, `target_actor_id`, опционально `item_code`.
- Сервер выбирает указанное оружие, экипированное оружие или fallback `longsword`.
- Для ближнего оружия цель должна быть на соседней клетке.
- В каталоге оружия есть `longsword` для ближней атаки от Силы и `shortbow` для дальней атаки от Ловкости.
- Бросок атаки: `D20 + модификатор основной характеристики оружия + floor((level - 1) / 4)`.
- Попадание: natural 20 или результат не natural 1 и сумма не ниже `armor_class` цели.
- Урон: кубик оружия + server-side `damage_bonus`.
- `trip_attack` требует соседнюю цель, считает СЛ как `10 + модификатор Силы атакующего + floor((level - 1) / 4)`.
- Цель делает спасбросок Силы `D20 + модификатор Силы`; при провале получает `prone` на 1 ход или 10 секунд вне боя.
- В encounter атака требует текущего хода и доступного основного действия, после атаки основное действие списывается.
- Результат атаки добавляется в `runtime_state.action_log` активной сцены и публикуется через realtime обновление сцены.
- На runtime-страницах игрока и мастера добавлена лента «Журнал действий» с броском, попаданием/промахом и уроном.

Основные файлы:

- `apps/api/app/Http/Requests/Game/RuntimeActorActionRequest.php`
- `apps/api/app/Application/Game/RuntimeSceneManagementService.php`
- `apps/api/tests/Feature/GmRuntimeSceneControllerTest.php`
- `apps/api/app/Domain/Actor/Items/ShortbowItem.php`
- `apps/frontend/src/components/runtime/RuntimeActionLog.vue`
- `apps/frontend/src/services/runtimeSceneApi.ts`
- `apps/frontend/src/pages/GmSceneRuntimePage.vue`
- `apps/frontend/src/pages/PlayerSceneRuntimePage.vue`

### Эффекты поверхностей

- Runtime-акторы могут иметь временные эффекты в `temporary_effects`.
- Сейчас заведены отрицательные эффекты:
  - `prone` / «Упал»: актор не может двигаться, в бою пропускает ход
  - `burning` / «Горение»: на актора действует стихия огня
  - `poisoned` / «Отравление»: на актора действует стихия яда
  - `slowed` / «Замедление»: скорость перемещения уменьшается на 1
- Поверхности задают правила наложения эффектов через `effectRules()`:
  - лед: `D20 < 5` накладывает «Упал»
  - огонь: `D20 < 21` накладывает «Горение»
  - яд: `D20 < 21` накладывает «Отравление»
  - земля: `D20 < 21` накладывает «Замедление»
- Длительность текущих surface-эффектов: 1 ход в бою или 10 секунд вне боя.
- Иконки активных эффектов выводятся над карточками акторов на runtime canvas и над портретом героя в HUD игрока.

Основные файлы:

- `apps/api/app/Domain/Actor/ActorEffect.php`
- `apps/api/app/Data/Game/SurfaceEffectRuleData.php`
- `apps/api/app/Application/Game/SurfaceEffectService.php`
- `apps/api/app/Domain/Scene/Surfaces/*SceneSurface.php`
- `apps/frontend/src/components/runtime/PlayerRuntimeToolbar.vue`
- `apps/frontend/src/pages/GmSceneRuntimePage.vue`
- `apps/frontend/src/pages/PlayerSceneRuntimePage.vue`

### Добавление сущностей в runtime

Во время игры ГМ может:

- добавлять NPC
- добавлять героев игроков
- менять поверхность клетки
- дропать предметы

### Инвентарь

- По ПКМ по актеру открывается контекстное меню.
- У всех есть пункт `Закрыть`.
- У ГМа есть пункт `Инвентарь` для любого актора.
- У игрока `Инвентарь` доступен только для своего героя.
- Инвентарь открывается в модалке.
- Предметы отображаются сеткой, при наведении показываются название и описание.

Основные файлы:

- `apps/frontend/src/components/runtime/RuntimeActorInventoryModal.vue`
- `apps/frontend/src/pages/GmSceneRuntimePage.vue`
- `apps/frontend/src/pages/PlayerSceneRuntimePage.vue`
- `apps/frontend/src/types/runtimeScene.ts`

## Визуальный слой canvas

### Поверхности

- Поверхности рендерятся текстурами с backend.
- Источник картинок: `storage/app/game-images/support/surfaces`
- Для каждой поверхности backend отдает `image_url`.

Основные файлы:

- `apps/api/app/Support/SceneCatalog/Surfaces/*`
- `apps/api/app/Application/SceneCatalog/SceneSurfaceImageStorageService.php`
- `apps/api/app/Http/Controllers/Api/SceneSurfaceImageController.php`

### Объекты

- Authored-объекты сцены сейчас кодовые.
- Основные примеры: `bush`, `barrel`.
- Картинки объектов берутся с backend.
- Источник картинок: `storage/app/game-images/support/objects`

Основные файлы:

- `apps/api/app/Support/SceneCatalog/Objects/*`
- `apps/api/app/Application/SceneCatalog/SceneObjectImageStorageService.php`
- `apps/api/app/Http/Controllers/Api/SceneObjectImageController.php`

### Предметы

- Отдельная библиотека загрузки изображений предметов удалена.
- Предметы переведены на ту же схему, что поверхности и объекты.
- Теперь картинка предмета определяется кодовым классом через `image()`.
- Источник картинок: `storage/app/game-images/support/items`

Основные файлы:

- `apps/api/app/Domain/Catalog/Item.php`
- `apps/api/app/Application/Catalog/ItemCatalog.php`
- `apps/api/app/Application/Catalog/ItemCatalogImageStorageService.php`
- `apps/api/app/Http/Controllers/Api/ItemCatalogImageController.php`
- `apps/api/app/Http/Controllers/Api/ItemController.php`

## Актеры

### NPC

- NPC хранятся не в игре, а в общей библиотеке ГМа.
- Их можно переиспользовать между играми.
- Создание NPC идет через wizard, похожий на создание player character.

Основные файлы:

- `apps/api/app/Models/Actor.php`
- `apps/api/app/Application/Game/ActorManagementService.php`
- `apps/frontend/src/components/gm/GmNpcManagerModal.vue`

### Герои игроков

- Игрок в ЛК видит только своих персонажей.
- Создание персонажа идет wizard-процессом:
  - раса/подраса
  - класс
  - распределение характеристик
  - имя, описание, фото
- Учитываются расовые и классовые бонусы характеристик.
- Основные характеристики класса подсвечиваются.

Основные файлы:

- `apps/frontend/src/components/player/PlayerCharacterCreateModal.vue`
- `apps/frontend/src/pages/PlayerCabinetPage.vue`
- `apps/api/app/Application/Player/PlayerCharacterManagementService.php`

## Приглашения в игру

- Игрок принимает или отклоняет приглашение.
- При принятии выбирает героя.
- Можно выбрать только героя, который не занят в другой активной игре.

Основные файлы:

- `apps/frontend/src/components/player/PlayerInvitationAcceptModal.vue`
- `apps/api/app/Application/Game/GameInvitationService.php`
- `apps/api/app/Http/Controllers/Api/GameInvitationController.php`

## Сиды для локальной разработки

Есть demo-сиды:

- GM: `gm@tavern.local`
- Player: `player@tavern.local`
- пароль: `password`

Создаются:

- игра
- authored-сцена
- NPC
- player character

Основные файлы:

- `apps/api/database/seeders/LocalDevelopmentDemoSeeder.php`
- `apps/api/database/seeders/DatabaseSeeder.php`

## Важные технические нюансы

### Тесты

- Тесты нужно запускать только безопасным способом:
  - `docker compose exec -T api composer test`
  - или `docker compose exec -T api php artisan test --env=testing`
- Уже настроен временный sqlite test DB через `apps/api/scripts/test.sh`.

### Runtime refresh

- Был исправлен кейс, когда runtime-страница не открывалась после refresh.
- Backend теперь умеет восстанавливать `active_scene_state_id`, если pointer потерян, но реальная active runtime scene еще существует.
- На frontend runtime-страницы добавлен retry reconnect.

### SSR / hydration

- Для runtime-страниц введен client-only gating через `isClientReady`, чтобы не ловить hydration mismatch после F5.

## Где мы остановились

Последнее заметное изменение:

- начата server-side система runtime-действий и экипировки: `weapon_attack`, `trip_attack`, action log, runtime equipment slots и API смены экипировки

Файлы:

- `apps/api/app/Application/Game/RuntimeSceneManagementService.php`
- `apps/api/app/Application/Game/ActorEquipmentService.php`
- `apps/api/app/Domain/Actor/ActorEquipmentSlot.php`
- `apps/frontend/src/components/runtime/RuntimeActionPalette.vue`
- `apps/frontend/src/components/runtime/RuntimeActorInventoryModal.vue`
- `apps/frontend/src/components/runtime/PlayerRuntimeToolbar.vue`
- `apps/frontend/src/pages/GmSceneRuntimePage.vue`
- `apps/frontend/src/pages/PlayerSceneRuntimePage.vue`

## План экипировки и action palette

Цель: активные предметы не должны использоваться просто из инвентаря. Оружие, броня и будущие активные предметы должны применяться как явно экипированные предметы.

### Backend-контракт экипировки

- Источник истины для экипировки: `slot` у inventory item.
- `isEquipped` можно оставлять как совместимый флаг отображения, но правила должны опираться на `slot`.
- Ввести enum слотов экипировки:
  - `main_hand`
  - `off_hand`
  - `ranged`
  - `armor`
  - `accessory_1`
  - `accessory_2`
- Введен сервис экипировки, который:
  - возвращает экипированные предметы актора;
  - валидирует совместимость предмета и слота;
  - выбирает доступное оружие для атаки;
- `weapon_attack` принимает `equipment_slot` и использует оружие из этого слота.
- `item_code` оставлен только как переходная совместимость для текущих экранов и должен быть удален после action palette.
- Временный fallback `longsword` нужно убрать после подключения минимальной экипировки в seed/UI.

### Боевые показатели от экипировки

- `armor_class` должен учитывать экипированную броню.
- Если броня задает `armorClassBase`, она заменяет базовый AC.
- `armorClassBonus` складывается.
- `armorClassAbility` и cap ограничивают бонус характеристики к КД.
- При смене runtime-экипировки сервер пересчитывает `runtime_state.armor_class`; атаки читают AC цели из runtime state.
- Оружейная атака должна использовать только экипированное оружие из подходящего слота.

### API

- Минимальный runtime endpoint для смены экипировки:
  - GM: `POST /api/games/{game}/runtime/actors/{actor}/equipment`
  - Player: `POST /api/player/games/{game}/runtime/actors/{actor}/equipment`
- Payload:
  - `slot`
  - `item_code`, либо `null` для снятия предмета
- Сервер проверяет, что предмет есть в inventory, подходит слоту и принадлежит доступному актору.
- Реализованные слоты совместимости:
  - `main_hand`, `off_hand`: melee weapon
  - `ranged`: ranged weapon
  - `armor`: armor
  - `accessory_1`, `accessory_2`: equipment

### Frontend action palette

- При выборе атаки по цели открывается action palette из трех колонок:
  - экипированное оружие;
  - skills;
  - usable items.
- На первом этапе активны экипированное оружие и skill `trip_attack`; usable items показаны как следующий этап.
- Выбор оружия отправляет `weapon_attack` с `equipment_slot`.
- В HUD игрока экипированные слоты визуально отделены от обычной двухрядной сетки инвентаря.
- Runtime inventory modal показывает экипировку сверху и отправляет `/equipment` intent для экипировки/снятия предмета.

## С чего логично продолжать

Наиболее естественные следующие шаги:

1. Убрать fallback `longsword` и legacy `item_code` из `weapon_attack`.
2. Обеспечить стартовую экипировку в seed/spawn, чтобы актеры не появлялись без оружия в слотах.
3. Добавить следующие typed runtime-действия для usable items/skills.
4. Расширить экипировку бонусами аксессуаров, щитов и расходников.
5. Добавить отдельные тесты player endpoint экипировки.
