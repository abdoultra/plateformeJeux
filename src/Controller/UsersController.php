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
}
