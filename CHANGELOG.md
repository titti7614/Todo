# Journal des modifications (Changelog) — Central Todo

## — 2026-10-11
### V3.0.1
- **Droits Super-Admin** : Correction du bug de gestion des rôles qui bloquait la suppression en cascade des projets par l'administrateur principal.
- **Sécurité** : Le compte Administrateur contourne désormais le double verrou multi-utilisateur pour le nettoyage de la base de données de production, tandis que les profils utilisateurs standards restent strictement cloisonnés dans leur périmètre de données.
- **IHM** : Dynamisation du badge de version en haut à droite de l'interface via l'introduction de la constante PHP `VERSION_APP`.
- **Structure** : Création d'un fichier CHANGELOG.md pour suivi des versions.