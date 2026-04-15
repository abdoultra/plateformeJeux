<?php
/**
 * @var iterable<\Cake\Datasource\EntityInterface> $boardGames
 * @var iterable<\Cake\Datasource\EntityInterface> $games
 * @var array<string, mixed>|null $currentUser
 */
?>
<section class="panel">
    <h2>Creer une partie</h2>
    <?php if (!$currentUser): ?>
        <p>Connecte-toi pour pouvoir creer ou rejoindre une partie.</p>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($boardGames as $boardGame): ?>
                <article class="card">
                    <h3><?= h($boardGame->name) ?></h3>
                    <p>Type : <?= h($boardGame->type) ?></p>
                    <?= $this->Html->link('Creer une partie', [
                        'controller' => 'Games',
                        'action' => 'add',
                        '?' => ['board_game_id' => $boardGame->id],
                    ], ['class' => 'button']) ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Liste des parties</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Jeu</th>
                <th>Statut</th>
                <th>Joueurs</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($games as $game): ?>
            <tr>
                <td><?= h((string)$game->id) ?></td>
                <td><?= h($game->board_game->name) ?></td>
                <td><?= h($game->status) ?></td>
                <td>
                    <?php
                    $players = [];
                    foreach ($game->users_ingames as $link) {
                        $players[] = $link->user->username;
                    }
                    echo h(implode(', ', $players));
                    ?>
                </td>
                <td>
                    <?= $this->Html->link('Ouvrir', ['controller' => 'Games', 'action' => 'view', $game->id]) ?>
                    <?php if (
                        $currentUser &&
                        $game->board_game->type === 'multiplayer' &&
                        count($game->users_ingames) < 2
                    ): ?>
                        |
                        <?= $this->Html->link('Rejoindre', ['controller' => 'Games', 'action' => 'join', $game->id]) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
