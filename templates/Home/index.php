<?php
/**
 * @var iterable<\Cake\Datasource\EntityInterface> $boardGames
 * @var iterable<\Cake\Datasource\EntityInterface> $recentGames
 * @var array<string, mixed>|null $currentUser
 */
?>
<section class="hero">
    <div>
        <h2>Une base simple, claire et prête pour le TP</h2>
        <p>
            Cette première version gère déjà les comptes, la liste des jeux,
            la création des parties et une version jouable de Mastermind.
        </p>
        <div class="hero-actions">
            <?= $this->Html->link('Voir les jeux', ['controller' => 'Games', 'action' => 'index'], ['class' => 'button']) ?>
            <?php if (!$currentUser): ?>
                <?= $this->Html->link('Créer un compte', ['controller' => 'Users', 'action' => 'register'], ['class' => 'button button-secondary']) ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="card-grid">
    <?php foreach ($boardGames as $boardGame): ?>
        <article class="card">
            <h3><?= h($boardGame->name) ?></h3>
            <p>Type : <?= h($boardGame->type) ?></p>
            <?php if ($currentUser): ?>
                <?= $this->Html->link('Créer une partie', ['controller' => 'Games', 'action' => 'add', $boardGame->id], ['class' => 'button']) ?>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <h2>Parties récentes</h2>
    <?php if ($recentGames->isEmpty()): ?>
        <p>Aucune partie pour le moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Jeu</th>
                    <th>Statut</th>
                    <th>Joueurs</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentGames as $game): ?>
                <tr>
                    <td><?= h($game->board_game->name) ?></td>
                    <td><?= h($game->status) ?></td>
                    <td>
                        <?php
                        $names = [];
                        foreach ($game->users_ingames as $link) {
                            $names[] = $link->user->username;
                        }
                        echo h(implode(', ', $names));
                        ?>
                    </td>
                    <td><?= $this->Html->link('Ouvrir', ['controller' => 'Games', 'action' => 'view', $game->id]) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
