<?php
declare(strict_types=1);

namespace App\Controller;

class UsersController extends AppController
{
    public function register()
    {
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->newEmptyEntity();

        if ($this->request->is('post')) {
            $user = $usersTable->patchEntity($user, $this->request->getData());

            if ($usersTable->save($user)) {
                $this->request->getSession()->write('Auth', [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ]);

                $this->Flash->success('Ton compte a été créé avec succès.');

                return $this->redirect(['controller' => 'Home', 'action' => 'index']);
            }

            $this->Flash->error('Impossible de créer le compte. Vérifie les champs.');
        }

        $this->set(compact('user'));
    }

    public function login()
    {
        if ($this->getCurrentUserId() !== null) {
            return $this->redirect(['controller' => 'Home', 'action' => 'index']);
        }

        if ($this->request->is('post')) {
            $usersTable = $this->fetchTable('Users');
            $username = trim((string)$this->request->getData('username'));
            $password = (string)$this->request->getData('password');

            $user = $usersTable->find()
                ->where(['username' => $username])
                ->first();

            if ($user !== null && password_verify($password, (string)$user->password)) {
                $this->request->getSession()->write('Auth', [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ]);

                $this->Flash->success('Connexion réussie.');

                return $this->redirect(['controller' => 'Home', 'action' => 'index']);
            }

            $this->Flash->error('Nom d’utilisateur ou mot de passe incorrect.');
        }
    }

    public function logout()
    {
        $this->request->getSession()->delete('Auth');
        $this->Flash->success('Tu es maintenant déconnecté.');

        return $this->redirect(['controller' => 'Home', 'action' => 'index']);
    }

    public function profile()
    {
        $userId = $this->requireLogin();
        if ($userId === null) {
            return null;
        }

        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($userId, contain: [
            'UsersIngames.Games.BoardGames',
        ]);

        $this->set(compact('user'));
    }

    public function leaderboard()
    {
        $usersTable = $this->fetchTable('Users');
        $users = $usersTable->find()
            ->contain([
                'UsersIngames.Games.BoardGames',
            ])
            ->all();

        $leaderboard = [];

        foreach ($users as $user) {
            $stats = [
                'user' => $user,
                'games_played' => 0,
                'finished_games' => 0,
                'wins' => 0,
                'total_score' => 0,
                'favorite_game' => 'Aucun',
            ];

            $gameCounts = [];

            foreach ($user->users_ingames as $link) {
                $stats['games_played']++;
                $stats['total_score'] += (int)$link->score_final;

                if (($link->game->status ?? null) === 'finished') {
                    $stats['finished_games']++;
                }

                if ((int)$link->score_final > 0) {
                    $stats['wins']++;
                }

                $gameName = $link->game->board_game->name ?? 'Inconnu';
                $gameCounts[$gameName] = ($gameCounts[$gameName] ?? 0) + 1;
            }

            if ($gameCounts !== []) {
                arsort($gameCounts);
                $stats['favorite_game'] = (string)array_key_first($gameCounts);
            }

            $leaderboard[] = $stats;
        }

        usort($leaderboard, function (array $left, array $right): int {
            if ($left['wins'] !== $right['wins']) {
                return $right['wins'] <=> $left['wins'];
            }

            if ($left['total_score'] !== $right['total_score']) {
                return $right['total_score'] <=> $left['total_score'];
            }

            if ($left['finished_games'] !== $right['finished_games']) {
                return $right['finished_games'] <=> $left['finished_games'];
            }

            return strcmp((string)$left['user']->username, (string)$right['user']->username);
        });

        $this->set(compact('leaderboard'));
    }
}
