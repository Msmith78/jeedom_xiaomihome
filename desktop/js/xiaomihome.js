
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.

*/
function typefieldChange(){
	if ($('#typefield').value() == 'aquara') {
    $('#modelfield option:not(:selected)').prop('disabled', true);
    $('#idfield').show();
    $('#ipfield').hide();
    $('.syncinfo').hide();
    if ($('#modelfield').value() == 'gateway') {
      $("#passtoken").text("Password");
      $('#passfield').show();
      $('.globalRemark').text("Le mot de passe à renseigner se trouve dans les options développeurs de la gateway dans Mi Home. Voir la documentation.");
      $('.globalRemark').show();
    } else {
      $('#passfield').hide();
      $('.globalRemark').hide();
    }
  }
  else if ($('#typefield').value() == 'yeelight') {
    $('#modelfield option:not(:selected)').prop('disabled', true);
    $('#passfield').hide();
    $('#idfield').hide();
    $('#ipfield').show();
    $('.syncinfo').hide();
    $('.globalRemark').hide();
  }
  else if ($('#typefield').value() == 'wifi') {
    $('#modelfield option:not(:selected)').prop('disabled', true);
    $("#passtoken").text("Token");
    $('#passfield').show();
    $('.globalRemark').text("Le token peut être trouvé via le bouton récupérer les infos. Si ça ne retourne pas de valeur correcte, il faut utilliser la documentation et une des méthodes fournies.");
    $('.globalRemark').show();
    $('.syncinfo').show();
    $('#idfield').hide();
    $('#ipfield').show();
  }
}
$( "#typefield" ).change(function(){
  setTimeout(typefieldChange,100);
});

$( "#sid" ).change(function(){
  if ($('#sid').value() == '') {
    $('#newmodelfield').hide();
    $('#newmodelfield2').show();
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').value($('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice2]').value());
  }
  else {
    $('#newmodelfield').show();
    $('#newmodelfield2').hide();
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').attr('disabled', true);
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').value($('.eqLogicAttr[data-l1key=configuration][data-l2key=model]').value());
  }
});

$('#bt_healthxiaomihome').on('click', function () {
  $('#md_modal').dialog({title: "{{Santé xiaomihome}}"});
  $('#md_modal').load('index.php?v=d&plugin=xiaomihome&modal=health').dialog('open');
});

$('.discover').on('click', function () {
	$('#div_alert').showAlert({message: '{{Détection en cours}}', level: 'warning'});
	$.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/xiaomihome/core/ajax/xiaomihome.ajax.php",
                data: {
                    action: "discover",
                    mode: $(this).attr('data-action'),
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
                    $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
                }
            });
});

function iconChange(){
if($('.li_eqLogic.active').attr('data-eqlogic_id') != ''){
     icon = $('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').value();
         if(icon == '' || icon == null){
			 icon = $('.eqLogicAttr[data-l1key=configuration][data-l2key=model]').value();
		 }
		 if(icon != '' && icon != null){
             $('#img_device').attr("src", 'plugins/xiaomihome/core/config/devices/'+icon+'/'+icon+'.png');
         } else {
			 $('#img_device').attr("src", 'plugins/xiaomihome/doc/images/xiaomihome_icon.png');
		 }
 }else{
    $('#img_device').attr("src",'plugins/xiaomihome/doc/images/xiaomihome_icon.png');
}
}
 $('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').on('change', function () {
  $('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').value();
  setTimeout(iconChange,50);
});

 $('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice2]').on('change', function () {
  if ($('#sid').value() == '') {
  $('.eqLogicAttr[data-l1key=configuration][data-l2key=applyDevice]').value($(this).value());
  }
});
$('body').on('xiaomihome::includeDevice', function (_event,_options) {
    if (modifyWithoutSave) {
        $('#div_inclusionAlert').showAlert({message: '{{Un périphérique vient d\'être découvert. Veuillez réactualiser la page.}}', level: 'warning'});
    } else {
        window.location.reload();
    }
});

$('#bt_autoDetectModule').on('click', function () {
    var dialog_title = '{{Recharge configuration}}';
    var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
    dialog_title = '{{Recharger la configuration}}';
    dialog_message += '<label class="control-label" > {{Sélectionner le mode de rechargement de la configuration ?}} </label> ' +
    '<div> <div class="radio"> <label > ' +
    '<input type="radio" name="command" id="command-0" value="0" checked="checked"> {{Sans supprimer les commandes}} </label> ' +
    '</div><div class="radio"> <label > ' +
    '<input type="radio" name="command" id="command-1" value="1"> {{En supprimant et recréant les commandes}}</label> ' +
    '</div> ' +
    '</div><br>' +
    '<label class="lbl lbl-warning" for="name">{{Attention, "En supprimant et recréant" va supprimer les commandes existantes.}}</label> ';
    dialog_message += '</form>';
    bootbox.dialog({
       title: dialog_title,
       message: dialog_message,
       buttons: {
           "{{Annuler}}": {
               className: "btn-danger",
               callback: function () {
               }
           },
           success: {
               label: "{{Démarrer}}",
               className: "btn-success",
               callback: function () {
                    if ($("input[name='command']:checked").val() == "1"){
						bootbox.confirm('{{Etes-vous sûr de vouloir récréer toutes les commandes ? Cela va supprimer les commandes existantes}}', function (result) {
                            if (result) {
                                $.ajax({
                                    type: "POST",
                                    url: "plugins/xiaomihome/core/ajax/xiaomihome.ajax.php",
                                    data: {
                                        action: "autoDetectModule",
                                        id: $('.eqLogicAttr[data-l1key=id]').value(),
                                        createcommand: 1,
                                    },
                                    dataType: 'json',
                                    global: false,
                                    error: function (request, status, error) {
                                        handleAjaxError(request, status, error);
                                    },
                                    success: function (data) {
                                        if (data.state != 'ok') {
                                            $('#div_alert').showAlert({message: data.result, level: 'danger'});
                                            return;
                                        }
                                        $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
                                        $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
                                    }
                                });
                            }
                        });
					} else {
						$.ajax({
                                    type: "POST",
                                    url: "plugins/xiaomihome/core/ajax/xiaomihome.ajax.php",
                                    data: {
                                        action: "autoDetectModule",
                                        id: $('.eqLogicAttr[data-l1key=id]').value(),
                                        createcommand: 0,
                                    },
                                    dataType: 'json',
                                    global: false,
                                    error: function (request, status, error) {
                                        handleAjaxError(request, status, error);
                                    },
                                    success: function (data) {
                                        if (data.state != 'ok') {
                                            $('#div_alert').showAlert({message: data.result, level: 'danger'});
                                            return;
                                        }
                                        $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
                                        $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
                                    }
                                });
					}
            }
        },
    }
});
});

