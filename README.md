# BileMo

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/0b2d7d5f13204d249a14d99221906f25)](https://www.codacy.com/gh/JoDeyme/BileMo/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=JoDeyme/BileMo&amp;utm_campaign=Badge_Grade)

# BileMo API

Création d'une API Rest BileMo dans le cadre du projet 7 d'OpenClassrooms

## Environnement utilisé durant le développement

* Symfony 6.1
* Composer 2.3.5
* PHP 8.1
* MySQL 8.0.27

## Installation

Exécutez la ligne de commande suivante pour télécharger le projet dans le répertoire de votre choix:
```
git clone https://github.com/JoDeyme/BileMo.git
```
Installez les dépendances en exécutant la commande suivante:
```
composer install
```
## Base de données
Modifier la connexion à la base de données dans le fichier .env.
```
mysql://root:@127.0.0.1:3306/bilemo?serverVersion=8.0.27&charset=utf8mb4
```
Créer une base de données:
```
php bin/console doctrine:migrations:migrate
```
Créez la structure de la base de données:
```
php bin/console doctrine:migrations:migrate
```
Chargez les données initiales:
```
php bin/console doctrine:fixtures:load
```
## Lancez l'application
Lancez l'environnement d'exécution Apache / Php en utilisant:
```
php bin/console server:run
```
## Documentation API Nelmio
```
https://127.0.0.1:8000/api/doc
```
## Compte d'admin par défaut
```
{
  "username": "admin",
  "password": "password",
}
```

## Comptes d'utilisateurs de test par défaut

```
{
  "username": "utilisateur{n}",
  "password": "password",
}
```