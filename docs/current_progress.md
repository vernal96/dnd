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

### Перемещение

- ГМ может перемещать runtime-акторов.
- Игрок может перемещать только своего героя.
- Перемещения анимируются.
- Realtime переведен на delta-события с fallback на полный reload, если потеряна версия или пришла неполная дельта.

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

- у карточек героев игроков на поле добавлено отдельное визуальное выделение по сравнению с NPC

Файлы:

- `apps/frontend/src/pages/GmSceneEditorPage.vue`
- `apps/frontend/src/pages/GmSceneRuntimePage.vue`
- `apps/frontend/src/pages/PlayerSceneRuntimePage.vue`

## С чего логично продолжать

Наиболее естественные следующие шаги:

1. Дальнейшая полировка runtime UX:
   - действия с предметами
   - передача предметов
   - использование предметов
2. Encounter / initiative / боевой цикл.
3. Нормализация SSR-поведения кабинетов и runtime-страниц, если снова появятся hydration-нюансы.
4. Дальнейшая унификация canvas-рендера между редактором и runtime.
