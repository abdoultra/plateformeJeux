<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;

class GamesController extends AppController
{
    public function index()
    {
        $boardGames = $this->fetchTable('BoardGames')->find()
            ->orderByAsc('name')
            ->all();

        $games = $this->fetchTable('Games')->find()
            ->contain(['BoardGames', 'UsersIngames.Users'])
            ->orderByDesc('Games.created')
            ->all();

        $this->set(compact('boardGames', 'games'));
    }

    public function add($boardGameId = null)
    {
        $userId = $this->requireLogin();
        if ($userId === null) {
            return null;
        }

        $resolvedBoardGameId = null;

        if ($boardGameId !== null && (string)$boardGameId !== '') {
            $resolvedBoardGameId = (int)$boardGameId;
        }

        if ($resolvedBoardGameId === null) {
            $queryBoardGameId = $this->request->getQuery('board_game_id');
            if ($queryBoardGameId !== null && (string)$queryBoardGameId !== '') {
                $resolvedBoardGameId = (int)$queryBoardGameId;
            }
        }

        if ($resolvedBoardGameId === null) {
            $routeBoardGameId = $this->request->getParam('boardGameId');
            if ($routeBoardGameId !== null && (string)$routeBoardGameId !== '') {
                $resolvedBoardGameId = (int)$routeBoardGameId;
            }
        }

        if ($resolvedBoardGameId === null) {
            $pass = (array)$this->request->getParam('pass', []);
            if (!empty($pass[0]) && (string)$pass[0] !== '') {
                $resolvedBoardGameId = (int)$pass[0];
            }
        }

        if ($resolvedBoardGameId === null) {
            $url = trim((string)$this->request->getRequestTarget(), '/');
            if (preg_match('#(?:games/add|jeux/creer)/(\\d+)#', $url, $matches) === 1) {
                $resolvedBoardGameId = (int)$matches[1];
            }
        }

        if ($resolvedBoardGameId === null || $resolvedBoardGameId <= 0) {
            $this->Flash->error('Jeu introuvable.');

            return $this->redirect(['action' => 'index']);
        }

        $boardGamesTable = $this->fetchTable('BoardGames');
        $gamesTable = $this->fetchTable('Games');
        $usersIngamesTable = $this->fetchTable('UsersIngames');
        $boardGame = $boardGamesTable->get($resolvedBoardGameId);

        $game = $gamesTable->newEntity([
            'board_game_id' => $boardGame->id,
            'status' => $boardGame->type === 'solo' ? 'in_progress' : 'waiting',
        ]);

        if (!$gamesTable->save($game)) {
            $this->Flash->error('Impossible de créer la partie.');

            return $this->redirect(['action' => 'index']);
        }

        $playerLink = $usersIngamesTable->newEntity([
            'user_id' => $userId,
            'game_id' => $game->id,
            'nom' => $this->request->getSession()->read('Auth.username'),
            'score_final' => 0,
        ]);
        $usersIngamesTable->saveOrFail($playerLink);

        if ($boardGame->name === 'Mastermind') {
            $this->createMastermindSettings($game->id);
        }

        if ($boardGame->name === 'Filler') {
            $this->createFillerSettings($game->id);
        }

        if ($boardGame->name === 'Labyrinthe') {
            $this->createLabyrinthSettings($game->id);
        }

        $this->Flash->success('La partie a bien été créée.');

        return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
    }

