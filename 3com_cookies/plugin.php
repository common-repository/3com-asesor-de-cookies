<?php

/*
Plugin Name: 3COM - Asesor de Cookies y políticas de privacidad.
Plugin URI: 
Description: Este plugin, basado en el trabajo de Carlos Doral Pérez (http://webartesanal.com), avisa a los nuevos visitantes de su web sobre la utilización de cookies en su página y le proporciona los textos iniciales para que pueda crear una política de cookies correcta, además de los textos para aviso legal y política de privacidad.
Version: 3.1
Author: 3COM Marketing
Author URI: http://www.3commarketing.com
License: GPLv2 or later
*/

/*  Copyright 2018 3COM Marketing
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

	// Configuración y definiciones
	require dirname( __FILE__ ) . '/config.php';
	require dirname( __FILE__ ) . '/lib/lib.php';
	require dirname( __FILE__ ) . '/lib/plugin.php';
	
	// Lógica del plugin
	try 
	{
		cdp_cookies::ejecutar();
	}
	catch( cdp_cookies_error $e )
	{
		cdp_cookies_log::pon( $e->getMessage() );
	}

?>