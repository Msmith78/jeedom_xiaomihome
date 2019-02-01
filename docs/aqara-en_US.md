# Le protocole zigbee Xiaomi Aqara

### Ajout de la passerelle Xiaomi Aqara à Jeedom

Une présentation complète de la gamme est disponible ici : [Article de présentation](https://lunarok-domotique.com/plugins-jeedom/xiaomi-home-jeedom/aqara-lumi-xiaomi-smart-home-security/).

La passerelle Xiaomi Aqara est nécessaire pour activer l'API locale, afin que Jeedom puisse communiquer avec ses différents équipements.

Pour cela, il faut :
* ajouter la passerelle dans l'application **Mi Home**,
* être sur le serveur **China Mainland**,
* utiliser la langue **Chinese** (oui, du coup des écrans peuvent contenir beaucoup de caractères illisibles à moins de maîtriser le mandarin...),
* cliquer sur la passerelle pour la sélectionner,
* cliquer sur les "...",
* cliquer sur **about**,
* vous avez alors un **numéro de version**, cliquez dessus de façon répétée jusqu'au message (oui en chinois ça n'aide pas :)) et deux nouvelles options apparaissent,
* choisissez la première option (nouvellement apparue),
* activer le bouton switch pour qu'il passe en vert,
* le mode local est actif et votre passerelle est accessible sur votre réseau local.

*Attention* : Sur **iOS** (iPhone), dans la page **about**, il n'y a pas de **numéro de version**. Vous devez cliquer 5 fois dans la partie blanche pour continuer.

*Attention* : Pour pouvoir envoyer des commandes vers la passerelle, vous devez renseigner son mot de passe sur sa page équipement, dans Jeedom (**Plugins->Protocole domotique->Xiaomi Home**, puis cliquez sur la passerelle que vous avez au préalable créée). Ce mot de passe est visible sur la page où l'on active le **mode développeur**, dans l'application Mi Home.

### Création des équipements dans Jeedom

Une fois que votre **passerelle Xiaomi** est ajoutée dans Jeedom, les équipements Aqara seront créés automatiquement. Si cela vous semble long, deux choses à savoir :
* Pour les **capteurs** Aqara, c'est la remontée d'information qui déclenche la création de l'équipement dans Jeedom. La fréquence varie donc en fonction du modèle et de ses caractéristiques. (par exemple : soufflez sur un capteur de température permettra de changer la valeur de la température et remontera donc l'information)
* Pour les **interrupteurs** et les **boutons** Aqara, il peut être nécessaire de les activer pour avoir la création des commandes. (par exemple : appuyez sur un des boutons)

### Commandes des équipements compatibles

Les équipements actuellement compatibles fournissent les informations suivantes :

* **Passerelle** : anneau RGB (avec couleur et intensité), capteur de luminosité, jouer les sons enregistrés (0 à 8, 10 à 13, 20+ correspondent aux sons par défaut dans Mi Home, 10000 pour éteindre, 10001 et plus pour les sons personnalisés). [Voir la présentation](https://lunarok-domotique.com/2017/03/mi-smart-gateway-domotique-jeedom/).
* **Détecteur de mouvement** : Commande statut binaire pour un mouvement détecté, temps d'absence de mouvement, capteur de luminosité.
* **Détecteur d'ouverture** : Commande statut binaire pour une ouverture, temps d'absence de fermeture.
* **Capteur température/humidité** : Information de température, humidité (et pression pour la v2).
* **Bouton Switch** : Commande clic avec pour valeurs click, double_click, long_click_press, long_click_release.
* **Prise** et **prise murale** : Commande statut binaire avec on et off, état d'utilisation et consommation.
* **Interrupteur mural** : Commande statut pour chaque interrupteur (click, double_click) et si double, une commande qui donne l'appui simultané.
* **Interrupteur encastré** (y compris celui avec neutre) : Commande statut binaire avec on et off pour chaque interrupteur.
* **Cube** : Commande statut du mouvement avec pour valeur move, tap_twice, shake_air, alert, flip90, flip180, free_fall, rotate_right, rotate_left et une commande Rotation avec la valeur en degré du mouvement. [Présentation](https://lunarok-domotique.com/2017/03/aqara-xiaomi-magic-controller-utilisation-dans-jeedom/)
* **Détecteur de fumée** : Commande Alarme (binaire), Densité fumée (numérique), Visibilité Capteur Optique (numérique %).
* **Détecteur de gaz** : Commande Alarme (binaire), Densité (numérique).
* **Détecteur d'eau** : Commande Statut (binaire).
* **Moteur pour rideau** : Commande statut (string), position (string) et les commandes actions associées.

### D'autres commandes communes

Pour tous ces équipements, une commande **Rafraichir** permet de forcer la mise à jour de ses informations dans Jeedom.

L'API fournit également l'**état des piles** des équipements. C'est le voltage de la pile qui est retourné. Avec une fiche technique de l'API indiquant qu'elle sera au maximum de 3.2V et logiquement à 2.8V la pile n'est plus utilisable. Dans les faits, ce sont des piles 3V, voici donc les informations fournies dans Jeedom :

* **Voltage** : en commande non visible sur le dashboard par défaut,
* **Batterie** : en statut standard, mais aussi en commande non visible. Sa valeur est considérée à 100% si supérieure ou égale à 3V, puis décroit à 0% pour 2.8V.
* **Type de pile** : Le type de pile est indiqué pour chaque équipement sur le récapitulatif.