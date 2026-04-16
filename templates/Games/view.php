<?php
/**
 * @var \Cake\Datasource\EntityInterface $game
 * @var array<int, array<string, mixed>> $decodedSteps
 * @var array<array<string>>|null $fillerGrid
 * @var array<string, mixed>|null $fillerState
 * @var array<string>|null $labyrinthMap
 * @var array<string, mixed>|null $labyrinthState
 * @var string $gameFingerprint
 */
?>
<?php
$statusLabels = [
    'waiting' => 'En attente',
    'in_progress' => 'En cours',
    'finished' => 'Terminee',
];
$statusClass = 'status-pill status-' . strtolower((string)$game->status);
$names = [];
foreach ($game->users_ingames as $link) {
    $names[] = $link->user->username;
}
?>
<section class="panel game-header-panel">
    <div class="game-header-top">
        <div>
            <span class="eyebrow">Partie #<?= h((string)$game->id) ?></span>
            <h2><?= h($game->board_game->name) ?></h2>
            <p class="game-header-lead">Suis l'etat de la partie, les joueurs presents et les informations utiles avant de jouer.</p>
        </div>
        <span class="<?= h($statusClass) ?>"><?= h($statusLabels[$game->status] ?? $game->status) ?></span>
    </div>

    <div class="game-meta-grid">
        <article class="game-meta-card">
            <span class="game-meta-label">Jeu</span>
            <strong><?= h($game->board_game->name) ?></strong>
        </article>
        <article class="game-meta-card">
            <span class="game-meta-label">Statut</span>
            <strong><?= h($statusLabels[$game->status] ?? $game->status) ?></strong>
        </article>
        <article class="game-meta-card">
            <span class="game-meta-label">Joueurs</span>
            <strong><?= h(implode(', ', $names)) ?></strong>
        </article>
    </div>
</section>

