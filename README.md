# Plateforme de jeux - TP CakePHP

Cette application est une base de travail pour ton TP avec :

- inscription et connexion
- liste des jeux disponibles
- création et consultation des parties
- Mastermind jouable
- préparation de Filler et du Labyrinthe
- commande CakePHP pour recharger les PA du labyrinthe

## Dossier du projet

Le projet se trouve dans `C:\wamp64\www\plateformeJeux`.

## Base de données

Le script SQL est dans `config/schema/plateforme_jeux.sql`.

Tu peux l'importer dans phpMyAdmin, puis vérifier que `config/app_local.php`
contient bien :

- base : `plateforme_jeux`
- utilisateur : `root`
- mot de passe : vide sous Wamp par défaut

## Lancer le projet

Avec Wamp, l'URL sera normalement :

`http://localhost/plateformeJeux/webroot`

## Commande pour le labyrinthe

La commande suivante ajoute 5 PA par minute, avec un maximum de 15 :

`bin\cake recharge_pa`

Pour le sujet, il faudra ensuite la brancher sur une tâche planifiée Windows
ou un cron selon ton environnement.
