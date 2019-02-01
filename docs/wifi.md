# Appliances Wifi Xiaomi

## Création des équipements Wifi

Pour les équipements Wifi supplémentaires supportés, il faut faire un ajout manuel. Ces informations sont nécessaires :
* Il faut renseigner leur **adresse IP**.
* Sélectionner le **type d'équipement** dans le menu déroulant.
* Renseigner le **token** : Pour l'obtenir, il suffit de cliquer sur le bouton bleu "Récupérer les infos". Dans le cas où le token récupéré est une suite de 0 seulement ou de f seulement, il faut alors le récupérer manuellement à l'aide d'une des procédures ci-dessous.

## Récupérer le token d'un équipement manuellement

Trois méthodes existent :
* la première avec l'outil **[Mi Toolkit](https://github.com/ultrara1n/MiToolkit)** qui va récupérer tous les tokens dans votre application Mi Home. Cela nécessite un Android avec le mode Debug USB activable.
* Les deux autres sont basées sur une **récupération de la base de données de Mi Home**, une pour **Android**, l'autre pour **iPhone**.

### 1ère méthode : MiToolkit

Pour télécharger l'outil : [Cliquez ici](https://github.com/ultrara1n/MiToolkit/releases).
Il vous faut aussi une version Mi Home compatible, les dernières posent problème : [ApkMirror](https://www.apkmirror.com/apk/xiaomi-inc/mihome/mihome-5-0-19-release/)

* Activez **ADB** sur votre téléphone (dans les options développeur).
* Lancez le fichier **MiToolkit.exe** en tant qu'admin, sur votre ordinateur.
* Passez l'application en **anglais** via le menu avec le drapeau allemand. L'application se ferme, il faut la **relancer**.
* Choisissez **Extract Token**.
* Choisissez **Extract Token** à nouveau. Laissez-le faire la sauvegarde sur le PC et **ne mettez pas de mot de passe**.
* Les **tokens** apparaissent dans la fenêtre.

### 2ème méthode (Android) : aSQLiteManager

Via **aSQLiteManager**, on peut ouvrir la base de Mi Home. Attention, un téléphone rooté peut être nécessaire (merci _Gouzou_ pour la technique).

Les tokens sont dans la table **devicerecord** dans `/data/data/com.xiaomi.smarthome./databases/miio2.db`.

Ce fichier, si il est transférable sur PC, devrait pouvoir être édité avec d'autres logiciels également.

### 3ème méthode (iPhone)

Merci _pierre_ pour cette technique :

* Faites une **sauvegarde** de l'iPhone avec iTunes.
* Ouvrez la sauvegarde avec **[iPhoneBackup Viewer](http://www.imactools.com/iphonebackupviewer/)**. Si votre sauvegarde est cryptée, il faudra passer par la version payante.
* Dans `Raw Data/com.xiaomi.mihome`, il y a plusieurs fichiers `.sqllite`, il faut extraire `votreuserid_mihome.sqlite`.
* Ouvrez le fichier **sqlite** avec un viewer sqlite, par exemple **[DB Browser](http://sqlitebrowser.org)**.
* Cliquez sur **Ouvrir une base de données** puis **Parcourir les données** et enfin choisissez la table **ZDEVICE**.
* Tout à droite, il doit y avoir une colonne **ZTOKEN** avec tous les tokens de vos périphériques Xiaomi.

## Configuration des équipements Wifi

Cette section traite des équipements Wifi additionnels. Il n'est pas question de Yeelight ou de la gateway Aqara.

* **Xiaomi Mi Robot Vacuum** : online, statut, batterie, aspiration (force + slider, attention au delà de 77 vous dépassez le mode turbo), surface nettoyée, durée nettoyage, état des erreurs, puissance aspiration, démarrer, arrêter, pause, retour socle, fonction spot, "où es-tu ?", rafraichir.
* **Xiaomi Smart Mi Air Purifier (y compris Pro ou V2 avec écran)** : statut, qualité d'air, humidité, température, filtre, vitesse, buzzer (on/off), led (action dessus aussi), démarrer/arrêter (avec les différents modes disponibles).
* **Xiaomi Smart Ultrasonic Humidifier** : statut, mode, humidité, humidité cible (+slider de set), température, buzzer (statut + activation), led (statut + activation), démarrer/arrêter (avec les différents modes disponibles).
* **Xiaomi Smart Air Quality Monitor PM2.5 Detector** : qualité d'air, batterie, rafraichir.
* **Xiaomi Mi Power Strip Wifi** : on, off, statut, température, Intensité, puissance, rafraichir.
* **Xiaomi Mi Power Plug Wifi** : on, off, statut, utilisation, voltage, charge voltage, charge puissance, puissance consommée, rafraichir.
* **Xiaomi Philips Eyecare Smart Lamp** : statut, on/off, luminosité (+slider), eyecare (statut, scènes + différents modes disponibles).
* **Xiaomi Philips Ceiling** : statut, on/off, luminosité (+slider), couleur de blanc (info+cmd), Auto CCT (statut+cmd), scènes (statut + activation scènes).
* **Xiaomi Philips E27** : statut, on/off, luminosité (+slider), couleur de blanc (info+cmd), Auto CCT (statut+cmd), scènes (statut + activation scènes).
* **Xiaomi Mi Electric Rice Cooker** : non implémenté en l'état.
* **Ventilateur** : statut, température, humidité, buzzer (statut + activation), led (statut + activation), démarrer/arrêter (avec les différents modes disponibles), info vitesse et vitesse naturelle, rotation (et les commandes pour la paramétrer, tourner).