<?php if ($game->board_game->type === 'multiplayer' && $game->status !== 'finished'): ?>
    <section
        class="live-sync"
        data-live-sync
        data-state-url="<?= h($this->Url->build(['controller' => 'Games', 'action' => 'state', $game->id])) ?>"
        data-fingerprint="<?= h($gameFingerprint) ?>"
    >
        <span class="live-sync-dot"></span>
        <span class="live-sync-text">Synchronisation active : la page se met à jour quand l'autre joueur joue.</span>
    </section>

    <script>
        (() => {
            const liveBox = document.querySelector('[data-live-sync]');
            if (!liveBox) {
                return;
            }

            const stateUrl = liveBox.dataset.stateUrl;
            let currentFingerprint = liveBox.dataset.fingerprint;
            let requestInProgress = false;
            let userIsInteractingUntil = 0;
            const text = liveBox.querySelector('.live-sync-text');

            const delayReloadIfUserIsInteracting = () => {
                if (Date.now() < userIsInteractingUntil) {
                    text.textContent = 'Changement détecté. Mise à jour dans quelques secondes...';
                    window.setTimeout(() => window.location.reload(), 2500);

                    return;
                }

                window.location.reload();
            };

            document.addEventListener('focusin', (event) => {
                if (event.target.closest('form')) {
                    userIsInteractingUntil = Date.now() + 7000;
                }
            });

            document.addEventListener('input', (event) => {
                if (event.target.closest('form')) {
                    userIsInteractingUntil = Date.now() + 7000;
                }
            });

            window.setInterval(async () => {
                if (document.hidden || requestInProgress) {
                    return;
                }

                requestInProgress = true;

                try {
                    const response = await fetch(stateUrl, {
                        headers: { Accept: 'application/json' },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const state = await response.json();
                    if (state.fingerprint && state.fingerprint !== currentFingerprint) {
                        currentFingerprint = state.fingerprint;
                        liveBox.classList.add('is-updating');
                        text.textContent = 'L’autre joueur a joué. Mise à jour...';
                        delayReloadIfUserIsInteracting();
                    }
                } catch (error) {
                    text.textContent = 'Synchronisation temporairement indisponible.';
                } finally {
                    requestInProgress = false;
                }
            }, 5000);
        })();
    </script>
<?php endif; ?>

<?php if ($game->board_game->name === 'Mastermind'): ?>
    <?php
    $mastermindColorLabels = [
        'R' => 'Rouge',
        'B' => 'Bleu',
        'J' => 'Jaune',
        'V' => 'Vert',
        'O' => 'Orange',
        'P' => 'Rose',
    ];
    $maxMastermindRows = 10;
    $mastermindRows = [];
    for ($row = 0; $row < $maxMastermindRows; $row++) {
        $mastermindRows[] = $decodedSteps[$row] ?? null;
    }
    ?>
    <section class="mastermind-stage">
        <div class="mastermind-stage-copy">
            <span class="eyebrow">Mastermind</span>
            <h2>Devinez la combinaison secrète !</h2>
            <p>Compose une suite de 4 couleurs, valide ton essai, puis observe les petits indices à droite du plateau pour progresser.</p>
            <p class="mastermind-attempts">Nombre de tentatives : <strong><?= h((string)count($decodedSteps)) ?></strong></p>
        </div>

        <div class="mastermind-table">
            <div class="mastermind-board">
                <div class="mastermind-board-title">MASTER<br>MIND</div>
                <div class="mastermind-rows">
                    <?php foreach (array_reverse($mastermindRows, true) as $rowIndex => $step): ?>
                        <?php $letters = $step !== null ? str_split((string)$step['guess']) : []; ?>
                        <div class="mastermind-board-row<?= $step === null ? ' is-empty' : '' ?>">
                            <div class="mastermind-row-number"><?= h((string)($rowIndex + 1)) ?></div>
                            <div class="mastermind-code-slots">
                                <?php for ($slot = 0; $slot < 4; $slot++): ?>
                                    <?php $letter = $letters[$slot] ?? null; ?>
                                    <?php if ($letter !== null): ?>
                                        <span class="mastermind-peg mastermind-peg-<?= strtolower($letter) ?>"><?= h($letter) ?></span>
                                    <?php else: ?>
                                        <span class="mastermind-hole"></span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <div class="mastermind-feedback">
                                <?php
                                $wellPlaced = $step !== null ? (int)$step['wellPlaced'] : 0;
                                $present = $step !== null ? (int)$step['present'] : 0;
                                ?>
                                <?php for ($hint = 0; $hint < 4; $hint++): ?>
                                    <?php
                                    $hintClass = '';
                                    if ($hint < $wellPlaced) {
                                        $hintClass = ' is-right';
                                    } elseif ($hint < $wellPlaced + $present) {
                                        $hintClass = ' is-present';
                                    }
                                    ?>
                                    <span class="mastermind-feedback-dot<?= $hintClass ?>"></span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mastermind-secret-drawer">
                    <span>Code secret</span>
                    <div class="mastermind-secret-preview" aria-label="Combinaison cachee">
                        <span>?</span>
                        <span>?</span>
                        <span>?</span>
                        <span>?</span>
                    </div>
                </div>
            </div>
        </div>

        <aside class="mastermind-control-card">
            <div class="mastermind-rules-grid">
                <article>
                    <strong>Objectif</strong>
                    <span>Trouver le code caché en le moins de coups possible.</span>
                </article>
                <article>
                    <strong>Indices</strong>
                    <span>Rouge = bien placé, blanc = présent mais mal placé.</span>
                </article>
            </div>

            <div class="mastermind-palette">
                <?php foreach ($mastermindColorLabels as $letter => $label): ?>
                    <span class="mastermind-palette-item">
                        <span class="mastermind-peg mastermind-peg-<?= strtolower($letter) ?>"><?= h($letter) ?></span>
                        <?= h($label) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <?php if ($game->status !== 'finished'): ?>
                <div class="mastermind-play-panel">
                    <h3>Votre tentative</h3>
                    <div class="mastermind-empty-guess" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <?= $this->Form->create() ?>
                    <?= $this->Form->control('guess', [
                        'label' => 'Saisis 4 lettres',
                        'placeholder' => 'Exemple : RBJV',
                        'maxlength' => 4,
                        'class' => 'mastermind-input',
                    ]) ?>
                    <?= $this->Form->button('Valider', ['class' => 'button mastermind-submit']) ?>
                    <?= $this->Form->end() ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <strong>Partie terminée.</strong>
                    <span>Le score correspond au nombre de tentatives utilisées.</span>
                </div>
            <?php endif; ?>
        </aside>
    </section>
<?php endif; ?>

<?php if ($game->board_game->name === 'Filler' && $fillerGrid !== null && $fillerState !== null): ?>
    <section class="filler-stage">
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
        $totalCells = count($fillerGrid) * count($fillerGrid[0]);
        $player1Percent = $totalCells > 0 ? round(((int)$fillerState['player1']['score'] / $totalCells) * 100) : 0;
        $player2Percent = $totalCells > 0 ? round(((int)$fillerState['player2']['score'] / $totalCells) * 100) : 0;
        ?>

        <div class="filler-hero">
            <div>
                <span class="eyebrow">Filler</span>
                <h2>Conquiers la grille couleur par couleur</h2>
                <p>Chaque joueur part d'un coin. À ton tour, choisis une couleur autorisée pour agrandir ton territoire et bloquer ton adversaire.</p>
            </div>
            <?php if ($game->status !== 'finished' && count($players) >= 2): ?>
                <div class="filler-turn-card">
                    <span>Tour actuel</span>
                    <strong><?= h((string)$currentTurnPlayer) ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <div class="filler-layout">
            <div class="filler-arena">
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
            </div>

            <aside class="filler-sidebar">
                <div class="filler-player-card filler-player-card-one">
                    <span>Joueur 1</span>
                    <strong><?= h($players[0]->user->username ?? 'en attente') ?></strong>
                    <small>Couleur : <?= h($colorLabels[$fillerState['player1']['color']] ?? $fillerState['player1']['color']) ?></small>
                    <div class="filler-score-line">
                        <b><?= h((string)$fillerState['player1']['score']) ?></b>
                        <small><?= h((string)$player1Percent) ?>% de la grille</small>
                    </div>
                    <div class="filler-progress"><span style="width: <?= h((string)$player1Percent) ?>%"></span></div>
                </div>

                <div class="filler-player-card filler-player-card-two">
                    <span>Joueur 2</span>
                    <strong><?= h($players[1]->user->username ?? 'en attente') ?></strong>
                    <small>Couleur : <?= h($colorLabels[$fillerState['player2']['color']] ?? $fillerState['player2']['color']) ?></small>
                    <div class="filler-score-line">
                        <b><?= h((string)$fillerState['player2']['score']) ?></b>
                        <small><?= h((string)$player2Percent) ?>% de la grille</small>
                    </div>
                    <div class="filler-progress"><span style="width: <?= h((string)$player2Percent) ?>%"></span></div>
                </div>

                <?php if (count($players) < 2): ?>
                    <div class="empty-state">
                        <strong>En attente du deuxième joueur.</strong>
                        <span>La partie commencera dès qu'un autre joueur rejoindra.</span>
                    </div>
                <?php elseif ($game->status !== 'finished'): ?>
                    <div class="filler-controls">
                        <h3>Choisir une couleur</h3>
                        <p>Les couleurs déjà utilisées par les deux joueurs sont bloquées.</p>
                        <?= $this->Form->create() ?>
                        <div class="filler-color-actions">
                            <?php foreach ($colorLabels as $color => $label): ?>
                                <?php $disabled = !in_array($color, $fillerState['availableColors'], true); ?>
                                <?= $this->Form->button($label, [
                                    'name' => 'color',
                                    'value' => $color,
                                    'class' => 'filler-color-button filler-color-button-' . strtolower($color),
                                    'disabled' => $disabled,
                                ]) ?>
                            <?php endforeach; ?>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>
                <?php else: ?>
                    <div class="filler-victory">
                        <strong>Partie terminée.</strong>
                        <span>Le score correspond au nombre de cases contrôlées.</span>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </section>
<?php endif; ?>

<?php if ($game->board_game->name === 'Labyrinthe' && $game->labyrinth_setting !== null && $labyrinthState !== null): ?>
    <section class="labyrinth-stage">
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
        $availableDirections = $labyrinthState['availableDirections'] ?? [];
        $directionLabels = [
            'UP' => 'Haut',
            'DOWN' => 'Bas',
            'LEFT' => 'Gauche',
            'RIGHT' => 'Droite',
        ];
        $paMax = 15;
        $player1PaPercent = min(100, ((int)$labyrinthState['player1']['pa'] / $paMax) * 100);
        $player2PaPercent = min(100, ((int)$labyrinthState['player2']['pa'] / $paMax) * 100);
        ?>

        <div class="labyrinth-hero">
            <div>
                <span class="eyebrow">Labyrinthe</span>
                <h2>Explore le donjon et trouve le trésor</h2>
                <p>Deux joueurs avancent case par case. Chaque déplacement coûte 1 PA, et le trésor reste caché jusqu'à ce qu'un joueur le trouve.</p>
            </div>
            <div class="labyrinth-current-player">
                <span>Joueur connecté</span>
                <strong><?= h($currentPlayer ?? 'inconnu') ?></strong>
            </div>
        </div>

        <div class="labyrinth-layout">
            <div class="labyrinth-map-panel">
                <div class="labyrinth-map-toolbar">
                    <span class="labyrinth-legend"><i class="legend-wall"></i> Mur</span>
                    <span class="labyrinth-legend"><i class="legend-path"></i> Chemin</span>
                    <span class="labyrinth-legend"><i class="legend-player1"></i> J1</span>
                    <span class="labyrinth-legend"><i class="legend-player2"></i> J2</span>
                    <?php if ($game->status === 'finished'): ?>
                        <span class="labyrinth-legend"><i class="legend-treasure"></i> Trésor</span>
                    <?php endif; ?>
                </div>

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
            </div>

            <aside class="labyrinth-sidebar">
                <div class="labyrinth-player-card labyrinth-player-card-one">
                    <span>Joueur 1</span>
                    <strong><?= h($players[0]->user->username ?? 'en attente') ?></strong>
                    <small>Position x=<?= h((string)$labyrinthState['player1']['x']) ?>, y=<?= h((string)$labyrinthState['player1']['y']) ?></small>
                    <div class="pa-bar">
                        <span style="width: <?= h((string)$player1PaPercent) ?>%"></span>
                    </div>
                    <small><?= h((string)$labyrinthState['player1']['pa']) ?> PA disponibles</small>
                </div>

                <div class="labyrinth-player-card labyrinth-player-card-two">
                    <span>Joueur 2</span>
                    <strong><?= h($players[1]->user->username ?? 'en attente') ?></strong>
                    <small>Position x=<?= h((string)$labyrinthState['player2']['x']) ?>, y=<?= h((string)$labyrinthState['player2']['y']) ?></small>
                    <div class="pa-bar">
                        <span style="width: <?= h((string)$player2PaPercent) ?>%"></span>
                    </div>
                    <small><?= h((string)$labyrinthState['player2']['pa']) ?> PA disponibles</small>
                </div>

                <?php if ($game->status !== 'finished' && !empty($availableDirections)): ?>
                    <div class="labyrinth-controls">
                        <h3>Se déplacer</h3>
                        <?= $this->Form->create() ?>
                        <div class="direction-pad">
                            <?php foreach (['UP', 'LEFT', 'RIGHT', 'DOWN'] as $direction): ?>
                                <?php
                                $icons = ['UP' => '↑', 'LEFT' => '←', 'RIGHT' => '→', 'DOWN' => '↓'];
                                $classes = 'direction-button direction-' . strtolower($direction);
                                $disabled = !in_array($direction, $availableDirections, true);
                                ?>
                                <?= $this->Form->button($icons[$direction] . ' ' . $directionLabels[$direction], [
                                    'name' => 'direction',
                                    'value' => $direction,
                                    'class' => $classes,
                                    'disabled' => $disabled,
                                ]) ?>
                            <?php endforeach; ?>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>
                    <p class="labyrinth-tip">Astuce : la commande <strong>bin\cake recharge_pa</strong> recharge les PA jusqu'à 15.</p>
                <?php elseif ($game->status !== 'finished'): ?>
                    <div class="empty-state">
                        <strong>Aucun déplacement possible.</strong>
                        <span>Recharge les PA ou attends la prochaine minute.</span>
                    </div>
                    <p class="labyrinth-tip">Astuce : la commande <strong>bin\cake recharge_pa</strong> recharge les PA jusqu'à 15.</p>
                <?php else: ?>
                    <div class="labyrinth-victory">
                        <strong>Partie terminée.</strong>
                        <span>Le trésor est maintenant visible sur la carte.</span>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </section>
<?php endif; ?>
