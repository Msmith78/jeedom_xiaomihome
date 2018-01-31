<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('xiaomihome');
sendVarToJS('eqType', 'xiaomihome');
$eqLogics = eqLogic::byType('xiaomihome');
$eqLogicsBlea = array();
if (class_exists('blea')){
  $eqLogicsBlea = eqLogic::byType('blea');
}
?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>

        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        echo '<legend><i class="fa fa-home"></i>  Aqara</legend>';
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('type') == 'aquara') {
            echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
          }
        }
        echo '<legend><i class="icon jeedom2-bright4"></i> Yeelight</legend>';
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('type') == 'yeelight') {
            echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
          }
        }
        echo '<legend><i class="fa fa-wifi"></i>  Wifi</legend>';
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('type') != 'aquara' && $eqLogic->getConfiguration('type') != 'yeelight') {
            echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
          }
        }
        echo '<legend><i class="fa fa-bluetooth"></i>  Bluetooth</legend>';
        foreach ($eqLogicsBlea as $eqLogic) {
          if ($eqLogic->getConfiguration('xiaomi',0) == 1){
            echo '<li title="Juste un listing : à configurer via Blea"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
          }
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <center>
          <i class="fa fa-plus-circle" style="font-size : 6em;color:#00979c;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#00979c"><center>Ajouter</center></span>
      </div>
      <div class="cursor eqLogicAction discover" data-action="scanyeelight" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
        <center>
          <i class="icon jeedom2-bright4" style="font-size : 6em;color:#767676;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Scan Yeelight}}</center></span>
      </div>
      <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
        <center>
          <i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
      </div>
      <div class="cursor" id="bt_healthxiaomihome" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <center>
          <i class="fa fa-medkit" style="font-size : 6em;color:#767676;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Santé}}</center></span>
      </div>
    </div>

    <legend><i class="fa fa-home"></i>  {{Mes Aqara}}</legend>
    <?php
    $status = 0;
    foreach ($eqLogics as $eqLogicGateway) {
      if ($eqLogicGateway->getConfiguration('type') == 'aquara' && $eqLogicGateway->getConfiguration('model') == 'gateway') {
        echo '<legend>' . $eqLogicGateway->getHumanName(true) . '</legend>';
        echo '<div class="eqLogicThumbnailContainer">';
        $status = 1;
        $opacity = ($eqLogicGateway->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogicGateway->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        $online = $eqLogicGateway->getCmd('info','online');
        if (is_object($online)){
          $onlinevalue= $online->execCmd();
        } else {
          $onlinevalue = '';
        }
        if ($onlinevalue !== '' && $onlinevalue == 0){
          echo '<i class="fa fa-times" style="float:right" title="Offline"></i>';
        }
        echo "<center>";
        if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogicGateway->getConfiguration('model') . '/' . $eqLogicGateway->getConfiguration('model') . '.png')) {
          echo '<img src="plugins/xiaomihome/core/config/devices/' . $eqLogicGateway->getConfiguration('model') . '/' . $eqLogicGateway->getConfiguration('model') . '.png' . '" height="105" width="95" />';
        } else {
          echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
        }
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogicGateway->getHumanName(true, true) . '</center></span>';
        echo '</div>';
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('type') == 'aquara' && $eqLogic->getConfiguration('model') != 'gateway' && $eqLogic->getConfiguration('gateway') == $eqLogicGateway->getConfiguration('gateway')) {
            $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
            echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
            if ($onlinevalue !== '' && $onlinevalue == 0){
              echo '<i class="fa fa-times" style="float:right" title="Offline"></i>';
            }
            echo "<center>";
            if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png')) {
              echo '<img src="plugins/xiaomihome/core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png' . '" height="105" width="95" />';
            } else {
              echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
            }
            echo "</center>";
            echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
            echo '</div>';
          }
        }
        echo '</div>';
      }
    }
    if ($status == 0) {
      echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Aucun équipement Aqara détecté. Démarrez un node pour en ajouter un.}}</span></center>";
    }
    ?>

    <legend><i class="icon jeedom2-bright4"></i>  {{Mes Yeelight}}</legend>
    <?php
    $status = 0;
    foreach ($eqLogics as $eqLogic) {
      if ($eqLogic->getConfiguration('type') == 'yeelight') {
        if ($status == 0) {echo '<div class="eqLogicThumbnailContainer">';}
        $status = 1;
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        $online = $eqLogic->getCmd('info','online');
        if (is_object($online)){
          $onlinevalue= $online->execCmd();
        } else {
          $onlinevalue = '';
        }
        if ($onlinevalue !== '' && $onlinevalue == 0){
          echo '<i class="fa fa-times" style="float:right" title="Offline"></i>';
        }
        echo "<center>";
        if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png')) {
          echo '<img src="plugins/xiaomihome/core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png' . '" height="105" width="95" />';
        } else {
          echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
        }
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
    }
    if ($status == 1) {
      echo '</div>';
    } else {
      echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Aucun équipement Yeelight détecté. Lancez un Scan Yeelight.}}</span></center>";
    }
    ?>

    <legend><i class="fa fa-wifi"></i>  {{Mes Xiaomi Wifi}}</legend>
    <?php
    $status = 0;
    foreach ($eqLogics as $eqLogic) {
      if ($eqLogic->getConfiguration('type') == 'wifi') {
        if ($status == 0) {echo '<div class="eqLogicThumbnailContainer">';}
        $status = 1;
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        $online = $eqLogic->getCmd('info','online');
        if (is_object($online)){
          $onlinevalue= $online->execCmd();
        } else {
          $onlinevalue = '';
        }
        if ($onlinevalue !== '' && $onlinevalue == 0){
          echo '<i class="fa fa-times" style="float:right" title="Offline"></i>';
        }
        echo "<center>";
        echo '<img src="plugins/xiaomihome/core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png' . '" height="105" width="95" />';                echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
    }
    if ($status == 1) {
      echo '</div>';
    } else {
      echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Aucun équipement Xiaomi Wifi. Ajoutez-en un.}}</span></center>";
    }
    ?>

    <legend><i class="fa fa-bluetooth"></i>  {{Mes Xiaomi Bluetooth}}</legend>
    <?php
    $status = 0;
    foreach ($eqLogicsBlea as $eqLogic) {
      if ($eqLogic->getConfiguration('xiaomi',0) == 1){
        if ($status == 0) {echo '<div class="eqLogicThumbnailContainer">';}
        $status = 1;
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard" title="Juste un listing : à configurer via Blea" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        $alternateImg = $eqLogic->getConfiguration('iconModel');
        if (file_exists(dirname(__FILE__) . '/../../../blea/core/config/devices/' . $alternateImg . '.jpg')) {
          echo '<img class="lazy" src="plugins/blea/core/config/devices/' . $alternateImg . '.jpg" height="105" width="95" />';
        } elseif (file_exists(dirname(__FILE__) . '/../../../blea/core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg')) {
          echo '<img class="lazy" src="plugins/blea/core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg" height="105" width="95" />';
        } else {
          echo '<img src="plugins/blea/core/plugin_info/blea.png" height="105" width="95" />';
        }
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
    }
    if ($status == 1) {
      echo '</div>';
    } else {
      echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Aucun équipement Xiaomi Bluetooth. Ajoutez-en via le plugin BLEA (Bluetooth Advertisement).}}</span></center>";
    }
    ?>

  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
    <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
    <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      </br>
      <div class="row">
        <div class="col-sm-6">
          <form class="form-horizontal">
            <fieldset>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                <div class="col-sm-3">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement xiaomihome}}"/>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                  <select class="form-control eqLogicAttr" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                    <?php
                    foreach (object::all() as $object) {
                      echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                <div class="col-sm-8">
                  <?php
                  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                  }
                  ?>

                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label" ></label>
                <div class="col-sm-8">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                </div>
              </div>
              <div class="form-group" id="ipfield">
                <label class="col-sm-3 control-label">{{Adresse IP}}</label>
                <div class="col-sm-6">
                  <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ipwifi" placeholder="Ip du device wifi"></span>
                </div>
              </div>
              <div class="form-group" id="passfield">
                <label class="col-sm-3 control-label" id="passtoken">{{Password/Token}}</label>
                <div class="col-sm-6">
                  <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" placeholder="Voir message en bleu"></span>
                </div>
              </div>
            </fieldset>
          </form>
        </div>
        <div class="col-sm-6">
          <a class="btn btn-danger btn-sm pull-right" id="bt_autoDetectModule"><i class="fa fa-search" title="{{Recréer les commandes}}"></i>  {{Recréer les commandes}}</a>
          <a class="btn btn-primary btn-sm eqLogicAction pull-right syncinfo" id="btn_sync"><i class="fa fa-spinner" title="{{Récupérer les infos}}"></i> {{Récupérer les infos}}</a><br/><br/>
          <form class="form-horizontal">
            <fieldset>
              <div class="form-group" id="newmodelfield2">
                <label class="col-sm-3 control-label">{{Equipement}}</label>
                <div class="col-sm-8">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="applyDevice2">
                    <?php
                    $groups = array();

                    foreach (xiaomihome::devicesParameters() as $key => $info) {
                      if (isset($info['groupe'])) {
                        $info['key'] = $key;
                        if (!isset($groups[$info['groupe']])) {
                          $groups[$info['groupe']][0] = $info;
                        } else {
                          array_push($groups[$info['groupe']], $info);
                        }
                      }
                    }
                    ksort($groups);
                    foreach ($groups as $group) {
                      usort($group, function ($a, $b) {
                        return strcmp($a['name'], $b['name']);
                      });
                      foreach ($group as $key => $info) {
                        if ($info['groupe'] == 'Aquara') {
                          break;
                        }
                        if ($key == 0) {
                          echo '<optgroup label="{{' . $info['groupe'] . '}}">';
                        }
                        echo '<option value="' . $info['key'] . '">' . $info['name'] . '</option>';
                      }
                      echo '</optgroup>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="form-group" id="newmodelfield">
                <label class="col-sm-3 control-label">{{Equipement}}</label>
                <div class="col-sm-8">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="applyDevice">
                    <?php
                    $groups = array();

                    foreach (xiaomihome::devicesParameters() as $key => $info) {
                      if (isset($info['groupe'])) {
                        $info['key'] = $key;
                        if (!isset($groups[$info['groupe']])) {
                          $groups[$info['groupe']][0] = $info;
                        } else {
                          array_push($groups[$info['groupe']], $info);
                        }
                      }
                    }
                    ksort($groups);
                    foreach ($groups as $group) {
                      usort($group, function ($a, $b) {
                        return strcmp($a['name'], $b['name']);
                      });
                      foreach ($group as $key => $info) {
                        if ($key == 0) {
                          echo '<optgroup label="{{' . $info['groupe'] . '}}">';
                        }
                        echo '<option value="' . $info['key'] . '">' . $info['name'] . '</option>';
                      }
                      echo '</optgroup>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">{{Type}}</label>
                <div class="col-sm-3">
                  <span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="type" id="typefield"></span>
                </div>
                <label class="col-sm-3 control-label">{{Modèle}}</label>
                <div class="col-sm-3">
                  <span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="model" id="modelfield"></span>
                </div>
              </div>

              <div class="form-group"  id="idfield">
                <label class="col-sm-3 control-label">{{Identifiant}}</label>
                <div class="col-sm-3">
                  <span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="sid" id="sid"></span>
                </div>
                <label class="col-sm-3 control-label">{{Identifiant court}}</label>
                <div class="col-sm-3">
                  <span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="short_id"></span>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">{{Gateway}}</label>
                <div class="col-sm-3">
                  <span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="gateway"></span>
                </div>
                <label class="col-sm-3 control-label">{{Dernière Activité}}</label>
                <div class="col-sm-3">
                  <span class="eqLogicAttr label label-default" data-l1key="status" data-l2key="lastCommunication"></span>
                </div>
              </div>

              <center>
                <img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;"  onerror="this.src='plugins/xiaomihome/doc/images/xiaomihome_icon.png'"/>
              </center>
            </fieldset>
          </form>
          <div class="alert alert-info globalRemark" style="display:none">{{Veuillez renseigner l'IP, puis sauvegardez. Ensuite, il vous suffit de cliquer sur "Récupérer les infos". Si l'équipement est trouvé, les identifiants et le token seront également trouvés. Certains équipements (aspirateur, plafonnier Xiaomi, cuiseur à riz petit format ...) sont une exception : Dans ce cas, il faut récupérer le token avant. Veuillez vous référer à la doc. Pour la gateway, il suffit juste de la récupérer dans les options développeurs de Mi Home.}}</div>
        </div>
      </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="commandtab">

      <table id="table_cmd" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 250px;">{{Nom}}</th>
            <th style="width: 100px;">{{Type}}</th>
            <th style="width: 100px;">{{Unité}}</th>
            <th style="width: 150px;">{{Paramètres}}</th>
            <th style="width: 100px;"></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>

    </div>
  </div>
</div>
</div>

<?php include_file('desktop', 'xiaomihome', 'js', 'xiaomihome'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
