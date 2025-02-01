# SF Events 🎇
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white) 
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![Javascript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![Jquery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-43853D?style=for-the-badge&logo=node.js&logoColor=whitee)

Bienvenue dans **SF Events**, une application web de gestion d'événements. 

Ce projet a été réalisé dans le cadre d'une formation en Mastère Ingénierie du Web à l'[ESGI](https://www.esgi.fr/).

# Table des matières 
- [SF Events 🎇](#sf-events-)
- [Table des matières](#table-des-matières)
- [Fonctionnalités](#fonctionnalités)
  - [Fonctionnalités présentes](#fonctionnalités-présentes)
    - [Authentification et gestion des utilisateurs](#authentification-et-gestion-des-utilisateurs)
    - [Gestion des événements](#gestion-des-événements)
    - [Interaction avec les événements](#interaction-avec-les-événements)
    - [Centre de notifications](#centre-de-notifications)
    - [Gestion des événements par le propriétaire](#gestion-des-événements-par-le-propriétaire)
    - [Administration](#administration)
    - [Sécurité de l'application](#sécurité-de-lapplication)
    - [Gestion des demandes de participation](#gestion-des-demandes-de-participation)
    - [Données de test](#données-de-test)
    - [Couverture par des tests](#couverture-par-des-tests)
    - [Pages d'erreur](#pages-derreur)
  - [Fonctionnalités manquantes](#fonctionnalités-manquantes)
    - [1. Système de mailing](#1-système-de-mailing)
    - [2. Responsive](#2-responsive)
    - [3. Websockets](#3-websockets)
      - [Problème rencontré](#problème-rencontré)
      - [Comment reproduire l'erreur ?](#comment-reproduire-lerreur-)
- [Installation 🛠](#installation-)
  - [Pré requis](#pré-requis)
  - [Guide d'installation](#guide-dinstallation)
- [Fixtures](#fixtures)
  - [User](#user)
  - [Type](#type)
  - [Event](#event)
  - [Comment](#comment)
- [Test](#test)
- [Dévelopeur 🧑‍💻](#dévelopeur-)

# Fonctionnalités

## Fonctionnalités présentes

### Authentification et gestion des utilisateurs
- Inscription d'un utilisateur  
- Connexion et déconnexion  

### Gestion des événements
- Consultation de la liste des événements  
  - Recherche et filtres disponibles  
- Création d'un événement  
- Participation à un événement  
  - **Événement privé** : demande de participation requise  
    - Le propriétaire peut accepter ou refuser  
  - **Événement public** : participation directe  

### Interaction avec les événements
- Commenter un événement  
- Se retirer d'un événement  

### Centre de notifications
Un utilisateur est notifié lorsque :  
- Il est invité à un événement  
- Il est exclu d'un événement  
- Un événement auquel il participe est supprimé  
- Sa demande de participation reçoit une réponse  
- Un nouveau commentaire est posté sur un événement auquel il participe  

### Gestion des événements par le propriétaire
Le propriétaire d'un événement peut :  
- Modifier ou supprimer son événement  
- Gérer les participants et les demandes de participation  
- Inviter des participants  

### Administration  
Un administrateur peut :  
- Supprimer un événement  
- Supprimer un utilisateur  
- Supprimer un commentaire  
- Définir les types d'événements  

### Sécurité de l'application  
- La gestion des accès repose sur une **logique de rôles**  
- Un **ensemble de voters** est utilisé pour affiner les permissions  

### Gestion des demandes de participation  
- La gestion des demandes est réalisée via le **composant Workflow de Symfony**, garantissant un suivi précis des statuts  

### Données de test  
- Des **fixtures** sont disponibles pour initialiser des données en environnement de développement  

### Couverture par des tests  
- L'application est couverte par des **tests automatisés** pour garantir la fiabilité et la stabilité des fonctionnalités  

### Pages d'erreur  
L'application propose des pages d'erreur adaptées :  
- **404** : Page non trouvée  
- **500** : Erreur serveur  
- Une page d'erreur par défaut  

## Fonctionnalités manquantes

Trois fonctionnalités n'ont pas été implémentées dans le projet :  

### 1. Système de mailing  

L'application ne dispose pas de **mailler**, ce qui signifie qu'aucun e-mail n'est envoyé (notifications, confirmations, etc.).

### 2. Responsive

L'application n'est pas **responsive**, elle n'est pas optimisée pour les appareils mobiles.

### 3. Websockets  
L'application n'utilise pas de **websockets**, ce qui implique que les notifications et autres mises à jour ne sont pas en temps réel et nécessitent un rafraîchissement manuel de la page.  

#### Problème rencontré  

Une tentative d'intégration des Websockets a été réalisée, mais une **erreur d'autorisation CORS** empêche la connexion au serveur WebSocket.  

#### Comment reproduire l'erreur ?  

Décommenter le bloc de code suivant dans le fichier `src/Controller/EventController.php` a la ligne 147. 

```php
$update = new Update(
    'event/' . $event->getId()
);
$hub->publish($update);
```

Décommenter le bloc de code suivant dans le fichier `assets/js/comment.js` à la ligne 11.

```javascript
const eventId = window.location.pathname.split('/').pop();
const url = new URL(window.location.href);
fetch(url.origin + `/mercure/subscribe/${eventId}`, { credentials: 'include' })
    .then(response => response.json())
    .then(data => {
        const jwt = data.jwt;
        const eventSource = new EventSource(
            `http://127.0.0.1:3000/.well-known/mercure?topic=event/${eventId}&jwt=${jwt}`
        );

        eventSource.onmessage = (event) => {
            console.log("Comment added");
        };
    })
    .catch(console.error);
```

Démarrer le serveur Mercure via Docker.
```bash
docker-compose up -d mercure
```

Lancer le serveur Symfony sans TLS.
```bash
symfony server:start --no-tls
```

L'erreur peut désormais être constatée dans la console du navigateur, lorsqu'un commentaire est ajouté à un événement.

# Installation 🛠

## Pré requis
Pour installer et exécuter le projet localement, vous devez disposer des éléments suivants :
  * PHP 8.O ou supérieur
  * Composer
  * NPM
  * Un moteur de base de données (MySQL, PostgreSQL, SQLite, etc.)

## Guide d'installation

Pour installer et exécuter le projet localement, suivez ces étapes :
  * Clonez le dépôt du projet sur votre machine.
```bash
git clone https://github.com/Buldozer42/sf_events.git
```
  * Accédez au répertoire du projet.

````bash
cd sf_events
````

  * Installez les dépendances.
```bash
composer install
npm install
```

 * Chargez le CSS et le Javascript.
```bash
npm run dev
```

  * Définissez les variables d'environnement dans un fichier `.env.local` à la racine du projet.
```bash
# .env.local

# Choissisez une valeur pour DATABASE_URL qui correspond à votre moteur de base de données.
DATABASE_URL="sqlite:///%kernel.project_dir%/<nom_de_votre_fichier>.db"

DATABASE_URL="mysql://<nom_utilisateur>:<mot_de_passe>@<adresse_ip>:<port>/<nom_base_de_données>?serverVersion=<version>&charset=utf8mb4"

DATABASE_URL="mysql://<nom_utilisateur>:<mot_de_passe>@<adresse_ip>:<port>/<nom_base_de_données>?serverVersion=<version>&charset=utf8mb4"

DATABASE_URL="postgresql://<nom_utilisateur>:<mot_de_passe>@<adresse_ip>:<port>/<nom_base_de_données>"
```

  * Créez la base de données.
```bash
php bin/console doctrine:database:create
```

  * Créez les tables de la base de données.
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

 * Peuplez votre base avec des données fictives. Une description des fixtures est disponible [ici](#fixtures).
```bash
php bin/console doctrine:fixtures:load
```
 * Lancez le serveur.
```bash
php -S localhost:8000 -t public 
# ou si vous avez symfony cli
symfony server:start
```

L'application est maintenant accessible à l'adresse [ci-dessous](http://localhost:8000).

# Fixtures

Les fixtures comportent des données fictives pour les entités suivantes :
  * User
  * Type
  * Event
  * Comment

## User

51 utilisateurs sont créés :
 * 1 administrateur
 * 50 utilisateurs normaux

L'utilisateur administrateur est le premier utilisateur créé.

Les mots de passe de tous les utilisateurs sont `A1<email de l'utilisateur>`.

Les autres champs sont générés aléatoirement.


## Type

10 types d'événements sont créés tous avec des noms générés aléatoirement.

## Event

25 événements sont créés :
 * Ils ont entre 10 et 100 participants maximum.
 * Ils ont 60% de chance d'être privés.
 * Leurs dates est comprise entre il y a 1 ans et dans 1 ans.
 * Leurs prix en compris entre 0.O et 100.O.
 * Leurs types sont choisis aléatoirement parmi les types créés.
 * Ils ont 80% de chance d'être visibles par tous les utilisateurs.
 * Ils ont entre 0 et le nombre maximum de participants.
 * Le reste des champs sont générés aléatoirement.

## Comment

Chaque événement a entre 0 et 5 commentaires :
 * Les commentaires sont créés par des invitées aléatoires.
 * Ils ont été postés entre une semaine avant la date de l'événement et aujourd'hui ou la date de l'événement.
 * Leurs contenus sont générés aléatoirement.

# Test

Un ensemble de tests unitaires et fonctionnels est disponible dans le projet.

Ils ne sont pas exhaustifs, mais ils couvrent les principales fonctionnalités de l'application.

Voici la couverture des tests actuelle :
- Classes: 45.45% (15/33)
- Methods: 82.32% (149/181)
- Lines:   81.37% (786/966)

Pour les exécuter, assurez-vous de plusieurs choses :
  * Avoir activé XDebug dans votre fichier `php.ini`.
```bash
<!-- php.ini -->
[XDebug]
xdebug.mode=coverage
xdebug.start_with_request=yes
zend_extension = xdebug
```
  * Avoir definit les variables d'environnement dans un fichier `.env.test` à la racine du projet.
```bash
# .env.test
KERNEL_CLASS='App\Kernel'

# Choissisez une valeur pour DATABASE_URL qui correspond à votre moteur de base de données.
DATABASE_URL="sqlite:///%kernel.project_dir%/<nom_de_votre_fichier>.db"

DATABASE_URL="mysql://<nom_utilisateur>:<mot_de_passe>@<adresse_ip>:<port>/<nom_base_de_données>?serverVersion=<version>&charset=utf8mb4"

DATABASE_URL="mysql://<nom_utilisateur>:<mot_de_passe>@<adresse_ip>:<port>/<nom_base_de_données>?serverVersion=<version>&charset=utf8mb4"

DATABASE_URL="postgresql://<nom_utilisateur>:<mot_de_passe>@<adresse_ip>:<port>/<nom_base_de_données>"
```

Pour exécuter les tests, commencez par créer la base de données de test.
```bash
php bin/console doctrine:database:create --env=test
```

Puis, effectuez les migrations.
```bash
php bin/console make:migration --env=test
php bin/console doctrine:migrations:migrate --env=test
```

Vous pouvez maintenant exécuter les tests.
```bash
# Pour effectuer un test donné
php bin/phpunit .<chemin vers le test>

# Pour obtenir un rapport de couverture complet
php bin/phpunit --coverage-text

# Pour obtenir un rapport de couverture pour une classe donnée
php bin/phpunit --filter <nom de la classe> --coverage-text
```

# Dévelopeur 🧑‍💻
- [Noé Garnier (Buldozer42)](https://www.github.com/Buldozer42)  
