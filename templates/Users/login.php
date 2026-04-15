<section class="panel narrow">
    <h2>Connexion</h2>
    <?= $this->Form->create() ?>
    <?= $this->Form->control('username', ['label' => 'Nom d’utilisateur']) ?>
    <?= $this->Form->control('password', ['label' => 'Mot de passe']) ?>
    <?= $this->Form->button('Se connecter', ['class' => 'button']) ?>
    <?= $this->Form->end() ?>
</section>
