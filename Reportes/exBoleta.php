<?php
// (c) Julio Belido
// Exemple de g�n�ration de devis/facture PDF

require('Boleta.php');

session_start();

$lo = $_SESSION["logo"];

require_once "../model/Configuracion.php";

      $objConf = new Configuracion();

      $query_conf = $objConf->Listar();

      $regConf = $query_conf->fetch_object();

require_once "../model/Pedido.php";

$objPedido = new Pedido();


$query_cli = $objPedido->GetVenta($_GET["id"]);

        $reg_cli = $query_cli->fetch_object();

$f = "";

      if ($_SESSION["superadmin"] == "S") {
        $f = $regConf->logo;
      } else {
        $f = $reg_cli->logo;
      }

      $archivo = $f;
      $trozos = explode(".", $archivo);
      $extension = end($trozos);


$pdf = new PDF_Invoice( 'P', 'mm', 'Letter' );
$pdf->AddPage();
$pdf->addSociete( utf8_decode( $reg_cli->razon_social),
                  "NIT:$reg_cli->num_sucursal\n" .
                  "Direccion:" .utf8_decode(" $reg_cli->direccion")."\n" .
                  "Telefono: ".utf8_decode(" $reg_cli->telefono_suc")."\n" .
                  "E-mail : $reg_cli->email_suc ","../$f","$extension");
$pdf->fact_dev ("RECIBO Nro. "," $reg_cli->serie_comprobante- $reg_cli->num_comprobante" );
$pdf->temporaire( "" );
$pdf->addDate($reg_cli->fecha );// cambiar el tiemppo dia fecha año dia mes año


//$pdf->addClient("NIT_123");
//$pdf->addPageNumber("1");

$pdf->addClientAdresse(utf8_decode($reg_cli->nombre),"Domicilio: ".utf8_decode($reg_cli->direccion_calle)." - ".utf8_decode($reg_cli->direccion_departamento),utf8_decode($reg_cli->doc).": ".$reg_cli->num_documento,"E-mail: ".$reg_cli->email,"Telefono: ".$reg_cli->telefono);

//$pdf->addReglement("Soluciones Innovadoras ");
//$pdf->addEcheance("NIT","2147715777");
//$pdf->addNumTVA("Chongoyape, Jos� G�lvez 1368");
//$pdf->addReference("Devis ... du ....");
$cols=array( "Codigo"    => 23,//23
             "Descripcion"  => 78,
             "Cantidad"     => 22,
             "Precio Unitario"      => 25,
             "Descuento (%)" => 26, //20
             "TOTAL Bs."          => 22 );//22
$pdf->addCols( $cols);
$cols=array( "Codigo"    => "L",
             "Descripcion"  => "L",
             "Cantidad"     => "C",
             "Precio Unitario"      => "R",
             "Descuento (%)" => "R",
             "TOTAL Bs."          => "C" );
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols);

$y    = 68; //PARA MOVER EL TEXTO DEL CODIGO DESCRIPCION CANTIDAD
//89

$query_ped = $objPedido->ImprimirDetallePedido($_GET["id"]);

        while ($reg = $query_ped->fetch_object()) {

            $line = array( "Codigo"    => "'$reg->codigo'",
                           "Descripcion"  => utf8_decode("$reg->articulo Serie:$reg->serie"),
                           "Cantidad"     => utf8_decode("$reg->cantidad"),
                           "Precio Unitario"      => "$reg->precio_venta",
                           "Descuento (%)" => "$reg->descuento",
                           "TOTAL Bs."          => "$reg->sub_total");
            $size = $pdf->addLine( $y, $line );
            $y   += $size + 2; //para el espacio de los articulos del llenado de sus descripciones
        }

$query_total = $objPedido->TotalPedido($_GET["id"]);

$reg_total = $query_total->fetch_object();

require_once "../ajax/Letras.php";

$V=new EnLetras();
 $con_letra=strtoupper($V->ValorEnLetras($reg_total->Total,"BOLIVIANOS"));

//$pdf->addCadreTVAs("---TRES MILLONES CUATROCIENTOS CINCUENTA Y UN MIL DOSCIENTOS CUARENTA PESOS 00/100 M.N.");
$pdf->addCadreTVAs ("".utf8_decode($con_letra));//solucion del problema de letras con acentos


require_once "../model/Configuracion.php";

$objConfiguracion = new Configuracion();


$query_global = $objConfiguracion->Listar();

$reg_igv = $query_global->fetch_object();

$pdf->addTVAs( $reg_cli->impuesto, $reg_total->Total,"$reg_igv->simbolo_moneda ");
$pdf->addCadreEurosFrancs("$reg_igv->nombre_impuesto"." $reg_cli->impuesto%");

$pdf->Output('Reporte de Venta','I');

?>
