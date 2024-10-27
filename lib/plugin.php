<?php

/* ======================================================================================
   @author     3com (http://www.3comunicacion.com)
   @version    0.1
   @copyright  Copyright &copy; 2015 3com, All Rights Reserved
               License: GPLv2 or later
   ====================================================================================== */

/**
 *
 */
class cdp_cookies
{
	//
	// Para añadir una sóla vez los enlaces en la página de plugins
	//
	static private $nombre_plugin;

	/**
	 *
	 */
	static function ejecutar()
	{
		//
		// Plugin no puede ser ejecutado directamente
		//
		if( !( function_exists( 'add_action' ) && defined( 'ABSPATH' ) ) )
			throw new cdp_cookies_error( 'Este plugin no puede ser llamado directamente' );
		
		//
		// Ejecutando Admin
		//
		if( is_admin() )
		{
			add_filter( 'plugin_action_links', array( __CLASS__, 'enlaces_pagina_plugins' ), 10, 2 );
			add_action( 'admin_menu', array( __CLASS__, 'crear_menu_admin' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'cargar_archivos_admin' ) );
			add_action( 'wp_ajax_guardar_config', array( __CLASS__, 'ajax_guardar_config' ) );			
			add_action( 'wp_ajax_crear_paginas', array( __CLASS__, 'ajax_crear_paginas' ) );			
			return;
		}
		
		//
		// Ejecutando front
		//
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'cargar_archivos_front' ) );
		add_action( 'wp_footer', array( __CLASS__, 'renderizar_aviso' ) );
	}

	/**
	 * Función encargada de mostrar el mensaje de aviso, cargando los parametros introducidos
	 * por el usuario.
	 */
	static function renderizar_aviso()
	{
		//
		// Posicionamiento en ventana o página
		//
		$class = '';
		if( self::parametro( 'layout' ) == 'ventana' )
			$class .= ' cdp-cookies-layout-ventana';
		else
			$class .= ' cdp-cookies-layout-pagina';
		
		//
		// Posición: superior, inferior
		//
		$class .= ' cdp-cookies-pos-' . self::parametro( 'posicion' );

		//
		// Alineación de los textos
		//
		if( self::parametro( 'alineacion' ) == 'izq' )
			$class .= ' cdp-cookies-textos-izq';

		//
		// Esquema de color
		//
                $color_fondo        =   self::parametro('color_fondo');     
                $estilo_fondo       =   'style="background-color:{color_fondo}"';  
                $estilo_fondo       =   str_replace( '{color_fondo}', $color_fondo, $estilo_fondo );

		//
		// Preparo el texto
		//

		$tam_fuente         =   self::parametro( 'tam_fuente' );
		$tam_fuente_titulo  =   str_replace( 'px', '', $tam_fuente ) + 3;
                $color_fuente       =   self::parametro('color_fuente');
		$estilo_texto       =   'style="font-size:{tam_fuente} !important;line-height:{tam_fuente} !important; color:{color_fuente} !important"';
		$estilo_titulo      =   'style="color:{color_fuente} !important"';
		$estilo_enlace      =   'style="font-size:{tam_fuente} !important;line-height:{tam_fuente} !important; color: '.self::parametro('color_link').'""';
		$texto_aviso        =   html_entity_decode( self::parametro( 'texto_aviso' ) );
		
		//
		$html = file_get_contents( CDP_COOKIES_DIR_HTML . 'front/aviso.html' );
		$html = do_shortcode(str_replace( '{texto_aviso}', $texto_aviso, $html ));
		$html = str_replace( '{estilo_texto}', $estilo_texto, $html );                
                $html = str_replace( '{estilo_titulo}', $estilo_titulo, $html );
                $html = str_replace( '{color_fuente}', $color_fuente, $html );
		$html = str_replace( '{estilo_enlace}', $estilo_enlace, $html );               
		$html = str_replace( '{class}', $class, $html );
                $html = str_replace( '{class}', $class, $html );
                $html = str_replace( '{background}', $estilo_fondo, $html );
		$html = str_replace( '{enlace_politica}', self::parametro( 'enlace_politica' ), $html );
		$html = str_replace( '{tam_fuente}', $tam_fuente, $html );
		$html = str_replace( '{tam_fuente_titulo}', $tam_fuente_titulo, $html );
		
		//
		$boton = '';	
                $boton = '<a href="javascript:;" class="cdp-cookies-boton-aviso" style="background-color: '.self::parametro('color_fondo_bot').'; color: '. self::parametro('color_fuente_bot').';">' . do_shortcode(self::parametro( 'comportamiento' )) . '</a>';
		$html = str_replace( '{boton_cerrar}', $boton, $html );
		
		//
		echo $html;
	}

	/**
	 * Añade el enlace de este plugin al panel de admin de wordpress
	 */
	static function enlaces_pagina_plugins( $enlaces, $archivo )
	{
		//
		// Sólo añado enlaces a mi plugin
		//
		if( !self::$nombre_plugin )
			self::$nombre_plugin = plugin_basename( CDP_COOKIES_DIR_RAIZ . '/plugin.php' );
		if( $archivo != self::$nombre_plugin )
			return $enlaces;

		//
		// Procedo
		//
		$enlace = array( 
			sprintf( 
				"<a href=\"%s\">%s</a>",
				admin_url( 'tools.php?page=cdp_cookies' ),
				__( 'Configuración' )
			) );
		return array_merge( $enlace, $enlaces );
	}

	/**
	 *
	 */
	static function cargar_archivos_front()
	{
		wp_enqueue_style( 'front-estilos', CDP_COOKIES_URL_HTML . 'front/estilos.css', false );		
		wp_enqueue_script( 'front-principal', CDP_COOKIES_URL_HTML . 'front/principal.js', array( 'jquery' ) );
		wp_localize_script
		( 
			'front-principal', 
			'cdp_cookies_info',
			array
			(
				'url_plugin' => CDP_COOKIES_URL_RAIZ . 'plugin.php',
				'url_admin_ajax' => admin_url() . 'admin-ajax.php',
				'comportamiento' => self::parametro( 'comportamiento' ),
				'posicion' => self::parametro( 'posicion' ),
				'layout' => self::parametro( 'layout' )
			) 
		);
	}

	/**
	 * Funcion que crea las paginas automaticamente
	 */
	static function ajax_crear_paginas()
	{
		try
		{
			//
			self::comprobar_usuario_admin();
                        
                        $pagina_web         = cdp_cookies_input::post( 'pagina_web' );
                        $correo_electronico = cdp_cookies_input::post( 'correo_electronico' );
                        $razon_social       = cdp_cookies_input::post( 'razon_social' );
                        $direccion          = cdp_cookies_input::post( 'direccion' );
                        $nombre_comercial   = cdp_cookies_input::post( 'nombre_comercial' );
                        $cif                = cdp_cookies_input::post( 'cif' );
                        $telefono           = cdp_cookies_input::post( 'telefono' );
                        
			//
			if( !wp_verify_nonce( cdp_cookies_input::post( 'nonce_crear_paginas' ), 'crear_paginas' ) )
				throw new cdp_cookies_error_nonce();

			// Pág. mas info
			$pag_info = new cdp_cookies_pagina();
			$pag_info->titulo = 'Más información sobre las cookies';
			$pag_info->html = file_get_contents( CDP_COOKIES_DIR_HTML . 'front/mas-informacion.html' );
			if( !$pag_info->crear() )
				throw new cdp_cookies_error( $pag_info->mensaje );
			
			// importante! Guardo la url de la página info que será usada por la política
			self::parametro( 'enlace_mas_informacion', $pag_info->url );
			
			// Pág. política
			$pag_pol = new cdp_cookies_pagina();
			$pag_pol->titulo = 'Política de cookies';
			$pag_pol->html =
				str_replace
				(
					'{enlace_mas_informacion}',
					self::parametro( 'enlace_mas_informacion' ),
					file_get_contents( CDP_COOKIES_DIR_HTML . 'front/politica.html' )
				);
			if( !$pag_pol->crear() )
				throw new cdp_cookies_error( $pag_pol->mensaje );
				
			// Pág. politica privacidad	
			$pag_pol_priv = new cdp_cookies_pagina();
			$pag_pol_priv->titulo = 'Politica de Privacidad';
			$pag_pol_priv->html = 
                                str_replace
                                (                                       
                                    array( '{correo_electronico}', '{razon_social}', '{direccion}' ),
                                    array( $correo_electronico, $razon_social, $direccion ),
                                    file_get_contents( CDP_COOKIES_DIR_HTML . 'front/politica-privacidad.html' )
                                );

			if( !$pag_pol_priv->crear() )
				throw new cdp_cookies_error( $pag_pol_priv->mensaje );
				
			// Pagina aviso legal
			$pag_aviso_legal = new cdp_cookies_pagina();
			$pag_aviso_legal->titulo = 'Aviso Legal';
			$pag_aviso_legal->html = 
                                str_replace(
                                    array( '{pagina_web}','{correo_electronico}', '{razon_social}', '{direccion}',
                                '{nombre_comercial}', '{cif}', '{telefono}'),
                                    array( $pagina_web, $correo_electronico, $razon_social, $direccion,
                                        $nombre_comercial, $cif, $telefono),
                                    file_get_contents( CDP_COOKIES_DIR_HTML . 'front/aviso-legal.html' )
                                );                                
                        
			if( !$pag_aviso_legal->crear() )
				throw new cdp_cookies_error( $pag_aviso_legal->mensaje );	
			
			// Pagina mapa web
			$pag_mapa_web = new cdp_cookies_pagina();
			$pag_mapa_web->titulo = 'Mapa Web';	                        
			if( !$pag_mapa_web->crear() )
				throw new cdp_cookies_error( $pag_mapa_web->mensaje );	

			// Todo ok!
			$resul = array(
						'ok'                        => 	true, 
						'url_info'                  => 	$pag_info->url, 
						'url_politica'              => 	$pag_pol->url, 
						'url_aviso_legal'           => 	$pag_aviso_legal->url,	
						'url_politica_privacidad'   => 	$pag_pol_priv->url,	
						'url_mapa_web'              =>	$pag_mapa_web->url,
                        'cif'                       =>  $cif,
                        'telefono'                  =>  $telefono,
                        'nombre_comercial'          =>  $nombre_comercial,
                        'direccion'                 =>  $direccion,
                        'razon_social'              =>  $razon_social
 
			);
			// Si alguna de pagina ya existia, se avisa al usuario
			if( $pag_pol->ya_existia || $pag_info->ya_existia || $pag_pol_priv->ya_existia || $pag_aviso_legal->ya_existia ||  $pag_mapa_web->ya_existia){
                if ($pag_pol->ya_existia)
			        $resul['txt'] .= 'Se ha sobreescrito la página "Política de cookies"<br/>';
                if ($pag_info->ya_existia)
                    $resul['txt'] .= 'Se ha sobreescrito la página "Más información sobre las cookies"<br/>';
                if ($pag_pol_priv->ya_existia)
                    $resul['txt'] .= 'Se ha sobreescrito la página "Política de Privacidad"<br/>';
                if ($pag_aviso_legal->ya_existia)
                    $resul['txt'] .= 'Se ha sobreescrito la página "Aviso Legal"<br/>';
                if ($pag_mapa_web->ya_existia)
                    $resul['txt'] .= 'Se ha sobreescrito la página "Mapa Web"<br/>';
            }else
				$resul['txt'] = 'Páginas creadas correctamente';
			echo json_encode( $resul );
		}
		catch( Exception $e )
		{
			cdp_cookies_log::pon( $e );
			echo json_encode( array( 'ok' => false, 'txt' => $e->getMessage() ) );
		}
		exit;
	}
        
        /**
         * Funcion encargada de construir la string que ira en el footer 
         * 
         * @param type string $mapa_web
         * @param type string $aviso_legal
         * @param type string $politica_cookies
         * @param type string $politica_privacidad
         * @param type boolean $escape
         * @return string
         */
        static function construirStringFooter($mapa_web, $aviso_legal, $politica_cookies, $politica_privacidad, $escape=true,$desarrollado,$desarrollador){
            
            $nombre_web             =	get_bloginfo();
            $mapa_web               =   (string) $mapa_web;
            $aviso_legal            =   (string) $aviso_legal;
            $politica_cookies       =   (string)$politica_cookies;
            $politica_privacidad    =   (string) $politica_privacidad;
			$desarrollado = (string) $desarrollado;
			$desarrollador = (string) $desarrollador;
			
			
            
            $contenido_footer   =   '© ' . $nombre_web . ' - ' .
                                    '<a href="' . $mapa_web . '" >Mapa Web</a> - ' .
                                    '<a href="' . $aviso_legal . '">Aviso Legal</a> - '.
                                    '<a href="' . $politica_cookies . '">Política de Cookies</a> - ' .
                                    '<a href="' . $politica_privacidad . '">Política de Privacidad</a> - ' .
                                    '<a href="'. $desarrollador .'" target="_blank">'.$desarrollado.'</a>';             
            if ($escape){
                return htmlspecialchars($contenido_footer);
            }
            
            return $contenido_footer;
        }

	/**
	 * Funcion que se encarga de validar todas las opciones y guardarlas si son correctas o
	 * mostrar los errores correspondientes en caso contrario
	 */
	static function ajax_guardar_config()
	{		
		try
		{	
			// 
			self::comprobar_usuario_admin();
				
			// Comprueba si se enviaron los datos por post
			if( !wp_verify_nonce( cdp_cookies_input::post( 'nonce_guardar' ), 'guardar' ) )
				throw new cdp_cookies_error_nonce();

			// Valida todas las opciones. Se lanza una excepcion si no tienen un valor correcto.
			cdp_cookies_input::validar_array( 'layout', array( 'ventana', 'pagina' ) );
                        cdp_cookies_input::validar_requerido( 'texto_aviso', 'Se requiere un texto para el aviso' );
                        cdp_cookies_input::validar_requerido( 'comportamiento', 'Se requiere un texto para el botón');
			cdp_cookies_input::validar_array( 'posicion', array( 'superior', 'inferior' ) );
			cdp_cookies_input::validar_array( 'alineacion', array( 'izq', 'cen' ) );			
			cdp_cookies_input::validar_color( 'color_fondo' );
			cdp_cookies_input::validar_color( 'color_fuente' );
			cdp_cookies_input::validar_url( 'enlace_politica' );
			cdp_cookies_input::validar_url( 'enlace_mas_informacion' );
			cdp_cookies_input::validar_url( 'enlace_aviso_legal' );
			cdp_cookies_input::validar_url( 'enlace_politica_privacidad' );
			cdp_cookies_input::validar_url( 'enlace_mapa_web' );
			cdp_cookies_input::validar_url( 'desarrollador' );
                        cdp_cookies_input::validar_url( 'pagina_web' );
                        cdp_cookies_input::validar_email( 'correo_electronico' );
                        cdp_cookies_input::validar_requerido( 'direccion', 'Se requiere un texto para la dirección' );

			if( !preg_match( '/^[0-9]+px$/i', cdp_cookies_input::post( 'tam_fuente' ) ) )
				throw new cdp_cookies_error( 
					"<b>Tamaño de fuente del texto</b> debe tener un valor en px, p.e: 12px" 
				);
			
                        // Obtiene los enlaces del footer
                        $mapa_web               =   cdp_cookies_input::post( 'enlace_mapa_web' ) ;
                        $aviso_legal            =   cdp_cookies_input::post( 'enlace_aviso_legal' );
                        $politica_cookies       =   cdp_cookies_input::post( 'enlace_politica' ) ;
                        $politica_privacidad    =   cdp_cookies_input::post( 'enlace_politica_privacidad'  );
						$desarrollado = cdp_cookies_input::post('desarrollado');
						$desarrollador = cdp_cookies_input::post('desarrollador');
						if($desarrollado == "#" || $desarrollado == "")
							$desarrollado = "3COM Marketing";
						if($desarrollador == "#" || $desarrollador == "")
							$desarrollador = "http://3commarketing.com";
                        
			// Almacen las opciones
			self::parametro( 'layout', cdp_cookies_input::post( 'layout' ) );
			self::parametro( 'posicion', cdp_cookies_input::post( 'posicion' ) );
			self::parametro( 'comportamiento', cdp_cookies_input::post( 'comportamiento' ) );
			self::parametro( 'alineacion', cdp_cookies_input::post( 'alineacion' ) );					
			self::parametro( 'color_fondo', cdp_cookies_input::post( 'color_fondo' ) );
			self::parametro( 'color_fuente', cdp_cookies_input::post( 'color_fuente' ) );
			self::parametro('color_fondo_bot',cdp_cookies_input::post('color_fondo_bot'));
			self::parametro('color_fuente_bot',cdp_cookies_input::post('color_fuente_bot'));
			self::parametro('color_link',cdp_cookies_input::post('color_link'));
			self::parametro( 'enlace_politica', $politica_cookies );
			self::parametro( 'enlace_mas_informacion', cdp_cookies_input::post( 'enlace_mas_informacion' ) );
			self::parametro( 'enlace_aviso_legal', $aviso_legal );
			self::parametro( 'enlace_politica_privacidad', $politica_privacidad );
			self::parametro( 'enlace_mapa_web', $mapa_web );
                        self::parametro( 'pagina_web', cdp_cookies_input::post( 'pagina_web' ) );
                        self::parametro( 'correo_electronico', cdp_cookies_input::post( 'correo_electronico' ) );
                        self::parametro( 'telefono', cdp_cookies_input::post( 'telefono' ) );
                        self::parametro( 'cif', cdp_cookies_input::post( 'cif' ) );
                        self::parametro( 'direccion', cdp_cookies_input::post( 'direccion' ) );
                        self::parametro( 'razon_social', cdp_cookies_input::post( 'razon_social' ) );
			self::parametro( 'texto_aviso', cdp_cookies_input::post( 'texto_aviso' ) );
			self::parametro( 'tam_fuente', cdp_cookies_input::post( 'tam_fuente' ) );
			self::parametro( 'nombre_comercial', cdp_cookies_input::post( 'nombre_comercial' ) );
            self::parametro( 'persona_autorizada', cdp_cookies_input::post( 'persona_autorizada' ) );
			self::parametro('desarrollado',$desarrollado);
			self::parametro('desarrollador',$desarrollador);
				
                        $contenido_footer   =   self::construirStringFooter($mapa_web, $aviso_legal, $politica_cookies, $politica_privacidad, false,$desarrollado,$desarrollador);                              
                        
			// Muestra el mensaje al usuario
			echo json_encode( array( 
                                            'ok'                    =>  true, 
                                            'txt'                   =>  'Configuración guardada correctamente',
                                            'contenido_footer'      =>  $contenido_footer,
                                           
                        ) );
		}
		catch( Exception $e )
		{				
                    echo json_encode( array( 'ok' => false, 'txt' => $e->getMessage() ) );
		}
		exit;
	}

	/**
	 * Funcion que devuelve los valores de las opciones. En caso de que no
	 * estén establecidas, se devuelve el valor por defecto.
	 */
	static function parametro( $nombre, $valor = null )
	{
		//
		$vdef =
			array
			(
				'layout' 				=>  'ventana',
				'posicion' 				=>  'inferior',
				'comportamiento' 			=>  'aceptar',
				'alineacion' 				=>  'cen',
				'color_fondo'				=>  '#ffffff',
				'color_fuente'				=>  '#000000',
				'color_fondo_bot'			=> '#000000',
				'color_fuente_bot'			=> '#ffffff',
				'color_link' => '#000000',
				'enlace_politica' 			=>  '#',
				'enlace_mas_informacion'                =>  '#',
				'enlace_aviso_legal'                    =>  '#',
				'enlace_politica_privacidad'            =>  '#',
				'desarrollado' => '3COM Marketing',
				'desarrollador' => 'http://3commarketing.com',
				'enlace_mapa_web'			=>  '#',
                                'pagina_web'                    =>  '#',
                                'correo_electronico'            =>  '#',
                                'razon_social'                  =>  '#',
                                'direccion'                     =>  '#',
                                'cif'                           =>  '#',
				                'nombre_comercial'              =>  '#',
				                'persona_autorizada'            =>  '#',
				                'telefono'                      =>  '#',
				'texto_aviso' 				=>  htmlspecialchars( '<h4 {estilo_titulo}>Uso de cookies</h4><p {estilo_texto}>Este sitio web utiliza cookies para que usted tenga la mejor experiencia de usuario. <a href="{enlace_politica}" {estilo_enlace}>Más info</a></p>' ),
				'tam_fuente' 				=>  '13px'
			);
		if( !key_exists( $nombre, $vdef ) )
			throw new cdp_cookies_error( sprintf( "Parámetro desconocido: %s", $nombre ) );
	
		// Obtiene el valor del campo si $valor es null, en caso
                // contrario actualiza el valor del campo.
		if( $valor === null )
		{
			// Hago una excepción si estoy mostrando el aviso en vista previa
			if( cdp_cookies_input::get( 'cdp_cookies_vista_previa' ) )
				if( ( $v = cdp_cookies_input::get( $nombre ) ) )
				{
					// Antes de devolver el valor me aseguro que soy el usuario administrador
					try
					{
						self::comprobar_usuario_admin();
						if( $nombre == 'texto_aviso' || $nombre=='comportamiento' )
							return rawurldecode( $v );
						return $v;
					}
					catch( cdp_cookies_error $e )
					{
					}
				}
			if( $nombre == 'texto_aviso' || $nombre == 'comportamiento')
                            return stripslashes( get_option( 'cdp_cookies_' . $nombre, $vdef[$nombre] ) );                        
			return get_option( 'cdp_cookies_' . $nombre, $vdef[$nombre] );
		}
	
		// Lo almaceno
		update_option( 'cdp_cookies_' . $nombre, $valor );
	}
	
	/**
	 * Añade los estilos y js necesarios para el panel de admin
	 */
	static function cargar_archivos_admin()
	{
		wp_enqueue_style( 'admin-estilos', CDP_COOKIES_URL_HTML . 'admin/estilos.css', false );
		wp_enqueue_style( 'wp-color-picker' );
		wp_register_script( 'admin-principal', CDP_COOKIES_URL_HTML . 'admin/principal.js', array( 'jquery', 'wp-color-picker' ) );
		wp_enqueue_script( 'admin-principal' );
		wp_localize_script(
			'admin-principal',
			'cdp_cookies_info',
			array
			(
				'nonce_guardar' => wp_create_nonce( 'guardar' ),
				'nonce_crear_paginas' => wp_create_nonce( 'crear_paginas' ),
				'siteurl' => site_url(),
				'comportamiento' => self::parametro( 'comportamiento' )
			) 
		);
	}
	
	/**
	 *
	 */
	static function comprobar_usuario_admin()
	{
		if( function_exists( 'current_user_can' ) )
			if( function_exists( 'wp_get_current_user' ) )
				if( current_user_can( 'manage_options' ) )
					return;
		throw new cdp_cookies_error( 'No tiene privilegios para acceder a esta página' );
	}
	
	/**
	 * Añade el plugin al panel de Herramientas del administrador
	 */
	static function crear_menu_admin()
	{
		//
		// Página configuración que cuelgue de Herramientas
		//
		add_submenu_page
		(
			'tools.php',
			'Asesor de cookies',
			'Asesor de cookies',
			'manage_options',
			'cdp_cookies',
			array( __CLASS__, 'pag_configuracion' )
		);
	}	

	/**
	 *
	 */
	static function pag_configuracion()
	{
		require_once CDP_COOKIES_DIR_HTML . 'admin/principal.html';
	}
}

