<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;

class RechargePaCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $gamesTable = TableRegistry::getTableLocator()->get('Games');
        $labyrinthSettingsTable = TableRegistry::getTableLocator()->get('LabyrinthSettings');

        $labyrinthGames = $gamesTable->find()
            ->contain(['BoardGames', 'UsersIngames', 'LabyrinthSettings'])
            ->where([
                'BoardGames.name' => 'Labyrinthe',
                'Games.status IN' => ['waiting', 'in_progress'],
            ])
            ->all();

        foreach ($labyrinthGames as $game) {
            if ($game->labyrinth_setting === null) {
                continue;
            }

            $playerCount = count($game->users_ingames);

            if ($playerCount >= 1) {
                $game->labyrinth_setting->pa_p1 = min(15, (int)$game->labyrinth_setting->pa_p1 + 5);
            }

            if ($playerCount >= 2) {
                $game->labyrinth_setting->pa_p2 = min(15, (int)$game->labyrinth_setting->pa_p2 + 5);
            }

            $labyrinthSettingsTable->saveOrFail($game->labyrinth_setting);
        }

        $io->success('Recharge des PA terminée.');

        return static::CODE_SUCCESS;
    }
}
