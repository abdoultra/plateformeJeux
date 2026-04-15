<?php
declare(strict_types=1);

namespace App\Controller;

class HomeController extends AppController
{
    public function index()
    {
        $boardGamesTable = $this->fetchTable('BoardGames');
        $gamesTable = $this->fetchTable('Games');

        $boardGames = $boardGamesTable->find()
            ->orderByAsc('name')
            ->all();

        $recentGames = $gamesTable->find()
            ->contain(['BoardGames', 'UsersIngames.Users'])
            ->orderByDesc('Games.created')
            ->limit(5)
            ->all();

        $this->set(compact('boardGames', 'recentGames'));
    }
}
