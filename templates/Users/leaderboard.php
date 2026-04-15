<?php
/**
 * @var array<int, array<string, mixed>> $leaderboard
 */
?>
<section class="panel">
    <h2>Classement des joueurs</h2>
    <p>
        Cette page classe les joueurs selon le nombre de victoires, puis le score total
        et enfin le nombre de parties terminees.
    </p>
</section>

<section class="panel">
    <?php if ($leaderboard === []): ?>
        <p>Aucun joueur pour le moment.</p>
    <?php else: ?>
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Joueur</th>
                    <th>Parties jouees</th>
                    <th>Parties terminees</th>
                    <th>Victoires</th>
                    <th>Score total</th>
                    <th>Jeu prefere</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($leaderboard as $index => $entry): ?>
                <tr>
                    <td><span class="leaderboard-rank"><?= h((string)($index + 1)) ?></span></td>
                    <td><?= h($entry['user']->username) ?></td>
                    <td><?= h((string)$entry['games_played']) ?></td>
                    <td><?= h((string)$entry['finished_games']) ?></td>
                    <td><?= h((string)$entry['wins']) ?></td>
                    <td><?= h((string)$entry['total_score']) ?></td>
                    <td><?= h((string)$entry['favorite_game']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
