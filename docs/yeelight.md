# Yeelight Wifi

## Configuration des lampes

Une présentation complète de la gamme est disponible ici : [Article de présentation](https://lunarok-domotique.com/plugins-jeedom/xiaomi-home-jeedom/yeelight-xiaomi-wifi-lamp/).

Dans l'application Yeelight vous devez activer l'**option de contrôle** sur réseau local. C'est un switch à activer dans les options de chaque ampoule/bandeau. Par ailleurs, il faut que les équipements soient sur le même réseau que Jeedom.

## Création des équipements

Un bouton de **scan** permet de créer automatiquement tous les équipements répondant au protocole Yeelight disponibles sur le réseau (uniquement celles qui n'existent pas déjà dans Jeedom, bien sûr).

## Commandes des équipements compatibles

Les lampes compatibles proposent les commandes suivantes par défaut : **Allumer**, **Eteindre**, **toggle**, **luminosité**, **enchainement**.

Certaines lampes ajoutent des commandes :

* **Yeelight Blanc** : pas d'autres commandes.
* **Desklamp** : température de blanc.
* **Yeelight RGB** : Online, Statut, Définir couleur RGB, Lumière de lune, Lumière de Soleil, Extinction programmée, Stop Enchainement, Information de luminosité, Température de blanc, Information de température de blanc, Mode, Définir couleur HSV, Couleur HSV, Définir saturation HSV, Saturation HSV, Couleur RGB, Rafraichir.
* **Bandeau RGB** : Online, Rafraichir, Statut, Définir couleur RGB, Lumière de lune, Lumière de Soleil, Extinction programmée, Température de blanc, Stop Enchainement, Information de luminosité, Mode, Définir couleur HSV, Couleur HSV, Définir saturation HSV, Saturation HSV, Couleur RGB.
* **Lampe de Chevet** (socle doré) : couleur RGB, température de blanc, mode jour, mode nuit, couleurs HSV.
* **Yeelight Ceiling** : température de blanc, mode jour, mode nuit.
* **Yeelight Ceiling 450 et 480** : température de blanc, mode jour, mode nuit.
* **Yeelight Ceiling 650** : température de blanc, mode jour, mode nuit, couleurs RGB.

### La commande enchainement

Une commande spéciale **enchainement** est créée. Elle a vocation à être utilisée dans un scénario uniquement, car on doit envoyer un contenu précis à la commande.
Voici un exemple commentée de cette commande :

> **`3 recover rgb,255,0,0,500,100-wait,400-rgb,255,255,0,500,100`**

* **`3`** : Définit le nombre de fois que la suite d'effets doit être appliquée avant de s'arrêter (0 veut dire illimité).
* **`recover`** : une des 3 options possibles pour la fin de l'enchaînement (`recover` = revient à l'état précédant l'enchaînement, `off` = s'éteint, `stay` = reste au statut de la fin de l'enchaînement)
* le troisième élément est la suite des états avec leur transition, il y a 4 possibles (attention : il ne faut pas mettre d'espace) :
  * **`hsv`** : paramètres (hue,saturation,duration=300,brightness=100).
  * **`rgb`** : paramètres (red,green,blue,duration=300,brightness=100).
  * **`temp`** : paramètres (degrees,duration=300,brightness=100).
  * **`wait`** : paramètre (duration=300).

Les nombres donnés pour **duration** ou **brightness** sont les maximums autorisés.

Il faut bien entrer l'enchainement avec des `-` entre chaque effet. Pour chacun, il doit y avoir son nom et tous les paramètres séparés par des virgules.
