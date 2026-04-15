<?php
/**
 * @var \App\Model\Entity\User $user
 */
?>
<section class="panel">
    <h2>Profil de <?= h($user->username) ?></h2>
    <p><strong>Email :</strong> <?= h($user->email) ?></p>
</section>

<section class="panel">
    <h2>Scores et parties</h2>
    <?php if (empty($user->users_ingames)): ?>
        <p>Aucune partie jouée pour le moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Jeu</th>
                    <th>Partie</th>
                    <th>Score final</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($user->users_ingames as $link): ?>
                <tr>
                    <td><?= h($link->game->board_game->name) ?></td>
                    <td><?= $this->Html->link('Voir la partie', ['controller' => 'Games', 'action' => 'view', $link->game->id]) ?></td>
                    <td><?= h((string)$link->score_final) ?></td>
                    <td><?= h($link->game->status) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
