<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->viewBuilder()->setHelpers(['Html', 'Form', 'Number', 'Text']);
    }

    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        $this->set('currentUser', $this->request->getSession()->read('Auth'));
    }

    protected function getCurrentUserId(): ?int
    {
        $userId = $this->request->getSession()->read('Auth.id');

        return $userId === null ? null : (int)$userId;
    }

    protected function requireLogin(): ?int
    {
        $userId = $this->getCurrentUserId();

        if ($userId === null) {
            $this->Flash->error('Tu dois être connecté pour accéder à cette page.');
            $this->redirect(['controller' => 'Users', 'action' => 'login'])->send();

            return null;
        }

        return $userId;
    }

    protected function ensurePlayerInGame(object $game): void
    {
        $currentUserId = $this->getCurrentUserId();
        $playerIds = [];

        foreach ($game->users_ingames ?? [] as $playerLink) {
            $playerIds[] = (int)$playerLink->user_id;
        }

        if ($currentUserId === null || !in_array($currentUserId, $playerIds, true)) {
            throw new ForbiddenException('Tu ne fais pas partie de cette partie.');
        }
    }
}
