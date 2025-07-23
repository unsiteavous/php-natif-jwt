# Gérer les JWT en PHP natif

Nous avons parfois besoin que nos applications soient contactables par API, mais nous voulons garder le contrôle sur les infos envoyées : seules des personnes autorisées peuvent recevoir des infos.

Grâce au JSON Web Token, c'est possible. Je ne rentre pas dans les détails de ce que c'est et de comment ça marche, vous retrouverez tout cela sur le web.

Ce qu'il faut retenir, c'est que le back a pour mission de donner un token au front quand il valide une connexion, et que lorsque le front le recontactera plus tard le back en fournissant ce token, il vérifie si ce token est valide avant de faire quoi que ce soit d'autre.

Cela remplace les sessions côté serveur.

## Conditions d'exercice
dans cet exercice, nous allons utiliser [firebase/php-jwt](https://github.com/firebase/php-jwt). 

Leur documentation est très bien faite et nous permet de mettre facilement en place ce genre de connexion.

C'est parti pour le cours 1.