<?php

$server_ssh_ip = '192.168.100.222';
$server_ssh_folder = '/var/www/contenido_especial/';
$server_ssh_user = 'root';
$server_ssh_pass = '123qwe';

$folder_local = "files/";
$config_file_name = 'config.xml';

echo "<html>";
echo '<body style="background:#000000">';
echo '<font color="green">';
/* 
   Mostramos la configuracion recibida en
   el formulario.
*/
/*
echo "<h1>Config:</h1>";
echo "pr: ".$_POST[ 'etiqueta' ]."<br>";
echo "di: ".$_POST[ 'bday'     ]."<br>";
echo "hi: ".$_POST[ 'bhour'    ]."<br>";
echo "de: ".$_POST[ 'eday'     ]."<br>";
echo "he: ".$_POST[ 'ehour'    ]."<br>";
*/
/*
  Creamos un archivo xml 
  con la configuracion recibida.
*/
$xml_doc = new DomDocument('1.0', 'UTF-8');
$item    = $xml_doc->createElement( 'item' );
$item    = $xml_doc->appendChild( $item );

$pr = $xml_doc->createAttribute( 'pr' );
$item->appendChild( $pr );
$pr_val = $xml_doc->createTextNode( $_POST[ 'etiqueta' ] );
$pr->appendChild( $pr_val );

$di = $xml_doc->createAttribute('di');
$item->appendChild( $di );
$di_val = $xml_doc->createTextNode( $_POST[ 'bday' ] );
$di->appendChild( $di_val );

$de = $xml_doc->createAttribute('de');
$item->appendChild( $de );
$de_val = $xml_doc->createTextNode( $_POST[ 'eday' ] );
$de->appendChild( $de_val );

$hi = $xml_doc->createAttribute('hi');
$item->appendChild( $hi );
$hi_val = $xml_doc->createTextNode( $_POST[ 'bhour' ] );
$hi->appendChild( $hi_val );

$he = $xml_doc->createAttribute('he');
$item->appendChild( $he );
$he_val = $xml_doc->createTextNode( $_POST[ 'ehour' ] );
$he->appendChild( $he_val );

$dlog = $xml_doc->createAttribute('dlog');
$item->appendChild( $dlog );
date_default_timezone_set('CST');
$dlog_str = date('l jS \of F Y h:i:s A');
echo $dlog_str."</br>";
$dlog_val = $xml_doc->createTextNode( $dlog_str );
$dlog->appendChild( $dlog_val );


/*
  Mostramos las ips incluidas en el XML
  y las escribimos a la vez.
*/
//echo "<h1>Players:</h1>";
foreach( $_POST[ 'ips' ] as $ip_e ) {
  //  echo $ip_e."</br>";  
  $ip = $xml_doc->createElement( 'ip' );
  $ip = $item->appendChild( $ip );
  $ip_v = $xml_doc->createAttribute( 'value' );
  $ip->appendChild( $ip_v );
  $ip_v_v = $xml_doc->createTextNode( $ip_e );
  $ip_v->appendChild( $ip_v_v );
}
//echo "</br></br>";

/*
  Establecemos la conexion con el server
  al que enviaremos el XML y los
  archivos recibidos.
*/
if (!function_exists("ssh2_connect")) die("function ssh2_connect doesn't exist");
//$connection = ssh2_connect( $server_ssh_ip, 22);//////////////////////////////
if( !$connection = ssh2_connect( '192.168.100.222', 22) )//////////////////////////////
  echo 'No se pudo establecer la conexion';
