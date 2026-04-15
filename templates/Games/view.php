<?php
/**
 * @var \Cake\Datasource\EntityInterface $game
 * @var array<int, array<string, mixed>> $decodedSteps
 * @var array<array<string>>|null $fillerGrid
 * @var array<string, mixed>|null $fillerState
 * @var array<string>|null $labyrinthMap
 * @var array<string, mixed>|null $labyrinthState
 */
?>
<section class="panel">
    <h2><?= h($game->board_game->name) ?> - Partie #<?= h((string)$game->id) ?></h2>
    <p><strong>Statut :</strong> <?= h($game->status) ?></p>
    <p>
        <strong>Joueurs :</strong>
        <?php
        $names = [];
        foreach ($game->users_ingames as $link) {
            $names[] = $link->user->username;
        }
        echo h(implode(', ', $names));
        ?>
    </p>
</section>

<?php if ($game->board_game->name === 'Mastermind'): ?>
    <section class="panel">
        <h2>Regles rapides</h2>
        <p>Entre 4 lettres parmi : R, B, J, V, O, P.</p>
        <p>R = rouge, B = bleu, J = jaune, V = vert, O = orange, P = rose.</p>
        <p>Le score final est le nombre de coups utilises pour trouver la combinaison.</p>
    </section>

    <?php if ($game->status !== 'finished'): ?>
        <section class="panel narrow">
            <h2>Jouer</h2>
            <?= $this->Form->create() ?>
            <?= $this->Form->control('guess', [
                'label' => 'Ta proposition',
                'placeholder' => 'Exemple : RBJV',
            ]) ?>
            <?= $this->Form->button('Envoyer la combinaison', ['class' => 'button']) ?>
            <?= $this->Form->end() ?>
        </section>
    <?php endif; ?>

    <section class="panel">
        <h2>Historique des essais</h2>
        <?php if (empty($decodedSteps)): ?>
            <p>Aucun essai pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Essai</th>
                        <th>Bien placees</th>
                        <th>Presentes mal placees</th>
                        <th>Heure</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($decodedSteps as $step): ?>
                    <tr>
                        <td><?= h($step['guess']) ?></td>
                        <td><?= h((string)$step['wellPlaced']) ?></td>
                        <td><?= h((string)$step['present']) ?></td>
                        <td><?= h($step['played_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if ($game->board_game->name === 'Filler' && $fillerGrid !== null && $fillerState !== null): ?>
    <section class="panel">
        <h2>Partie de Filler</h2>
        <p>Deux joueurs partent chacun d'un coin de la grille.</p>
        <p>A chaque tour, le joueur actif choisit une couleur autorisee pour agrandir son territoire.</p>
        <p>La partie se termine quand toute la grille est controlee ou quand aucun joueur ne peut encore s'agrandir.</p>
        <?php
        $players = is_array($game->users_ingames) ? $game->users_ingames : $game->users_ingames->toList();
        usort($players, fn ($left, $right) => $left->id <=> $right->id);
        $currentTurnPlayer = null;
        if (count($players) >= 2) {
            $currentTurnPlayer = $players[$game->filler_setting->current_player - 1]->user->username;
        }

        $colorLabels = [
            'R' => 'Rouge',
            'B' => 'Bleu',
            'J' => 'Jaune',
            'V' => 'Vert',
            'O' => 'Orange',
            'P' => 'Rose',
        ];
        ?>

        <p><strong>Joueur 1 :</strong> <?= h($players[0]->user->username ?? 'en attente') ?></p>
        <p><strong>Joueur 2 :</strong> <?= h($players[1]->user->username ?? 'en attente') ?></p>
        <p><strong>Score joueur 1 :</strong> <?= h((string)$fillerState['player1']['score']) ?></p>
        <p><strong>Score joueur 2 :</strong> <?= h((string)$fillerState['player2']['score']) ?></p>
        <p><strong>Couleur joueur 1 :</strong> <?= h($colorLabels[$fillerState['player1']['color']] ?? $fillerState['player1']['color']) ?></p>
        <p><strong>Couleur joueur 2 :</strong> <?= h($colorLabels[$fillerState['player2']['color']] ?? $fillerState['player2']['color']) ?></p>

        <?php if (count($players) < 2): ?>
            <p>La partie attend encore le deuxieme joueur.</p>
        <?php elseif ($game->status !== 'finished'): ?>
            <p><strong>Tour actuel :</strong> <?= h((string)$currentTurnPlayer) ?></p>
            <section class="panel narrow">
                <h3>Choisir une couleur</h3>
                <?= $this->Form->create() ?>
                <?= $this->Form->control('color', [
                    'label' => 'Couleur du tour',
                    'options' => array_combine(
                        $fillerState['availableColors'],
                        array_map(fn ($color) => $colorLabels[$color] ?? $color, $fillerState['availableColors'])
                    ),
                    'empty' => 'Choisir une couleur',
                ]) ?>
                <?= $this->Form->button('Jouer ce coup', ['class' => 'button']) ?>
                <?= $this->Form->end() ?>
            </section>
        <?php else: ?>
            <p>La partie est terminee. Le score correspond au nombre de cases controlees.</p>
        <?php endif; ?>

        <div class="filler-board">
            <?php foreach ($fillerGrid as $y => $row): ?>
                <div class="filler-row">
                    <?php foreach ($row as $x => $cell): ?>
                        <?php
                        $key = $y . '-' . $x;
                        $ownerClass = '';
                        if (($fillerState['territoryMap'][$key] ?? null) === 1) {
                            $ownerClass = ' filler-player1';
                        }
                        if (($fillerState['territoryMap'][$key] ?? null) === 2) {
                            $ownerClass = ' filler-player2';
                        }
                        ?>
                        <span class="filler-cell filler-color-<?= strtolower($cell) . $ownerClass ?>">
                            <?= h($cell) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($game->board_game->name === 'Labyrinthe' && $game->labyrinth_setting !== null && $labyrinthState !== null): ?>
    <section class="panel">
        <h2>Partie de Labyrinthe</h2>
        <p>Deux joueurs se deplacent dans le labyrinthe pour atteindre un tresor cache.</p>
        <p>Chaque deplacement consomme 1 PA. Quand les PA tombent a zero, il faut attendre la recharge.</p>
        <p>Le premier joueur qui atteint le tresor gagne la partie.</p>
        <?php
        $players = is_array($game->users_ingames) ? $game->users_ingames : $game->users_ingames->toList();
        usort($players, fn ($left, $right) => $left->id <=> $right->id);
        $currentPlayer = null;
        if (($labyrinthState['playerSlot'] ?? null) === 1) {
            $currentPlayer = $players[0]->user->username ?? null;
        }
        if (($labyrinthState['playerSlot'] ?? null) === 2) {
            $currentPlayer = $players[1]->user->username ?? null;
        }
        ?>

        <p>Le tresor reste cache jusqu'a ce qu'un joueur le trouve.</p>
        <p><strong>Joueur connecte :</strong> <?= h($currentPlayer ?? 'inconnu') ?></p>
        <p><strong>Joueur 1 :</strong> <?= h($players[0]->user->username ?? 'en attente') ?></p>
        <p><strong>Joueur 2 :</strong> <?= h($players[1]->user->username ?? 'en attente') ?></p>
        <p>
            <strong>Position joueur 1 :</strong>
            x=<?= h((string)$labyrinthState['player1']['x']) ?>,
            y=<?= h((string)$labyrinthState['player1']['y']) ?>
        </p>
        <p>
            <strong>Position joueur 2 :</strong>
            x=<?= h((string)$labyrinthState['player2']['x']) ?>,
            y=<?= h((string)$labyrinthState['player2']['y']) ?>
        </p>
        <p>
            <strong>PA :</strong>
            joueur 1 = <?= h((string)$labyrinthState['player1']['pa']) ?>,
            joueur 2 = <?= h((string)$labyrinthState['player2']['pa']) ?>
        </p>

        <?php if ($game->status !== 'finished' && !empty($labyrinthState['availableDirections'])): ?>
            <section class="panel narrow">
                <h3>Se deplacer</h3>
                <?= $this->Form->create() ?>
                <?= $this->Form->control('direction', [
                    'label' => 'Direction',
                    'options' => [
                        'UP' => 'Haut',
                        'DOWN' => 'Bas',
                        'LEFT' => 'Gauche',
                        'RIGHT' => 'Droite',
                    ],
                    'empty' => 'Choisir une direction',
                ]) ?>
                <?= $this->Form->button('Se deplacer', ['class' => 'button']) ?>
                <?= $this->Form->end() ?>
            </section>
        <?php elseif ($game->status !== 'finished'): ?>
            <p>Aucun deplacement possible pour le moment. Recharge les PA si besoin.</p>
        <?php else: ?>
            <p>La partie est terminee. Le tresor est maintenant visible sur la carte.</p>
        <?php endif; ?>

        <div class="labyrinth-board">
            <?php foreach ($labyrinthState['cells'] as $row): ?>
                <div class="labyrinth-row">
                    <?php foreach ($row as $cell): ?>
                        <span class="labyrinth-cell labyrinth-<?= h($cell['type']) ?>">
                            <?= h($cell['label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p>La commande `bin\cake recharge_pa` ajoute 5 PA par minute avec un maximum de 15.</p>
    </section>
<?php endif; ?>
