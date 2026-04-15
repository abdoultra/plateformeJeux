# Plateforme de jeux - TP CakePHP

Cette application est une base de travail pour ton TP avec :

- inscription et connexion
- liste des jeux disponibles
- creation et consultation des parties
- Mastermind jouable
- Filler jouable
- Labyrinthe jouable
- classement des joueurs
- structure Sass pour organiser les styles
- commande CakePHP pour recharger les PA du labyrinthe

## Dossier du projet

Le projet se trouve dans `C:\wamp64\www\plateformeJeux`.

## Base de donnees

Le script SQL est dans `config/schema/plateforme_jeux.sql`.

Tu peux l'importer dans phpMyAdmin, puis verifier que `config/app_local.php`
contient bien :

- base : `plateforme_jeux`
- utilisateur : `root`
- mot de passe : vide sous Wamp par defaut

## Lancer le projet

Avec Wamp, l'URL sera normalement :

`http://localhost/plateformeJeux/webroot`

## Utilisation de Sass

Oui, le projet utilise maintenant `Sass` en plus de `CakePHP`.

- source Sass principale : `webroot/scss/app.scss`
- fichier CSS utilise par CakePHP : `webroot/css/app.css`

Si tu veux recompiler le style plus tard, tu peux installer Sass une seule fois :

`npm install --save-dev sass`

Puis lancer :

`npx sass webroot/scss/app.scss webroot/css/app.css --watch`

Ce qu'il faut retenir pour ton TP :

- `app.scss` rassemble les fichiers Sass
- `_variables.scss` contient les couleurs et tailles communes
- les autres fichiers `.scss` rangent le style par zone du site
- le navigateur continue de lire `app.css`

## Commande pour le labyrinthe

La commande suivante ajoute 5 PA par minute, avec un maximum de 15 :

`bin\cake recharge_pa`

Pour le sujet, il faudra ensuite la brancher sur une tache planifiee Windows
ou un cron selon ton environnement.
