<?php
/**
 * @var \App\Model\Entity\User $user
 */
?>
<section class="panel narrow">
    <h2>Inscription</h2>
    <?= $this->Form->create($user) ?>
    <?= $this->Form->control('username', ['label' => 'Nom d’utilisateur']) ?>
    <?= $this->Form->control('email') ?>
    <?= $this->Form->control('password', ['label' => 'Mot de passe']) ?>
    <?= $this->Form->button('Créer le compte', ['class' => 'button']) ?>
    <?= $this->Form->end() ?>
</section>
