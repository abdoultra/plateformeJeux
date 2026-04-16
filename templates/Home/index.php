<?php
/**
 * @var iterable<\Cake\Datasource\EntityInterface> $boardGames
 * @var iterable<\Cake\Datasource\EntityInterface> $recentGames
 * @var array<string, mixed>|null $currentUser
 */
?>
<section class="hero hero-home">
    <div class="hero-copy">
        <span class="eyebrow">Plateforme multijeux</span>
        <h2>Trois jeux, une seule plateforme, une interface enfin plus vivante.</h2>
        <p class="hero-lead">
            Le projet combine comptes utilisateurs, parties solo ou multijoueur, scores,
            classement et logique metier en CakePHP avec une organisation visuelle en Sass.
        </p>
        <div class="hero-actions">
            <?= $this->Html->link('Explorer les jeux', ['controller' => 'Games', 'action' => 'index'], ['class' => 'button']) ?>
            <?= $this->Html->link('Voir le classement', ['controller' => 'Users', 'action' => 'leaderboard'], ['class' => 'button button-secondary']) ?>
            <?php if (!$currentUser): ?>
                <?= $this->Html->link('Creer un compte', ['controller' => 'Users', 'action' => 'register'], ['class' => 'button button-ghost']) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-side">
        <div class="hero-panel">
            <p class="hero-panel-label">Contenu actuel</p>
            <div class="hero-stats">
                <article>
                    <strong>3</strong>
                    <span>jeux disponibles</span>
                </article>
                <article>
                    <strong>2</strong>
                    <span>styles de partie</span>
                </article>
                <article>
                    <strong>5</strong>
                    <span>parties affichees</span>
                </article>
            </div>
            <div class="hero-mini-board" aria-hidden="true">
                <span class="token token-mastermind">M</span>
                <span class="token token-filler">F</span>
                <span class="token token-labyrinth">L</span>
                <span class="token token-score">#</span>
            </div>
        </div>
    </div>
</section>

<section class="section-heading">
    <div>
        <span class="eyebrow">Catalogue</span>
        <h2>Choisis une experience</h2>
    </div>
    <p>Chaque jeu a son propre rythme, ses regles et sa logique metier dans l'application.</p>
</section>

<section class="card-grid game-showcase">
    <?php foreach ($boardGames as $boardGame): ?>
        <?php
        $descriptions = [
            'Mastermind' => 'Un jeu solo de deduction avec combinaison secrete et historique des essais.',
            'Filler' => 'Un duel de territoire sur grille avec alternance de tours et controle de couleurs.',
            'Labyrinthe' => 'Une course au tresor avec deplacements, PA et lecture d une carte.',
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
            <p><?= h($descriptions[$boardGame->name] ?? 'Jeu disponible sur la plateforme.') ?></p>
            <?php if ($currentUser): ?>
                <?= $this->Html->link('Creer une partie', [
                    'controller' => 'Games',
                    'action' => 'add',
                    '?' => ['board_game_id' => $boardGame->id],
                ], ['class' => 'button']) ?>
            <?php else: ?>
                <p class="game-card-note">Connecte-toi pour creer une partie.</p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<section class="section-heading">
    <div>
        <span class="eyebrow">Activite</span>
        <h2>Parties recentes</h2>
    </div>
    <p>Un apercu rapide des parties deja lancees sur la plateforme.</p>
</section>

<section class="panel recent-games-panel">
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
                        $isCurrentUserInGame = false;
                        foreach ($game->users_ingames as $link) {
                            $names[] = $link->user->username;
                            if ($currentUser && (int)$link->user_id === (int)$currentUser['id']) {
                                $isCurrentUserInGame = true;
                            }
                        }
                        echo h(implode(', ', $names));
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
    <?php endif; ?>
</section>
