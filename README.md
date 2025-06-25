# Système de Gestion des QCM

## Description
Application web de gestion de QCM permettant aux enseignants de créer et gérer des questionnaires à choix multiples, et aux étudiants de passer ces tests. Le système est multilingue (Français, Anglais, Espagnol, Arabe) et dispose d'une interface responsive.

## Fonctionnalités

### Interface Administrateur
- Gestion complète des étudiants (CRUD)
  - Ajout, modification, suppression d'étudiants
  - Visualisation détaillée des profils
  - Gestion des photos de profil
- Gestion des QCM
  - Création et modification de QCM
  - Attribution par filière et niveau
  - Ajout/modification de questions et réponses
  - Visualisation des statistiques détaillées
- Tableau de bord administratif
  - Statistiques globales des étudiants
  - Statistiques des QCM
  - Graphiques de répartition
  - Derniers résultats

### Interface Étudiant
- Passage des QCM
  - Interface intuitive de réponse aux questions
  - Chronomètre intégré
  - Barre de progression
  - Sauvegarde automatique des réponses
- Consultation des résultats
  - Historique des QCM passés
  - Notes obtenues
  - Temps de passage
- Tableau de bord personnalisé
  - Statistiques personnelles
  - QCM disponibles
  - Derniers résultats

### Caractéristiques Techniques
- Multi-langue (FR, EN, ES, AR)
- Interface responsive (Bootstrap 5)
- Sécurité
  - Sessions utilisateurs
  - Protection contre les injections SQL
  - Validation des données
- Base de données MySQL
- Bibliothèques utilisées :
  - Chart.js pour les graphiques
  - Font Awesome pour les icônes
  - jQuery pour les interactions AJAX

## Structure du Projet
```
QCM_Projet/
├── connexion.php          # Configuration de la base de données
├── index.php             # Page de connexion
├── header_simple.php     # En-tête pour les pages simples
├── header_identifier.php # En-tête avec identification
├── langues/             # Fichiers de traduction
│   ├── fr.php
│   ├── en.php
│   ├── es.php
│   └── ar.php
├── accueil.php          # Dashboard principal
├── passer_qcm.php       # Interface de passage de QCM
├── faire_qcm.php        # Processus de réponse au QCM
├── resultat_qcm.php     # Affichage des résultats
├── gestion_qcms.php     # Administration des QCM
├── gestion_etudiants.php # Gestion des étudiants
└── assets/             
    ├── css/
    ├── js/
    └── img/
```

## Installation

1. Cloner le dépôt :
```bash
git clone https://github.com/votre-username/qcm-project.git
```

2. Importer la base de données :
```bash
mysql -u username -p database_name < database.sql
```

3. Configurer la connexion à la base de données dans `connexion.php`

4. Démarrer un serveur web local (Apache/PHP)

## Configuration Requise
- PHP 7.4+
- MySQL 5.7+
- Serveur web (Apache recommandé)
- Extensions PHP :
  - PDO
  - PDO_MySQL
  - GD (pour les images)

## Sécurité
- Validation des entrées utilisateur
- Protection contre les injections SQL (requêtes préparées)
- Hashage des mots de passe
- Gestion des sessions sécurisées
- Contrôle d'accès basé sur les rôles

## Auteur
Ayoub LAOUAD

## Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
