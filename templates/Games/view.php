<?php
/**
 * @var \Cake\Datasource\EntityInterface $game
 * @var array<int, array<string, mixed>> $decodedSteps
 * @var array<array<string>>|null $fillerGrid
 * @var array<string>|null $labyrinthMap
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
        <h2>Règles rapides</h2>
        <p>Entre 4 lettres parmi : R, B, J, V, O, P.</p>
        <p>R = rouge, B = bleu, J = jaune, V = vert, O = orange, P = rose.</p>
        <p>Le score final est le nombre de coups utilisés pour trouver la combinaison.</p>
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
                        <th>Bien placées</th>
                        <th>Présentes mal placées</th>
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

<?php if ($game->board_game->name === 'Filler' && $fillerGrid !== null): ?>
    <section class="panel">
        <h2>Préparation Filler</h2>
        <p>
            La grille aléatoire est déjà générée. L’étape suivante sera de coder
            le changement de couleur, la propagation du territoire et le calcul du score.
        </p>
        <div class="grid-preview">
            <?php foreach ($fillerGrid as $row): ?>
                <div class="grid-row">
                    <?php foreach ($row as $cell): ?>
                        <span class="grid-cell"><?= h($cell) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($game->board_game->name === 'Labyrinthe' && $game->labyrinth_setting !== null): ?>
    <section class="panel">
        <h2>Préparation Labyrinthe</h2>
        <p>La carte est chargée depuis un fichier texte et le trésor est placé aléatoirement.</p>
        <p>
            <strong>Trésor :</strong>
            x=<?= h((string)$game->labyrinth_setting->treasure_x) ?>,
            y=<?= h((string)$game->labyrinth_setting->treasure_y) ?>
        </p>
        <p>
            <strong>PA :</strong>
            joueur 1 = <?= h((string)$game->labyrinth_setting->pa_p1) ?>,
            joueur 2 = <?= h((string)$game->labyrinth_setting->pa_p2) ?>
        </p>
        <?php if ($labyrinthMap !== null): ?>
            <pre class="map-preview"><?php foreach ($labyrinthMap as $line) { echo h($line) . "\n"; } ?></pre>
        <?php endif; ?>
        <p>
            La commande `bin\cake recharge_pa` est prête pour la tâche planifiée
            qui ajoute 5 PA par minute jusqu’à 15.
        </p>
    </section>
<?php endif; ?>
