# Xiaomi Home

## Presentation

A complete presentation of the plugin is avalaible on this article : [Article de présentation, in french](https://lunarok-domotique.com/plugins-jeedom/xiaomi-home/).

The plugin allows Jeedom to integrate 3 protocols used by Xiaomi devices :

### **Zigbee protocol with Xiaomi Aqara**
La Xiaomi Aqara Home Gateway (aussi appelée **passerelle Xiaomi**) permet d'utiliser différents capteurs Zigbee de Xiaomi. [See doc Aqara](aqara.md).

### **Lights Yeelight**

Les lampes qui supportent le protocole Yeelight Wifi. [See doc Yeelight](yeelight.md).

### **Wifi Appliances**

Il existe aussi un protocole Xiaomi commun à beaucoup de périphériques Wifi. Ce protocole est implémenté dans le plugin et rend compatibles les appliances l'utilisant. [See doc Wifi Appliances](wifi.md).

> Et les périphériques Bluetooth ?
>
> Les produits Xiaomi Bluetooth ne sont pas pris en charge par ce plugin. Néanmoins, le plugin **BLEA** (Bluetooth Advertisement) disponible sur le market de Jeedom, en gère une bonne partie, dont :
>
> * bracelets Miband (au moins pour la présence sur le 2),
> * lampe de chevet (1ère génération, base grise),
> * balance (1ère génération avec le poids seulement).

## FAQ

>Quel type de gateway Xiaomi est supportée ?

La "Aqara" avec anneau led pour le Zigbee. Il faut bien la paramétrer en **mode développeur** pour y accéder localement. Pour cela, il faut un firmware au moins en **1.4** .

>Je n'arrive pas à passer ma gateway Aqara en mode local ?

Il faut également bien utiliser l'application sur smartphone **Mi Home** en **anglais** et réglée sur **China Mainland** si on rencontre des problèmes d'inclusion.

>Une information de mes capteurs ne remonte pas ?

Si vous n'avez pas de commande équivalente créée dans Jeedom, l'API n'expose peut-être pas cette information. Il faut attendre peut être une mise à jour du firmware.
Toute les informations ne remontent pas à la même fréquence, par exemple la batterie peut n'être envoyée que quotidiennement.

>Je n'arrive pas à contrôler mes capteurs Aqara (couleur et son gateway, prise ...) ?

Vous devez bien saisir la **clé** de la gateway, visible dans Mi Home, sur l'équipement gateway dans Jeedom.

>Mes commandes sur capteurs Aqara qui fonctionnaient auparavant, ne fonctionnent plus ?

Vérifiez si la clé a changé dans Mi Home, cela peut se produire après une mise à jour du firmware par exemple.

>Jeedom ne parvient pas à découvrir la gateway et les capteurs ?

Vérifiez que votre routeur laisse bien passer les paquets broadcast du réseau Wifi vers l'ethernet par exemple.

>Combien de capteurs au maximum peut-il y avoir sur une Gateway Aqara ?

Les remontées utilisateurs indiquent **31 capteurs + la Gateway**. Au delà il faut supprimer l'appairage d'un capteur pour en remettre un. Le plugin supporte plusieurs Gateways.

>Quelles ampoules sont supportées par ce plugin ?

Les **Yeelight Wifi** uniquement (la liste est fournie dans la [documentation](yeelight.md)).

>Certaines ampoules Yeelight ne remontent pas dans Jeedom ?

Les ampoules Yeelight Wifi doivent toutes être paramétrées avec le mode **développeur** pour être joignables sur le réseau local.
Pour ceci, il vous faut installer l'application Yeelight (et non pas seulement Mi Home), renseigner votre identifiant/mot de passe, aller dans chaque équipement, accéder aux paramètres via les ' ... ' et activer le 'Developer Mode'.
Note : Il n'est pas nécessaire de changer la langue de l'application.

>Je n'arrive pas à contrôler une appliance, comme le robot, par exemple ?

Vérifiez que vous avez bien obtenu le **token**, pour les appliances qui ne le fournissent pas, des tutos sont présents dans la [doc](wifi.md).

## Changelog

[Voir la page dédiée](changelog.md).
