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
driver= ?
username= ?
password= ?
host= ?
database= ?
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

## Les fonctionnalités

### Participant

- [X] *Afficher une liste de souhaits* (Sangoan, Yoan)
- [ ] *Afficher un item d'une liste* (Matthieu, Victor)
- [ ] *Réserver un item* (Matthieu, Victor)
- [ ] *Ajouter un message avec sa réservation* (Matthieu, Victor)
- [X] *Ajouter un message sur une liste* (Sangoan, Yoan)

### Créateur
- [X] *Créer une liste* (Sangoan, Yoan)
- [X] *Modifier les informations générales d'une de ses listes* (Sangoan, Yoan)
- [ ] *Ajouter des items* (Matthieu, Victor)
- [ ] *Modifier un item*  (Matthieu, Victor)
- [ ] *Supprimer un item* (Matthieu, Victor)
- [ ] *Rajouter une image à un item* (Matthieu, Victor)
- [ ] *Modifier une image à un item* (Matthieu, Victor)
- [ ] *Supprimer une image d'un item* (Matthieu, Victor)
- [X] *Partager une liste* (Sangoan, Yoan)
- [ ] *Consulter les réservations d'une de ses listes avant échéance* (Sangoan, Yoan)
- [ ] *Consulter les réservations et messages d'une de ses listes après échéance* (Sangoan, Yoan)

### Extensions
- [ ] *Créer un compte* 
- [ ] *S'authentifier* 
- [ ] *Modifier son compte* 
- [X] *Rendre une liste publique* (Sangoan, Yoan)
- [X] *Afficher les listes de souhaits publiques* (Sangoan, Yoan)
- [ ] *Créer une cagnotte sur un item*
- [ ] *Participer à une cagnotte*
- [ ] *Uploader une image*
- [ ] *Créer un compte participant*
- [ ] *Afficher la liste des créateurs*
- [ ] *Supprimer son compte*
- [ ] *Joindre les listes à son compte*

## Les contributeurs
**BRIGUÉ Sangoan** - S3D 

**NOUGUÉ-RUIZ Yoan** - S3D 

**GALANTE Matthieu** - S3D 

**NOËL Victor** - S3D 
