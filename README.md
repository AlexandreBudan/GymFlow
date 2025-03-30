# Gymflow API

Gymflow est une API destinée à une future application mobile dédiée à la musculation et aux salles de sport, inspirée d'applications comme Oblyk ou SocialBoulder pour l'escalade. L'objectif est d'offrir une expérience immersive aux utilisateurs en leur permettant de visualiser en 3D les plans des salles de sport, d'accéder à une bibliothèque d'exercices détaillés avec tutoriels, et d'optimiser leur entraînement grâce à des recommandations personnalisées.

## Fonctionnalités principales

* Visualisation interactive en 3D des salles de sport
* Gestion des utilisateurs avec authentification JWT
* Accès à une bibliothèque complète d'exercices avec tutoriels vidéo
* Informations détaillées sur l'équipement disponible dans chaque salle
* Suivi des entraînements et recommandations personnalisées
* Documentation Swagger disponible à `https://localhost/api/doc`

## Démarrage rapide

1. Si ce n'est pas encore fait, [installez Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Exécutez `docker compose build --no-cache` pour construire des images fraîches
3. Exécutez `docker compose up --pull always -d --wait` pour configurer et démarrer l'API
4. Ouvrez `https://localhost` dans votre navigateur et [acceptez le certificat TLS auto-généré](https://stackoverflow.com/a/15076602/1352334)
5. Exécutez `docker compose down --remove-orphans` pour arrêter les conteneurs Docker.

## Générer une paire de clés

Pour générer la paire de clés nécessaire à l'authentification, exécutez les commandes suivantes :

```sh
mkdir -p config/jwt
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Problèmes connus

Lors de l'initialisation avec Docker, vous pourriez rencontrer des problèmes liés à l'extension `Vich/Uploader`. Si cela se produit, exécutez :

```sh
composer install
```

sur votre machine locale avant de redémarrer le conteneur.

**Profitez-en !**

## Licence

Gymflow API est disponible sous la licence MIT.

## Crédits

Développé par une communauté de passionnés de musculation et de développement web.