$('#btn_sync').on('click', function () {
    bootbox.confirm('{{Assurez-vous d\'avoir renseigné l\'adresse IP de l\'équipement et d\'avoir sauvegardé avant de lancer. Ensuite le retour se fera dans les 5 secondes.}}', function (result) {
        if (result) {
            $('#div_alert').showAlert({message: '{{Recherche en cours...}}', level: 'warning'});
            $.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/xiaomihome/core/ajax/xiaomihome.ajax.php",
                data: {
                    action: "sync",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
                }
            });
        }
    });
});

$("#table_cmd").delegate(".listEquipementInfo", 'click', function () {
  var el = $(this);
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']');
    calcul.atCaret('insert', result.human);
  });
});

$("#table_cmd").delegate(".listEquipementAction", 'click', function () {
  var el = $(this);
  var subtype = $(this).closest('.cmd').find('.cmdAttr[data-l1key=subType]').value();
  jeedom.cmd.getSelectModal({cmd: {type: 'action', subType: subtype}}, function (result) {
    var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']');
    calcul.atCaret('insert', result.human);
  });
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }

  if (init(_cmd.type) == 'info') {
    var disabled = (init(_cmd.configuration.virtualAction) == '1') ? 'disabled' : '';
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom du capteur}}"></td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" style="width : 90px;" placeholder="{{Unité}}">';
    tr += '</td><td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    if (_cmd.subType == "binary") {
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="display" data-l2key="invertBinary" />{{Inverser}}</label></span>';
    }
    if (_cmd.subType == "numeric") {
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width : 40%;display : inline-block;"> ';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width : 40%;display : inline-block;">';
    }
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
      tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
      tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    /*if (isset(_cmd.type)) {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
  }
  jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));*/
}

if (init(_cmd.type) == 'action') {
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="id"></span>';
  tr += '</td>';
  tr += '<td>';
  tr += '<div class="row">';
  tr += '<div class="col-lg-6">';
  tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icône</a>';
  tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
  tr += '</div>';
  tr += '<div class="col-lg-6">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
  tr += '</div>';
  tr += '</div>';
  //tr += '<select class="cmdAttr form-control tooltips input-sm" data-l1key="value" style="display : none;margin-top : 5px;" title="{{La valeur de la commande vaut par défaut la commande}}">';
  //tr += '<option value="">Aucune</option>';
  //tr += '</select>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style=""></span>';
  //tr += '<input class="cmdAttr" data-l1key="configuration" data-l2key="virtualAction" value="1" style="display:none;" >';
  tr += '</td>';
  tr += '<td>';
  tr += '</td><td>';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="margin-top : 5px;"> ';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="margin-top : 5px;">';
  tr += '</td>';
  tr += '<td>';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
  tr += '</tr>';

  $('#table_cmd tbody').append(tr);
  //$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  var tr = $('#table_cmd tbody tr:last');
  jeedom.eqLogic.builSelectCmd({
    id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
    filter: {type: 'info'},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result);
      tr.setValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    }
  });

}
$('body').on('xiaomihome::found', function (_event,_options) {
    window.location.href = 'index.php?v=d&p=xiaomihome&m=xiaomihome&id=' + _options+'&nocache=' + (new Date()).getTime();
});

$('body').on('xiaomihome::notfound', function (_event,_options) {
    $('#div_alert').showAlert({message: '{{Equipement non trouvé. Veuillez vérifier l\'IP et relancer.}}', level: 'danger'});
});
}

$('.inclusion').on('click', function () {
  var id = $(this).attr('data-id');
  var dialog_title = '';
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
  dialog_title = '{{Démarrer l\'inclusion d\'un nouveau module}}';
  dialog_message += '<label class="control-label" > {{Etes vous sûr de vouloir mettre la gateway en inclusion ?}} </label> ' +

  ' ';
  dialog_message += '</form>';
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
       "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {
          }
      },
      success: {
        label: "{{Démarrer}}",
        className: "btn-success",
        callback: function () {
			$.ajax({
        type: "POST",
        url: "plugins/xiaomihome/core/ajax/xiaomihome.ajax.php",
        data: {
            action: "InclusionGateway",
            id: id,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
        }
    });
     }
 },
}
});
});