else {
if( ssh2_auth_password( $connection, $server_ssh_user, $server_ssh_pass ) ) {
  echo "Se ha establecido la conexion SSH con ".$server_ssh_ip."</br>";
}
else {
  echo "ERROR NO se pudo establecer la conexion SSH con ".$server_ssh_ip."</br>";
}

/*
  Creamos el directorio de la IP
  en caso de que no exista.
*/
//echo "<h1>Players:</h1>";
foreach( $_POST[ 'ips' ] as $ip_e ) {
  ssh2_exec( $connection, 'mkdir /var/www/contenido_especial/'.$ip_e );
}

/*
  Mostramos los archivos recibidos
  y a la vez los enviamos al server SSH.
*/
//echo "<h1>Archivos:</h1>";
foreach( $_FILES[ "archivo" ][ "error" ] as $key => $error ) {
  if( $error == UPLOAD_ERR_OK ) {
    $status  = "";
    $sum     = "";
    $nombre  = $_FILES[ "archivo" ][ "name" ][ $key ];
    //      $prefijo = substr( md5( uniqid( rand() ) ), 0, 6 );
      if( $nombre != "" ) {       
	//	$destino =  "files/".$prefijo."_".$nombre;
	//	$destino =  "files/".$nombre;
	$destino = $folder_local."".$nombre;
	if( copy( $_FILES["archivo"]["tmp_name"][ $key ], $destino ) ) {
	  ssh2_scp_send( $connection, $destino, $server_ssh_folder."".$nombre, 0644 ); /////////////////////////////
	  $status = "<b>".$nombre."</b></br>";
	  $sum    = md5_file( $destino );
	}
	else {
	  $status = "Error al subir el archivo ".$nombre."</br>";
	}
      }
      //      echo $status;
      //      echo $sum."</br></br>";
  }
  $file = $xml_doc->createElement( 'file' );
  $file = $item->appendChild( $file );
  $mds = $xml_doc->createAttribute('mds');
  $file->appendChild( $mds );
  $mds_val = $xml_doc->createTextNode( $sum);
  $mds->appendChild( $mds_val );
  $name_f  = $xml_doc->createAttribute('name');
  $file->appendChild( $name_f );
  $name_f_val = $xml_doc->createTextNode( $nombre );
  $name_f->appendChild( $name_f_val );
}

$xml_doc->formatOutput = true;
$el_xml_doc = $xml_doc->save();
$xml_doc->save( $folder_local.'config.xml' );

//leer();

/*$xml_file_in=simplexml_load_file( $folder_local.$config_file_name );
//print_r( $xml_file_in );

echo "<h2>Config: </h2>";
echo "pr: ".$xml_file_in[0][ 'pr' ];
echo "</br>";
echo "di: ".$xml_file_in[0][ 'di' ];
echo "</br>";
echo "de: ".$xml_file_in[0][ 'de' ];
echo "</br>";
echo "hi: ".$xml_file_in[0][ 'hi' ];
echo "</br>";
echo "he: ".$xml_file_in[0][ 'he' ];
echo "</br>";

echo "<h2>IPs:</h2>";
for( $i = 0; $i < count( $xml_file_in->ip ) ; $i++ ) {
  echo $xml_file_in->ip[$i][ 'value' ];
  echo "</br>";
}


//for( $i = 0; $i < count( $xml_file_in->file ) ; $i++ );
//echo "<h2>Archivos (".$i.")</h2>";
echo "<h2>Archivos:</h2>";
for( $i = 0; $i < count( $xml_file_in->file ) ; $i++ ) {
  echo "<b>".$xml_file_in->file[$i][ 'name' ]."</b>";
  echo "</br>";
  echo $xml_file_in->file[$i][ 'mds' ];
  echo "</br>";
  echo "</br>";
}

*/


$xml_file_in=simplexml_load_file( $folder_local.$config_file_name );
//print_r( $xml_file_in );

echo "<h1>Config: </h1>";
echo "Prioridad: <b>".$xml_file_in[0][ 'pr' ]."</b>";
echo "</br>";
echo "Inicio: <b>".$xml_file_in[0][ 'di' ]." ... ".$xml_file_in[0][ 'hi' ]."</b>";
echo "</br>";
echo "Término: <b>".$xml_file_in[0][ 'de' ]." ... ".$xml_file_in[0][ 'he' ]."</b>";
echo "</br>";


echo "<h1>Players:</h1>";
for( $i = 0; $i < count( $xml_file_in->ip ) ; $i++ ) {
  echo $xml_file_in->ip[$i][ 'value' ];
  echo "</br>";
}

echo "</br>";
echo "<h1>Archivos:</h1>";
for( $i = 0; $i < count( $xml_file_in->file ) ; $i++ ) {
  echo "<b>".$xml_file_in->file[$i][ 'name' ]."</b>";
  echo "</br>";
  echo $xml_file_in->file[$i][ 'mds' ];
  echo "</br>";
  echo "</br>";
}





//ssh2_scp_send( $connection, 'files/config.xml', '/var/www/contenido_especial/config.xml', 0644 );
ssh2_scp_send( $connection, $folder_local.$config_file_name, $server_ssh_folder.$config_file_name, 0644 );/////////////////
foreach( $_POST[ 'ips' ] as $ip_e ) {
  ssh2_scp_send( $connection, $folder_local.$config_file_name, $server_ssh_folder.$ip_e.'/'.$config_file_name, 0644 );
}

echo "</br>------------------------------------------</br>Ready";
echo '</font>';
echo '</body>';
echo '</html>';
ssh2_exec( $connection, 'exit' );
}
function leer(){
}

/*
  isset( $_POST[ 'name' ] ) 
  isset( $_REQUEST[ 'name' ] ) 
  ( $_POST[ "action" ] == "upload" ) 
  
  for($i = 0, $size = count($_REQUEST['archivos']); $i < $size; ++$i) {
  echo $_REQUEST[ 'archivos' ][$i]."</br>";
  }

  aptitude install libssh2-1-dev libssh2-php
*/


?>