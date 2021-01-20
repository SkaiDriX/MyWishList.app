# MyWishList.app
Projet PHP - 2e année de DUT Informatique

## Installation

Utilisez [composer](https://getcomposer.org/) pour installer ce projet.

```bash
git clone git@github.com:SkaiDriX/MyWishList.app.git
cd MyWishList.app
composer install
```
Il faut maintenant créer un fichier de configuration nommé **conf.ini** dans le répertoire conf/ pour pouvoir établir la connexion avec la base de donnée.
Ce fichier doit être de la forme suivante :

```bash
driver=?
username=?
password=?
host=?
database=?
charset=utf8
collation=utf8_unicode_ci
```

Avec les valeurs suivantes :

| Paramètre     | Description                                   |
| :------------:|:---------------------------------------------:|
| driver        | Le driver utilisé (exemple : mysql)           |
| host          | Le serveur de votre BDD (exemple : localhost) |
| database      | Nom de la base de donnée (exemple : wishlist) |
| username      | Nom d'utilisateur de la BDD (exemple : root)  |
| password      | Mot de passe de la BDD (exemple : root)       |

Pour terminer, veuillez importer le fichier **base.sql** (présent dans le répertoire conf/) dans votre base de donnée pour charger les tables requises.

## Jeu de test

Vous pouvez tester le projet sur : https://webetu.iutnc.univ-lorraine.fr/www/brigue2u/mywishlist/
Quelques listes, messages, objets on été pré-crées.

Un compte pré-créé :
- nom d'utilisateur : azerty123
- mot de passe : azerty123

Voici quelques lien de liste :

- Anniversaire de Bob : https://webetu.iutnc.univ-lorraine.fr/www/brigue2u/mywishlist/liste/a5e93b7ef126
MOFICATION : https://webetu.iutnc.univ-lorraine.fr/www/brigue2u/mywishlist/liste/a5e93b7ef126/edit/62ba67af8e09

- Soirée du 02/02/2021 : https://webetu.iutnc.univ-lorraine.fr/www/brigue2u/mywishlist/liste/4fea9bf46645
MOFICATION : https://webetu.iutnc.univ-lorraine.fr/www/brigue2u/mywishlist/liste/4fea9bf46645/edit/23ea0d87adf8

## Quelques informations par rapport au projet

Voici quelques informations à savoir par rapport à notre fonctionnement :
- Tout d'abord comme le sujet nous le demande, nous sommes parti du principe que le créateur d'une liste ne change jamais de navigateur et ne supprime pas ses cookies.
- Lorsque l'on créer une liste, il faut garder le token publique ET le token d'édition, car pour accéder à la page de modification l'url est de la forme /liste/{tokenPublique}/edit/{tokenEdition}

- Lorsqu'une liste n'est pas expirée, le créateur ne peut pas voir les messages de sa liste et ne peut pas voir les réservations sur ses items (il voit uniquement le status, pas l'identité de la personne ni le message associé)

- Un utilisateur normal (non créateur de la liste) verra quand-à lui les messages et réservations effectuées.

- Il y a aussi un système de compte utilisateur, celui-ci ne sert à rien pour le moment dans le projet mais c'était juste histoire de le mettre en place et montrer que l'on savait le faire. Lorsqu'une personne est connecté : c'est son nom d'utilisateur qui sera utilisé pour les messages sur les listes et pour les réservations. Dans le cas où la personne n'est pas connecté, alors on gére son nom d'utilisateur via un cookie.


## Les fonctionnalités

### Participant

- [X] *Afficher une liste de souhaits* (Sangoan, Yoan)
- [X] *Afficher un item d'une liste* (Matthieu, Victor)
- [X] *Réserver un item* (Matthieu, Victor)
- [X] *Ajouter un message avec sa réservation* (Matthieu, Victor)
- [X] *Ajouter un message sur une liste* (Sangoan, Yoan)

### Créateur
- [X] *Créer une liste* (Sangoan, Yoan)
- [X] *Modifier les informations générales d'une de ses listes* (Sangoan, Yoan)
- [X] *Ajouter des items* (Matthieu, Victor)
- [X] *Modifier un item*  (Matthieu, Victor)
- [X] *Supprimer un item* (Matthieu, Victor)
- [X] *Rajouter une image à un item* (Matthieu, Victor)
- [X] *Modifier une image à un item* (Matthieu, Victor)
- [X] *Supprimer une image d'un item* (Matthieu, Victor)
- [X] *Partager une liste* (Sangoan, Yoan)
- [X] *Consulter les réservations d'une de ses listes avant échéance* (Sangoan, Yoan)
- [X] *Consulter les réservations et messages d'une de ses listes après échéance* (Sangoan, Yoan)

### Extensions
- [X] *Créer un compte* (Sangoan) 
- [X] *S'authentifier* (Sangoan) 
- [X] *Se déconnecter* (Sangoan) 
- [ ] *Modifier son compte* 
- [X] *Rendre une liste publique* (Sangoan, Yoan)
- [X] *Afficher les listes de souhaits publiques* (Sangoan, Yoan)
- [ ] *Créer une cagnotte sur un item*
- [ ] *Participer à une cagnotte*
- [ ] *Uploader une image*
- [ ] *Créer un compte participant*
- [ ] *Afficher la liste des créateurs*
- [X] *Supprimer son compte* (Sangoan) 
- [ ] *Joindre les listes à son compte*

## Les contributeurs
**BRIGUÉ Sangoan** - S3D 

**NOUGUÉ-RUIZ Yoan** - S3D 

**GALANTE Matthieu** - S3D 

**NOËL Victor** - S3D 