    public function join(int $id)
    {
        $userId = $this->requireLogin();
        if ($userId === null) {
            return null;
        }

        $gamesTable = $this->fetchTable('Games');
        $usersIngamesTable = $this->fetchTable('UsersIngames');
        $game = $gamesTable->get($id, contain: ['BoardGames', 'UsersIngames']);

        if ($game->board_game->type !== 'multiplayer') {
            $this->Flash->error('Cette partie ne peut pas être rejointe.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $id]);
        }

        foreach ($game->users_ingames as $link) {
            if ((int)$link->user_id === $userId) {
                $this->Flash->info('Tu fais déjà partie de cette partie.');

                return $this->redirect(['controller' => 'Games', 'action' => 'view', $id]);
            }
        }

        if (count($game->users_ingames) >= 2) {
            $this->Flash->error('La partie a déjà deux joueurs.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $id]);
        }

        $playerLink = $usersIngamesTable->newEntity([
            'user_id' => $userId,
            'game_id' => $game->id,
            'nom' => $this->request->getSession()->read('Auth.username'),
            'score_final' => 0,
        ]);
        $usersIngamesTable->saveOrFail($playerLink);

        $game->status = 'in_progress';
        $gamesTable->saveOrFail($game);

        $this->Flash->success('Tu as rejoint la partie.');

        return $this->redirect(['controller' => 'Games', 'action' => 'view', $id]);
    }

    public function view(int $id)
    {
        $gamesTable = $this->fetchTable('Games');
        $game = $gamesTable->get($id, contain: [
            'BoardGames',
            'UsersIngames.Users',
            'MastermindSettings',
            'FillerSettings',
            'LabyrinthSettings',
        ]);

        if (!$this->ensurePlayerInGame($game)) {
            $this->Flash->error('Tu ne fais pas partie de cette partie.');

            return $this->redirect(['controller' => 'Games', 'action' => 'index']);
        }

        if ($this->request->is('post') && $game->board_game->name === 'Mastermind') {
            return $this->playMastermind($game);
        }

        if ($this->request->is('post') && $game->board_game->name === 'Filler') {
            return $this->playFiller($game);
        }

        if ($this->request->is('post') && $game->board_game->name === 'Labyrinthe') {
            return $this->playLabyrinth($game);
        }

        $decodedSteps = [];
        if (!empty($game->mastermind_setting?->steps)) {
            $decodedSteps = json_decode((string)$game->mastermind_setting->steps, true) ?: [];
        }

        $fillerGrid = null;
        if (!empty($game->filler_setting?->grid)) {
            $fillerGrid = json_decode((string)$game->filler_setting->grid, true);
        }

        $fillerState = null;
        if ($fillerGrid !== null) {
            $fillerState = $this->buildFillerState($game, $fillerGrid);
        }

        $labyrinthMap = null;
        if (!empty($game->labyrinth_setting?->map)) {
            $labyrinthMap = explode("\n", trim((string)$game->labyrinth_setting->map));
        }

        $labyrinthState = null;
        if ($game->labyrinth_setting !== null && $labyrinthMap !== null) {
            $labyrinthState = $this->buildLabyrinthState($game, $labyrinthMap);
        }

        $this->set(compact('game', 'decodedSteps', 'fillerGrid', 'fillerState', 'labyrinthMap', 'labyrinthState'));
    }

    protected function playMastermind(object $game)
    {
        $mastermindSettingsTable = $this->fetchTable('MastermindSettings');
        $usersIngamesTable = $this->fetchTable('UsersIngames');
        $gamesTable = $this->fetchTable('Games');

        $guess = strtoupper(trim((string)$this->request->getData('guess')));
        $allowedColors = ['R', 'B', 'J', 'V', 'O', 'P'];

        if (strlen($guess) !== 4) {
            $this->Flash->error('Entre une combinaison de 4 lettres.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        foreach (str_split($guess) as $letter) {
            if (!in_array($letter, $allowedColors, true)) {
                $this->Flash->error('Les lettres autorisées sont R, B, J, V, O et P.');

                return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
            }
        }

        $settings = $game->mastermind_setting;
        $steps = json_decode((string)$settings->steps, true) ?: [];
        $feedback = $this->buildMastermindFeedback($guess, (string)$settings->combinaison);
        $steps[] = [
            'guess' => $guess,
            'wellPlaced' => $feedback['wellPlaced'],
            'present' => $feedback['present'],
            'played_at' => FrozenTime::now()->i18nFormat('yyyy-MM-dd HH:mm:ss'),
        ];

        $settings->steps = json_encode($steps, JSON_UNESCAPED_UNICODE);
        $mastermindSettingsTable->saveOrFail($settings);

        if ($guess === $settings->combinaison) {
            $game->status = 'finished';
            $gamesTable->saveOrFail($game);

            $playerLink = $usersIngamesTable->find()
                ->where([
                    'game_id' => $game->id,
                    'user_id' => $this->getCurrentUserId(),
                ])
                ->firstOrFail();

            $playerLink->score_final = count($steps);
            $usersIngamesTable->saveOrFail($playerLink);

            $this->Flash->success('Bravo, tu as trouvé la bonne combinaison.');
        } else {
            $this->Flash->success('Essai enregistré.');
        }

        return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
    }

    protected function buildMastermindFeedback(string $guess, string $combination): array
    {
        $guessLetters = str_split($guess);
        $combinationLetters = str_split($combination);
        $wellPlaced = 0;
        $present = 0;
        $remainingGuess = [];
        $remainingCombination = [];

        foreach ($guessLetters as $index => $letter) {
            if ($letter === $combinationLetters[$index]) {
                $wellPlaced++;
            } else {
                $remainingGuess[] = $letter;
                $remainingCombination[] = $combinationLetters[$index];
            }
        }

        foreach ($remainingGuess as $letter) {
            $position = array_search($letter, $remainingCombination, true);
            if ($position !== false) {
                $present++;
                unset($remainingCombination[$position]);
            }
        }

        return [
            'wellPlaced' => $wellPlaced,
            'present' => $present,
        ];
    }

    protected function createMastermindSettings(int $gameId): void
    {
        $colors = ['R', 'B', 'J', 'V', 'O', 'P'];
        shuffle($colors);

        $settings = $this->fetchTable('MastermindSettings')->newEntity([
            'game_id' => $gameId,
            'combinaison' => implode('', array_slice($colors, 0, 4)),
            'steps' => json_encode([], JSON_UNESCAPED_UNICODE),
        ]);

        $this->fetchTable('MastermindSettings')->saveOrFail($settings);
    }

    protected function createFillerSettings(int $gameId): void
    {
        $colors = ['R', 'B', 'J', 'V', 'O', 'P'];
        $grid = [];

        for ($row = 0; $row < 7; $row++) {
            $currentRow = [];
            for ($column = 0; $column < 7; $column++) {
                $currentRow[] = $colors[array_rand($colors)];
            }
            $grid[] = $currentRow;
        }

        while ($grid[0][0] === $grid[6][6]) {
            $grid[6][6] = $colors[array_rand($colors)];
        }

        $settings = $this->fetchTable('FillerSettings')->newEntity([
            'game_id' => $gameId,
            'grid' => json_encode($grid, JSON_UNESCAPED_UNICODE),
            'current_player' => 1,
        ]);

        $this->fetchTable('FillerSettings')->saveOrFail($settings);
    }

    protected function playFiller(object $game)
    {
        $usersIngames = is_array($game->users_ingames) ? $game->users_ingames : $game->users_ingames->toList();
        usort($usersIngames, fn ($left, $right) => $left->id <=> $right->id);

        if (count($usersIngames) < 2) {
            $this->Flash->error('Il faut deux joueurs pour commencer une partie de Filler.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        $currentUserId = $this->getCurrentUserId();
        $currentPlayerNumber = (int)$game->filler_setting->current_player;
        $expectedUserId = (int)$usersIngames[$currentPlayerNumber - 1]->user_id;

        if ($currentUserId !== $expectedUserId) {
            $this->Flash->error('Ce n’est pas encore ton tour.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        $chosenColor = strtoupper(trim((string)$this->request->getData('color')));
        $fillerGrid = json_decode((string)$game->filler_setting->grid, true) ?: [];
        $state = $this->buildFillerState($game, $fillerGrid);

        if (!in_array($chosenColor, $state['availableColors'], true)) {
            $this->Flash->error('Cette couleur n’est pas autorisée pour ce tour.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        $activePlayerKey = $currentPlayerNumber === 1 ? 'player1' : 'player2';
        $territory = $state[$activePlayerKey]['territory'];

        foreach ($territory as [$x, $y]) {
            $fillerGrid[$y][$x] = $chosenColor;
        }

        $updatedState = $this->buildFillerState($game, $fillerGrid);
        $nextPlayer = $currentPlayerNumber === 1 ? 2 : 1;

        $fillerSettingsTable = $this->fetchTable('FillerSettings');
        $usersIngamesTable = $this->fetchTable('UsersIngames');
        $gamesTable = $this->fetchTable('Games');

        $game->filler_setting->grid = json_encode($fillerGrid, JSON_UNESCAPED_UNICODE);
        $game->filler_setting->current_player = $nextPlayer;
        $fillerSettingsTable->saveOrFail($game->filler_setting);

        if ($this->isFillerFinished($updatedState)) {
            $game->status = 'finished';
            $gamesTable->saveOrFail($game);

            $usersIngames[0]->score_final = count($updatedState['player1']['territory']);
            $usersIngames[1]->score_final = count($updatedState['player2']['territory']);
            $usersIngamesTable->saveOrFail($usersIngames[0]);
            $usersIngamesTable->saveOrFail($usersIngames[1]);

            $this->Flash->success('La partie de Filler est terminée.');
        } else {
            $this->Flash->success('Le coup a été joué.');
        }

        return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
    }

    protected function buildFillerState(object $game, array $grid): array
    {
        $height = count($grid);
        $width = $height > 0 ? count($grid[0]) : 0;
        $player1Start = [0, 0];
        $player2Start = [$width - 1, $height - 1];

        $player1Color = $grid[$player1Start[1]][$player1Start[0]];
        $player2Color = $grid[$player2Start[1]][$player2Start[0]];

        $player1Territory = $this->collectFillerTerritory($grid, $player1Start, $player1Color);
        $player2Territory = $this->collectFillerTerritory($grid, $player2Start, $player2Color);

        $territoryMap = [];
        foreach ($player1Territory as [$x, $y]) {
            $territoryMap[$y . '-' . $x] = 1;
        }
        foreach ($player2Territory as [$x, $y]) {
            $territoryMap[$y . '-' . $x] = 2;
        }

        $allColors = ['R', 'B', 'J', 'V', 'O', 'P'];
        $forbiddenColors = [$player1Color, $player2Color];
        $availableColors = array_values(array_filter(
            $allColors,
            fn (string $color) => !in_array($color, $forbiddenColors, true)
        ));

        return [
            'grid' => $grid,
            'player1' => [
                'color' => $player1Color,
                'territory' => $player1Territory,
                'score' => count($player1Territory),
            ],
            'player2' => [
                'color' => $player2Color,
                'territory' => $player2Territory,
                'score' => count($player2Territory),
            ],
            'territoryMap' => $territoryMap,
            'availableColors' => $availableColors,
        ];
    }

    protected function collectFillerTerritory(array $grid, array $start, string $color): array
    {
        $height = count($grid);
        $width = $height > 0 ? count($grid[0]) : 0;
        $queue = [$start];
        $seen = [];
        $territory = [];

        while (!empty($queue)) {
            [$x, $y] = array_shift($queue);
            $key = $y . '-' . $x;

            if (isset($seen[$key])) {
                continue;
            }

            if ($x < 0 || $y < 0 || $x >= $width || $y >= $height) {
                continue;
            }

            if ($grid[$y][$x] !== $color) {
                continue;
            }

            $seen[$key] = true;
            $territory[] = [$x, $y];

            $queue[] = [$x + 1, $y];
            $queue[] = [$x - 1, $y];
            $queue[] = [$x, $y + 1];
            $queue[] = [$x, $y - 1];
        }

        return $territory;
    }

    protected function isFillerFinished(array $state): bool
    {
        $grid = $state['grid'];
        $height = count($grid);
        $width = $height > 0 ? count($grid[0]) : 0;
        $occupiedCells = count($state['player1']['territory']) + count($state['player2']['territory']);
        $totalCells = $width * $height;

        if ($occupiedCells >= $totalCells) {
            return true;
        }

        $player1CanExpand = $this->canFillerPlayerExpand(
            $grid,
            [0, 0],
            $state['player1']['territory'],
            $state['availableColors']
        );
        $player2CanExpand = $this->canFillerPlayerExpand(
            $grid,
            [$width - 1, $height - 1],
            $state['player2']['territory'],
            $state['availableColors']
        );

        return !$player1CanExpand && !$player2CanExpand;
    }

    protected function canFillerPlayerExpand(array $grid, array $start, array $territory, array $availableColors): bool
    {
        $currentSize = count($territory);

        foreach ($availableColors as $color) {
            $testGrid = $grid;

            foreach ($territory as [$x, $y]) {
                $testGrid[$y][$x] = $color;
            }

            $newTerritory = $this->collectFillerTerritory($testGrid, $start, $color);
            if (count($newTerritory) > $currentSize) {
                return true;
            }
        }

        return false;
    }

    protected function createLabyrinthSettings(int $gameId): void
    {
        $mapFile = RESOURCES . 'maps' . DS . 'labyrinth_default.txt';
        $lines = file($mapFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $walkableCells = [];

        foreach ($lines as $y => $line) {
            foreach (str_split($line) as $x => $cell) {
                if ($cell === '.') {
                    $walkableCells[] = [$x, $y];
                }
            }
        }

        $availableTreasureCells = array_values(array_filter($walkableCells, function (array $cell): bool {
            return !($cell[0] === 0 && $cell[1] === 0) && !($cell[0] === 1 && $cell[1] === 0);
        }));

        $treasure = $availableTreasureCells[array_rand($availableTreasureCells)];

        $settings = $this->fetchTable('LabyrinthSettings')->newEntity([
            'game_id' => $gameId,
            'map' => implode("\n", $lines),
            'treasure_x' => $treasure[0],
            'treasure_y' => $treasure[1],
            'pos_p1_x' => 0,
            'pos_p1_y' => 0,
            'pos_p2_x' => 1,
            'pos_p2_y' => 0,
            'pa_p1' => 5,
            'pa_p2' => 5,
        ]);

        $this->fetchTable('LabyrinthSettings')->saveOrFail($settings);
    }

    protected function playLabyrinth(object $game)
    {
        $direction = strtoupper(trim((string)$this->request->getData('direction')));
        $directions = [
            'UP' => [0, -1],
            'DOWN' => [0, 1],
            'LEFT' => [-1, 0],
            'RIGHT' => [1, 0],
        ];

        if (!isset($directions[$direction])) {
            $this->Flash->error('Direction invalide.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        $playerSlot = $this->getLabyrinthPlayerSlot($game);
        if ($playerSlot === null) {
            $this->Flash->error('Tu ne fais pas partie de cette partie.');

            return $this->redirect(['controller' => 'Games', 'action' => 'index']);
        }

        $labyrinthSettingsTable = $this->fetchTable('LabyrinthSettings');
        $usersIngamesTable = $this->fetchTable('UsersIngames');
        $gamesTable = $this->fetchTable('Games');

        $settings = $game->labyrinth_setting;
        $currentPaField = $playerSlot === 1 ? 'pa_p1' : 'pa_p2';
        $currentXField = $playerSlot === 1 ? 'pos_p1_x' : 'pos_p2_x';
        $currentYField = $playerSlot === 1 ? 'pos_p1_y' : 'pos_p2_y';

        if ((int)$settings->{$currentPaField} <= 0) {
            $this->Flash->error('Tu n’as plus de PA. Lance la recharge ou attends la prochaine minute.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        $mapLines = explode("\n", trim((string)$settings->map));
        $delta = $directions[$direction];
        $newX = (int)$settings->{$currentXField} + $delta[0];
        $newY = (int)$settings->{$currentYField} + $delta[1];

        if (!$this->isWalkableLabyrinthCell($mapLines, $newX, $newY)) {
            $this->Flash->error('Tu ne peux pas aller sur cette case.');

            return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
        }

        $settings->{$currentXField} = $newX;
        $settings->{$currentYField} = $newY;
        $settings->{$currentPaField} = (int)$settings->{$currentPaField} - 1;
        $labyrinthSettingsTable->saveOrFail($settings);

        if ($newX === (int)$settings->treasure_x && $newY === (int)$settings->treasure_y) {
            $game->status = 'finished';
            $gamesTable->saveOrFail($game);

            $links = is_array($game->users_ingames) ? $game->users_ingames : $game->users_ingames->toList();
            usort($links, fn ($left, $right) => $left->id <=> $right->id);
            foreach ($links as $index => $link) {
                $link->score_final = ($index + 1) === $playerSlot ? 1 : 0;
                $usersIngamesTable->saveOrFail($link);
            }

            $this->Flash->success('Bravo, tu as trouvé le trésor.');
        } else {
            $this->Flash->success('Déplacement effectué.');
        }

        return $this->redirect(['controller' => 'Games', 'action' => 'view', $game->id]);
    }

    protected function getLabyrinthPlayerSlot(object $game): ?int
    {
        $currentUserId = $this->getCurrentUserId();
        $links = is_array($game->users_ingames) ? $game->users_ingames : $game->users_ingames->toList();
        usort($links, fn ($left, $right) => $left->id <=> $right->id);

        foreach ($links as $index => $link) {
            if ((int)$link->user_id === $currentUserId) {
                return $index + 1;
            }
        }

        return null;
    }

    protected function isWalkableLabyrinthCell(array $mapLines, int $x, int $y): bool
    {
        if ($y < 0 || $y >= count($mapLines)) {
            return false;
        }

        $line = $mapLines[$y];
        if ($x < 0 || $x >= strlen($line)) {
            return false;
        }

        return $line[$x] === '.';
    }

    protected function buildLabyrinthState(object $game, array $mapLines): array
    {
        $playerSlot = $this->getLabyrinthPlayerSlot($game);
        $settings = $game->labyrinth_setting;
        $cells = [];

        foreach ($mapLines as $y => $line) {
            $row = [];
            foreach (str_split($line) as $x => $cell) {
                $type = $cell === '#' ? 'wall' : 'path';
                $label = '';

                if ($x === (int)$settings->pos_p1_x && $y === (int)$settings->pos_p1_y) {
                    $type = 'player1';
                    $label = 'J1';
                } elseif ($x === (int)$settings->pos_p2_x && $y === (int)$settings->pos_p2_y) {
                    $type = 'player2';
                    $label = 'J2';
                } elseif (
                    $game->status === 'finished' &&
                    $x === (int)$settings->treasure_x &&
                    $y === (int)$settings->treasure_y
                ) {
                    $type = 'treasure';
                    $label = 'T';
                }

                $row[] = [
                    'type' => $type,
                    'label' => $label,
                ];
            }
            $cells[] = $row;
        }

        $availableDirections = [];
        if ($playerSlot !== null && $game->status !== 'finished') {
            $currentX = $playerSlot === 1 ? (int)$settings->pos_p1_x : (int)$settings->pos_p2_x;
            $currentY = $playerSlot === 1 ? (int)$settings->pos_p1_y : (int)$settings->pos_p2_y;
            $currentPa = $playerSlot === 1 ? (int)$settings->pa_p1 : (int)$settings->pa_p2;

            if ($currentPa > 0) {
                $checks = [
                    'UP' => [0, -1],
                    'DOWN' => [0, 1],
                    'LEFT' => [-1, 0],
                    'RIGHT' => [1, 0],
                ];

                foreach ($checks as $direction => [$deltaX, $deltaY]) {
                    if ($this->isWalkableLabyrinthCell($mapLines, $currentX + $deltaX, $currentY + $deltaY)) {
                        $availableDirections[] = $direction;
                    }
                }
            }
        }

        return [
            'playerSlot' => $playerSlot,
            'cells' => $cells,
            'player1' => [
                'x' => (int)$settings->pos_p1_x,
                'y' => (int)$settings->pos_p1_y,
                'pa' => (int)$settings->pa_p1,
            ],
            'player2' => [
                'x' => (int)$settings->pos_p2_x,
                'y' => (int)$settings->pos_p2_y,
                'pa' => (int)$settings->pa_p2,
            ],
            'availableDirections' => $availableDirections,
        ];
    }
}
