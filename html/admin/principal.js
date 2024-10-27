
/* ======================================================================================
   @author     3com (http://www.3comunicacion.com)
   @version    0.1
   @copyright  Copyright &copy; 2015 3com, All Rights Reserved
               License: GPLv2 or later
   ====================================================================================== */

//
function cdp_cookies_mensaje( texto, clase )
{
	jQuery( '.cdp-cookies-mensajes' ).removeClass( 'error' ).addClass( clase );
	jQuery( '.cdp-cookies-mensajes' ).html( texto ).fadeIn( 500 ).delay( 2000 ).fadeOut( 500 );
}

//
function cdp_cookies_mensaje_error( texto )
{
	cdp_cookies_mensaje( texto, 'error' );
}

// Funcion que obtiene los datos introducidos por el usuario y envia la peticion
function guardar()
{
	//
	var datos = {
		action: 'guardar_config',
		texto_aviso: jQuery( '#texto_aviso' ).val(),
		tam_fuente: jQuery( '#tam_fuente' ).val(),
		layout: jQuery( '#layout' ).val(),
		posicion: jQuery( '#posicion' ).val(),
		alineacion: jQuery( '#alineacion' ).val(),
		color_fondo: jQuery( '#color_fondo' ).val(),
		color_fuente: jQuery( '#color_fuente' ).val(),
		color_fondo_bot: jQuery('#color_fondo_bot').val(),
		color_fuente_bot: jQuery('#color_fuente_bot').val(),
		color_link: jQuery('#color_link').val(),
		desarrollador: jQuery( '#desarrollador' ).val(),
		desarrollado: jQuery( '#desarrollado' ).val(),
		enlace_politica: jQuery( '#enlace_politica' ).val(),
		enlace_mas_informacion: jQuery( '#enlace_mas_informacion' ).val(),
		enlace_aviso_legal: jQuery( '#enlace_aviso_legal' ).val(),
		enlace_politica_privacidad: jQuery( '#enlace_politica_privacidad' ).val(),
                enlace_mapa_web: jQuery('#enlace_mapa_web').val(),
                pagina_web: jQuery('#pagina_web').val(),
                correo_electronico: jQuery('#correo_electronico').val(),
        		razon_social: jQuery('#razon_social').val(),
				direccion: jQuery('#direccion').val(),
				telefono: jQuery('#telefono').val(),
				cif: jQuery('#cif').val(),
				persona_autorizada: jQuery('#persona_autorizada').val(),
				nombre_comercial: jQuery('#nombre_comercial').val(),

		nonce_guardar: cdp_cookies_info.nonce_guardar,
		comportamiento: jQuery( '#comportamiento' ).val()
	};
	
	//
	jQuery.post( ajaxurl, datos, function( resul ) {
		if( resul.ok ){
                    cdp_cookies_mensaje( resul.txt );
                    jQuery( '#cdp-cookies-contenido-footer' ).text( resul.contenido_footer ).html();
                }
		else{
                    cdp_cookies_mensaje_error( resul.txt );
                }
	}, 'json' );
}

// Funcion que envia la peticion de crear las paginas
function crear_paginas()
{
	//
	var datos = {
		action: 'crear_paginas',
		nonce_crear_paginas : cdp_cookies_info.nonce_crear_paginas,
                pagina_web: jQuery('#pagina_web').val(),
                correo_electronico: jQuery('#correo_electronico').val(),
       			razon_social: jQuery('#razon_social').val(),
				direccion: jQuery('#direccion').val(),
				cif: jQuery('#cif').val(),
				telefono: jQuery('#telefono').val(),
				nombre_comercial: jQuery('#nombre_comercial').val()
	};

	//
	jQuery.post( ajaxurl, datos, function( resul ) {
		if( resul.ok )
		{
			cdp_cookies_mensaje( resul.txt );
			jQuery( '#enlace_mas_informacion' ).val( resul.url_info );
			jQuery( '#enlace_politica' ).val( resul.url_politica );
			jQuery( '#enlace_aviso_legal' ).val( resul.url_aviso_legal );
			jQuery( '#enlace_politica_privacidad' ).val( resul.url_politica_privacidad );
			jQuery( '#enlace_mapa_web' ).val( resul.url_mapa_web );
            jQuery( '#razon_social' ).val( resul.razon_social );
            jQuery( '#direccion' ).val( resul.direccion );
            jQuery( '#cif' ).val( resul.cif );
            jQuery( '#telefono' ).val( resul.telefono );
            jQuery( '#nombre_comercial' ).val( resul.nombre_comercial );
		}
		else
		{
			cdp_cookies_mensaje_error( resul.txt );
		}
	}, 'json' );
}

//
jQuery( document ).ready( function( $ ) {

	// Color picker para color de fondo
	jQuery('#color_fondo').wpColorPicker();
	 
	// Color picker para color de la fuente 
	jQuery('#color_fuente').wpColorPicker();
	// Color picker para color de fondo boton
	jQuery('#color_fondo_bot').wpColorPicker();
	// Color picker para color de fuente boton
	jQuery('#color_fuente_bot').wpColorPicker();
	// Color picker para color de fuente enlace
	jQuery('#color_link').wpColorPicker();
	// Ocultar/mostrar instrucciones
	jQuery( '.cdp-cookies-bot-instrucciones' ).click( function() {
		$( '.cdp-cookies-instrucciones' ).toggle();
	} );

	// Guardar config
	$( 'a.cdp-cookies-guardar' ).click( function() {
		guardar();
	} );

	// Crear págs
	$( 'a.cdp-cookies-crear-politica' ).click( function() {
		crear_paginas();
	} );

	// Ver pág. más info
	$( 'a.cdp-cookies-ver-mas-info' ).click( function() {
		window.open( $( '#enlace_mas_informacion' ).val() );
	} );

	// Ver pág. politica
	$( 'a.cdp-cookies-ver-politica' ).click( function() {
		window.open( $( '#enlace_politica' ).val() );
	} );
	
	// Ver pág. aviso legal	
	$( 'a.cdp-cookies-ver-aviso_legal' ).click( function() {
		window.open( $( '#enlace_aviso_legal' ).val() );
	} );
	
	// Ver pág. politica privacidad
	$( 'a.cdp-cookies-ver-politica_privacidad' ).click( function() {
		window.open( $( '#enlace_politica_privacidad' ).val() );
	} );

	// Ver pág. mapa web
	$( 'a.cdp-cookies-ver-mapa-web' ).click( function() {
		window.open( $( '#enlace_mapa_web' ).val() );
	} );

	// Vista previa del aviso
	$( 'a.cdp-cookies-vista-previa' ).click( function() {
		window.open( 
			cdp_cookies_info.siteurl + 
			'?cdp_cookies_vista_previa=1' +
			'&layout=' + $( '#layout' ).val() +
                        '&comportamiento=' + $( '#comportamiento' ).val().replace(/\'|"/g, '') +
			'&posicion=' + $( '#posicion' ).val() +
			'&alineacion=' + $( '#alineacion' ).val() +
                        '&color_fondo=' + encodeURIComponent($( '#color_fondo' ).val()) +
			'&color_fuente=' + encodeURIComponent($( '#color_fuente' ).val()) +
			'&tam_fuente=' + $( '#tam_fuente' ).val()
		);
	} );

} );