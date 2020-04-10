$("select").change(function(){
    var divId = $(this).find(":selected").attr("data-div-id");
    
    if (divId != "null") {
        $("#" + divId).show();
        testServeurLi = $('#testServeurLi');
        testServeurLi.show();   
    } else {
        testServeurLi.hide();
        $(".jsonapiCB").hide();
    }
});

$('form#testServeur').submit(function() {
    
    //Se connecte au serveur
    $('#chargement').css('display', 'inline');
    $('.submit').attr('disabled', true);
    
    $.ajax({
        url: '../include/jsonapi.php',
        type: 'post',
        data: $(this).serialize(),
        success: function(html) {
            infos = html.split('[{NEW}]');
            
            //Nombre de serveurs utilisés.
            nbrServeurs = $("#nbrServeurs").html();
            
            //Actions sur les serveurs
            actions = $('#actions').html();
            
            if (infos[0] == 'success') {
                $('html,body').animate({scrollTop: 0}, 'slow');
                
                setTimeout(function() {
                    if (install == 'true') {
                        $('#content').html('<div class="warning_v" style="margin:10px;">La configuration de JSONAPI a bien fonctionnée.<br>Vous pouvez passer à l\'étape suivante <a href="index.php">en cliquant ici</a>.</div>');
                    } else {
                        alert('Votre serveur a bien été ajouté.');
                        
                        if (infos[1] != "") {
                            
                            if (nbrServeurs == 0) {
                                $('#listeServeurs tr:nth-child(2)').remove();
                                var tr = actions;
                            } else {
                                var tr = $('#listeServeurs tr:nth-child(2) td:last-child').html();
                            }

                            $('#listeServeurs').append('<tr id="' + infos[3] + '" jsonapi="' + infos[4] + '"><td><strong>' + infos[1] + '</strong><br><small>' + infos[2] + '</small></td><td>' + tr + '</td></tr>');
                            
                            //Gestion du bouton de la configuration
                            $('#listeServeurs tr:last-child td:last-child img:last-child').css('opacity', '0.2');
                            
                            $("#listeServeurs tr:last-child td:last-child img:last-child").click(function() {
                                alert("Veuillez recharger la page pour accéder aux configurations de ce serveur.");
                                return false;
                            });
                            
                            $('#listeServeurs tr:last-child a.poplight').attr('rel', '');
                            
                            //Affichage du fondu
                            $('#listeServeurs tr:last').hide().fadeIn(1000);
                        }
                        
                        $('input:not([type=submit])').val('');
                    }
                }, 500);
                
            } else if(infos[0] == 'jsonapi') {
                alert("Il est impossible de se connecter à votre serveur via JSONAPI.\nSi le problème persiste, veuillez consulter le support de CraftMyCMS.");
            } else if(infos[0] == 'infos') {
                alert("Veuillez remplir toutes les informations demandées.");
            } else {
                alert("Une erreur est survenue, merci de réessayer ultérieurement.");
            }
            
            $('#chargement').css('display', 'none');
            $('.submit').attr('disabled', false);
        }
    });
    return false;
});