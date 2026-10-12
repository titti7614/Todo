# Journal des Modifications — Central To-Do Multi-Projets

Toutes les modifications notables apportées à ce projet sont documentées dans ce fichier. Le format est basé sur [Keep a Changelog](https://keepachangelog.com) et ce projet respecte le [Gestion de version sémantique](https://semver.org).

## - 2026-10-12
### 👥 Fonctionnalités Collaboratives & Cercles de Confiance (Livraison Officielle)

#### Ajouté
- **Gestion des Cercles d'Équipe** : Introduction du cloisonnement étanche par groupes (via les tables `todo_groupes` et `todo_groupe_membres`) permettant de sectoriser les accès des collaborateurs.
- **Bandeau d'Accueil Asynchrone** : Système de notification intelligent en page d'accueil informant en temps réel un utilisateur qu'un nouveau projet a été partagé avec lui.
- **Accès Rapide "Consulter"** : Intégration d'un bouton d'aiguillage automatique sur le bandeau d'accueil redirigeant instantanément l'utilisateur vers l'espace de travail d'équipe.
- **Mémoire d'Acquittement Fetch** : Ajout d'un bouton `[✓ OK, j'ai vu]` masquant le bandeau de notification de manière asynchrone et persistant l'état "lu" en base de données (table `todo_notifications_lectures`).
- **Le "Point X" Historique** : Menu déroulant interactif stabilisé en haut à droite (Dropdown CSS/JS au clic exclusif) servant de coffre-fort de relecture pour lister et consulter à tout moment les projets partagés par des tiers.
- **Indicateur Visuel de Périmètre** : Ajout automatique des icônes de confidentialité `🔒` (Projet Privé/Solo) et `👥` (Projet Partagé/Équipe) à gauche des titres dans la liste déroulante principale de l'IHM.
- **Création Adaptative par Cercle** : Intégration d'une case à cocher contextuelle (ou sélecteur si cercles multiples) affichant explicitement le prénom des collègues invités (ex: `📢 Partager avec : BSI, Cindy`) avant de sceller le projet.
- **Réassignation en Modification** : Menu déroulant injecté dans l'écran `ihm/modification_taches.php` permettant de déléguer ou de transférer une tâche existante à un autre participant du groupe.

#### Modifié
- **Architecture Pure MVC** : Centralisation et relocalisation des moteurs JavaScript globaux au pied du contrôleur principal `index.php` (Partie 4/4) pour parer aux fonctions vacantes en mémoire lors des chargements d'accueil.
- **Aiguillage Anti-Boucle** : Refonte de la structure du routeur de l'index en séparant strictement l'interception des formulaires `POST` des mécanismes de clics `GET` pour éradiquer les erreurs 302 de redirections infinies.
- **Algorithme de Sectorisation de Liste** : Réécriture de la fonction `getTachesParProjet()` dans `services/lister_taches.php` :
  - *Mode "Affectées à moi"* (Bouton Blanc) : Charge de travail personnelle stricte (tâches privées de l'utilisateur + tâches qui lui ont été déléguées).
  - *Mode "Voir toute l'équipe"* (Bouton Bleu) : Tableau de bord global du projet incluant les tâches assignées à des tiers, tout en conservant le masquage hermétique des tâches privées des autres utilisateurs.
- **Dédoublonnement de Base de Données** : Implantation de l'instruction SQL `DISTINCT` dans les fonctions du fichier `services/projet_services.php` réglant le blocage visuel des compteurs d'historique et des bandeaux d'invitation.

#### Sécurisé
- **Nettoyage Automatique a Posteriori** : Amélioration du service `services/modifier_projets.php`. Si un projet d'équipe est rebasculé en mode `🔒 Privé (Solo)`, le script révoque instantanément les accès des tiers, supprime les notifications et désassigne automatiquement les tâches associées (`SET attribue_a = NULL`) pour protéger la confidentialité du créateur.
- **Destruction en Cascade Sécurisée** : Le fichier `services/supprimer_projets.php` vérifie les droits d'écriture en amont et purge proprement la table pivot `todo_notifications_lectures` en cas de suppression administrative par l'Admin pour éviter les lignes orphelines.
- **Droits d'Écriture Collaboratifs** : Extension des droits dans `services/ajouter_taches.php` permettant à un membre du groupe d'ajouter une tâche dans un projet possédé par un tiers, à condition qu'il appartienne au même cercle de confiance.
- **Blindage du Mode Strict** : Application d'un `ALTER TABLE` modifiant la colonne obsolète `categorie_id` en `NULL` par défaut dans la table `todo_taches`, résolvant l'erreur d'insertion MySQL 1364 sur les serveurs de production (o2switch).

## [4.0.0-DEV] - 2026-10-11
### 🚀 Révolution Collaborative & Cloisonnement Étanche

#### 🧱 Évolution de la Base de Données (MySQL unique)
- **`todo_groupes`** : Création de la table des cercles de confiance (ex: *Famille Proche*, *Pro BSI*) pour isoler les réseaux d'utilisateurs.
- **`todo_groupe_membres`** : Table pivot d'étanchéité absolue gérée exclusivement par le Super-Admin (interdiction stricte de l'auto-invitation).
- **`todo_projets`** : Injection d'une clé étrangère `groupe_id` pour sceller chaque projet dans son cercle de confiance dès sa création.
- **`todo_notifications_lectures`** : Création de la table de persistance de lecture pour mémoriser les acquittements des utilisateurs (mécanisme anti-spam hors-session).

#### 🪄 Algorithmes de Filtrage (Chef d'Équipe)
- **Vue « Affectées à moi »** : Repensée pour isoler strictement la charge de travail personnelle (tâches créées privées + tâches assignées par l'équipe).
- **Vue « Voir toute l'équipe »** : Repensée comme un tableau de bord de délégation global (visibilité à 100 % des tâches partagées du projet actif).

#### 👤 Expérience Utilisateur (UX) & Identité
- **Badge de Connexion** : Intégration en en-tête du nom réel de l'utilisateur connecté (`portail_users.name`) pour sécuriser les tests d'intégration croisés.

#### 🗂️ Workflow d'Accueil Collaboratif (Spécifications validées)
- Conception de la cinématique du **Bandeau Flash éphémère** à l'ouverture de session pour notifier l'intégration à un nouveau projet partagé.
- Spécification de l'emplacement **« Point X »** en haut à droite avec hyperliens d'axes rapides pour naviguer instantanément entre les projets d'équipe.

# Journal des modifications (Changelog) — Central Todo

## — 2026-10-11
### V3.0.1
- **Droits Super-Admin** : Correction du bug de gestion des rôles qui bloquait la suppression en cascade des projets par l'administrateur principal.
- **Sécurité** : Le compte Administrateur contourne désormais le double verrou multi-utilisateur pour le nettoyage de la base de données de production, tandis que les profils utilisateurs standards restent strictement cloisonnés dans leur périmètre de données.
- **IHM** : Dynamisation du badge de version en haut à droite de l'interface via l'introduction de la constante PHP `VERSION_APP`.
- **Structure** : Création d'un fichier CHANGELOG.md pour suivi des versions.