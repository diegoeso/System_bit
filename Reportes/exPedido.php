<?php

require('Pedido.php');

require_once "../model/Pedido.php";
require_once "../ajax/Letras.php";
require_once "../model/Configuracion.php";

$objPedido = new Pedido();

$query_cli = $objPedido->GetClienteSucursalPedido($_GET["id"]);
$reg_cli = $query_cli->fetch_object();

$archivo = $reg_cli->logo;
$trozos = explode(".", $archivo);
$extension = end($trozos);

$pdf = new PDF_Invoice( 'P', 'mm', 'letter' );
$pdf->AddPage();
$pdf->addSociete( utf8_decode($reg_cli->razon_social),
                  "NIT: $reg_cli->num_sucursal\n" .
                  utf8_decode("Dirección: ").($reg_cli->direccion)."\n".
                  utf8_decode("Teléfono: ").($reg_cli->telefono_suc)."\n" .
                  "E-mail : $reg_cli->email_suc ","../$reg_cli->logo","$extension");
$pdf->fact_dev( "$reg_cli->tipo_pedido ", "Nro. $reg_cli->numero" );
$pdf->temporaire( "" );
$pdf->addDate( $reg_cli->fecha);

$pdf->addClientAdresse($reg_cli->nombre,"Domicilio: ".$reg_cli->direccion_calle." - ".$reg_cli->direccion_departamento,$reg_cli->doc.": ".$reg_cli->num_documento,"E-mail: ".$reg_cli->email,"Telefono: ".$reg_cli->telefono);

$cols=array( "Codigo"    => 20, //18
            "Descripcion"  => 70,
            "Marca"=> 15,
            "Cantidad"     => 17, //21
            "Precio Unitario"   => 20, //20
            "Descuento (%)" => 19,//20
            "Precio C/desto" => 17,//20
            "TOTAL Bs." => 18 ); //21
$pdf->addCols( $cols);
$cols=array(
    "Codigo" => "C",
    "Descripcion"  => "L",
    "Marca" => "R",
    "Cantidad"     => "C",
    "Precio Unitario"  => "R",
    "Descuento (%)" => "R",
    "Precio C/desto" => "R",
    "TOTAL Bs." => "C" );
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols);
$y    = 70; // para mover los articulos 
$query_ped = $objPedido->ImprimirDetallePedido($_GET["id"]);
while ($reg = $query_ped->fetch_object()) {

    $line = array(
        "Codigo"    => "'$reg->codigo'",
        "Descripcion"  => utf8_decode("$reg->articulo Serie: $reg->serie"),
        "Marca" => "$reg->marca",
        "Cantidad"     => "$reg->cantidad",
        "Precio Unitario"   => "$reg->precio_venta",
        "Descuento (%)" => "$reg->descuento",
        "Precio C/desto" => ($reg->descuento+ $reg->precio_venta),
        "TOTAL Bs."  => "$reg->sub_total");
    $size = $pdf->addLine( $y, $line );
    $y   += $size + 2;
}
$query_total = $objPedido->TotalPedido($_GET["id"]);
$reg_total = $query_total->fetch_object();
$V=new EnLetras();
$con_letra=strtoupper($V->ValorEnLetras($reg_total->Total,"BOLIVIANOS")); //cmabiar bolivianos
//$pdf->addCadreTVAs("---TRES MILLONES CUATROCIENTOS CINCUENTA Y UN MIL DOSCIENTOS CUARENTA PESOS 00/100 M.N.");
$pdf->addCadreTVAs ("---".utf8_decode($con_letra));
require_once "../model/Configuracion.php";
$objConfiguracion = new Configuracion();
$query_global = $objConfiguracion->Listar();
$reg_igv = $query_global->fetch_object();
$pdf->addTVAs( $reg_igv->porcentaje_impuesto, $reg_total->Total,"$reg_igv->simbolo_moneda ");
//$pdf->addCadreEurosFrancs("$reg_igv->nombre_impuesto"." $reg_igv->porcentaje_impuesto%");
$pdf->Output('Reporte de Pedido','I');

?>
