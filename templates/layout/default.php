<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, mixed>|null $currentUser
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plateforme de jeux</title>
    <?= $this->Html->css(['app']) ?>
</head>
<body>
    <header class="site-header">
        <div class="wrapper header-content">
            <div class="header-brand">
                <h1>Plateforme de jeux</h1>
                <p>TP CakePHP avec Mastermind, Filler et Labyrinthe.</p>
            </div>
            <div class="header-nav-shell">
                <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Ouvrir le menu">
                <label for="nav-toggle" class="nav-toggle-button">
                    <span></span>
                    <span></span>
                    <span></span>
                </label>
                <nav class="main-nav">
                    <?= $this->Html->link('Accueil', ['controller' => 'Home', 'action' => 'index']) ?>
                    <?= $this->Html->link('Jeux', ['controller' => 'Games', 'action' => 'index']) ?>
                    <?= $this->Html->link('Classement', ['controller' => 'Users', 'action' => 'leaderboard']) ?>
                    <?php if ($currentUser): ?>
                        <?= $this->Html->link('Profil', ['controller' => 'Users', 'action' => 'profile']) ?>
                        <?= $this->Html->link('Déconnexion', ['controller' => 'Users', 'action' => 'logout']) ?>
                    <?php else: ?>
                        <?= $this->Html->link('Inscription', ['controller' => 'Users', 'action' => 'register']) ?>
                        <?= $this->Html->link('Connexion', ['controller' => 'Users', 'action' => 'login']) ?>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="wrapper page-content">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
</body>
</html>
