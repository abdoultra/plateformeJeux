<?php
/**
 * @var iterable<\Cake\Datasource\EntityInterface> $boardGames
 * @var iterable<\Cake\Datasource\EntityInterface> $games
 * @var array<string, mixed>|null $currentUser
 */
?>
<section class="section-heading">
    <div>
        <span class="eyebrow">Lancement</span>
        <h2>Creer une nouvelle partie</h2>
    </div>
    <p>Choisis un jeu, lance une partie et invite un autre joueur si le mode est multijoueur.</p>
</section>

<section class="panel games-launch-panel">
    <?php if (!$currentUser): ?>
        <p>Connecte-toi pour pouvoir creer ou rejoindre une partie.</p>
    <?php else: ?>
        <div class="card-grid game-showcase">
            <?php foreach ($boardGames as $boardGame): ?>
                <?php
                $descriptions = [
                    'Mastermind' => 'Deduction, essais successifs et historique de progression.',
                    'Filler' => 'Conquete de cases en duel avec tours et couleurs.',
                    'Labyrinthe' => 'Course au tresor avec gestion des deplacements et des PA.',
                ];
                $labels = [
                    'solo' => 'Solo',
                    'multiplayer' => 'Multijoueur',
                ];
                $cardClass = 'game-card game-card-' . strtolower((string)$boardGame->name);
                ?>
                <article class="card <?= h($cardClass) ?>">
                    <div class="game-card-top">
                        <span class="game-badge"><?= h($labels[$boardGame->type] ?? $boardGame->type) ?></span>
                        <h3><?= h($boardGame->name) ?></h3>
                    </div>
                    <p><?= h($descriptions[$boardGame->name] ?? 'Jeu disponible dans la plateforme.') ?></p>
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

<section class="section-heading">
    <div>
        <span class="eyebrow">Suivi</span>
        <h2>Liste des parties</h2>
    </div>
    <p>Retrouve rapidement les parties en attente, en cours ou deja terminees.</p>
</section>

<section class="panel recent-games-panel">
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
                <td><span class="table-id">#<?= h((string)$game->id) ?></span></td>
                <td><?= h($game->board_game->name) ?></td>
                <td>
                    <?php
                    $statusLabels = [
                        'waiting' => 'En attente',
                        'in_progress' => 'En cours',
                        'finished' => 'Terminee',
                    ];
                    $statusClass = 'status-pill status-' . strtolower((string)$game->status);
                    ?>
                    <span class="<?= h($statusClass) ?>"><?= h($statusLabels[$game->status] ?? $game->status) ?></span>
                </td>
                <td>
                    <?php
                    $players = [];
                    $isCurrentUserInGame = false;
                    foreach ($game->users_ingames as $link) {
                        $players[] = $link->user->username;
                        if ($currentUser && (int)$link->user_id === (int)$currentUser['id']) {
                            $isCurrentUserInGame = true;
                        }
                    }
                    echo h(implode(', ', $players));
                    ?>
                </td>
                <td>
                    <div class="table-actions">
                        <?php if ($isCurrentUserInGame): ?>
                            <?= $this->Html->link('Ouvrir', ['controller' => 'Games', 'action' => 'view', $game->id], ['class' => 'table-link']) ?>
                        <?php elseif (
                            $currentUser &&
                            $game->board_game->type === 'multiplayer' &&
                            count($game->users_ingames) < 2
                        ): ?>
                            <?= $this->Html->link('Rejoindre', ['controller' => 'Games', 'action' => 'join', $game->id], ['class' => 'table-link table-link-join']) ?>
                        <?php elseif (!$currentUser): ?>
                            <?= $this->Html->link('Connexion requise', ['controller' => 'Users', 'action' => 'login'], ['class' => 'table-link']) ?>
                        <?php else: ?>
                            <span class="table-muted">Non accessible</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