/**
 * Clase encargada de crear las páginas
 */
class cdp_cookies_pagina
{
	/**
	 * entrada
	 */
	public $titulo	=	null, $html	=	null;

	/**
	 * salida
	 */
	public $ya_existia, $url, $ok, $mensaje;
	
	/**
	 * 
	 */
	function crear()
	{
		// Validación del título
		if( !$this->titulo )
		{
			$this->ok = false;
			$this->mensaje = 'Falta el título de la página';
			return false;
		}
		
		// Compruebo si ya existe
		if( $pag = get_page_by_title( $this->titulo ) )
		{
			/* Si está en la papelera...
			if( $pag->post_status == 'trash' )
			{
				$this->ok = false;
				$this->mensaje = 'Alguna de las páginas está en la papelera, debe eliminarla primero';
				return false;
			}*/
			// Todo bien...
			$this->ya_existia = true;
			$this->url = get_permalink( $pag );
            wp_delete_post( $pag->ID, true );
		}else{
            $this->ya_existia = false;
        }
		
		// Me dispongo a crear la página insertando el post en BD
		$p 				=	array();
		$p['post_title'] 		=	$this->titulo;
                if (isset($this->html)){ 
                    $p['post_content']          =	$this->html;
                }
		$p['post_status'] 		=	'publish';
		$p['post_type'] 		=	'page';
		$p['comment_status']            =	'closed';
		$p['ping_status'] 		=	'closed';
		$p['post_category']		=	array( 1 );
		if( !( $id = wp_insert_post( $p ) ) )
		{
			$this->ok = false;
			$this->mensaje = "No es posible crear la página";
			return false;
		}
		
		// Se ha creado la página correctamente
		$this->ok = true;
		$this->url = get_permalink( get_post( $id ) );
		return true;
	}	
}

?>